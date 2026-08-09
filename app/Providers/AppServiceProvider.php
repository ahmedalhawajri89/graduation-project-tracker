<?php

namespace App\Providers;

use App\Models\Semester;
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
                static $shared = null; // memo لنفس الطلب — يمنع تكرار الاستعلامات

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
                        ];

                        // مشروع الطالب النشط (لبطاقة السايدبار)
                        if (auth('student')->check()) {
                            $group = $user->groups()->with('project.milestones')->first();
                            if ($group && $group->project && $group->project->status != 'reject') {
                                $shared['studentProject'] = $group->project;
                            }
                        }
                    }
                }

                if ($shared !== false) {
                    $view->with('layoutShared', $shared);
                }
            }
        );
    }
}
