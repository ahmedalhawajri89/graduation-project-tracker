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
     * @param  array{starts_at: Carbon, duration_minutes: int, mode: string, room_id: ?int, examiner_ids: int[]}  $d
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

        // اقتراح planAll يحمل ممتحناً واحداً (examiner_id)، والنافذة قائمة
        $examinerIds = array_map('intval', $d['examiner_ids'] ?? [$d['examiner_id']]);
        if (in_array((int) $project->supervisor_id, $examinerIds, true)) {
            $errors['examiner_id'] = 'مشرف المشروع عضو في اللجنة أصلاً — اختر ممتحناً غيره.';
        } elseif (count($examinerIds) !== count(array_unique($examinerIds))) {
            $errors['examiner_id'] = 'الممتحن نفسه مكرّر في اللجنة.';
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

        // كل عضو (المشرف والممتحنون) لا يكون في لجنة أخرى في الوقت نفسه
        $busy = fn ($supervisorId) => $others()->whereHas('members', fn ($q) => $q->where('supervisor_id', $supervisorId))->with('project')->first();
        $span = fn ($clash) => ' في لجنة مناقشة «' . $clash->project?->title . '» في هذا الوقت ('
            . $clash->starts_at->format('H:i') . '–' . $clash->endsAt()->format('H:i') . ').';

        if ($project->supervisor_id && ($clash = $busy($project->supervisor_id))) {
            $errors['time'] = 'مشرف المشروع' . $span($clash);
        }

        if (! isset($errors['examiner_id'])) {
            foreach ($examinerIds as $examinerId) {
                if ($clash = $busy($examinerId)) {
                    // مع أكثر من ممتحن يُسمّى المشغول منهم
                    $who = count($examinerIds) > 1 ? 'الممتحن ' . Supervisor::find($examinerId)?->name : 'الممتحن';
                    $errors['examiner_id'] = $who . $span($clash);
                    break;
                }
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

    /**
     * خانات الدوام القادمة بالترتيب: من بعد مهلة الإشعار حتى أفق البحث،
     * في أيام العمل، من أول الدوام حتى آخر خانة تنتهي قبل نهايته.
     *
     * @return \Generator<int, Carbon>
     */
    public static function slots(?Carbon $from = null): \Generator
    {
        $cfg = config('defenses');
        $day = ($from ?? today()->addDays($cfg['lead_days']))->copy()->startOfDay();
        $until = today()->addDays($cfg['horizon_days']);

        for (; $day->lte($until); $day->addDay()) {
            if (! in_array($day->dayOfWeek, $cfg['days'], true)) {
                continue;
            }
            $slot = $day->copy()->setTimeFromTimeString($cfg['start']);
            $end = $day->copy()->setTimeFromTimeString($cfg['end']);
            for (; $slot->copy()->addMinutes($cfg['slot'])->lte($end); $slot->addMinutes($cfg['slot'])) {
                if ($slot->isFuture()) {
                    yield $slot->copy();
                }
            }
        }
    }

    /**
     * جدول مقترح لمجموعة مشاريع — بلا حفظ.
     *
     * لكل مشروع أول خانة يكون فيها مشرفه حرّاً، وقاعة متاحة، وممتحن حرّ (من
     * تخصص المشروع أولاً ثم الأقلّ مناقشاتٍ، محسوباً معها ما اقتُرح للتوّ
     * فيتوزّع الحمل). ما يُقترح لمشروع يُحجز في الذاكرة فلا يتعارض اقتراحان.
     * الاقتراح حضوري: رابط الاجتماع لا يُخترَع. والحفظ يمرّ بـ conflicts() نفسها.
     *
     * @param  Collection<int, Project>  $projects
     * @return array<int, ?array{starts_at: Carbon, duration_minutes: int, room_id: int, room: string, examiner_id: int, examiner: string, same_specialize: bool}>
     */
    public static function planAll(Collection $projects): array
    {
        $plan = $projects->mapWithKeys(fn ($p) => [$p->id => null])->all();
        $rooms = DefenseRoom::where('is_active', true)->orderBy('name')->get();
        if ($projects->isEmpty() || $rooms->isEmpty()) {
            return $plan;
        }

        $duration = (int) config('defenses.slot');

        // المشغول الآن: لكل قاعة ولكل مشرف فتراته القادمة
        $busyRoom = [];
        $busyPerson = [];
        $load = [];
        foreach (Defense::active()->where('starts_at', '>=', now()->startOfDay())->with('members')->get() as $d) {
            $span = [$d->starts_at, $d->endsAt()];
            if ($d->room_id) {
                $busyRoom[$d->room_id][] = $span;
            }
            foreach ($d->members as $m) {
                $busyPerson[$m->supervisor_id][] = $span;
                $load[$m->supervisor_id] = ($load[$m->supervisor_id] ?? 0) + 1;
            }
        }

        $free = function (array $spans, Carbon $from, Carbon $to): bool {
            foreach ($spans as [$a, $b]) {
                if ($a->lt($to) && $b->gt($from)) {
                    return false;
                }
            }

            return true;
        };

        $supervisors = Supervisor::orderBy('name')->get(['id', 'name', 'specialize_id']);

        foreach ($projects as $project) {
            $specializeId = $project->project_type?->specialize_id;
            // المرشّحون: التخصص أولاً، ثم الأقلّ حملاً (مع ما اقتُرح في هذا الجدول)
            $candidates = $supervisors->where('id', '!=', $project->supervisor_id)
                ->sortBy(fn ($s) => [$specializeId && (int) $s->specialize_id === (int) $specializeId ? 0 : 1, $load[$s->id] ?? 0, $s->name])
                ->values();

            foreach (self::slots() as $from) {
                $to = $from->copy()->addMinutes($duration);

                if (! $free($busyPerson[$project->supervisor_id] ?? [], $from, $to)) {
                    continue;
                }
                $room = $rooms->first(fn ($r) => $free($busyRoom[$r->id] ?? [], $from, $to));
                $examiner = $room ? $candidates->first(fn ($s) => $free($busyPerson[$s->id] ?? [], $from, $to)) : null;
                if (! $room || ! $examiner) {
                    continue;
                }

                $plan[$project->id] = [
                    'starts_at' => $from,
                    'duration_minutes' => $duration,
                    'room_id' => $room->id,
                    'room' => $room->name,
                    'examiner_id' => $examiner->id,
                    'examiner' => $examiner->name,
                    'same_specialize' => $specializeId && (int) $examiner->specialize_id === (int) $specializeId,
                ];
                $busyRoom[$room->id][] = [$from, $to];
                $busyPerson[$project->supervisor_id][] = [$from, $to];
                $busyPerson[$examiner->id][] = [$from, $to];
                $load[$examiner->id] = ($load[$examiner->id] ?? 0) + 1;
                break;
            }
        }

        return $plan;
    }

    /** اقتراح لمشروع واحد */
    public static function suggestSlot(Project $project): ?array
    {
        return self::planAll(collect([$project]))[$project->id] ?? null;
    }

    /**
     * اللجنة: المشرف ثم الممتحنون. من خرج منها يُحذف، ومن بقي يبقى صفّه.
     *
     * @param  int[]  $examinerIds
     */
    public static function syncMembers(Defense $defense, Project $project, array $examinerIds): void
    {
        $defense->members()->whereNotIn('supervisor_id', [$project->supervisor_id, ...$examinerIds])->delete();

        DefenseMember::updateOrCreate(
            ['defense_id' => $defense->id, 'supervisor_id' => $project->supervisor_id],
            ['role' => 'supervisor']
        );
        foreach ($examinerIds as $examinerId) {
            DefenseMember::updateOrCreate(
                ['defense_id' => $defense->id, 'supervisor_id' => $examinerId],
                ['role' => 'examiner']
            );
        }
    }
}
