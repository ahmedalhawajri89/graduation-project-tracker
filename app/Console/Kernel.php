<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        // عامل واحد يخرج حين يفرغ الطابور ولا يتداخل مع سابقه. كان
        // ‎queue:work‎ بلا حدّ كل دقيقة: عامل جديد كل دقيقة لا يخرج أبداً،
        // فتتراكم العمليات حتى تنفد الذاكرة، و‎snapshot:capture‎ لا يصل دوره.
        // على خادم فيه Supervisor أو systemd يُفضَّل عامل دائم هناك وحذف هذا السطر.
        $schedule->command('queue:work --stop-when-empty --max-time=50 --tries=3')
            ->everyMinute()
            ->withoutOverlapping()
            ->runInBackground();

        // لقطة إحصائيات يومية (لمؤشّرات الاتجاه في لوحة التحكم)
        $schedule->command('snapshot:capture')->dailyAt('23:55');

        // تذكير الفرق بالمراحل قبل موعدها بيومين وفي يومه — صباحاً ليبقى وقت للتسليم
        $schedule->command('milestones:remind')->dailyAt('09:00')->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
