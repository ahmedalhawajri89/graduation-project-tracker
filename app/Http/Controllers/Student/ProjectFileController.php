<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    /** رفع ملف لمشروع الطالب */
    public function store(Request $request, Project $project)
    {
        // الطالب يجب أن يكون عضواً في فريق المشروع
        abort_unless(
            $project->group()->where('student_id', auth('student')->id())->exists(),
            403
        );

        if (! in_array($project->status, ['accept', 'complete'])) {
            return redirect()->back()->with('fail', __('لا يمكن رفع ملفات قبل قبول المشروع'));
        }

        // بعد رصد التقييم يصبح المشروع أرشيفياً
        if ($project->is_locked) {
            return redirect()->back()->with('fail', __('المشروع مؤرشف بعد التقييم — لا يمكن رفع ملفات جديدة'));
        }

        $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,png,jpg,jpeg'],
        ], [], [
            'title' => __('اسم الملف'),
            'file' => __('الملف'),
        ]);

        // القرص الخاص: الملف لا يُفتح إلا عبر راوت التنزيل المحمي بالصلاحيات
        $path = $request->file('file')->store('project_files/' . $project->id, 'local');

        $project->files()->create([
            'title' => $request->title,
            'path' => $path,
            'size' => $request->file('file')->getSize(),
            'uploader_type' => \App\Models\Student::class,
            'uploader_id' => auth('student')->id(),
        ]);

        // إشعار المشرف
        if ($project->supervisor_id) {
            Notification::send($project->supervisor, new ProjectActivityNotify([
                'project' => $project->title,
                'supervisor_name' => auth('student')->user()->name,
                'msg' => 'رفع الطالب ملفاً جديداً: ' . $request->title,
            ]));
        }

        return redirect()->back()->with('success', __('تم رفع الملف بنجاح'));
    }

    /** حذف ملف رفعه الطالب نفسه */
    public function destroy(ProjectFile $file)
    {
        abort_unless(
            $file->uploader_type === \App\Models\Student::class
                && (int) $file->uploader_id === (int) auth('student')->id(),
            403
        );

        if ($file->project->is_locked) {
            return redirect()->back()->with('fail', __('المشروع مؤرشف بعد التقييم — لا يمكن حذف ملفاته'));
        }

        // حذف من القرص الخاص، مع دعم الملفات القديمة على العام
        Storage::disk('local')->delete($file->path);
        Storage::disk('public')->delete($file->path);
        $file->delete();

        return redirect()->back()->with('success', __('تم حذف الملف'));
    }
}
