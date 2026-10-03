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

        /*
        | الترجمة: النصّ العربي نفسه مفتاح (__('المناقشات'))، فلا ملف ar.json
        | والعربية لا تنكسر أبداً. الإنجليزية في ملف JSON لكل جزء من الواجهة
        | (resources/lang/json/<الجزء>/en.json) بدل ملف واحد يتضارب فيه الجميع.
        */
        foreach (glob(resource_path('lang/json/*'), GLOB_ONLYDIR) ?: [] as $dir) {
            $this->app['translator']->addJsonPath($dir);
        }

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
                'layouts.admin.inc.sidebar.admin',
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
                        // العدّادات من مصدر واحد مع التحديث الحيّ (LiveController)
                        $counts = \App\Support\LiveCounts::for($user);
                        $shared = [
                            'counts' => $counts,
                            'unreadCount' => $counts['notifications'],
                            'latestNotifications' => $user->notifications()->latest()->take(5)->get(),
                            'studentProject' => null,
                            'discussionUnread' => (int) $counts['discussion'],
                            'pendingRequests' => (int) $counts['requests'],
                        ];

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
