<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * قاعدة الاختبار: نسخة من قاعدة التطوير باسم ‎<الاسم>_testing‎.
 *
 * كانت الاختبارات تعمل على قاعدة التطوير نفسها: تشغيل ينقطع في منتصفه يترك
 * بياناتها معدّلة، وتشغيلها على خادم إعداده للإنتاج يعدّل الإنتاج. والاختبارات
 * تعتمد على بيانات حقيقية (مشاريع ومشرفين وطلاب) لا تُنتجها البذور، فقاعدة
 * فارغة كانت ستتخطّاها كلّها وتنجح نجاحاً كاذباً — لذا نسخة لا قاعدة فارغة.
 *
 * النسخ على الخادم نفسه بـ SQL وحده — بلا mysqldump.
 */
class PrepareTestDatabase extends Command
{
    protected $signature = 'test:prepare {--force : أعد النسخ ولو كانت قاعدة الاختبار موجودة}';

    protected $description = 'ينسخ قاعدة التطوير إلى <الاسم>_testing لتعمل عليها الاختبارات';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('لا يُشغَّل في الإنتاج.');

            return self::FAILURE;
        }

        $connection = config('database.default');
        $source = config("database.connections.{$connection}.database");
        $target = $source . '_testing';

        // حارس: لا يُكتب إلا في قاعدة تنتهي بـ _testing وتختلف عن المصدر
        if (! str_ends_with($target, '_testing') || $target === $source || ! preg_match('/^[A-Za-z0-9_]+$/', $target)) {
            $this->error("اسم هدف غير آمن: {$target}");

            return self::FAILURE;
        }

        $exists = DB::select('SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?', [$target]);

        if ($exists && ! $this->option('force')) {
            $this->info("قاعدة الاختبار موجودة: {$target} — استعمل --force لإعادة نسخها.");

            return self::SUCCESS;
        }

        DB::statement("DROP DATABASE IF EXISTS `{$target}`");
        DB::statement("CREATE DATABASE `{$target}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $tables = collect(DB::select('SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?', [$source, 'BASE TABLE']))
            ->pluck('TABLE_NAME');

        // اتصال بقاعدة الاختبار: SHOW CREATE TABLE يُخرج أسماء الجداول المرجعيّة
        // بلا قاعدة، فيُنفَّذ هناك لتُنشأ فيها لا في المصدر
        config(["database.connections.test_prepare" => array_merge(
            config("database.connections.{$connection}"),
            ['database' => $target]
        )]);
        $targetDb = DB::connection('test_prepare');
        $targetDb->statement('SET FOREIGN_KEY_CHECKS = 0');

        try {
            foreach ($tables as $table) {
                // SHOW CREATE TABLE لا CREATE TABLE … LIKE: الثانية لا تنسخ المفاتيح
                // الأجنبية، فكانت قاعدة الاختبار بلا أيّ منها — ولا يعمل فيها
                // ON DELETE CASCADE ولا SET NULL كما في التطوير
                $create = (array) DB::selectOne("SHOW CREATE TABLE `{$source}`.`{$table}`");
                $targetDb->statement($create['Create Table']);
                $targetDb->statement("INSERT INTO `{$target}`.`{$table}` SELECT * FROM `{$source}`.`{$table}`");
            }
        } finally {
            $targetDb->statement('SET FOREIGN_KEY_CHECKS = 1');
            DB::purge('test_prepare');
        }

        $this->info("نُسخت {$tables->count()} جداول من {$source} إلى {$target}.");

        return self::SUCCESS;
    }
}
