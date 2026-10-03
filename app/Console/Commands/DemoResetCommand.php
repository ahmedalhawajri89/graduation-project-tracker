<?php

namespace App\Console\Commands;

use App\Support\DemoSnapshot;
use Illuminate\Console\Command;

/**
 * يعيد النسخة التجريبية إلى لقطتها — يُجدوَل كل ليلة حين يكون DEMO_MODE مفعّلاً.
 *
 * يمسح القاعدة ويعيد كتابتها: يرفض العمل خارج وضع العرض مهما كانت الخيارات،
 * فلا يمسح قاعدة حقيقية بخطأ في أمر.
 */
class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'يعيد بيانات النسخة التجريبية وملفاتها من اللقطة (DEMO_MODE وحده)';

    public function handle(): int
    {
        if (! config('demo.enabled')) {
            $this->error('demo:reset يعمل في النسخة التجريبية وحدها (DEMO_MODE=true) — لا يمسح قاعدة حقيقية.');

            return self::FAILURE;
        }

        $snapshot = DemoSnapshot::default();
        if (! $snapshot->exists()) {
            $this->error('لا لقطة في database/demo — أنشئها أولاً: php artisan demo:snapshot');

            return self::FAILURE;
        }

        $r = $snapshot->restore();
        $this->call('cache:clear');
        $this->info("أُعيدت النسخة التجريبية: {$r['tables']} جدولاً، {$r['rows']} صفاً، والتواريخ أُزيحت {$r['shifted_days']} يوماً.");

        return self::SUCCESS;
    }
}
