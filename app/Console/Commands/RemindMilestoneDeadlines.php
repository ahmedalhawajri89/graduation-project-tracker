<?php

namespace App\Console\Commands;

use App\Models\ProjectMilestone;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * تذكير الفرق بمواعيد المراحل: قبل الموعد بيومين، وفي يومه.
 *
 * كانت المنصّة تعرض المرحلة «متأخّرة» بعد فوات موعدها — أي حين لا ينفع
 * التنبيه. التذكير يصل بالبريد وفي الجرس، لما لم يُسلَّم بعد وحده: المسلَّمة
 * بانتظار المراجعة والمعتمدة لا تُذكَّر، ولا مشروع مقيَّم أو غير مقبول.
 */
class RemindMilestoneDeadlines extends Command
{
    protected $signature = 'milestones:remind';

    protected $description = 'تذكير الفرق بالمراحل التي يحلّ موعدها بعد يومين أو اليوم';

    /** قبل الموعد بكم يوم يُرسل التذكير */
    public const DAYS_BEFORE = [2, 0];

    public function handle(): int
    {
        $today = today();
        $dates = collect(self::DAYS_BEFORE)->map(fn ($d) => $today->copy()->addDays($d)->toDateString());

        $milestones = ProjectMilestone::query()
            ->whereIn('status', [ProjectMilestone::OPEN, ProjectMilestone::REVISION])
            ->whereIn('due_date', $dates)
            ->where(fn ($q) => $q->whereNull('reminded_on')->orWhere('reminded_on', '<', $today->toDateString()))
            ->whereHas('project', fn ($q) => $q->where('status', 'accept')->whereNull('grade'))
            ->with('project')
            ->get();

        $sent = 0;

        foreach ($milestones as $milestone) {
            // الحجز قبل الإرسال وبشرط: تشغيلان متزامنان لا يُرسلان مرّتين
            $claimed = ProjectMilestone::whereKey($milestone->id)
                ->where(fn ($q) => $q->whereNull('reminded_on')->orWhere('reminded_on', '<', $today->toDateString()))
                ->update(['reminded_on' => $today->toDateString()]);

            if (! $claimed) {
                continue;
            }

            try {
                $this->remind($milestone);
                $sent++;
            } catch (\Throwable $e) {
                // مجموعة واحدة لا توقف تذكير البقية
                Log::warning('تعذّر تذكير مجموعة بموعد مرحلة', ['milestone' => $milestone->id, 'exception' => $e->getMessage()]);
            }
        }

        $this->info("أُرسل {$sent} تذكيراً.");

        return self::SUCCESS;
    }

    private function remind(ProjectMilestone $milestone): void
    {
        $isToday = $milestone->due_date->isToday();
        $when = $isToday
            ? 'اليوم'
            : 'بعد يومين (' . $milestone->due_date->locale('ar')->translatedFormat('l j F') . ')';

        $msg = $milestone->needsRevision()
            ? "تذكير: آخر موعد لإعادة تسليم مرحلة «{$milestone->title}» بعد التعديل {$when}."
            : "تذكير: آخر موعد لتسليم مرحلة «{$milestone->title}» {$when}.";

        $subject = $isToday
            ? "آخر يوم لتسليم «{$milestone->title}»"
            : "موعد «{$milestone->title}» بعد يومين";

        Notification::send($milestone->project->students(), ProjectActivityNotify::withMail([
            'kind' => 'reminder',
            'project' => $milestone->project->title,
            'supervisor_name' => 'تخرُّج',
            'msg' => $msg,
            'milestone_id' => $milestone->id,
        ], $subject, route('student.dashboard') . '#milestone-' . $milestone->id));
    }
}
