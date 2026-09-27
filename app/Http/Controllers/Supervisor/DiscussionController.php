<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\Discussion;

/**
 * النقاش — صندوق وارد بمجموعات المشرف والمحادثة المختارة بجانبه.
 *
 * كان قسماً في ذيل صفحة كل مشروع: يطول بالرسائل ويدفع الصفحة إلى
 * الأسفل، ولا يقول أيّ المجموعات كتبت دون أن تُقرأ.
 */
class DiscussionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:supervisor');
    }

    public function index(?Project $project = null)
    {
        $supervisor = auth('supervisor')->user();

        if ($project) {
            abort_unless((int) $project->supervisor_id === (int) $supervisor->id, 403);
        }

        $projects = Discussion::projectsFor($supervisor)
            ->load([
                'comments' => fn ($q) => $q->reorder('id', 'desc')->limit(1)->with('author'),
                // أسماء الطلاب للبحث في القائمة
                'group.student',
            ]);

        $unread = Discussion::unreadFor($supervisor, $projects->pluck('id'));

        // غير المقروء أولاً، ثم الأحدث نشاطاً، ثم الصامت
        $projects = $projects->sortBy([
            fn ($a, $b) => (isset($unread[$b->id]) <=> isset($unread[$a->id])),
            fn ($a, $b) => ($b->comments->first()?->id ?? 0) <=> ($a->comments->first()?->id ?? 0),
        ])->values();

        $lastRead = null;

        // لا محادثة تُفتح تلقائياً: فتحها يُعلّمها مقروءة، والمشرف
        // على الجوال يرى القائمة وحدها فلا يكون قد قرأ شيئاً
        if ($project) {
            $project->load(['comments.author', 'group.student']);
            $lastRead = Discussion::lastReadId($project, $supervisor);
            Discussion::markRead($project, $supervisor);
            unset($unread[$project->id]);
        }

        return view('dashboard.supervisor.discussion', [
            'projects' => $projects,
            'unread' => $unread,
            'project' => $project,
            'lastRead' => $lastRead,
        ]);
    }
}
