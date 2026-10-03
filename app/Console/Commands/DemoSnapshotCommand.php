<?php

namespace App\Console\Commands;

use App\Support\DemoSnapshot;
use Illuminate\Console\Command;

/**
 * يحفظ الحالة الحالية لقطةً للنسخة التجريبية في database/demo — تُشغَّل مرة
 * على بيانات العرض المنسّقة، وتُرفع مع المشروع، فيعيدها demo:reset كل ليلة.
 */
class DemoSnapshotCommand extends Command
{
    protected $signature = 'demo:snapshot {--no-files : الجداول وحدها بلا الملفات المرفوعة}';

    protected $description = 'يحفظ قاعدة البيانات والملفات المرفوعة لقطةً للنسخة التجريبية (database/demo)';

    public function handle(): int
    {
        if (app()->environment('production') && ! config('demo.enabled')) {
            $this->error('لا لقطة من خادم إنتاج حقيقي — بياناته ليست بيانات عرض.');

            return self::FAILURE;
        }

        $r = DemoSnapshot::default()->capture(null, ! $this->option('no-files'));
        $this->info("حُفظت اللقطة: {$r['tables']} جدولاً، {$r['rows']} صفاً، {$r['files']} ملفاً — في database/demo.");

        return self::SUCCESS;
    }
}
