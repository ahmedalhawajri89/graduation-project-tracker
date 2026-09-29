<?php

namespace App\Support;

use App\Models\Defense;
use App\Models\DefenseMember;
use App\Models\DefenseRoom;
use App\Models\Project;
use App\Models\Supervisor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * قواعد جدولة المناقشات — مكانها هنا وحده.
 *
 * نافذة الجدولة، وإعادة الجدولة، والاختبارات تقرأ الشرط نفسه: لا قاعة
 * لمناقشتين متداخلتين، ولا عضو في لجنتين في الوقت نفسه، ولا ممتحن هو
 * مشرف المشروع نفسه.
 */
class DefenseScheduler
{
    private static ?bool $enabled = null;

    /**
     * الميزة تعمل حين تُنشأ جداولها. قبل تشغيل الترحيل لا تُكسر اللوحات:
     * الشريط الجانبي ولوحتا المشرف والطالب تتخطّى المناقشات.
     */
    public static function enabled(): bool
    {
        return self::$enabled ??= \Illuminate\Support\Facades\Schema::hasTable('defenses')
            && \Illuminate\Support\Facades\Schema::hasTable('defense_members');
    }

    /**
     * المشاريع التي تنتظر لجنةً وموعداً — تعريف واحد لصفحة المناقشات وعدّاد
     * الشريط الجانبي: مكتملة في الفصل الحالي، بلا درجة بعد (المقيَّمة قبل
     * وجود المناقشات لا تنتظر شيئاً)، ولا مناقشة قائمة لها (لا شيء، أو ملغاة).
     */
    public static function awaiting(): \Illuminate\Database\Eloquent\Builder
    {
        $semester = \App\Models\Semester::current();

        return Project::query()
            ->where('status', 'complete')
            ->whereNull('grade')
            ->whereNull('grade_locked_at')
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id))
            ->whereDoesntHave('defense', fn ($q) => $q->whereIn('status', [Defense::SCHEDULED, Defense::DONE]));
    }

    /** المشروع جاهز للمناقشة: مكتمل، ولم يُقيَّم بعد */
    public static function isReady(Project $project): bool
    {
        return $project->status === 'complete' && is_null($project->grade) && ! $project->isGradeLocked();
    }

    /**
     * أخطاء التعارض بمفتاح الحقل — فارغة إن صحّت الجدولة.
     *
     * @param  array{starts_at: Carbon, duration_minutes: int, mode: string, room_id: ?int, examiner_id: int}  $d
     * @return array<string, string>
     */
    public static function conflicts(Project $project, array $d, ?Defense $ignore = null): array
    {
        $errors = [];
        $from = $d['starts_at'];
        $to = $from->copy()->addMinutes($d['duration_minutes']);
        $others = fn () => Defense::overlapping($from, $to)->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id));

        if (! self::isReady($project)) {
            $errors['project'] = 'المشروع ليس جاهزاً للمناقشة — يجب أن يكون مكتملاً ولم يُقيَّم بعد.';
        }

        if ($from->isPast()) {
            $errors['time'] = 'موعد المناقشة يجب أن يكون في المستقبل.';
        }

        if ((int) $d['examiner_id'] === (int) $project->supervisor_id) {
            $errors['examiner_id'] = 'مشرف المشروع عضو في اللجنة أصلاً — اختر ممتحناً غيره.';
        }

        if (in_array($d['mode'], ['in_person', 'hybrid'], true)) {
            $room = $d['room_id'] ? DefenseRoom::find($d['room_id']) : null;
            if (! $room || ! $room->is_active) {
                $errors['room_id'] = 'اختر قاعة متاحة للمناقشة الحضورية.';
            } elseif ($clash = $others()->where('room_id', $room->id)->with('project')->first()) {
                $errors['room_id'] = 'القاعة محجوزة في هذا الوقت لمناقشة «' . $clash->project?->title . '» ('
                    . $clash->starts_at->format('H:i') . '–' . $clash->endsAt()->format('H:i') . ').';
            }
        }

        // كل عضو (المشرف والممتحن) لا يكون في لجنة أخرى في الوقت نفسه
        foreach (['supervisor' => $project->supervisor_id, 'examiner_id' => $d['examiner_id']] as $field => $supervisorId) {
            if (! $supervisorId || isset($errors[$field])) {
                continue;
            }
            $clash = $others()->whereHas('members', fn ($q) => $q->where('supervisor_id', $supervisorId))->with('project')->first();
            if ($clash) {
                $who = $field === 'supervisor' ? 'مشرف المشروع' : 'الممتحن';
                $errors[$field === 'supervisor' ? 'time' : $field] = $who . ' في لجنة مناقشة «' . $clash->project?->title . '» في هذا الوقت ('
                    . $clash->starts_at->format('H:i') . '–' . $clash->endsAt()->format('H:i') . ').';
            }
        }

        return $errors;
    }

    /**
     * الممتحنون المقترحون: مشرفو تخصص المشروع أولاً، ثم غيرهم — بلا مشرف
     * المشروع — مرتّبين بعدد مناقشاتهم القادمة (الأقلّ حملاً أولاً).
     *
     * @return Collection<int, Supervisor> مع upcoming_defenses و same_specialize
     */
    public static function suggestExaminers(Project $project, int $take = 6): Collection
    {
        $specializeId = $project->project_type?->specialize_id;

        return Supervisor::query()
            ->whereKeyNot($project->supervisor_id)
            ->with('specialize')
            ->withCount(['defenseMemberships as upcoming_defenses' => fn ($q) => $q
                ->whereHas('defense', fn ($d) => $d->active()->where('starts_at', '>=', now()))])
            ->get()
            ->each(fn ($s) => $s->setAttribute('same_specialize', $specializeId && (int) $s->specialize_id === (int) $specializeId))
            ->sortBy(fn ($s) => [$s->same_specialize ? 0 : 1, $s->upcoming_defenses, $s->name])
            ->take($take)
            ->values();
    }

    /** اللجنة: المشرف ثم الممتحن */
    public static function syncMembers(Defense $defense, Project $project, int $examinerId): void
    {
        $defense->members()->whereNotIn('supervisor_id', [$project->supervisor_id, $examinerId])->delete();

        DefenseMember::updateOrCreate(
            ['defense_id' => $defense->id, 'supervisor_id' => $project->supervisor_id],
            ['role' => 'supervisor']
        );
        DefenseMember::updateOrCreate(
            ['defense_id' => $defense->id, 'supervisor_id' => $examinerId],
            ['role' => 'examiner']
        );
    }
}
