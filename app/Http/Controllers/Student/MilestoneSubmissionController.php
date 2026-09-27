<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MilestoneSubmission;
use App\Models\ProjectMilestone;
use App\Notifications\ProjectActivityNotify;
use App\Support\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * تسليم المرحلة — الطالب يقول «سلّمنا» بملاحظة وملف، فتنتظر مراجعة المشرف.
 *
 * كانت المرحلة زرّاً عند المشرف وحده: لا طريق للفريق ليقول إنه أنهاها.
 */
class MilestoneSubmissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    public function store(Request $request, ProjectMilestone $milestone)
    {
        $project = $milestone->project;
        $student = auth('student')->user();

        abort_unless($project->group()->where('student_id', $student->id)->exists(), 403);

        if (! in_array($project->status, ['accept', 'complete'], true)) {
            return back()->with('fail', 'تُسلَّم المراحل بعد قبول المشروع.');
        }

        if ($project->is_locked) {
            return back()->with('fail', 'المشروع مؤرشف بعد التقييم — لا تسليمات جديدة.');
        }

        $data = $request->validate([
            // أحدهما على الأقل: تسليم فارغ لا يُراجَع
            'note' => ['nullable', 'string', 'max:2000', 'required_without:file'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,png,jpg,jpeg'],
        ], [
            'note.required_without' => 'اكتب ملاحظة أو أرفق ملفاً — تسليم فارغ لا يُراجَع.',
        ], [
            'note' => 'الملاحظة',
            'file' => 'الملف',
        ]);

        $path = $request->hasFile('file')
            ? $request->file('file')->store('submissions/' . $project->id, 'local')
            : null;

        try {
            $submission = DB::transaction(function () use ($milestone, $student, $data, $request, $path) {
                // القفل: عضوان يسلّمان معاً لا يصنعان جولتين برقم واحد
                $locked = ProjectMilestone::whereKey($milestone->id)->lockForUpdate()->first();

                if (! $locked->canSubmit()) {
                    return null;
                }

                $submission = MilestoneSubmission::create([
                    'milestone_id' => $locked->id,
                    'student_id' => $student->id,
                    'round' => (int) $locked->submissions()->max('round') + 1,
                    'note' => $data['note'] ?? null,
                    'file_path' => $path,
                    'file_name' => $path ? mb_substr($request->file('file')->getClientOriginalName(), 0, 150) : null,
                    'file_size' => $path ? $request->file('file')->getSize() : null,
                ]);

                $locked->update(['status' => ProjectMilestone::SUBMITTED]);

                return $submission;
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }

        if (! $submission) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }

            return back()->with('fail', $milestone->fresh()->isSubmitted()
                ? 'سُلّمت هذه المرحلة وتنتظر مراجعة المشرف.'
                : 'اعتُمدت هذه المرحلة — لا تسليم جديد لها.');
        }

        Audit::record('milestone.submitted', $project, ['milestone' => ['to' => $milestone->title], 'round' => ['to' => $submission->round]]);

        if ($project->supervisor) {
            try {
                $project->supervisor->notify(new ProjectActivityNotify([
                    'project' => $project->title,
                    'supervisor_name' => $student->name,
                    'msg' => ($submission->round > 1 ? 'أعاد تسليم' : 'سلّم') . ' مرحلة «' . $milestone->title . '» — بانتظار مراجعتك',
                ]));
            } catch (\Exception $ex) {
                Log::warning('تعذّر إشعار المشرف بتسليم مرحلة', ['milestone' => $milestone->id, 'exception' => $ex]);
            }
        }

        return redirect()->to(url()->previous() . '#milestone-' . $milestone->id)
            ->with('success', $submission->round > 1
                ? 'أُعيد تسليم المرحلة — الجولة ' . $submission->round . ' بانتظار المشرف.'
                : 'سُلّمت المرحلة — بانتظار مراجعة المشرف.');
    }
}
