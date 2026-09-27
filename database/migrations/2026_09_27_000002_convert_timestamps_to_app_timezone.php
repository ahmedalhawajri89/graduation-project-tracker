<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * نقل الأوقات المخزّنة من UTC إلى توقيت التطبيق (\u200EAsia/Gaza\u200E).
 *
 * كان التطبيق بتوقيت UTC وجلسة القاعدة بتوقيت نظام الخادم (SYSTEM)؛ صار
 * بتوقيت المستخدمين وجلسة بإزاحة ثابتة \u200E+00:00\u200E. تُقرأ القيم كما كُتبت (بجلسة
 * SYSTEM)، وتُحوَّل في PHP — فجداول المناطق الزمنية في MySQL غالباً غير محمّلة،
 * والإزاحة تتغيّر بالتوقيت الصيفي — ثم تُكتب بالجلسة الجديدة.
 */
return new class extends Migration
{
    public function up()
    {
        $this->convert('UTC', config('app.timezone'), readTz: 'SYSTEM');
    }

    public function down()
    {
        $this->convert(config('app.timezone'), 'UTC', writeTz: 'SYSTEM');
    }

    private function convert(string $from, string $to, string $readTz = '+00:00', string $writeTz = '+00:00'): void
    {
        if ($from === $to || ! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $schema = DB::getDatabaseName();
        $columns = collect(DB::select(
            "SELECT TABLE_NAME AS t, COLUMN_NAME AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND DATA_TYPE IN ('timestamp', 'datetime') AND TABLE_NAME <> 'migrations'",
            [$schema]
        ))->groupBy('t')->map(fn ($rows) => $rows->pluck('c')->all());

        foreach ($columns as $table => $cols) {
            $key = DB::selectOne(
                "SELECT GROUP_CONCAT(COLUMN_NAME) AS k FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = 'PRIMARY'",
                [$schema, $table]
            )->k;

            // بلا مفتاح أساسي أحادي (جداول استرجاع كلمة السر): رموز مؤقّتة لا تستحق النقل
            if (! $key || str_contains($key, ',')) {
                continue;
            }

            DB::statement("SET time_zone = '{$readTz}'");
            $rows = DB::table($table)->select(array_merge([$key], $cols))->get();
            DB::statement("SET time_zone = '{$writeTz}'");

            foreach ($rows as $row) {
                $changes = [];
                foreach ($cols as $col) {
                    if ($row->{$col} !== null) {
                        $changes[$col] = Carbon::parse($row->{$col}, $from)->setTimezone($to)->format('Y-m-d H:i:s');
                    }
                }

                if ($changes) {
                    DB::table($table)->where($key, $row->{$key})->update($changes);
                }
            }
        }

        // الجلسة كما يضبطها الاتصال
        DB::statement("SET time_zone = '" . config('database.connections.' . DB::getDefaultConnection() . '.timezone', '+00:00') . "'");
    }
};
