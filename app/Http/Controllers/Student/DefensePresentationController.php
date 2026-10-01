<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Defense;
use App\Models\ProjectFile;
use App\Models\Student;
use App\Support\DefenseNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * العرض التقديمي للمناقشة: يرفعه قائد الفريق من بطاقة «مناقشتك» ويستبدله
 * حتى بدء المناقشة، فتجده اللجنة أعلى صفحة المناقشة لا بين ملفات المشروع.
 */
class DefensePresentationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    public function store(Request $request, Defense $defense)
    {
        $project = $defense->project;
        $group = $project->group()->get();
        $me = $group->firstWhere('student_id', auth('student')->id());
        abort_unless($me, 403);

        // القائد وحده — وفريق بلا قائد يرفع أي عضو فيه
        if ($me->type !== 'leader' && $group->contains('type', 'leader')) {
            return back()->with('fail', __('قائد الفريق هو من يرفع العرض التقديمي.'));
        }

        if ($defense->status !== Defense::SCHEDULED || $defense->starts_at->isPast()) {
            return back()->with('fail', __('يُرفع العرض التقديمي قبل بدء المناقشة.'));
        }

        $request->validate([
            'presentation' => ['required', 'file', 'max:20480', 'mimes:pdf,ppt,pptx'],
        ], [
            'presentation.mimes' => __('العرض التقديمي يكون PDF أو PowerPoint.'),
            'presentation.max' => __('حجم العرض التقديمي لا يزيد على 20 ميغابايت.'),
        ], ['presentation' => __('العرض التقديمي')]);

        $previous = $project->presentation;
        $upload = $request->file('presentation');
        $path = $upload->store(ProjectFile::PRESENTATION_DIR . '/' . $project->id, 'local');

        $project->files()->create([
            'title' => 'العرض التقديمي',
            'path' => $path,
            'size' => $upload->getSize(),
            'uploader_type' => Student::class,
            'uploader_id' => auth('student')->id(),
        ]);

        if ($previous) {
            Storage::disk('local')->delete($previous->path);
            $previous->delete();
        }

        DefenseNotifier::presentationUploaded($defense, auth('student')->user()->name, (bool) $previous);

        return back()->with('success', $previous ? __('استُبدل العرض التقديمي وأُشعرت اللجنة.') : __('رُفع العرض التقديمي وأُشعرت اللجنة.'));
    }
}
