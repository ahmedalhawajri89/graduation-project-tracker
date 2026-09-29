<?php

namespace App\Http\Controllers;

use App\Models\MilestoneSubmission;
use App\Models\ProjectFile;
use App\Models\SupervisorStage;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    /** تنزيل ملف مشروع — متاح للأدمن، ومشرف المشروع، وأعضاء فريقه فقط */
    public function download(ProjectFile $file)
    {
        // مشروع محذوف حذفاً مرناً: \u200E$file->project\u200E يعيد null فكان التنزيل يسقط
        // بخطأ 500. الأدمن وحده يراه (صفحة المحذوفات)، ولغيره كأنه غير موجود
        $project = $file->project()->withTrashed()->first();
        $isAdmin = auth('admin')->check();

        abort_unless($project && ($isAdmin || ! $project->trashed()), 404);

        $allowed = false;

        if ($isAdmin) {
            $allowed = true;
        } elseif (auth('supervisor')->check()) {
            // مشرف المشروع، أو عضو لجنة مناقشته (الممتحن يحضّر من ملفاته)
            $allowed = (int) $project->supervisor_id === (int) auth('supervisor')->id()
                || $project->hasCommitteeMember((int) auth('supervisor')->id());
        } elseif (auth('student')->check()) {
            $allowed = $project->group()
                ->where('student_id', auth('student')->id())
                ->exists();
        }

        abort_unless($allowed, 403);

        // عنوان يحمل / أو \ كان يُفشل التنزيل دائماً (اسم مرفق غير صالح)
        $extension = pathinfo($file->path, PATHINFO_EXTENSION);
        $safeTitle = trim(preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]+/u', '-', (string) $file->title), ' .-') ?: 'ملف';
        $downloadName = $safeTitle . '.' . $extension;

        // الملفات الجديدة على القرص الخاص (غير متاح عبر الويب مباشرة)،
        // مع دعم الملفات القديمة المخزنة سابقاً على القرص العام.
        if (Storage::disk('local')->exists($file->path)) {
            return Storage::disk('local')->download($file->path, $downloadName);
        }
        if (Storage::disk('public')->exists($file->path)) {
            return Storage::disk('public')->download($file->path, $downloadName);
        }

        abort(404);
    }

    /**
     * قالب مرحلة من خطة المشرف: للأدمن، ولصاحب الخطة، ولطالب في مجموعة
     * تحمل هذه المرحلة — لا لكل طالب يعرف رقمها.
     */
    public function stageTemplate(SupervisorStage $stage)
    {
        abort_unless($stage->hasTemplate(), 404);

        $allowed = match (true) {
            auth('admin')->check() => true,
            auth('supervisor')->check() => (int) $stage->supervisor_id === (int) auth('supervisor')->id(),
            auth('student')->check() => $stage->milestones()
                ->whereHas('project.group', fn ($q) => $q->where('student_id', auth('student')->id()))
                ->exists(),
            default => false,
        };

        abort_unless($allowed, 403);
        abort_unless(Storage::disk('local')->exists($stage->template_path), 404);

        return Storage::disk('local')->download($stage->template_path, $stage->template_name ?: 'قالب');
    }

    /** ملف تسليم مرحلة: للأدمن، ولمشرف المشروع، ولأعضاء فريقه */
    public function submissionFile(MilestoneSubmission $submission)
    {
        abort_unless($submission->hasFile(), 404);

        $project = $submission->milestone->project;

        $allowed = match (true) {
            auth('admin')->check() => true,
            auth('supervisor')->check() => (int) $project->supervisor_id === (int) auth('supervisor')->id()
                || $project->hasCommitteeMember((int) auth('supervisor')->id()),
            auth('student')->check() => $project->group()->where('student_id', auth('student')->id())->exists(),
            default => false,
        };

        abort_unless($allowed, 403);
        abort_unless(Storage::disk('local')->exists($submission->file_path), 404);

        return Storage::disk('local')->download($submission->file_path, $submission->file_name ?: 'تسليم');
    }
}
