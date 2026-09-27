<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * التوقيت: «اليوم» يُحسب بتوقيت المستخدمين، وجلسة القاعدة بإزاحة ثابتة.
 */
class TimezoneTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_the_app_runs_on_the_users_timezone(): void
    {
        $this->assertSame('Asia/Gaza', config('app.timezone'));
        $this->assertSame('Asia/Gaza', date_default_timezone_get());
    }

    /** كانت SYSTEM: قيم TIMESTAMP المقروءة تتبع توقيت نظام الخادم */
    public function test_the_database_session_has_a_fixed_offset(): void
    {
        $this->assertSame('+00:00', DB::selectOne('SELECT @@session.time_zone AS tz')->tz);
    }

    /** بعد منتصف ليل غزة وقبل منتصف ليل UTC: اليوم هو يوم المستخدم */
    public function test_today_follows_the_users_midnight(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-27 22:30:00', 'UTC')); // \u200E01:30\u200E في غزة

        $this->assertSame('2026-09-28', today()->toDateString());
    }
}
