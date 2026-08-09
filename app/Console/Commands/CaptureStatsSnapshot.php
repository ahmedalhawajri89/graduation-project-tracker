<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\Project;
use App\Models\Semester;
use App\Models\StatSnapshot;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Console\Command;

class CaptureStatsSnapshot extends Command
{
    protected $signature = 'snapshot:capture {--date= : تاريخ اللقطة (Y-m-d)، الافتراضي اليوم}';

    protected $description = 'تسجيل لقطة يومية لإحصائيات النظام لحساب مؤشّرات الاتجاه';

    public function handle()
    {
        $date = $this->option('date') ?: now()->toDateString();
        $semester = Semester::current();
        $semesterId = $semester->id ?? 0;

        $metrics = [
            'students'      => Student::count(),
            'supervisors'   => Supervisor::count(),
            'groups'        => Project::whereIn('status', ['accept', 'complete'])
                                    ->where('semester_id', $semesterId)->count(),
            'messages'      => Contact::count(),
            'has_group'     => Student::has('groups')->count(),
            'not_has_group' => Student::doesntHave('groups')->count(),
        ];

        StatSnapshot::updateOrCreate(['date' => $date], $metrics);

        $this->info("تم تسجيل لقطة الإحصائيات لتاريخ {$date}.");

        return self::SUCCESS;
    }
}
