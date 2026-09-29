<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Defense;
use App\Models\DefenseRoom;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Supervisor;
use App\Support\Audit;
use App\Support\DefenseNotifier;
use App\Support\DefenseScheduler;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * المناقشات: المشاريع المكتملة تنتظر لجنةً وموعداً، والمجدولة أجندةٌ
 * باليوم. القواعد في DefenseScheduler والإشعارات في DefenseNotifier.
 */
class DefenseController extends Controller
{
    public function index()
    {
        abort_unless(DefenseScheduler::enabled(), 503, 'شغّل الترحيل أولاً: php artisan migrate');

        $tab = in_array(request('tab'), ['awaiting', 'upcoming', 'past'], true) ? request('tab') : null;

        // المكتملة بلا مناقشة قائمة (لا شيء، أو ملغاة) — ودرجتها لم تُعتمد
        $awaiting = DefenseScheduler::awaiting()
            ->with(['group.student', 'supervisor.specialize', 'project_type', 'defense'])
            ->withCount(['milestones', 'milestones as milestones_done' => fn ($q) => $q->where('is_done', true)])
            ->latest('updated_at')
            ->get();

        $with = ['project.group.student', 'project.project_type', 'members.supervisor', 'room'];

        $upcoming = Defense::active()
            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration_minutes MINUTE) >= ?', [now()])
            ->with($with)->orderBy('starts_at')->get();

        $past = Defense::query()
            ->where(fn ($q) => $q->whereIn('status', [Defense::DONE, Defense::CANCELLED])
                ->orWhereRaw('DATE_ADD(starts_at, INTERVAL duration_minutes MINUTE) < ?', [now()]))
            ->with($with)->orderByDesc('starts_at')->limit(60)->get();

        $rooms = DefenseRoom::withCount(['defenses as upcoming_count' => fn ($q) => $q->active()->where('starts_at', '>=', now())])
            ->orderByDesc('is_active')->orderBy('name')->get();

