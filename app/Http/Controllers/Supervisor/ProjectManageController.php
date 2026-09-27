<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectFile;
use App\Models\ProjectMilestone;
use App\Models\Semester;
use App\Notifications\ProjectActivityNotify;
use App\Support\Audit;
use App\Support\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

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

    /** رسالة المشروع الذي لم يُقبل — أو رُفض */
    private const INACTIVE_MSG = 'المشروع لم يُقبل بعد أو رُفض — المراحل والملفات والموعد تُدار بعد قبوله.';

    /**
     * هل يُمنع تعديل محتوى المشروع؟ يعيد سبب المنع أو null.
     *
     * كانت الملكية وحدها تُفحص: المراحل والملفات والموعد تعمل على مشروع
     * معلّق أو مرفوض، ويُشعَر طلابه بها.
     */
    private function blocked(Project $project): ?string
    {
        if (! in_array($project->status, ['accept', 'complete'], true)) {
            return self::INACTIVE_MSG;
        }

        return $project->is_locked ? self::LOCKED_MSG : null;
    }

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
        // الفصول السابقة وحدها: مجموعات الفصل الحالي في اللوحة، وتكرارها
        // هنا يجعل الأرشيف نسخة ثانية منها. ولا ترقيم: مجموعات المشرف عبر
        // الفصول عشرات، والتجميع بالفصل لا يتعايش مع الصفحات.
        $projects = Project::where('supervisor_id', auth('supervisor')->id())
            ->whereIn('status', ['accept', 'complete'])
            ->where('semester_id', '!=', Semester::current()?->id)
            ->when(request('q'), function ($q) {
                // باسم الطالب أيضاً: المشرف يُسأل عن طالب قديم باسمه لا بعنوان مشروعه
                $term = '%' . request('q') . '%';
                $q->where(fn ($q) => $q->where('title', 'like', $term)
                    ->orWhereHas('group.student', fn ($s) => $s->where('name', 'like', $term)
                        ->orWhere('university_id', 'like', $term)));
            })
            ->with(['project_type', 'semester', 'group.student:id,name,university_id'])
            ->orderByDesc('semester_id')
            ->latest()
            ->get();

        return view('dashboard.supervisor.archive', [
            'bySemester' => $projects->groupBy('semester_id'),
        ]);
    }

    /* ==================== صفحة إدارة المشروع ==================== */

    public function show(Project $project)
    {
        $this->authorizeProject($project);

        // النقاش صار تبويباً مستقلّاً: لا تعليقات هنا، عدّاد غير المقروء وحده
        $project->load([
            'group.student',
            'group.roles',
            'milestones.stage',
            'files.uploader',
            'files.notes.author',
            'files.notes.mentioned',
            'files.notes.resolver',
            'project_type',
            'semester',
        ]);

        return view('dashboard.supervisor.project', [
            'project' => $project,
            'unread' => Discussion::unreadFor(auth('supervisor')->user(), [$project->id])[$project->id] ?? 0,
        ]);
    }

    /* ==================== المراحل (Milestones) ==================== */

    public function milestoneStore(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        if ($reason = $this->blocked($project)) {
            return redirect()->back()->with('fail', $reason);
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

        if ($reason = $this->blocked($milestone->project)) {
            return redirect()->back()->with('fail', $reason);
        }

        $done = ! $milestone->is_done;

        // الإنجاز المباشر (مناقشة شفهية مثلاً) اعتماد: تسليم معلّق يُعتمد معه
        DB::transaction(function () use ($milestone, $done) {
            $milestone->update([
                'is_done' => $done,
                'status' => $done ? ProjectMilestone::APPROVED : ProjectMilestone::OPEN,
                'done_at' => $done ? now() : null,
            ]);

            if ($done) {
                $milestone->submissions()->whereNull('decision')->update([
                    'decision' => ProjectMilestone::APPROVED,
                    'reviewed_by' => auth('supervisor')->id(),
                    'reviewed_at' => now(),
                ]);
            }
        });

        $this->notifyStudents(
            $milestone->project,
            $milestone->is_done
                ? 'تم إنجاز مرحلة: ' . $milestone->title . ' ✅'
                : 'أُعيدت مرحلة "' . $milestone->title . '" إلى قيد التنفيذ'
        );

        return redirect()->back()->with('success', 'تم تحديث حالة المرحلة');
    }

    /**
     * مراجعة تسليم: اعتماد، أو «مطلوب تعديل» بملاحظة تصل الفريق.
     *
     * كان الردّ على التسليم في النقاش وحده، فيضيع سبب الإرجاع بين الرسائل.
     */
    public function milestoneReview(Request $request, ProjectMilestone $milestone)
    {
        $this->authorizeProject($milestone->project);

        if ($reason = $this->blocked($milestone->project)) {
            return redirect()->back()->with('fail', $reason);
        }

        $data = $request->validate([
            'decision' => ['required', 'in:approve,revision'],
            'feedback' => ['nullable', 'string', 'max:2000', 'required_if:decision,revision'],
        ], [
            'feedback.required_if' => 'اكتب ما يجب تعديله — الفريق يحتاج السبب ليعدّل.',
        ], [
            'feedback' => 'ملاحظة التعديل',
        ]);

        $approve = $data['decision'] === 'approve';

        $round = DB::transaction(function () use ($milestone, $data, $approve) {
            $locked = ProjectMilestone::whereKey($milestone->id)->lockForUpdate()->first();

            // مراجعة مزدوجة (نقرتان، أو تبويبان) لا تُسجَّل مرّتين
            if (! $locked->isSubmitted()) {
                return null;
            }

            $submission = $locked->submissions()->first();
            $submission->update([
                'decision' => $approve ? ProjectMilestone::APPROVED : ProjectMilestone::REVISION,
                'feedback' => $data['feedback'] ?? null,
                'reviewed_by' => auth('supervisor')->id(),
                'reviewed_at' => now(),
            ]);

            $locked->update([
                'status' => $approve ? ProjectMilestone::APPROVED : ProjectMilestone::REVISION,
                'is_done' => $approve,
                'done_at' => $approve ? now() : null,
            ]);

            return $submission->round;
        });

        if (! $round) {
            return redirect()->back()->with('fail', 'لا تسليم بانتظار المراجعة في هذه المرحلة.');
        }

        Audit::record($approve ? 'milestone.approved' : 'milestone.revision', $milestone->project, [
            'milestone' => ['to' => $milestone->title],
            'round' => ['to' => $round],
        ]);

        $this->notifyStudents($milestone->project, $approve
            ? 'اعتُمدت مرحلة «' . $milestone->title . '» ✅'
            : 'مطلوب تعديل في مرحلة «' . $milestone->title . '»: ' . \Illuminate\Support\Str::limit($data['feedback'], 120));

        return redirect()->to(url()->previous() . '#milestone-' . $milestone->id)
            ->with('success', $approve ? 'اعتُمدت المرحلة.' : 'أُرسل طلب التعديل إلى الفريق.');
    }

    public function milestoneDestroy(ProjectMilestone $milestone)
    {
        $this->authorizeProject($milestone->project);

        if ($reason = $this->blocked($milestone->project)) {
            return redirect()->back()->with('fail', $reason);
        }

        // ملفات التسليمات على القرص: الصفوف تُحذف بالتتابع، والملفات لا
        $files = $milestone->submissions()->whereNotNull('file_path')->pluck('file_path')->all();
        $milestone->delete();
        Storage::disk('local')->delete($files);

        return redirect()->back()->with('success', 'تم حذف المرحلة');
    }

    /* ==================== الملفات ==================== */

    public function fileStore(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        if ($reason = $this->blocked($project)) {
            return redirect()->back()->with('fail', $reason);
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

        if ($reason = $this->blocked($file->project)) {
            return redirect()->back()->with('fail', $reason);
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

        // لا إشعار: عدّاد النقاش يحلّ محلّه — انظر \App\Support\Discussion
        Discussion::markRead($project, auth('supervisor')->user());

        return redirect()->back()->with('success', 'تم إضافة التعليق');
    }

    public function commentDestroy(ProjectComment $comment)
    {
        $this->authorizeProject($comment->project);

        // نقاش الفريق ليس للمشرف: لا يُحذف برقمه ولا يُعترف بوجوده
        abort_if($comment->isTeam(), 404);

        $comment->delete();

        return redirect()->back()->with('success', 'تم حذف التعليق');
    }

    /* ==================== الموعد النهائي ==================== */

    public function deadlineUpdate(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        if ($reason = $this->blocked($project)) {
            return redirect()->back()->with('fail', $reason);
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

        // الحراسة على الخادم لا في الواجهة وحدها: إخفاء النموذج لا
        // يمنع طلباً مُلفَّقاً، والدرجة المعتمدة هي ما يُتنازَع عليه
        if ($project->isGradeLocked()) {
            return redirect()->back()->with('fail',
                'الدرجة معتمدة ولا يمكن تعديلها. راجع مسؤول النظام لفكّ الاعتماد.');
        }

        $request->validate([
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'evaluation_note' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'grade' => 'الدرجة',
            'evaluation_note' => 'ملاحظات التقييم',
        ]);

        $previous = $project->grade;

        $project->update([
            'grade' => $request->grade,
            'evaluation_note' => $request->evaluation_note,
            'evaluated_at' => now(),
            'graded_by' => auth('supervisor')->id(),
        ]);

        Audit::record(
            is_null($previous) ? 'grade.set' : 'grade.changed',
            $project,
            ['grade' => ['from' => $previous, 'to' => (float) $request->grade]]
        );

        $this->notifyStudents(
            $project,
            'تم تقييم مشروعكم — الدرجة: ' . $request->grade . ' (' . $project->fresh()->grade_label . ') 🎓'
        );

        return redirect()->back()->with('success', 'تم حفظ التقييم وإشعار الفريق');
    }

    /**
     * اعتماد الدرجة — يقفلها على المشرف.
     *
     * فعل لا رجعة فيه من طرف المشرف: بعده يلزم مسؤول النظام. وهذا هو
     * المقصود — الدرجة المعتمدة نتيجة معلنة لا مسوّدة.
     */
    public function lockGrade(Project $project)
    {
        $this->authorizeProject($project);

        if (is_null($project->grade)) {
            return redirect()->back()->with('fail', 'ضع الدرجة أولاً ثم اعتمدها.');
        }

        if ($project->isGradeLocked()) {
            return redirect()->back()->with('fail', 'الدرجة معتمدة أصلاً.');
        }

        $project->update(['grade_locked_at' => now()]);

        Audit::record('grade.locked', $project, ['grade' => ['to' => (float) $project->grade]]);

        return redirect()->back()->with('success', 'تم اعتماد الدرجة. لم يعد بالإمكان تعديلها.');
    }
}
