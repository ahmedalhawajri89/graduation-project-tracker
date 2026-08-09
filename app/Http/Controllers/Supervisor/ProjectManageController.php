<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\Semester;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectManageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:supervisor');
    }

    /** المشروع يجب أن يتبع المشرف الحالي */
    private function authorizeProject(Project $project)
    {
        abort_unless((int) $project->supervisor_id === (int) auth('supervisor')->id(), 403);
    }

    /** رسالة القفل الموحدة بعد التقييم */
    private const LOCKED_MSG = 'المشروع مؤرشف بعد رصد التقييم — لا يمكن تعديل مراحله أو ملفاته أو موعده. النقاش وتعديل الدرجة فقط متاحان.';

    /** إشعار جميع طلاب المشروع بتحديث */
    private function notifyStudents(Project $project, string $msg)
    {
        Notification::send($project->students(), new ProjectActivityNotify([
            'project' => $project->title,
            'supervisor_name' => auth('supervisor')->user()->name,
            'msg' => $msg,
        ]));
    }

    /* ==================== أرشيف مشاريعي (كل الفصول) ==================== */

    public function archive()
    {
        $projects = Project::where('supervisor_id', auth('supervisor')->id())
            ->whereIn('status', ['accept', 'complete'])
            ->when(request('semester'), function ($q) {
                $q->where('semester_id', request('semester'));
            })
            ->when(request('q'), function ($q) {
                $q->where('title', 'like', '%' . request('q') . '%');
            })
            ->with(['project_type', 'semester', 'milestones'])
            ->withCount('group')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard.supervisor.archive', [
            'projects' => $projects,
            'semesters' => Semester::latest()->get(['id', 'name']),
        ]);
    }

    /* ==================== صفحة إدارة المشروع ==================== */

    public function show(Project $project)
    {
        $this->authorizeProject($project);

        $project->load([
            'group.student.specialize',
            'milestones',
            'files',
            'comments.author',
            'project_type',
            'semester',
        ]);

        return view('dashboard.supervisor.project', ['project' => $project]);
    }

    /* ==================== المراحل (Milestones) ==================== */

    public function milestoneStore(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        if ($project->is_locked) {
            return redirect()->back()->with('fail', self::LOCKED_MSG);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'due_date' => ['nullable', 'date'],
        ], [], [
            'title' => 'عنوان المرحلة',
            'due_date' => 'تاريخ الاستحقاق',
        ]);

        $project->milestones()->create($request->only('title', 'due_date'));

        $this->notifyStudents($project, 'أضاف المشرف مرحلة جديدة للمشروع: ' . $request->title);

        return redirect()->back()->with('success', 'تمت إضافة المرحلة بنجاح');
    }

    public function milestoneToggle(ProjectMilestone $milestone)
    {
        $this->authorizeProject($milestone->project);

        if ($milestone->project->is_locked) {
            return redirect()->back()->with('fail', self::LOCKED_MSG);
        }

        $milestone->update([
            'is_done' => ! $milestone->is_done,
            'done_at' => $milestone->is_done ? null : now(),
        ]);

        $this->notifyStudents(
            $milestone->project,
            $milestone->is_done
                ? 'تم إنجاز مرحلة: ' . $milestone->title . ' ✅'
                : 'أُعيدت مرحلة "' . $milestone->title . '" إلى قيد التنفيذ'
        );

        return redirect()->back()->with('success', 'تم تحديث حالة المرحلة');
    }

    public function milestoneDestroy(ProjectMilestone $milestone)
    {
        $this->authorizeProject($milestone->project);

        if ($milestone->project->is_locked) {
            return redirect()->back()->with('fail', self::LOCKED_MSG);
        }

        $milestone->delete();

        return redirect()->back()->with('success', 'تم حذف المرحلة');
    }

    /* ==================== الملفات ==================== */

    public function fileStore(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        if ($project->is_locked) {
            return redirect()->back()->with('fail', self::LOCKED_MSG);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,png,jpg,jpeg'],
        ], [], [
            'title' => 'اسم الملف',
            'file' => 'الملف',
        ]);

        // القرص الخاص: الملف لا يُفتح إلا عبر راوت التنزيل المحمي بالصلاحيات
        $path = $request->file('file')->store('project_files/' . $project->id, 'local');

        $project->files()->create([
            'title' => $request->title,
            'path' => $path,
            'size' => $request->file('file')->getSize(),
            'uploader_type' => \App\Models\Supervisor::class,
            'uploader_id' => auth('supervisor')->id(),
        ]);

        $this->notifyStudents($project, 'رفع المشرف ملفاً جديداً: ' . $request->title);

        return redirect()->back()->with('success', 'تم رفع الملف بنجاح');
    }

    public function fileDestroy(ProjectFile $file)
    {
        $this->authorizeProject($file->project);

        if ($file->project->is_locked) {
            return redirect()->back()->with('fail', self::LOCKED_MSG);
        }

        // حذف من القرص الخاص، مع دعم الملفات القديمة على العام
        Storage::disk('local')->delete($file->path);
        Storage::disk('public')->delete($file->path);
        $file->delete();

        return redirect()->back()->with('success', 'تم حذف الملف');
    }

    /* ==================== التعليقات ==================== */

    public function commentStore(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ], [], ['body' => 'التعليق']);

        $project->comments()->create([
            'body' => $request->body,
            'author_type' => \App\Models\Supervisor::class,
            'author_id' => auth('supervisor')->id(),
        ]);

        $this->notifyStudents($project, 'تعليق جديد من المشرف: ' . Str::limit($request->body, 80));

        return redirect()->back()->with('success', 'تم إضافة التعليق');
    }

    public function commentDestroy(ProjectComment $comment)
    {
        $this->authorizeProject($comment->project);
        $comment->delete();

        return redirect()->back()->with('success', 'تم حذف التعليق');
    }

    /* ==================== الموعد النهائي ==================== */

    public function deadlineUpdate(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        if ($project->is_locked) {
            return redirect()->back()->with('fail', self::LOCKED_MSG);
        }

        $request->validate([
            'date_line' => ['required', 'date', 'after_or_equal:today'],
        ], [
            'date_line.after_or_equal' => 'الموعد النهائي يجب أن يكون اليوم أو تاريخاً مستقبلياً',
        ], ['date_line' => 'الموعد النهائي']);

        $project->update(['date_line' => $request->date_line]);

        $this->notifyStudents($project, 'حدّد المشرف الموعد النهائي للمشروع: ' . $request->date_line);

        return redirect()->back()->with('success', 'تم تحديد الموعد النهائي');
    }

    /* ==================== التقييم النهائي ==================== */

    public function evaluate(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        if ($project->status !== 'complete') {
            return redirect()->back()->with('fail', 'لا يمكن التقييم قبل اكتمال المشروع');
        }

        $request->validate([
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'evaluation_note' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'grade' => 'الدرجة',
            'evaluation_note' => 'ملاحظات التقييم',
        ]);

        $project->update([
            'grade' => $request->grade,
            'evaluation_note' => $request->evaluation_note,
            'evaluated_at' => now(),
        ]);

        $this->notifyStudents(
            $project,
            'تم تقييم مشروعكم — الدرجة: ' . $request->grade . ' (' . $project->fresh()->grade_label . ') 🎓'
        );

        return redirect()->back()->with('success', 'تم حفظ التقييم وإشعار الفريق');
    }
}
