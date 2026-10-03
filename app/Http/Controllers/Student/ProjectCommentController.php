<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Student;
use App\Notifications\ProjectActivityNotify;
use App\Support\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * نقاش الطالب — قناتان: «مع المشرف»، و«الفريق» لأعضائه وحدهم.
 *
 * نقاش الفريق كان ينتقل إلى واتساب لأن المشرف يقرأ الخيط الوحيد كلّه.
 * ولا إشعار لكل رسالة (العدّاد يكفي)، إلا لمن ذُكر بـ@ في نقاش الفريق.
 */
class ProjectCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    public function index(Request $request)
    {
        $student = auth('student')->user();
        $project = Discussion::projectsFor($student)->first();
        $channel = $request->query('tab') === 'team' ? ProjectComment::TEAM : ProjectComment::SUPERVISOR;
        $lastRead = null;
        $unread = [ProjectComment::SUPERVISOR => 0, ProjectComment::TEAM => 0];

        if ($project) {
            $project->load([
                $channel === ProjectComment::TEAM ? 'teamComments.author' : 'comments.author',
                'supervisor.specialize',
                'group.student',
            ]);

            // يُقرأ قبل التعليم: الفاصل يقع عند ما كان جديداً لحظة الفتح
            $lastRead = Discussion::lastReadId($project, $student, $channel);
            Discussion::markRead($project, $student, $channel);

            // عدّاد القناة الأخرى — القناة المفتوحة قُرئت للتوّ
            foreach ($unread as $ch => $n) {
                $unread[$ch] = $ch === $channel ? 0 : (Discussion::unreadFor($student, [$project->id], $ch)[$project->id] ?? 0);
            }
        }

        return view('dashboard.student.discussion', [
            'project' => $project,
            'channel' => $channel,
            'comments' => $project ? ($channel === ProjectComment::TEAM ? $project->teamComments : $project->comments) : collect(),
            'lastRead' => $lastRead,
            'unread' => $unread,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        $student = auth('student')->user();

        abort_unless($project->group()->where('student_id', $student->id)->exists(), 403);

        // الزملاء وحدهم يُذكرون — لا الكاتب نفسه ولا طالب من خارج الفريق
        $mates = $project->group()->where('student_id', '!=', $student->id)->pluck('student_id')->map(fn ($id) => (int) $id)->all();

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'channel' => ['nullable', 'in:supervisor,team'],
            'mentions' => ['nullable', 'array', 'max:10'],
            'mentions.*' => ['integer', 'in:' . implode(',', $mates ?: [0])],
        ], [
            'mentions.*.in' => __('يُذكر زملاء الفريق وحدهم.'),
        ], ['body' => __('الرسالة')]);

        $channel = ($data['channel'] ?? null) === ProjectComment::TEAM ? ProjectComment::TEAM : ProjectComment::SUPERVISOR;

        // الذكر في نقاش الفريق وحده: قناة المشرف بلا إشعارات، كما كانت
        $mentions = $channel === ProjectComment::TEAM
            ? array_values(array_unique(array_map('intval', $data['mentions'] ?? [])))
            : [];

        ProjectComment::create([
            'project_id' => $project->id,
            'channel' => $channel,
            'body' => $data['body'],
            'mentions' => $mentions ?: null,
            'author_type' => Student::class,
            'author_id' => $student->id,
        ]);

        Discussion::markRead($project, $student, $channel);

        foreach (Student::whereIn('id', $mentions)->get() as $mate) {
            try {
                $mate->notify(new ProjectActivityNotify([
                    'project' => $project->title,
                    'supervisor_name' => $student->name,
                    'msg' => 'ذكرك ' . $student->name . ' في نقاش الفريق: ' . Str::limit($data['body'], 90),
                ]));
            } catch (\Exception $ex) {
                Log::warning('تعذّر إشعار زميل بذكره', ['project' => $project->id, 'exception' => $ex]);
            }
        }

        // الإرسال بلا تحميل (النقاش الحيّ): ما وصل بعد آخر رسالة معروضة، ومعه رسالتي
        if ($request->expectsJson()) {
            return response()->json(\App\Http\Controllers\DiscussionLiveController::payload($project, 'student', $channel, (int) $request->input('after', 0)));
        }

        return redirect()->route('student.discussion', $channel === ProjectComment::TEAM ? ['tab' => 'team'] : [])
            ->with('success', $mentions ? __('أُرسلت الرسالة ووصل التنبيه لمن ذكرتهم.') : __('تم إرسال الرسالة'));
    }

    public function destroy(ProjectComment $comment)
    {
        abort_unless(
            $comment->author_type === Student::class
                && (int) $comment->author_id === (int) auth('student')->id(),
            403
        );

        $channel = $comment->channel;
        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('student.discussion', $channel === ProjectComment::TEAM ? ['tab' => 'team'] : [])
            ->with('success', __('تم حذف الرسالة'));
    }
}
