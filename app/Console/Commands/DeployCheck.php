<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Semester;
use App\Support\DefenseScheduler;
use Illuminate\Console\Command;

/**
 * فحص جاهزية الخادم قبل الإطلاق.
 *
 * كل ما في قسم النشر في README يُفحص هنا آلياً بدل أن يُتذكَّر: بيئة
 * الإنتاج، والأمان، والبريد، والطابور، والترحيلات، والبيانات الأساسية،
 * والمسارات القابلة للكتابة، وحدود الرفع، والكاش. لا يطبع أي سرّ — قيماً
 * عامة فقط. ويخرج بفشل إن وُجد خلل حرج، فيصلح خطوةً في سكربت النشر.
 */
class DeployCheck extends Command
{
    protected $signature = 'deploy:check';

    protected $description = 'يفحص جاهزية الخادم للإطلاق (البيئة، الأمان، البريد، الطابور، الترحيلات، الرفع)';

    /** @var array<int, array{0: string, 1: string, 2: string, 3: string}> [الحالة، القسم، البند، التفاصيل] */
    private array $rows = [];

    public function handle(): int
    {
        $this->environment();
        $this->security();
        $this->mail();
        $this->queue();
        $this->data();
        $this->storage();
        $this->uploads();
        $this->caches();

        $this->table(['', 'القسم', 'البند', 'التفاصيل'], $this->rows);

        $fail = collect($this->rows)->where(0, '✗')->count();
        $warn = collect($this->rows)->where(0, '!')->count();

        if ($fail) {
            $this->error("{$fail} خلل حرج يجب إصلاحه قبل الإطلاق" . ($warn ? "، و{$warn} تنبيه." : '.'));

            return self::FAILURE;
        }

        $this->info('جاهز للإطلاق' . ($warn ? " — مع {$warn} تنبيه يُستحسن مراجعته." : '.'));

        return self::SUCCESS;
    }

    private function row(bool|string $ok, string $section, string $item, string $detail = ''): void
    {
        $mark = $ok === true ? '✓' : ($ok === 'warn' ? '!' : '✗');
        $this->rows[] = [$mark, $section, $item, $detail];
    }

    private function environment(): void
    {
        $env = (string) config('app.env');
        $this->row($env === 'production', 'البيئة', 'APP_ENV=production', $env);
        $this->row(! config('app.debug'), 'البيئة', 'APP_DEBUG=false', config('app.debug') ? 'مفعّل — صفحة الخطأ تكشف تتبّع الكود' : 'معطّل');
        $this->row(filled(config('app.key')), 'البيئة', 'APP_KEY', filled(config('app.key')) ? 'موجود' : 'شغّل php artisan key:generate');

        $url = (string) config('app.url');
        $this->row(str_starts_with($url, 'https://') ? true : 'warn', 'البيئة', 'APP_URL بـ https', $url ?: 'فارغ — روابط البريد تُبنى منه');
    }

    private function security(): void
    {
        $this->row(config('session.secure') ? true : 'warn', 'الأمان', 'SESSION_SECURE_COOKIE=true', config('session.secure') ? 'مفعّل' : 'ملفّ الجلسة يُرسل بلا HTTPS');

        $level = (string) config('logging.channels.' . config('logging.default') . '.level', config('app.log_level', 'debug'));
        $this->row($level !== 'debug' ? true : 'warn', 'الأمان', 'LOG_LEVEL ليس debug', $level);
    }

    private function mail(): void
    {
        $mailer = (string) config('mail.default');
        $this->row(! in_array($mailer, ['log', 'array'], true), 'البريد', 'MAIL_MAILER حقيقي', $mailer . (in_array($mailer, ['log', 'array'], true) ? ' — لا يُرسل شيء' : ''));

        $from = (string) config('mail.from.address');
        // نطاقات لا بريد لها: الرسائل تُرفض أو تذهب إلى البريد المزعج
        $fake = $from === '' || (bool) preg_match('/@(example\.(com|org|net)|[^@]+\.(local|test|invalid|localhost))$/i', $from);
        $this->row(! $fake, 'البريد', 'MAIL_FROM_ADDRESS', $fake ? 'غير مضبوط' : $from);
    }

    private function queue(): void
    {
        $queue = (string) config('queue.default');
        $this->row($queue !== 'sync' ? true : 'warn', 'الطابور', 'QUEUE_CONNECTION ليس sync', $queue . ($queue === 'sync' ? ' — البريد يُرسل أثناء الطلب فيبطئه' : ''));
        $this->row('warn', 'الطابور', 'cron: schedule:run كل دقيقة', 'لا يُفحص آلياً — تحقّق من crontab (التذكيرات 08:00 و09:00 واللقطة 23:55)');
    }

    private function data(): void
    {
        $this->row(DefenseScheduler::enabled(), 'البيانات', 'الترحيلات مطبّقة', DefenseScheduler::enabled() ? 'جداول المناقشات موجودة' : 'شغّل php artisan migrate --force');

        $admins = rescue(fn () => Admin::count(), 0, false);
        $this->row($admins > 0, 'البيانات', 'مسؤول نظام', $admins ? "{$admins}" : 'شغّل php artisan admin:create');

        $semester = rescue(fn () => Semester::where('is_active', true)->first(), null, false);
        // الطرفية لا تحتاج علامات العزل (LRI/PDI) التي في label
        $semesterName = $semester ? trim(implode(' ', array_filter($semester->parts()))) : null;
        $this->row($semester ? true : 'warn', 'البيانات', 'فصل دراسي نشط', $semesterName ?? 'فعّل الفصل من الإدارة › الفصول');
    }

    private function storage(): void
    {
        foreach (['storage' => storage_path(), 'bootstrap/cache' => base_path('bootstrap/cache'), 'public/uploads' => public_path('uploads')] as $name => $path) {
            $ok = is_dir($path) && is_writable($path);
            $this->row($ok, 'الملفات', "{$name} قابل للكتابة", $ok ? 'نعم' : (is_dir($path) ? 'غير قابل للكتابة' : 'غير موجود'));
        }
    }

    private function uploads(): void
    {
        $bytes = function (string $v): int {
            $n = (int) $v;

            return match (strtolower(substr(trim($v), -1))) {
                'g' => $n * 1024 ** 3, 'm' => $n * 1024 ** 2, 'k' => $n * 1024, default => $n,
            };
        };
        $upload = (string) ini_get('upload_max_filesize');
        $post = (string) ini_get('post_max_size');
        $this->row($bytes($upload) >= 12 * 1024 ** 2 ? true : 'warn', 'الرفع', 'upload_max_filesize ≥ 12M', $upload . ' (الملفات حتى 10MB)');
        $this->row($bytes($post) >= 16 * 1024 ** 2 ? true : 'warn', 'الرفع', 'post_max_size ≥ 16M', $post);
    }

    private function caches(): void
    {
        $config = app()->configurationIsCached();
        $routes = app()->routesAreCached();
        $this->row($config ? true : 'warn', 'الأداء', 'config:cache', $config ? 'مخزّن' : 'شغّل php artisan config:cache');
        $this->row($routes ? true : 'warn', 'الأداء', 'route:cache', $routes ? 'مخزّن' : 'شغّل php artisan route:cache');
    }
}
