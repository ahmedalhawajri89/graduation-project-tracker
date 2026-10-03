<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * لقطة النسخة التجريبية: الحالة المنسّقة (مناقشات، رسائل، ملفات، درجات)
 * تُحفظ مرة وتُعاد كل ليلة — البذور وحدها لا تُنتج هذه الحالة.
 *
 * كل جدول ملف JSON مضغوط، والملفات المرفوعة نسخة كما هي. عند الإعادة تُزاح
 * كل التواريخ بأسابيع كاملة بقدر ما مضى منذ اللقطة: المناقشة تبقى «قادمة»،
 * والمواعيد منطقية، والأحد يبقى أحداً (أيام الدوام في config/defenses).
 */
class DemoSnapshot
{
    /** جداول لا معنى لحفظها: طوابير وذاكرة مؤقتة ورموز استعادة */
    public const SKIP = ['jobs', 'failed_jobs', 'job_batches', 'cache', 'cache_locks', 'sessions', 'password_resets', 'password_reset_tokens'];

    private const DATE = '/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/';

    public function __construct(private string $path) {}

    public static function default(): self
    {
        return new self(database_path('demo'));
    }

    public function exists(): bool
    {
        return File::exists($this->path . '/meta.json');
    }

    /** @return array{taken_at: string, tables: string[]} */
    public function meta(): array
    {
        return json_decode(File::get($this->path . '/meta.json'), true);
    }

    /**
     * @param  string[]|null  $only  جداول بعينها (للاختبار)، وإلا كل الجداول
     * @param  bool  $withFiles  نسخ الملفات المرفوعة من storage/app
     * @return array{tables: int, rows: int, files: int}
     */
    public function capture(?array $only = null, bool $withFiles = true): array
    {
        $tables = $only ?? $this->allTables();
        File::ensureDirectoryExists($this->path . '/db');
        File::cleanDirectory($this->path . '/db');

        $rows = 0;
        foreach ($tables as $table) {
            $data = DB::table($table)->get()->map(fn ($r) => (array) $r)->all();
            $rows += count($data);
            File::put($this->path . "/db/{$table}.json.gz", gzencode(json_encode($data, JSON_UNESCAPED_UNICODE), 9));
        }

        $files = 0;
        if ($withFiles) {
            File::deleteDirectory($this->path . '/files');
            foreach ($this->uploadDirs() as $dir) {
                File::copyDirectory(storage_path("app/{$dir}"), $this->path . "/files/{$dir}");
                $files += count(File::allFiles($this->path . "/files/{$dir}"));
            }
        }

        File::put($this->path . '/meta.json', json_encode(['taken_at' => now()->toIso8601String(), 'tables' => $tables], JSON_PRETTY_PRINT));

        return ['tables' => count($tables), 'rows' => $rows, 'files' => $files];
    }

    /**
     * يعيد الجداول والملفات من اللقطة، ويزيح التواريخ بأسابيع كاملة.
     *
     * @return array{tables: int, rows: int, shifted_days: int}
     */
    public function restore(?Carbon $now = null, bool $withFiles = true): array
    {
        $meta = $this->meta();
        $days = (int) Carbon::parse($meta['taken_at'])->startOfDay()->diffInDays(($now ?? now())->copy()->startOfDay(), false);
        $shift = intdiv(max(0, $days), 7) * 7;

        $rows = 0;
        Schema::disableForeignKeyConstraints();
        try {
            foreach ($meta['tables'] as $table) {
                $file = $this->path . "/db/{$table}.json.gz";
                if (! Schema::hasTable($table) || ! File::exists($file)) {
                    continue;
                }
                // DELETE لا TRUNCATE: الأول داخل المعاملة، والثاني يُنهيها ضمناً
                DB::table($table)->delete();
                $data = json_decode(gzdecode(File::get($file)), true) ?: [];
                foreach (array_chunk($data, 500) as $chunk) {
                    DB::table($table)->insert(array_map(fn ($row) => $this->shift($row, $shift), $chunk));
                }
                $rows += count($data);
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        if ($withFiles && File::isDirectory($this->path . '/files')) {
            foreach ($this->uploadDirs() as $dir) {
                File::deleteDirectory(storage_path("app/{$dir}"));
            }
            File::copyDirectory($this->path . '/files', storage_path('app'));
        }

        return ['tables' => count($meta['tables']), 'rows' => $rows, 'shifted_days' => $shift];
    }

    /** كل قيمة بشكل تاريخ تُزاح — الأعمدة بلا قائمة: ما يُضاف لاحقاً يُزاح أيضاً */
    private function shift(array $row, int $days): array
    {
        if (! $days) {
            return $row;
        }
        foreach ($row as $col => $value) {
            if (is_string($value) && preg_match(self::DATE, $value)) {
                $at = Carbon::parse($value)->addDays($days);
                $row[$col] = strlen($value) === 10 ? $at->toDateString() : $at->toDateTimeString();
            }
        }

        return $row;
    }

    /** @return string[] */
    private function allTables(): array
    {
        $db = DB::connection()->getDatabaseName();

        return collect(DB::select('SELECT TABLE_NAME AS t FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?', [$db, 'BASE TABLE']))
            ->pluck('t')->reject(fn ($t) => in_array($t, self::SKIP, true))->values()->all();
    }

    /** مجلّدات الملفات المرفوعة في storage/app (عدا ما ليس منها) */
    private function uploadDirs(): array
    {
        return collect(File::directories(storage_path('app')))
            ->map(fn ($d) => basename($d))
            ->reject(fn ($d) => in_array($d, ['demo', 'framework', 'livewire-tmp'], true))
            ->values()->all();
    }
}
