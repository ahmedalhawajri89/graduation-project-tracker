<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Defense;
use App\Models\DefenseMember;
use App\Support\DefenseGrading;
use App\Support\DefenseScheduler;
use Illuminate\Http\Request;

/**
 * «مناقشاتي»: كل مناقشة المشرفُ عضوٌ في لجنتها — مشرفاً لمشروعه أو ممتحناً
 * لغيره. أجندتها، وملف المشروع للتحضير، ورصد درجته بعد بدئها.
 */
class DefenseController extends Controller
{
    private function me(): int
    {
        return (int) auth('supervisor')->id();
    }

    public function index()
    {
        abort_unless(DefenseScheduler::enabled(), 404);

        $defenses = Defense::whereIn('status', [Defense::SCHEDULED, Defense::DONE])
            ->whereHas('members', fn ($q) => $q->where('supervisor_id', $this->me()))
            ->with(['project.group.student', 'project.project_type', 'members.supervisor', 'room'])
            ->orderBy('starts_at')
            ->get()
            ->each(fn ($d) => $d->setRelation('mine', $d->members->firstWhere('supervisor_id', $this->me())));

        // للرصد: بدأت ولم أرصد، ودرجتها لم تُعتمد — أولى ما ينتظرني
        $toGrade = $defenses->filter(fn ($d) => $d->starts_at->isPast() && is_null($d->mine->grade) && ! $d->project->isGradeLocked());
        $upcoming = $defenses->filter(fn ($d) => $d->starts_at->isFuture());
        $graded = $defenses->filter(fn ($d) => ! is_null($d->mine->grade))->sortByDesc('starts_at');

        return view('dashboard.supervisor.defenses', compact('toGrade', 'upcoming', 'graded'));
    }

    public function show(Defense $defense)
    {
        abort_unless(DefenseScheduler::enabled(), 404);
        $mine = $this->membership($defense);

        $defense->load([
            'room', 'members.supervisor.specialize',
            'project.group.student', 'project.group.roles', 'project.project_type.specialize',
            'project.milestones', 'project.files', 'project.supervisor',
        ]);

        // تقييم مستقلّ: درجة الزميل تُكشف لمن رصد درجته فقط
        $revealed = ! is_null($mine->grade) || $defense->project->isGradeLocked();

        return view('dashboard.supervisor.defense', [
            'defense' => $defense,
            'project' => $defense->project,
            'mine' => $mine,
            'revealed' => $revealed,
            'blocker' => DefenseGrading::blocker($defense),
        ]);
    }

    public function grade(Request $request, Defense $defense)
    {
        $mine = $this->membership($defense);
        $defense->load('project');

        if ($reason = DefenseGrading::blocker($defense)) {
            return back()->with('fail', $reason);
        }

        $data = $request->validate([
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ], [], ['grade' => 'الدرجة', 'comments' => 'الملاحظات']);

        $result = DefenseGrading::record($mine, (float) $data['grade'], $data['comments'] ?? null);

        return back()->with('success', $result['final']
            ? 'رُصدت درجتك واكتملت درجات اللجنة — الدرجة النهائية ' . rtrim(rtrim(number_format($result['grade'], 2, '.', ''), '0'), '.') . '. أُشعر الفريق.'
            : 'رُصدت درجتك. تكتمل درجة المشروع حين يرصد زميلك في اللجنة درجته.');
    }

    /** العضوية شرط الدخول: غير العضو كأن المناقشة غير موجودة */
    private function membership(Defense $defense): DefenseMember
    {
        $mine = $defense->members()->where('supervisor_id', $this->me())->first();
        abort_unless($mine && $defense->status !== Defense::CANCELLED, 404);

        return $mine;
    }
}
