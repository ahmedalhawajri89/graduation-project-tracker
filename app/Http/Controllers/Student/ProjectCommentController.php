<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Student;
use App\Support\Discussion;
use Illuminate\Http\Request;

class ProjectCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    /** النقاش — محادثة الطالب الواحدة مع مشرفه */
    public function index()
    {
        $student = auth('student')->user();
        $project = Discussion::projectsFor($student)->first();
        $lastRead = null;

        if ($project) {
            $project->load(['comments.author', 'supervisor.specialize']);

            // يُقرأ قبل التعليم: الفاصل يقع عند ما كان جديداً لحظة الفتح
            $lastRead = Discussion::lastReadId($project, $student);
            Discussion::markRead($project, $student);
        }

        return view('dashboard.student.discussion', [
            'project' => $project,
            'lastRead' => $lastRead,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        abort_unless(
            $project->group()->where('student_id', auth('student')->id())->exists(),
            403
        );

        $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ], [], ['body' => 'التعليق']);

        $project->comments()->create([
            'body' => $request->body,
            'author_type' => Student::class,
            'author_id' => auth('student')->id(),
        ]);

        // لا إشعار: عدّاد النقاش في الشريط الجانبي يحلّ محلّه، وبقاؤه
        // يعدّ الرسالة الواحدة مرّتين — في الجرس وفي النقاش
        Discussion::markRead($project, auth('student')->user());

        return redirect()->back()->with('success', 'تم إضافة التعليق');
    }

    public function destroy(ProjectComment $comment)
    {
        abort_unless(
            $comment->author_type === Student::class
                && (int) $comment->author_id === (int) auth('student')->id(),
            403
        );

        $comment->delete();

        return redirect()->back()->with('success', 'تم حذف التعليق');
    }
}
