<?php

namespace App\Http\Controllers;

use App\Models\FileNote;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\ProjectActivityNotify;
use App\Support\Audit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ملاحظات على ملفات المشروع — للفريق ولمشرفه.
 *
 * «صفحة ٣ ينقصها المرجع — @آية»: الملاحظة على ملفها، تنبّه عضواً بعينه،
 * وتُعلَّم «عولجت» فتصل كاتبَها. مسار واحد للطالب وللمشرف.
 */
class FileNoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student,supervisor');
    }

    /** الطالب أو المشرف — من الحارس المسجَّل */
    private function actor(): Model
    {
        return auth('student')->user() ?? auth('supervisor')->user();
    }

    /** عضو الفريق أو مشرف المشروع وحدهما */
    private function authorizeProject(Project $project, Model $actor): void
    {
        $allowed = $actor instanceof Supervisor
            ? (int) $project->supervisor_id === (int) $actor->id
            : $project->group()->where('student_id', $actor->id)->exists();

        abort_unless($allowed, 403);
    }

    private function blocked(Project $project): ?string
    {
        if (! in_array($project->status, ['accept', 'complete'], true)) {
            return 'تُكتب الملاحظات بعد قبول المشروع.';
        }

        return $project->is_locked ? 'المشروع مؤرشف بعد التقييم — لا ملاحظات جديدة.' : null;
    }

    private function back(ProjectFile $file)
    {
        return redirect()->to(url()->previous() . '#file-' . $file->id);
    }

    public function store(Request $request, ProjectFile $file)
    {
        $project = $file->project;
        $actor = $this->actor();
        $this->authorizeProject($project, $actor);

        if ($reason = $this->blocked($project)) {
            return $this->back($file)->with('fail', $reason);
        }

        $members = $project->group()->pluck('student_id')->map(fn ($id) => (int) $id)->all();

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            // المنبَّه من الفريق نفسه — لا يُنبَّه طالب من خارجه عبر هذا الملف
            'mentioned_id' => ['nullable', 'integer', 'in:' . implode(',', $members ?: [0])],
        ], [
            'mentioned_id.in' => 'العضو المختار ليس من فريق المشروع.',
        ], [
            'body' => 'الملاحظة',
        ]);

        $note = $file->notes()->create([
            'author_type' => $actor::class,
            'author_id' => $actor->id,
            'mentioned_id' => $data['mentioned_id'] ?? null,
            'body' => $data['body'],
        ]);

        Audit::record('file.note', $project, ['file' => ['to' => $file->title]]);

        // المنبَّه أولاً؛ وبلا منبَّه: رافع الملف إن كان طالباً غير الكاتب
        $target = $note->mentioned
            ?? ($file->uploader_type === Student::class ? Student::find($file->uploader_id) : null);

        if ($target && ! $target->is($actor)) {
            $this->notify($target, $project, $actor, 'ملاحظة على «' . $file->title . '»: ' . Str::limit($data['body'], 90));
        }

        return $this->back($file)->with('success', 'أُضيفت الملاحظة' . ($target && ! $target->is($actor) ? ' ووصل ' . $target->name . ' تنبيهٌ بها.' : '.'));
    }

    /** «عولجت» أو إعادة فتحها: الكاتب، والمنبَّه، والقائد، والمشرف */
    public function toggle(FileNote $note)
    {
        $file = $note->file;
        $project = $file->project;
        $actor = $this->actor();
        $this->authorizeProject($project, $actor);

        if ($project->is_locked) {
            return $this->back($file)->with('fail', 'المشروع مؤرشف بعد التقييم.');
        }

        $isLeader = $actor instanceof Student
            && $project->group()->where('student_id', $actor->id)->where('type', 'leader')->exists();

        abort_unless(
            $note->isBy($actor)
                || ($actor instanceof Student && (int) $note->mentioned_id === (int) $actor->id)
                || $isLeader
                || $actor instanceof Supervisor,
            403
        );

        $resolving = $note->isOpen();
        $note->update($resolving
            ? ['resolved_at' => now(), 'resolved_by_type' => $actor::class, 'resolved_by_id' => $actor->id]
            : ['resolved_at' => null, 'resolved_by_type' => null, 'resolved_by_id' => null]);

        // الكاتب يعرف أن ملاحظته عولجت — ما لم يعالجها بنفسه
        if ($resolving && ! $note->isBy($actor) && ($author = $note->author)) {
            $this->notify($author, $project, $actor, 'عولجت ملاحظتك على «' . $file->title . '»');
        }

        return $this->back($file)->with('success', $resolving ? 'عُلّمت الملاحظة: عولجت.' : 'أُعيد فتح الملاحظة.');
    }

    /** الحذف: الكاتب، أو المشرف */
    public function destroy(FileNote $note)
    {
        $file = $note->file;
        $actor = $this->actor();
        $this->authorizeProject($file->project, $actor);

        abort_unless($note->isBy($actor) || $actor instanceof Supervisor, 403);

        $note->delete();

        return $this->back($file)->with('success', 'حُذفت الملاحظة.');
    }

    private function notify(Model $to, Project $project, Model $actor, string $msg): void
    {
        try {
            $to->notify(new ProjectActivityNotify([
                'project' => $project->title,
                'supervisor_name' => $actor->name,
                'msg' => $msg,
            ]));
        } catch (\Exception $ex) {
            Log::warning('تعذّر إشعار بملاحظة ملف', ['project' => $project->id, 'exception' => $ex]);
        }
    }
}
