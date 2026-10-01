<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Supervisor;
use App\Notifications\StudentReplayProjectNotify;
use App\Support\Audit;
use App\Support\Discussion;
use App\Support\ProjectActivity;
use App\Support\ProjectSimilarity;
use App\Support\StagePlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:supervisor');
    }

    private function getInfo($semester_id = 0)
    {
        return Supervisor::where('id', auth()->id())
            ->with([
                'specialize' => function ($q) use ($semester_id) {
                    $q->with([
                        'projects',
                        'supervisorsAvailable' => function ($q) use ($semester_id) {
                            $q->withCount(['projectsAccept' => function ($q) use ($semester_id) {
                                $q->where('semester_id', $semester_id);
                            }]);
                        },
                    ]);
                },
                'projectsAccept' => function ($q) use ($semester_id) {
                    // \u200Emilestones\u200E و\u200Ecomments\u200E تُحمَّل هنا: لوح «ما يحتاجني
                    // الآن» يقرؤهما لكل مجموعة، وبدونها استعلامان لكل
                    // صفّ — ستة مشاريع تعني اثني عشر استعلاماً زائداً
                    $q->where('semester_id', $semester_id)
                        ->with([
                            'group.student',
                            'milestones',
                            // \u200Ereorder\u200E لا \u200Elatest()\u200E: العلاقة مرتّبة تصاعدياً، و\u200Elatest()\u200E
                            // يضيف ترتيباً ثانياً فيُعيد **أقدم** تعليق لا آخره
                            'comments' => fn ($c) => $c->reorder('id', 'desc')->limit(1),
                            'project_type',
                        ])
                        // آخر نشاط في البطاقة: آخر ملف مرفوع بلا تحميل الملفات
                        ->withMax('files as last_file_at', 'created_at');
                },
            ])
            ->first();

    }

    public function index()
    {

        $semester = Semester::current();
        $supervisor = $this->getInfo($semester->id);
        $groups = $supervisor->projectsAccept;
        $ids = $groups->pluck('id');

        // المتأخّر أولاً، ثم الأقرب موعداً — كان ترتيب العلاقة، فالمتأخّر والمستقرّ سواء
        $ranked = $groups->sortBy(function ($project) {
            $days = $project->days_left;

            return [$this->lateCount($project) ? 0 : 1, is_null($days) ? 9999 : $days];
        })->values();

        // من حالة المشروع لا من الإشعار: إشعار مقروء كان يُخفي طلباً معلّقاً
        $requests = $supervisor->pendingRequests()->with(['group.student', 'project_type'])->oldest()->get();

        $unread = Discussion::unreadFor($supervisor, $ids);
        $withStages = $groups->filter(fn ($p) => ! is_null($p->progress));
        $late = $groups->sum(fn ($p) => $this->lateCount($p));

        $stages = $supervisor->stages()->where('semester_id', $semester->id)->get();

        return view('dashboard.supervisor.index', [
            'supervisor' => $supervisor,
            'semester' => $semester,
            'groups' => $groups,
            'ranked' => $ranked,
            'requests' => $requests,
            'unread' => $unread,
            'kpi' => [
                'groups' => $groups->count(),
                'max' => (int) $supervisor->max_group,
                'seats' => $supervisor->seatsLeft(),
                'avg' => $withStages->isEmpty() ? null : (int) round($withStages->avg('progress')),
                'withStages' => $withStages->count(),
                'late' => $late,
                'lateGroups' => $groups->filter(fn ($p) => $this->lateCount($p))->count(),
                'unread' => array_sum($unread),
                'completed' => $groups->where('status', 'complete')->count(),
            ],
            'hasPlan' => $stages->isNotEmpty(),
            'upcoming' => $this->upcoming($stages, $groups),
            'activity' => ProjectActivity::recent($ids, $supervisor),
        ]);
    }

    private function lateCount(Project $project): int
    {
        return $project->milestones->filter(
            fn ($m) => $m->isLate()
        )->count();
    }

    /**
     * القادم خلال أسبوعين: مراحل الخطة، والمواعيد النهائية للمجموعات.
     *
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function upcoming($stages, $groups)
    {
        $until = today()->addDays(14);
        $active = $groups->where('status', 'accept');

        $soonStages = $stages->filter(fn ($s) => $s->due_date && $s->due_date->betweenIncluded(today(), $until));
        $progress = StagePlan::progress($soonStages, $active);

        $items = $soonStages->map(fn ($s) => [
            'date' => $s->due_date,
            'title' => $s->title,
            'kind' => 'stage',
            'done' => $progress[$s->id]['done'] ?? 0,
            'total' => $progress[$s->id]['total'] ?? 0,
            'href' => route('supervisor.plan'),
        ]);

        $deadlines = $active
            ->filter(fn ($p) => $p->date_line && $p->date_line->betweenIncluded(today(), $until))
            ->map(fn ($p) => [
                'date' => $p->date_line,
                'title' => __('التسليم النهائي — :title', ['title' => $p->title]),
                'kind' => 'deadline',
                'href' => route('supervisor.projects.show', $p->id),
            ]);

        // مناقشاته: مشرفاً لمشروعه أو ممتحناً لغيره
        $me = auth('supervisor')->id();
        $defenses = \App\Support\DefenseScheduler::enabled()
            ? \App\Models\Defense::active()
                ->whereBetween('starts_at', [now()->startOfDay(), $until->copy()->endOfDay()])
                ->whereHas('members', fn ($q) => $q->where('supervisor_id', $me))
                ->with(['project', 'room', 'members'])
                ->get()
                ->map(fn ($d) => [
                    'date' => $d->starts_at,
                    'title' => __('مناقشة — :title', ['title' => $d->project->title]),
                    'kind' => 'defense',
                    'role' => optional($d->members->firstWhere('supervisor_id', $me))->role_label,
                    'meta' => $d->starts_at->format('H:i') . ' · ' . $d->place_label,
                    'link' => $d->needsLink() ? $d->meeting_url : null,
                    'href' => (int) $d->project->supervisor_id === (int) $me
                        ? route('supervisor.projects.show', $d->project_id)
                        : $d->googleCalendarUrl(),
                ])
            : collect();

        return $items->concat($deadlines)->concat($defenses)->sortBy(fn ($i) => $i['date']->timestamp)->values()->take(5);
    }

    /**
     * طلبات الإشراف — قرارات تنتظر المشرف، لا صندوق إشعارات.
     *
     * الطلبات من حالة المشروع (\u200Erequest\u200E)، والتحديثات الأخرى تحتها.
     * كانت الطلبات تُقرأ من الإشعارات غير المقروءة، وزرّا القبول والرفض
     * مطويّان في أكورديون تحت جدولَي إشعارات.
     */
    public function showNotification()
    {
        $supervisor = auth('supervisor')->user();

        $requests = $supervisor->pendingRequests()
            ->with(['group.student', 'project_type', 'semester'])
            ->oldest()
            ->get();

        // تُعلَّم مقروءة عند الفتح: تحديثات النشاط وتغيير المجموعات.
        // إشعار الطلب يُعلَّم عند الردّ عليه وحده.
        $updates = $supervisor->notifications()
            ->whereIn('type', [
                \App\Notifications\AdminChangeGroupNotify::class,
                \App\Notifications\ProjectActivityNotify::class,
            ])
            ->latest()
            ->take(15)
            ->get();

        $supervisor->unreadNotifications()
            ->whereIn('type', [
                \App\Notifications\AdminChangeGroupNotify::class,
                \App\Notifications\ProjectActivityNotify::class,
            ])
            ->update(['read_at' => now()]);

        // إشعار طلب لم يعد مشروعه معلّقاً (رُدّ عليه، أو غيّره الأدمن، أو
        // حُذف) كان يبقى غير مقروء إلى الأبد فيعدّه الجرس بلا طلب خلفه
        $pendingIds = $requests->pluck('id')->all();
        $supervisor->unreadNotifications()
            ->where('type', \App\Notifications\SuperVisorRequestProjectNotify::class)
            ->get()
            ->reject(fn ($n) => in_array((int) ($n->data['project_id'] ?? 0), $pendingIds, true))
            ->each->markAsRead();

        // «هل نُفّذت هذه الفكرة؟» — يُسأل الطالب عنها وهو يكتب، والمشرف أولى
        // بها وهو يقرّر. الطلبات المعلّقة قليلة، فاستعلام لكلٍّ مقبول
        $similar = $requests->mapWithKeys(fn ($p) => [
            $p->id => ProjectSimilarity::find($p->title, $p->project_type?->specialize_id, 2),
        ]);

        $semester = Semester::current();

        // ما قرّره هذا الفصل: المقبول والمرفوض، الأحدث أولاً
        $decisions = Project::where('supervisor_id', $supervisor->id)
            ->where('semester_id', $semester->id)
            ->whereIn('status', ['accept', 'complete', 'reject'])
            ->with(['group' => fn ($q) => $q->where('type', 'leader')->with('student:id,name')])
            ->latest('updated_at')
            ->limit(8)
            ->get(['id', 'title', 'status', 'updated_at']);

        return view('dashboard.supervisor.requestProjects', [
            'requests' => $requests,
            'updates' => $updates,
            'similar' => $similar,
            'decisions' => $decisions,
            'seatsLeft' => $supervisor->seatsLeft(),
            'maxGroup' => (int) $supervisor->max_group,
            'acceptedCount' => $supervisor->projectsAccept()->where('semester_id', $semester->id)->count(),
            'hasPlan' => $supervisor->stages()->where('semester_id', $semester->id)->exists(),
        ]);
    }

    public function replayProject($project_id)
    {
        $supervisor = auth('supervisor')->user();

        $project = Project::where('id', $project_id)
            ->where('supervisor_id', $supervisor->id)
            ->where('status', 'request')
            ->with('group.student')
            ->first();

        if (! $project) {
            return redirect()->back()->with('fail', __('الطلب غير موجود أو رُدّ عليه من قبل.'));
        }

        $isAccept = (bool) request()->btnAccept;

        // لا قبول بلا مقعد — كان يُقبل فوق الحدّ، والرفض التلقائي يقارن
        // بعدد كل الفصول فلا يعمل
        if ($isAccept && $supervisor->seatsLeft() <= 0) {
            return redirect()->back()->with('fail', __('اكتمل حدّ مجموعاتك لهذا الفصل — القبول يحتاج رفع الحدّ من الإدارة.'));
        }

        // سبب الرفض (اختياري) — يُرسل للطلاب ضمن الإشعار
        $reason = mb_substr(trim((string) request('reason', '')), 0, 500);
        $msg = $isAccept ? 'قبول' : 'رفض';

        // الإشعارات تُجمَع وتُرسل بعد الالتزام: كان البريد داخل المعاملة، فتعطّل
        // SMTP يُرجع القرار كلّه بعد أن يكون بعض الطلاب قد بلغهم «قُبل»
        $outbox = [];

        try {
            $autoRejected = DB::transaction(function () use ($project, $supervisor, $isAccept, $reason, $msg, &$outbox) {
                // قفل صفّ المشرف ثم الطلب: قبولان متزامنان كانا يقرآن «مقعد واحد»
                // معاً فيتجاوزان الحدّ، وطلب واحد كان يُردّ عليه مرّتين
                Supervisor::whereKey($supervisor->id)->lockForUpdate()->first();
                $locked = Project::whereKey($project->id)->where('status', 'request')->lockForUpdate()->first();

                if (! $locked) {
                    throw new \DomainException(__('رُدّ على هذا الطلب من قبل.'));
                }
                if ($isAccept && $supervisor->seatsLeft() <= 0) {
                    throw new \DomainException(__('اكتمل حدّ مجموعاتك لهذا الفصل — القبول يحتاج رفع الحدّ من الإدارة.'));
                }

                $to = $isAccept ? 'accept' : 'reject';
                $project->update(['status' => $to]);
                Audit::record('project.statusChanged', $project, ['status' => ['from' => 'request', 'to' => $to]]);

                // المقبولة تأخذ خطة المراحل كاملة لحظة قبولها
                if ($isAccept) {
                    StagePlan::applyTo($project);
                }
                $outbox[] = [$project, "تم {$msg} فكرة المشروع" . (! $isAccept && $reason !== '' ? ' — السبب: ' . $reason : '')];
                $this->markRequestRead($project->id);

                // آخر مقعد: الطلبات الباقية تُرفض ويُبلَّغ أصحابها
                $count = 0;
                if ($isAccept && $supervisor->seatsLeft() <= 0) {
                    foreach ($supervisor->pendingRequests()->with('group.student')->lockForUpdate()->get() as $req) {
                        $req->update(['status' => 'reject']);
                        Audit::record('project.statusChanged', $req, ['status' => ['from' => 'request', 'to' => 'reject', 'reason' => 'seats_full']]);
                        $outbox[] = [$req, 'تم رفض المشروع بسبب اكتمال مجموعات المشرف'];
                        $this->markRequestRead($req->id);
                        $count++;
                    }
                }

                return $count;
            });
        } catch (\DomainException $ex) {
            return redirect()->back()->with('fail', $ex->getMessage());
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error('فشل الردّ على طلب إشراف', ['project' => $project->id, 'exception' => $ex]);

            return back()->with('fail', __('حدث خطأ .. الرجاء المحاولة مرة أخرى'));
        }

        foreach ($outbox as [$target, $text]) {
            try {
                $this->notifyTeam($target, $text);
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::warning('حُفظ القرار وتعذّر إشعار الفريق', ['project' => $target->id, 'exception' => $ex]);
            }
        }

        return redirect()->back()->with('success', ($isAccept ? __('تم قبول المشروع بنجاح') : __('تم رفض المشروع بنجاح'))
            . ($autoRejected ? ' — ' . __('واكتمل حدّك فرُفض :n طلب معلّق تلقائياً', ['n' => $autoRejected]) : ''));
    }

    private function notifyTeam(Project $project, string $msg): void
    {
        $notify = [
            'project' => $project->title,
            'supervisor_name' => auth('supervisor')->user()->name,
            'msg' => $msg,
        ];

        foreach ($project->group as $gp) {
            if ($gp->student) {
                Notification::send($gp->student, new StudentReplayProjectNotify($notify));
            }
        }
    }

    /** إشعار هذا الطلب وحده — كان الردّ الأخير يُعلِّم كل الإشعارات مقروءة */
    private function markRequestRead(int $projectId): void
    {
        auth('supervisor')->user()->unreadNotifications()
            ->where('type', \App\Notifications\SuperVisorRequestProjectNotify::class)
            ->get()
            ->filter(fn ($n) => (int) ($n->data['project_id'] ?? 0) === $projectId)
            ->each->markAsRead();
    }

    /**
     * إكمال المشروع — من «مقبول» وحده.
     *
     * كانت الملكية وحدها تُفحص: مشروع معلّق يُكمَل فيتخطّى القبول والمقاعد،
     * ومرفوض يُحيا وطلابه ربما في مشروع جديد فيصيرون في مشروعين.
     */
    public function compoleteProject($project_id)
    {
        $project = Project::where('id', $project_id)
            ->where('supervisor_id', auth('supervisor')->id())
            ->with('group.student')
            ->first();

        if (! $project) {
            return redirect()->back()->with('fail', __('المشروع غير موجود.'));
        }

        if ($project->status !== 'accept') {
            return redirect()->back()->with('fail', $project->status === 'complete'
                ? __('المشروع مكتمل من قبل.')
                : __('لا يُكمَل إلا مشروع مقبول.'));
        }

        $project->update(['status' => 'complete']);
        Audit::record('project.statusChanged', $project, ['status' => ['from' => 'accept', 'to' => 'complete']]);

        // بعد الحفظ: فشل البريد كان يُرجع الإكمال كلّه ويقول «حدث خطأ»
        try {
            $this->notifyTeam($project, 'تم اكمال فكرة المشروع');
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::warning('أُكمل المشروع وتعذّر إشعار الفريق', ['project' => $project->id, 'exception' => $ex]);
        }

        return redirect()->back()->with('success', __('اكتمل المشروع — يمكنك الآن رصد التقييم.'));
    }
}
