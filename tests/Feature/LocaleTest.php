<?php

namespace Tests\Feature;

use App\Models\Admin;
use Tests\TestCase;

/** لغة الواجهة: كوكي locale يقلب الاتجاه وملف Tabler والنصوص، والعربية افتراضاً */
class LocaleTest extends TestCase
{
    public function test_arabic_is_the_default(): void
    {
        $this->get(route('login'))->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('tabler.rtl.min.css', false)
            ->assertSee('تسجيل الدخول');
    }

    public function test_the_cookie_switches_to_english(): void
    {
        $this->withUnencryptedCookie('locale', 'en')->get(route('login'))->assertOk()
            ->assertSee('lang="en" dir="ltr"', false)
            ->assertSee('vendor/tabler/css/tabler.min.css', false)
            ->assertSee('Sign in')
            ->assertDontSee('تسجيل الدخول');
    }

    public function test_an_unknown_locale_falls_back_to_arabic(): void
    {
        $this->withUnencryptedCookie('locale', 'fr')->get(route('login'))->assertOk()->assertSee('dir="rtl"', false);
        $this->get('/locale/fr')->assertNotFound();
    }

    public function test_the_switch_route_sets_the_cookie_and_goes_back(): void
    {
        $this->from(route('login'))->get(route('locale.switch', 'en'))
            ->assertRedirect(route('login'))
            ->assertCookie('locale', 'en', false);
    }

    public function test_the_dashboard_renders_in_english(): void
    {
        $admin = Admin::first();
        if (! $admin) {
            $this->markTestSkipped('لا أدمن.');
        }

        $this->actingAs($admin, 'admin')->withUnencryptedCookie('locale', 'en')
            ->get(route('admin.defenses.index'))->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('Defenses')
            ->assertSee('Auto-schedule all', false);
    }

    public function test_error_pages_follow_the_locale_too(): void
    {
        $this->withUnencryptedCookie('locale', 'en')->get('/no-such-page-' . uniqid())
            ->assertNotFound()->assertSee('dir="ltr"', false);
    }
}
