<?php

namespace App\Support;

use App\Models\DiscussionRead;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * عدّاد النقاش غير المقروء.
 *
 * غير المقروء = تعليقات **غير القارئ** بمعرّف أكبر من آخر ما قرأه.
 * تعليق المرء لا يُعدّ عليه، ومن لم يفتح النقاش قطّ فكل تعليقات
 * غيره غير مقروءة عنده.
 */
class Discussion
{
    /**
     * عدد غير المقروء لكل مشروع — استعلام واحد مهما كثرت المشاريع.
     *
     * @param  iterable<int>  $projectIds
     * @return array<int, int>  [project_id => count]، والمشروع بلا جديد غائب
     */
    public static function unreadFor(Model $reader, iterable $projectIds, string $channel = ProjectComment::SUPERVISOR): array
    {
        $ids = collect($projectIds)->filter()->unique()->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $type = $reader::class;
        $id = $reader->getKey();

        return ProjectComment::query()
            ->leftJoin('discussion_reads as dr', function ($join) use ($type, $id, $channel) {
                $join->on('dr.project_id', '=', 'project_comments.project_id')
                    ->where('dr.reader_type', $type)
                    ->where('dr.reader_id', $id)
                    ->where('dr.channel', $channel);
            })
            // لكل قناة عدّادها: رسائل الفريق لا تُعدّ على المشرف أبداً
            ->channel($channel)
            ->whereIn('project_comments.project_id', $ids)
            ->where(function ($q) use ($type, $id) {
                $q->where('project_comments.author_type', '!=', $type)
                    ->orWhere('project_comments.author_id', '!=', $id);
            })
            ->where(function ($q) {
                $q->whereNull('dr.last_read_comment_id')
                    ->orWhereColumn('project_comments.id', '>', 'dr.last_read_comment_id');
            })
            ->groupBy('project_comments.project_id')
            ->pluck(DB::raw('count(*)'), 'project_comments.project_id')
            ->map(fn ($n) => (int) $n)
            ->all();
    }

    /**
     * المشاريع التي يتحدّث فيها القارئ — مصدر واحد للشريط الجانبي
     * وصفحتي النقاش، فلا يعدّ العدّاد ما لا تعرضه الصفحة.
     *
     * الطالب: مشروعه النشط (غير المرفوض). المشرف: مشاريع الفصل الحالي
     * غير المرفوضة — والطلب المعلّق منها: السؤال قبل القبول مشروع.
     *
     * @return \Illuminate\Support\Collection<int, Project>
     */
    public static function projectsFor(Model $reader)
    {
        if ($reader instanceof Student) {
            $project = $reader->groups()->with('project')->first()?->project;

            return collect($project && $project->status !== 'reject' ? [$project] : []);
        }

        if ($reader instanceof Supervisor) {
            return $reader->projects()
                ->where('semester_id', Semester::current()?->id)
                ->where('status', '!=', 'reject')
                ->get();
        }

        return collect();
    }

    /** آخر ما قرأه في هذا المشروع وهذه القناة — لفاصل «رسائل جديدة» */
    public static function lastReadId(Project $project, Model $reader, string $channel = ProjectComment::SUPERVISOR): ?int
    {
        return DiscussionRead::where('project_id', $project->id)
            ->where('reader_type', $reader::class)
            ->where('reader_id', $reader->getKey())
            ->where('channel', $channel)
            ->value('last_read_comment_id');
    }

    /**
     * فتح النقاش أو الكتابة فيه يعني قراءة كل ما قبله — في قناته وحدها.
     * كان بأكبر رقم في المشروع، فقراءة قناة كانت ستعلّم الأخرى مقروءة.
     */
    public static function markRead(Project $project, Model $reader, string $channel = ProjectComment::SUPERVISOR): void
    {
        $last = ProjectComment::where('project_id', $project->id)->channel($channel)->max('id');

        if (! $last) {
            return;
        }

        DiscussionRead::updateOrCreate(
            [
                'project_id' => $project->id,
                'reader_type' => $reader::class,
                'reader_id' => $reader->getKey(),
                'channel' => $channel,
            ],
            ['last_read_comment_id' => $last]
        );
    }
}
