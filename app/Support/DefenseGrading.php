<?php

namespace App\Support;

use App\Models\Defense;
use App\Models\DefenseMember;
use App\Models\Project;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * درجة لجنة المناقشة.
 *
 * كل عضو (المشرف والممتحن) يرصد درجته وملاحظاته بعد بدء المناقشة، دون أن
 * يرى درجة زميله حتى يرصد درجته (تقييم مستقلّ لا يتأثّر بالأول). حين
 * تكتمل الدرجات تصير درجة المشروع متوسطها، وتنتهي المناقشة، ويُشعر الفريق.
 * التعديل ممكن حتى يعتمد المشرف الدرجة — بالقفل الموجود نفسه.
 */
class DefenseGrading
{
    /** المناقشة التي تُرصد منها درجة المشروع: مجدولة أو منتهية (لا ملغاة) */
    public static function defenseFor(Project $project): ?Defense
    {
        if (! DefenseScheduler::enabled()) {
            return null;
        }

        return $project->defense()->whereIn('status', [Defense::SCHEDULED, Defense::DONE])->first();
    }

    /** سبب منع العضو من الرصد الآن — null إن كان مسموحاً */
    public static function blocker(Defense $defense): ?string
    {
        return match (true) {
            $defense->status === Defense::CANCELLED => 'المناقشة ملغاة.',
            $defense->project?->isGradeLocked() => 'اعتُمدت الدرجة النهائية — لا تعديل بعد الاعتماد.',
            $defense->starts_at->isFuture() => 'يُفتح رصد الدرجة عند بدء المناقشة (' . $defense->starts_at->format('Y-m-d H:i') . ').',
            default => null,
        };
    }

    /**
     * يرصد درجة عضو، وإن اكتملت درجات اللجنة يحسب النهائية.
     *
     * @return array{final: bool, grade: ?float}
     */
    public static function record(DefenseMember $member, float $grade, ?string $comments): array
    {
        $defense = $member->defense()->with(['project', 'members.supervisor'])->firstOrFail();
        $project = $defense->project;
        $previousFinal = $project->grade;
        $wasGraded = ! is_null($member->grade);

        $result = DB::transaction(function () use ($member, $grade, $comments, $defense, $project) {
            $member->update(['grade' => $grade, 'comments' => $comments, 'graded_at' => now()]);

            $members = $defense->members()->with('supervisor')->get();
            if ($members->contains(fn ($m) => is_null($m->grade))) {
                return ['final' => false, 'grade' => null];
            }

            $weights = self::weights($members);
            $final = round($members->sum(fn ($m) => $m->grade * $weights[$m->id]) / 100, 2);
            $note = $members->filter(fn ($m) => filled($m->comments))
                ->map(fn ($m) => $m->role_label . ' (' . $m->supervisor->name . '): ' . $m->comments)
                ->implode("\n");

            $project->update([
                'grade' => $final,
                'evaluation_note' => $note ?: null,
                'evaluated_at' => now(),
                'graded_by' => $project->supervisor_id,
            ]);
            $defense->update(['status' => Defense::DONE]);

            return ['final' => true, 'grade' => $final];
        });

        if ($result['final']) {
            $changed = (float) $previousFinal !== (float) $result['grade'] || is_null($previousFinal);
            if ($changed) {
                Audit::record(is_null($previousFinal) ? 'grade.set' : 'grade.changed', $project,
                    ['grade' => ['from' => $previousFinal, 'to' => $result['grade']]]);
                self::notifyTeam($project->fresh(), $result['grade'], ! is_null($previousFinal));
            }
        } elseif (! $wasGraded) {
            // زميله في اللجنة يعرف أن دوره بقي — بلا الدرجة نفسها
            self::nudgeOthers($defense, $member);
        }

        return $result;
    }

    /** نسبة المشرف من الدرجة (1–99)، أو null = متوسط متساوٍ بين الأعضاء */
    public static function supervisorWeight(): ?float
    {
        $w = config('defenses.supervisor_weight');

        return is_numeric($w) && $w > 0 && $w < 100 ? (float) $w : null;
    }

    /**
     * وزن كل عضو من 100. بلا إعداد: بالتساوي. بإعداد: المشرف نسبته، والباقي
     * يُقسم على الممتحنين بالتساوي (لجنة بلا ممتحن: المشرف وحده 100).
     *
     * @param  \Illuminate\Support\Collection<int, DefenseMember>  $members
     * @return array<int, float> مفتاحه معرّف العضو
     */
    public static function weights($members): array
    {
        $w = self::supervisorWeight();
        $examiners = $members->where('role', 'examiner')->count();

        return $members->mapWithKeys(fn ($m) => [$m->id => match (true) {
            $w === null || ! $examiners => 100 / max(1, $members->count()),
            $m->role === 'supervisor' => $w,
            default => (100 - $w) / $examiners,
        }])->all();
    }

    private static function notifyTeam(Project $project, float $grade, bool $updated): void
    {
        $fmt = rtrim(rtrim(number_format($grade, 2, '.', ''), '0'), '.');
        $msg = ($updated ? 'عُدّلت درجة مشروعكم بعد المناقشة: ' : 'رصدت لجنة المناقشة درجة مشروعكم: ')
            . $fmt . ' (' . $project->grade_label . ') 🎓';

        try {
            Notification::send($project->students(), ProjectActivityNotify::withMail([
                'project' => $project->title,
                'supervisor_name' => 'لجنة المناقشة',
                'msg' => $msg,
            ], 'درجة مشروعكم', route('student.dashboard')));
        } catch (\Throwable $e) {
            Log::warning('defense grade notify failed', ['project' => $project->id, 'error' => $e->getMessage()]);
        }
    }

    private static function nudgeOthers(Defense $defense, DefenseMember $by): void
    {
        $others = $defense->members->where('id', '!=', $by->id)->filter(fn ($m) => is_null($m->grade))->pluck('supervisor');

        try {
            Notification::send($others, new ProjectActivityNotify([
                'project' => $defense->project->title,
                'supervisor_name' => $by->supervisor->name,
                'msg' => 'رصد ' . $by->role_label . ' درجته لمناقشة «' . $defense->project->title . '» — بقيت درجتك لتكتمل درجة المشروع.',
                'kind' => 'defense',
                'title' => 'بقيت درجتك',
            ]));
        } catch (\Throwable $e) {
            Log::warning('defense nudge failed', ['defense' => $defense->id, 'error' => $e->getMessage()]);
        }
    }
}
