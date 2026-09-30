<?php

namespace App\Console\Commands;

use App\Models\Defense;
use App\Support\DefenseNotifier;
use App\Support\DefenseScheduler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * تذكير الفريق ولجنته بالمناقشة: قبلها بيوم، وفي يومها صباحاً.
 *
 * على نمط تذكير المراحل (milestones:remind): الصفّ يُحجز بتحديث شرطي
 * قبل الإرسال، فتشغيلان متزامنان أو مكرّران في اليوم نفسه لا يُرسلان مرّتين.
 */
class RemindDefenses extends Command
{
    protected $signature = 'defenses:remind';

    protected $description = 'تذكير الفرق ولجان المناقشة بالمناقشات التي موعدها غداً أو اليوم';

    public const DAYS_BEFORE = [1, 0];

    public function handle(): int
    {
        if (! DefenseScheduler::enabled()) {
            $this->warn('جداول المناقشات غير موجودة — شغّل الترحيل أولاً.');

            return self::SUCCESS;
        }

        $today = today();
        $sent = 0;

        $defenses = Defense::active()
            ->where(function ($q) use ($today) {
                foreach (self::DAYS_BEFORE as $d) {
                    $q->orWhereDate('starts_at', $today->copy()->addDays($d)->toDateString());
                }
            })
            ->where(fn ($q) => $q->whereNull('reminded_on')->orWhere('reminded_on', '<', $today->toDateString()))
            ->get();

        foreach ($defenses as $defense) {
            $claimed = Defense::whereKey($defense->id)
                ->where(fn ($q) => $q->whereNull('reminded_on')->orWhere('reminded_on', '<', $today->toDateString()))
                ->update(['reminded_on' => $today->toDateString()]);

            if (! $claimed) {
                continue;
            }

            try {
                DefenseNotifier::reminder($defense, $defense->starts_at->isToday());
                $sent++;
                // قبلها بيوم: من لم يرفع عرضه يُنبَّه وفي الوقت متّسع
                if (! $defense->starts_at->isToday() && ! $defense->project?->presentation) {
                    DefenseNotifier::presentationMissing($defense);
                }
            } catch (\Throwable $e) {
                Log::warning('تعذّر التذكير بمناقشة', ['defense' => $defense->id, 'exception' => $e->getMessage()]);
            }
        }

        $this->info("أُرسل {$sent} تذكيراً بالمناقشات.");

        return self::SUCCESS;
    }
}
