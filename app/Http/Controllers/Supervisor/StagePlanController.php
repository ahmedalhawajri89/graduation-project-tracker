<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Student;
use App\Models\SupervisorStage;
use App\Notifications\ProjectActivityNotify;
use App\Support\Audit;
use App\Support\StagePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

/**
 * خطة المراحل — المشرف يعرّف المرحلة مرّة بموعدها وتعليماتها وقالبها،
 * فتُنشأ في كل مجموعاته. انظر \App\Support\StagePlan
 */
class StagePlanController extends Controller
{
    private const TEMPLATE_RULE = ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,png,jpg,jpeg'];

    public function __construct()
    {
        $this->middleware('auth:supervisor');
    }

    private function own(SupervisorStage $stage): void
    {
        abort_unless((int) $stage->supervisor_id === (int) auth('supervisor')->id(), 403);
    }

    public function index()
    {
        $supervisor = auth('supervisor')->user();
        $semester = Semester::current();

        $stages = $supervisor->stages()->where('semester_id', $semester->id)->get();

        // المقبولة والمكتملة: المكتملة تُظهر ما أنجزته، ولا تستقبل جديداً
        $projects = Project::where('supervisor_id', $supervisor->id)
            ->where('semester_id', $semester->id)
            ->whereIn('status', ['accept', 'complete'])
            ->with(['group' => fn ($q) => $q->where('type', 'leader')->with('student:id,name')])
            ->orderBy('title')
            ->get(['id', 'title', 'status', 'grade']);

        return view('dashboard.supervisor.plan', [
            'semester' => $semester,
            'stages' => $stages,
            'projects' => $projects,
            'progress' => StagePlan::progress($stages, $projects),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, creating: true);
        $supervisor = auth('supervisor')->user();

        [$stage, $result] = DB::transaction(function () use ($data, $request, $supervisor) {
            $stage = SupervisorStage::create([
                'supervisor_id' => $supervisor->id,
                'semester_id' => Semester::current()->id,
                'title' => $data['title'],
                'instructions' => $data['instructions'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ] + $this->storeTemplate($request, $supervisor->id));

            return [$stage, StagePlan::propagate($stage)];
        });

        $this->notifyGroups($result['created'], 'مرحلة جديدة: ' . $stage->title
            . ($stage->due_date ? ' — حتى ' . $stage->due_date->format('Y-m-d') : '')
            . ($stage->hasTemplate() ? ' (مع قالب من المشرف)' : ''));

        $n = count($result['created']);

        return redirect()->route('supervisor.plan')->with('success', $n
            ? "أُضيفت المرحلة إلى {$n} " . ($n === 1 ? 'مجموعة' : 'مجموعات') . '.'
            : 'أُضيفت المرحلة — وتصل كل مجموعة تقبلها لاحقاً.');
    }

    public function update(Request $request, SupervisorStage $stage)
    {
        $this->own($stage);
        $data = $this->validated($request, creating: false);
        $dueChanged = ($data['due_date'] ?? null) !== $stage->due_date?->format('Y-m-d');

        $result = DB::transaction(function () use ($stage, $data, $request) {
            $fields = [
                'title' => $data['title'],
                'instructions' => $data['instructions'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ];

            if ($request->hasFile('template') || $request->boolean('remove_template')) {
                if ($stage->template_path) {
                    Storage::disk('local')->delete($stage->template_path);
                }
                $fields += ['template_path' => null, 'template_name' => null, 'template_size' => null];
                $fields = array_merge($fields, $this->storeTemplate($request, $stage->supervisor_id));
            }

            $stage->update($fields);

            return StagePlan::propagate($stage);
        });

        if ($dueChanged && $stage->due_date) {
            $affected = $stage->milestones()->where('is_done', false)->pluck('project_id')->all();
            $this->notifyGroups($affected, 'تغيّر موعد مرحلة «' . $stage->title . '» إلى ' . $stage->due_date->format('Y-m-d'));
        }

        return redirect()->route('supervisor.plan')
            ->with('success', 'حُفظت المرحلة' . ($result['updated'] ? " وحُدّثت في {$result['updated']} " . ($result['updated'] === 1 ? 'مجموعة' : 'مجموعات') : '') . '.');
    }

    public function destroy(SupervisorStage $stage)
    {
        $this->own($stage);

        $title = $stage->title;
        $removed = DB::transaction(fn () => StagePlan::remove($stage));
        Audit::record('stage.deleted', null, ['stage' => ['from' => $title], 'removed' => ['to' => $removed]]);

        return redirect()->route('supervisor.plan')
            ->with('success', 'حُذفت المرحلة' . ($removed ? " من {$removed} " . ($removed === 1 ? 'مجموعة' : 'مجموعات') : '') . ' — وما أُنجز منها بقي.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            // موعد جديد لا يكون في الماضي؛ والتعديل يقبل موعداً قائماً مضى
            'due_date' => $creating ? ['nullable', 'date', 'after_or_equal:today'] : ['nullable', 'date'],
            'template' => self::TEMPLATE_RULE,
            'remove_template' => ['nullable', 'boolean'],
        ], [], [
            'title' => 'عنوان المرحلة',
            'instructions' => 'التعليمات',
            'due_date' => 'الموعد',
            'template' => 'القالب',
        ]);
    }

    /** @return array<string, mixed> */
    private function storeTemplate(Request $request, int $supervisorId): array
    {
        if (! $request->hasFile('template')) {
            return [];
        }

        $file = $request->file('template');

        // القرص الخاص: يُنزَّل عبر مسار محميّ لا رابط مباشر
        return [
            'template_path' => $file->store('stage_templates/' . $supervisorId, 'local'),
            'template_name' => mb_substr($file->getClientOriginalName(), 0, 150),
            'template_size' => $file->getSize(),
        ];
    }

    /** @param array<int, int> $projectIds */
    private function notifyGroups(array $projectIds, string $msg): void
    {
        if (! $projectIds) {
            return;
        }

        $projects = Project::whereIn('id', $projectIds)->get(['id', 'title']);
        $name = auth('supervisor')->user()->name;

        foreach ($projects as $project) {
            try {
                Notification::send($project->students(), new ProjectActivityNotify([
                    'project' => $project->title,
                    'supervisor_name' => $name,
                    'msg' => $msg,
                ]));
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::warning('تعذّر إشعار مجموعة بمرحلة', ['project' => $project->id, 'exception' => $ex]);
            }
        }
    }
}
