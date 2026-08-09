<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Supervisor;
use App\Notifications\StudentReplayProjectNotify;
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
                    $q->where('semester_id', $semester_id)
                        ->with(['group' => function ($q) {
                            $q->with('student');
                        }]);
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

    public function showNotification()
    {
        // تُعلَّم كمقروءة: إشعارات الإدارة وإشعارات نشاط المشاريع.
        // أما إشعارات طلبات المشاريع فتبقى غير مقروءة حتى يُبَتّ فيها (قبول/رفض)
        // لأن عدّاد "الطلبات المعلقة" يعتمد عليها.
        auth()->user()->unreadNotifications()
            ->whereIn('type', [
                "App\Notifications\AdminChangeGroupNotify",
                "App\Notifications\ProjectActivityNotify",
            ])
            ->update(['read_at' => now()]);

        return view('dashboard.supervisor.requestProjects');
    }

    public function replayProject($project_id, $notify_id)
    {

        $project = Project::where('id', $project_id)
            ->where('supervisor_id', auth()->id())
            ->with(['group' => function ($q) {
                $q->with('student');
            }])
            ->first();

        if (!$project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        try {
            // 'accept', 'reject'
            $isAccept = (bool) request()->btnAccept;
            $data = [
                'status' => $isAccept ? 'accept' : 'reject',
            ];

            // سبب الرفض (اختياري) — يُرسل للطلاب ضمن الإشعار
            $reason = trim((string) request('reason', ''));
            $reason = mb_substr($reason, 0, 500);

            DB::beginTransaction();

            $project->update($data);

            $msg = $isAccept ? 'قبول' : 'رفض';

            $notifyMsg = "تم {$msg} فكرة المشروع";
            if (! $isAccept && $reason !== '') {
                $notifyMsg .= ' — السبب: ' . $reason;
            }

            $notify = [
                'project' => $project->title,
                'supervisor_name' => auth()->user()->name,
                'msg' => $notifyMsg,
            ];
            foreach ($project->group as $gp) {
                Notification::send($gp->student, new StudentReplayProjectNotify($notify));
            }

            $notification = auth()->user()->notifications()->where('id', $notify_id)->first();
            if ($notification) {
                $notification->markAsRead();
            }

            DB::commit();

            $supervisor = Supervisor::where('id', auth()->id())
                ->withCount('projectsAccept')
                ->with(['projects' => function ($q) {
                    $q->with(['group' => function ($q) {
                        $q->with('student');
                    }]);

                }])
                ->first();

            if ($supervisor->max_group == $supervisor->projects_accept_count) {

                foreach ($supervisor->projects->where('status', 'request') as $req) {
                    $req->update([
                        'status' => 'reject',
                    ]);
                    $notify = [
                        'project' => $req->title,
                        'supervisor_name' => auth()->user()->name,
                        'msg' => "تم رفض المشروع بسبب اكتمال مجموعات المشرف",
                    ];

                    foreach ($req->group as $gp) {
                        Notification::send($gp->student, new StudentReplayProjectNotify($notify));
                    }

                }

                $supervisor->unreadNotifications()->update(['read_at' => now()]);

            }

            return redirect()->back()->with('success', "تم {$msg} المشروع بنجاح");

        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

    }

    public function compoleteProject($project_id)
    {

        $project = Project::where('id', $project_id)
            ->where('supervisor_id', auth()->id())
            ->with(['group' => function ($q) {
                $q->with('student');
            }])
            ->first();

        if (!$project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        try {

            DB::beginTransaction();

            $project->update([
                'status' => 'complete',
            ]);

            $notify = [
                'project' => $project->title,
                'supervisor_name' => auth()->user()->name,
                'msg' => "تم اكمال فكرة المشروع",
            ];
            foreach ($project->group as $gp) {
                Notification::send($gp->student, new StudentReplayProjectNotify($notify));
            }

            DB::commit();

            return redirect()->back()->with('success', "تم اكمال المشروع بنجاح");

        } catch (\Exception $ex) {
            DB::rollBack();
            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

    }
}
