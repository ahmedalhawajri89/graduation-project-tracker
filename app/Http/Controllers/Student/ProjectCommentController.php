<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Student;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ProjectCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
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

        // إشعار المشرف
        if ($project->supervisor_id) {
            Notification::send($project->supervisor, new ProjectActivityNotify([
                'project' => $project->title,
                'supervisor_name' => auth('student')->user()->name,
                'msg' => 'تعليق جديد من الطالب: ' . Str::limit($request->body, 80),
            ]));
        }

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