        // الممتحنون المحتملون مرّة واحدة؛ النافذة ترتّبهم لكل مشروع (التخصص أولاً)
        $examiners = Supervisor::with('specialize')
            ->withCount(['defenseMemberships as upcoming_defenses' => fn ($q) => $q
                ->whereHas('defense', fn ($d) => $d->active()->where('starts_at', '>=', now()))])
            ->orderBy('name')->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'specialize_id' => $s->specialize_id,
                'specialize' => $s->specialize->name ?? '',
                'load' => $s->upcoming_defenses,
            ])->values();

        return view('dashboard.admin.defense.index', [
            'tab' => $tab ?? ($awaiting->count() ? 'awaiting' : 'upcoming'),
            'awaiting' => $awaiting,
            'upcoming' => $upcoming,
            'past' => $past,
            'rooms' => $rooms,
            'examiners' => $examiners,
            'stats' => [
                'awaiting' => $awaiting->count(),
                'today' => $upcoming->filter(fn ($d) => $d->starts_at->isToday())->count(),
                'week' => $upcoming->filter(fn ($d) => $d->starts_at->lte(now()->addDays(7)))->count(),
                'rooms' => $rooms->where('is_active', true)->count(),
            ],
        ]);
    }

    /** جدولة مناقشة لمشروع مكتمل — أو إعادة جدولة الملغاة منها */
    public function store(Request $request)
    {
        $project = Project::with('project_type')->findOrFail($request->input('project_id'));
        $data = $this->validated($request);

        if ($errors = DefenseScheduler::conflicts($project, $data)) {
            return $this->back($errors, $project->id);
        }

        $defense = DB::transaction(function () use ($project, $data) {
            $defense = Defense::updateOrCreate(['project_id' => $project->id], $this->attributes($data) + [
                'status' => Defense::SCHEDULED,
                'reminded_on' => null,
                'scheduled_by' => auth('admin')->id(),
            ]);
            DefenseScheduler::syncMembers($defense, $project, $data['examiner_id']);
            // مناقشة ملغاة يُعاد استعمال صفّها: لا تُحمل درجاتها القديمة إلى الجديدة
            $defense->members()->update(['grade' => null, 'comments' => null, 'graded_at' => null]);

            return $defense;
        });

        Audit::record('defense.scheduled', $project, ['defense' => ['to' => DefenseNotifier::when($defense->fresh('room'))]]);
        DefenseNotifier::scheduled($defense->fresh(['room', 'members.supervisor', 'project.group']));

        return redirect()->route('admin.defenses.index', ['tab' => 'upcoming'])
            ->with('success', 'جُدولت المناقشة وأُشعر الفريق واللجنة.');
    }

    /** إعادة جدولة: موعد أو مكان أو ممتحن آخر */
    public function update(Request $request, Defense $defense)
    {
        $project = $defense->project()->with('project_type')->firstOrFail();

        if ($defense->members()->whereNotNull('grade')->exists()) {
            return back()->with('fail', 'بدأت اللجنة رصد الدرجات — لا يُعدَّل موعد مناقشة جرت.');
        }

        $data = $this->validated($request);

        if ($errors = DefenseScheduler::conflicts($project, $data, $defense)) {
            return $this->back($errors, $project->id, $defense->id);
        }

        $before = DefenseNotifier::when($defense->load('room'));

        DB::transaction(function () use ($defense, $project, $data) {
            $defense->update($this->attributes($data) + ['status' => Defense::SCHEDULED, 'reminded_on' => null]);
            DefenseScheduler::syncMembers($defense, $project, $data['examiner_id']);
        });

        $defense = $defense->fresh(['room', 'members.supervisor', 'project.group']);
        Audit::record('defense.rescheduled', $project, ['defense' => ['from' => $before, 'to' => DefenseNotifier::when($defense)]]);
        DefenseNotifier::rescheduled($defense, $before);

        return redirect()->route('admin.defenses.index', ['tab' => 'upcoming'])
            ->with('success', 'عُدّل موعد المناقشة وأُشعر الفريق واللجنة.');
    }

    public function cancel(Request $request, Defense $defense)
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:300']], [], ['reason' => 'سبب الإلغاء']);

        if ($defense->status !== Defense::SCHEDULED) {
            return back()->with('fail', 'المناقشة ليست مجدولة.');
        }

        if ($defense->members()->whereNotNull('grade')->exists()) {
            return back()->with('fail', 'بدأت اللجنة رصد الدرجات — لا تُلغى مناقشة جرت.');
        }

        $defense->update(['status' => Defense::CANCELLED]);
        $defense->load(['room', 'members.supervisor', 'project.group']);

        Audit::record('defense.cancelled', $defense->project, array_filter([
            'defense' => ['from' => DefenseNotifier::when($defense)],
            'reason' => $request->reason ? ['to' => $request->reason] : null,
        ]));
        DefenseNotifier::cancelled($defense, $request->reason);

        return redirect()->route('admin.defenses.index', ['tab' => 'awaiting'])
            ->with('success', 'أُلغيت المناقشة، وعاد المشروع إلى «بانتظار الجدولة».');
    }

    /* ==================== القاعات ==================== */

    public function storeRoom(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('defense_rooms', 'name')],
            'location' => ['nullable', 'string', 'max:120'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ], [], ['name' => 'اسم القاعة', 'location' => 'المكان', 'capacity' => 'السعة']);

        DefenseRoom::create($data + ['is_active' => true]);

        return redirect()->route('admin.defenses.index', ['tab' => request('tab')])->with('success', 'أُضيفت القاعة.');
    }

    public function toggleRoom(DefenseRoom $room)
    {
        if ($room->is_active && $room->defenses()->active()->where('starts_at', '>=', now())->exists()) {
            return back()->with('fail', 'على القاعة مناقشات قادمة — أعد جدولتها أولاً.');
        }

        $room->update(['is_active' => ! $room->is_active]);

        return back()->with('success', $room->is_active ? 'فُعّلت القاعة.' : 'عُطّلت القاعة — لن تظهر في الجدولة.');
    }

    /** الحذف لقاعة بلا مناقشات؛ ذات السجلّ تُعطَّل ليبقى تاريخها */
    public function destroyRoom(DefenseRoom $room)
    {
        if ($room->defenses()->exists()) {
            return back()->with('fail', 'للقاعة مناقشات مسجّلة — عطّلها بدل حذفها.');
        }

        $room->delete();

        return back()->with('success', 'حُذفت القاعة.');
    }

    /* ==================== مساعدات ==================== */

    private function validated(Request $request): array
    {
        $v = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', Rule::in(Defense::DURATIONS)],
            'mode' => ['required', Rule::in(array_keys(Defense::MODES))],
            'room_id' => ['nullable', 'integer', 'exists:defense_rooms,id'],
            'meeting_url' => ['nullable', 'required_if:mode,online,hybrid', 'url:http,https', 'max:500'],
            'examiner_id' => ['required', 'integer', 'exists:supervisors,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'meeting_url.required_if' => 'رابط الاجتماع مطلوب للمناقشة عن بُعد أو المدمجة.',
            'meeting_url.url' => 'رابط الاجتماع غير صالح — انسخه كاملاً من Google Meet أو غيره.',
        ], [
            'date' => 'التاريخ', 'time' => 'الوقت', 'duration_minutes' => 'المدة', 'mode' => 'النوع',
            'room_id' => 'القاعة', 'meeting_url' => 'رابط الاجتماع', 'examiner_id' => 'الممتحن', 'notes' => 'الملاحظات',
        ]);

        $v['starts_at'] = Carbon::createFromFormat('Y-m-d H:i', $v['date'] . ' ' . $v['time']);
        $v['duration_minutes'] = (int) $v['duration_minutes'];
        $v['room_id'] = in_array($v['mode'], ['in_person', 'hybrid'], true) ? ($v['room_id'] ?? null) : null;
        $v['meeting_url'] = in_array($v['mode'], ['online', 'hybrid'], true) ? ($v['meeting_url'] ?? null) : null;
        $v['examiner_id'] = (int) $v['examiner_id'];

        return $v;
    }

    private function attributes(array $d): array
    {
        return [
            'starts_at' => $d['starts_at'],
            'duration_minutes' => $d['duration_minutes'],
            'mode' => $d['mode'],
            'room_id' => $d['room_id'],
            'meeting_url' => $d['meeting_url'],
            'notes' => $d['notes'] ?? null,
        ];
    }

    /** أخطاء التعارض تحت حقولها، والنافذة تُفتح من جديد على المشروع نفسه */
    private function back(array $errors, int $projectId, ?int $defenseId = null)
    {
        $map = ['project' => 'date', 'supervisor' => 'time'];
        $bag = [];
        foreach ($errors as $key => $msg) {
            $bag[$map[$key] ?? $key] = $msg;
        }

        return back()->withErrors($bag)->withInput()
            ->with('defense_modal', ['project' => $projectId, 'defense' => $defenseId]);
    }
}
