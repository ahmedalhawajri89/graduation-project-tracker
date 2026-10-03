<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Student;
use App\Models\Supervisor;
use App\Support\Discussion;
use Illuminate\Http\Request;

/**
 * النقاش الحيّ: المحادثة المفتوحة تسأل كل بضع ثوانٍ «ما الجديد بعد الرسالة X؟».
 *
 * الطالب عضو الفريق يرى القناتين، والمشرف صاحب المشروع قناته وحدها — نقاش
 * الفريق ليس له، فلا يُعترف بوجوده (404). الرسائل تُرسم من الجزء نفسه الذي
 * ترسمه الصفحة (_messages)، فلا نسختان من شكل الفقاعة.
 */
class DiscussionLiveController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        [$reader, $role] = $this->reader($project);

        $channel = $request->query('channel') === ProjectComment::TEAM ? ProjectComment::TEAM : ProjectComment::SUPERVISOR;
        abort_if($channel === ProjectComment::TEAM && $role !== 'student', 404);

        // ما رآه على شاشته يُعلَّم مقروءاً — والتبويب ظاهر وحده (السكربت يقرّر)
        if ($request->boolean('read')) {
            Discussion::markRead($project, $reader, $channel);
        }

        return response()->json(self::payload($project, $role, $channel, (int) $request->query('after', 0)) + [
            'unread' => $this->unread($project, $reader, $role, $channel),
        ])->header('Cache-Control', 'no-store');
    }

    /**
     * الرسائل بعد after مرسومة، وآخر رقم، وأرقام الرسائل الحالية (لإزالة ما حُذف).
     * يستعمله الإرسال بلا تحميل أيضاً.
     *
     * @return array{html: string, last_id: int, ids: int[]}
     */
    public static function payload(Project $project, string $role, string $channel, int $after): array
    {
        $project->loadMissing('group.student');
        $base = fn () => ProjectComment::where('project_id', $project->id)->channel($channel);

        $comments = $base()->where('id', '>', $after)->with('author')->orderBy('id')->get();
        // الرسالة المعروضة قبلها: ليصحّ التجميع تحت اسم واحد وفاصل اليوم
        $prev = $after ? $base()->where('id', '<=', $after)->with('author')->orderByDesc('id')->first() : null;
        $ids = $base()->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();

        return [
            'html' => $comments->isEmpty() ? '' : view('dashboard.discussion._messages', [
                'comments' => $comments,
                'project' => $project,
                'role' => $role,
                'channel' => $channel,
                'prev' => $prev,
                'withDivider' => false,
            ])->render(),
            'last_id' => $ids ? max($ids) : 0,
            'ids' => $ids,
        ];
    }

    /** @return array{0: Student|Supervisor, 1: string} */
    private function reader(Project $project): array
    {
        if ($student = auth('student')->user()) {
            abort_unless($project->group()->where('student_id', $student->id)->exists(), 403);

            return [$student, 'student'];
        }

        if ($supervisor = auth('supervisor')->user()) {
            abort_unless((int) $project->supervisor_id === (int) $supervisor->id, 403);

            return [$supervisor, 'supervisor'];
        }

        abort(403);
    }

    /**
     * العدّادات حول المحادثة: الطالب لقناتيه، والمشرف لقائمة مشاريعه.
     *
     * @return array<string, mixed>
     */
    private function unread(Project $project, Student|Supervisor $reader, string $role, string $channel): array
    {
        if ($role === 'student') {
            return collect([ProjectComment::SUPERVISOR, ProjectComment::TEAM])
                ->mapWithKeys(fn ($ch) => [$ch => Discussion::unreadFor($reader, [$project->id], $ch)[$project->id] ?? 0])
                ->all();
        }

        return ['projects' => Discussion::unreadFor($reader, Discussion::projectsFor($reader)->pluck('id'))];
    }
}
