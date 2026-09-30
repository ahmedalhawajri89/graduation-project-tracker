<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * deploy:check — يفشل على إعداد تطوير، ويمرّ على إعداد إنتاج، ولا يقبل
 * عنوان مرسل بنطاق لا بريد له.
 */
class DeployCheckTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function production(array $over = []): void
    {
        config($over + [
            'app.env' => 'production',
            'app.debug' => false,
            'app.url' => 'https://takharruj.example.edu',
            'session.secure' => true,
            'mail.default' => 'smtp',
            'mail.from.address' => 'no-reply@university.edu',
            'queue.default' => 'database',
        ]);
    }

    private function run_check(): array
    {
        $code = Artisan::call('deploy:check');

        return [$code, Artisan::output()];
    }

    public function test_development_settings_fail_the_check(): void
    {
        config(['app.env' => 'local', 'app.debug' => true, 'mail.default' => 'log']);

        [$code, $out] = $this->run_check();

        $this->assertSame(1, $code);
        $this->assertStringContainsString('APP_DEBUG=false', $out);
        $this->assertStringContainsString('خلل حرج', $out);
    }

    public function test_production_settings_pass(): void
    {
        $this->production();

        [$code, $out] = $this->run_check();

        $this->assertSame(0, $code, $out);
        $this->assertStringContainsString('جاهز للإطلاق', $out);
    }

    public function test_a_sender_on_a_non_routable_domain_is_critical(): void
    {
        $this->production(['mail.from.address' => 'no-reply@takharruj.local']);

        [$code, $out] = $this->run_check();

        $this->assertSame(1, $code);
        $this->assertStringContainsString('غير مضبوط', $out);
    }

    public function test_it_never_prints_secrets(): void
    {
        $this->production(['mail.mailers.smtp.password' => 'super-secret-password']);

        [, $out] = $this->run_check();

        $this->assertStringNotContainsString('super-secret-password', $out);
        $this->assertStringNotContainsString((string) config('app.key'), $out);
    }
}
