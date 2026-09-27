<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\SupervisorStage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * خطة المراحل ↔ مراحل المجموعات.
 *
 * القاعدة: المرحلة في المجموعة نسخة من أصلها في الخطة (\u200Estage_id\u200E). التعديل
 * يسري على غير المنجز وحده — المنجز سجلّ لا يُعاد كتابته. والمشروع المقيَّم
 * (مؤرشف) لا يُمسّ.
 */
class StagePlan
{
    /** المجموعات التي تحمل الخطة: مقبولة، غير مقيَّمة، في فصل الخطة */
    public static function projectsFor(int $supervisorId, int $semesterId): Collection
    {
        return Project::where('supervisor_id', $supervisorId)
            ->where('semester_id', $semesterId)
            ->where('status', 'accept')
            ->whereNull('grade')
            ->get();
    }

    /** مجموعة قُبلت للتوّ: تأخذ الخطة كاملة */
    public static function applyTo(Project $project): int
    {
        if ($project->status !== 'accept' || ! is_null($project->grade)) {
            return 0;
        }

        $stages = SupervisorStage::where('supervisor_id', $project->supervisor_id)
            ->where('semester_id', $project->semester_id)
            ->get();

        $created = 0;
        foreach ($stages as $stage) {
            $milestone = ProjectMilestone::firstOrCreate(
                ['project_id' => $project->id, 'stage_id' => $stage->id],
                ['title' => $stage->title, 'due_date' => $stage->due_date]
            );
            $created += $milestone->wasRecentlyCreated ? 1 : 0;
        }

        return $created;
    }

    /**
     * أُنشئت مرحلة أو عُدّلت: تُنشأ حيث تغيب، وتُحدَّث حيث لم تُنجز.
     *
     * @return array{created: array<int, int>, updated: int} created = معرّفات المشاريع التي أُضيفت إليها
     */
    public static function propagate(SupervisorStage $stage): array
    {
        $created = [];
        $updated = 0;

        foreach (self::projectsFor($stage->supervisor_id, $stage->semester_id) as $project) {
            $milestone = ProjectMilestone::where('project_id', $project->id)->where('stage_id', $stage->id)->first();

            if (! $milestone) {
                ProjectMilestone::create([
                    'project_id' => $project->id,
                    'stage_id' => $stage->id,
                    'title' => $stage->title,
                    'due_date' => $stage->due_date,
                ]);
                $created[] = $project->id;
            } elseif (! $milestone->is_done) {
                $milestone->update(['title' => $stage->title, 'due_date' => $stage->due_date]);
                $updated++;
            }
        }

        return ['created' => $created, 'updated' => $updated];
    }

    /** حذف المرحلة: غير المنجز في المجموعات الجارية يُحذف، والمنجز يبقى مرحلةً خاصة */
    public static function remove(SupervisorStage $stage): int
    {
        // ما له تسليم يبقى كالمنجز: عمل الفريق لا يُحذف بحذف أصله من الخطة
        $removed = ProjectMilestone::where('stage_id', $stage->id)
            ->where('is_done', false)
            ->whereDoesntHave('submissions')
            ->whereHas('project', fn ($q) => $q->whereNull('grade'))
            ->delete();

        if ($stage->template_path) {
            Storage::disk('local')->delete($stage->template_path);
        }

        $stage->delete(); // nullOnDelete: ما بقي يصير مرحلة خاصة بمجموعته

        return $removed;
    }

    /**
     * تقدّم كل مرحلة عبر المجموعات — لكل مجموعة حالتها: منجزة، متأخّرة، جارية، غائبة.
     *
     * @return array<int, array{done: int, late: int, total: int, groups: array<int, string>}>
     */
    public static function progress(Collection $stages, Collection $projects): array
    {
        $milestones = ProjectMilestone::whereIn('stage_id', $stages->pluck('id'))
            ->whereIn('project_id', $projects->pluck('id'))
            ->get()
            ->groupBy('stage_id');

        $out = [];
        foreach ($stages as $stage) {
            $byProject = ($milestones[$stage->id] ?? collect())->keyBy('project_id');
            $groups = [];
            $done = $late = $review = 0;

            foreach ($projects as $project) {
                $m = $byProject[$project->id] ?? null;
                // المسلَّمة قبل المتأخّرة: الانتظار على المشرف لا على الفريق
                $state = match (true) {
                    ! $m => 'missing',
                    $m->is_done => 'done',
                    $m->isSubmitted() => 'submitted',
                    $m->needsRevision() => 'revision',
                    $m->isLate() => 'late',
                    default => 'open',
                };
                $done += $state === 'done';
                $late += $state === 'late';
                $review += $state === 'submitted';
                $groups[$project->id] = $state;
            }

            $out[$stage->id] = ['done' => $done, 'late' => $late, 'review' => $review, 'total' => $projects->count(), 'groups' => $groups];
        }

        return $out;
    }
}
