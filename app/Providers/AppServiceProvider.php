<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Supervisor;
use App\Support\Discussion;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        try {
            $viewSemester = Semester::current();

            View()->share([
                'viewSemester' => $viewSemester,
            ]);
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }

        /*
        |------------------------------------------------------------------
        | بيانات اللايوت المشتركة (هيدر + سايدبار) — تُحسب مرة واحدة للطلب
        |------------------------------------------------------------------
        | بدل استدعاء unreadNotifications ثلاث/أربع مرات لكل صفحة
        | وتحميل كل الإشعارات في الذاكرة، نحسب المطلوب مرة واحدة ونشاركه.
        */
        View::composer(
            [
                'layouts.admin.inc.header',
                'layouts.admin.inc.sidebar',
                'layouts.admin.inc.sidebar.student',
                'layouts.admin.inc.sidebar.supervisor',
            ],
            function ($view) {
                // memo على الطلب لا \u200Estatic\u200E: المتغيّر الساكن يعيش ما عاشت عملية
                // PHP، فيرث الطلب التالي بيانات مستخدم سابق (الاختبارات، Octane)
                $shared = request()->attributes->get('layoutShared');

                if ($shared === null) {
                    $user = auth('admin')->user()
                        ?? auth('supervisor')->user()
                        ?? auth('student')->user();

                    if (! $user) {
                        $shared = false;
                    } else {
                        $shared = [
                            'unreadCount' => $user->unreadNotifications()->count(),
                            'latestNotifications' => $user->notifications()->latest()->take(5)->get(),
                            'studentProject' => null,
                            'discussionUnread' => 0,
                            'pendingRequests' => 0,
                        ];

                        // شارة «طلبات الإشراف»: الطلبات المعلّقة وحدها، لا كل الإشعارات
                        if ($user instanceof Supervisor) {
                            $shared['pendingRequests'] = $user->pendingRequests()->count();
                        }

                        // عدّاد تبويب «النقاش» — بمعزل عن الإشعارات
                        // بنوع المستخدم المختار لا بـ\u200Eauth(guard)->check()\u200E: قد يُسجَّل
                        // أكثر من حارس في الطلب نفسه، فيُستدعى \u200Egroups()\u200E على مشرف
                        // المشرف: قناته وحدها. الطالب: القناتان معاً — تبويب واحد يحملهما
                        if (! $user instanceof Admin) {
                            $ids = Discussion::projectsFor($user)->pluck('id');
                            $shared['discussionUnread'] = array_sum(Discussion::unreadFor($user, $ids))
                                + ($user instanceof Student
                                    ? array_sum(Discussion::unreadFor($user, $ids, \App\Models\ProjectComment::TEAM))
                                    : 0);
                        }

                        // مشروع الطالب النشط (لبطاقة السايدبار)
                        if ($user instanceof Student) {
                            $group = $user->groups()->with('project.milestones')->first();
                            if ($group && $group->project && $group->project->status != 'reject') {
                                $shared['studentProject'] = $group->project;
                            }
                        }
                    }

                    request()->attributes->set('layoutShared', $shared);
                }

                if ($shared !== false) {
                    $view->with('layoutShared', $shared);
                }
            }
        );
    }
}
