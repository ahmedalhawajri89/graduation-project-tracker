<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Supervisor;
use App\Notifications\StudentReplayProjectNotify;
use App\Support\Audit;
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
                        ]);
                },
            ])
            ->first();

    }

    public function index()
    {

        $semester = Semester::current();
        $supervisor = $this->getInfo($semester->id);
        $data['supervisor'] = $supervisor;
        $data['semester'] = $semester;

        return view('dashboard.supervisor.index', $data);
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

        return view('dashboard.supervisor.requestProjects', [
            'requests' => $requests,
            'updates' => $updates,
            'seatsLeft' => $supervisor->seatsLeft(),
            'maxGroup' => (int) $supervisor->max_group,
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
            return redirect()->back()->with('fail', 'الطلب غير موجود أو رُدّ عليه من قبل.');
        }

        $isAccept = (bool) request()->btnAccept;

        // لا قبول بلا مقعد — كان يُقبل فوق الحدّ، والرفض التلقائي يقارن
        // بعدد كل الفصول فلا يعمل
        if ($isAccept && $supervisor->seatsLeft() <= 0) {
            return redirect()->back()->with('fail', 'اكتمل حدّ مجموعاتك لهذا الفصل — القبول يحتاج رفع الحدّ من الإدارة.');
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
                    throw new \DomainException('رُدّ على هذا الطلب من قبل.');
                }
                if ($isAccept && $supervisor->seatsLeft() <= 0) {
                    throw new \DomainException('اكتمل حدّ مجموعاتك لهذا الفصل — القبول يحتاج رفع الحدّ من الإدارة.');
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

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

        foreach ($outbox as [$target, $text]) {
            try {
                $this->notifyTeam($target, $text);
            } catch (\Exception $ex) {
                \Illuminate\Support\Facades\Log::warning('حُفظ القرار وتعذّر إشعار الفريق', ['project' => $target->id, 'exception' => $ex]);
            }
        }

        return redirect()->back()->with('success', "تم {$msg} المشروع بنجاح"
            . ($autoRejected ? " — واكتمل حدّك فرُفض {$autoRejected} طلب معلّق تلقائياً" : ''));
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
            return redirect()->back()->with('fail', 'المشروع غير موجود.');
        }

        if ($project->status !== 'accept') {
            return redirect()->back()->with('fail', $project->status === 'complete'
                ? 'المشروع مكتمل من قبل.'
                : 'لا يُكمَل إلا مشروع مقبول.');
        }

        $project->update(['status' => 'complete']);
        Audit::record('project.statusChanged', $project, ['status' => ['from' => 'accept', 'to' => 'complete']]);

        // بعد الحفظ: فشل البريد كان يُرجع الإكمال كلّه ويقول «حدث خطأ»
        try {
            $this->notifyTeam($project, 'تم اكمال فكرة المشروع');
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::warning('أُكمل المشروع وتعذّر إشعار الفريق', ['project' => $project->id, 'exception' => $ex]);
        }

        return redirect()->back()->with('success', 'اكتمل المشروع — يمكنك الآن رصد التقييم.');
    }
}
