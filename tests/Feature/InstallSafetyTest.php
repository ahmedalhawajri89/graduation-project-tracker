<?php

namespace Tests\Feature;

use App\Models\Admin;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Database\Seeders\StudentSeeder;
use Database\Seeders\SupervisorSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * سلامة التثبيت والاختبار.
 *
 * تعمل على قاعدة ‎*_testing‎ (يحرسها ‎TestCase‎)، ومع ذلك يُحذف ما يُنشأ.
 */
class InstallSafetyTest extends TestCase
{
    private const EMAIL = 'first.admin@example.test';

    protected function tearDown(): void
    {
        Admin::where('email', self::EMAIL)->delete();

        parent::tearDown();
    }

    /** كانت الاختبارات تعمل على قاعدة التطوير */
    public function test_tests_run_on_a_separate_database(): void
    {
        $this->assertStringEndsWith('_testing', DB::connection()->getDatabaseName());
    }

    /**
     * نسخة الاختبار تحمل المفاتيح الأجنبية كالتطوير. كان test:prepare ينسخ
     * بـ CREATE TABLE … LIKE التي لا تنسخها، فلا يعمل فيها CASCADE ولا SET NULL.
     */
    public function test_the_test_copy_keeps_foreign_keys(): void
    {
        $count = DB::selectOne(
            'SELECT COUNT(*) AS n FROM information_schema.REFERENTIAL_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = ?',
            [DB::connection()->getDatabaseName()]
        )->n;

        $this->assertGreaterThan(0, (int) $count, 'قاعدة الاختبار بلا مفاتيح أجنبية — أعد test:prepare --force.');
    }

    /** في الإنتاج: لا حسابات تجريبية بكلمة سر معروفة، ولا DemoSeeder */
    public function test_production_seeds_no_demo_accounts_or_demo_data(): void
    {
        $production = DatabaseSeeder::seedersFor('production');

        foreach ([AdminSeeder::class, SupervisorSeeder::class, StudentSeeder::class, DemoSeeder::class] as $demo) {
            $this->assertNotContains($demo, $production, class_basename($demo) . ' يُزرع في الإنتاج.');
        }

        $this->assertContains(DemoSeeder::class, DatabaseSeeder::seedersFor('local'));
    }

    public function test_admin_create_makes_an_admin_with_a_hashed_password(): void
    {
        $this->artisan('admin:create', [
            '--name' => 'أدمن أول',
            '--email' => self::EMAIL,
            '--password' => 'a-strong-pass',
        ])->assertSuccessful();

        $admin = Admin::where('email', self::EMAIL)->first();
        $this->assertNotNull($admin);
        $this->assertTrue(Hash::check('a-strong-pass', $admin->password));
        $this->assertNotSame('a-strong-pass', $admin->password);
    }

    public function test_admin_create_rejects_a_short_password_and_a_duplicate_email(): void
    {
        $this->artisan('admin:create', ['--name' => 'x', '--email' => self::EMAIL, '--password' => 'short'])
            ->assertFailed();
        $this->assertNull(Admin::where('email', self::EMAIL)->first());

        $existing = Admin::value('email');
        $this->artisan('admin:create', ['--name' => 'x', '--email' => $existing, '--password' => 'long-enough-1'])
            ->assertFailed();
        $this->assertSame(1, Admin::where('email', $existing)->count());
    }

    /** فوق حدّ الدخول (٦٠) يُرفض — كلمة لا يقبلها الدخول تُقفل صاحبها خارج حسابه */
    public function test_admin_create_rejects_a_password_the_login_would_refuse(): void
    {
        $this->artisan('admin:create', ['--name' => 'x', '--email' => self::EMAIL, '--password' => str_repeat('a', 61)])
            ->assertFailed();
    }
}
