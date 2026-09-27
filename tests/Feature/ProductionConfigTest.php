<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Notifications\ResetPasswordNotify;
use Tests\TestCase;

/**
 * إعداد الإنتاج: صفحات الخطأ، ورابط الاسترجاع، والخروج.
 */
class ProductionConfigTest extends TestCase
{
    /** كانت صفحات Laravel الإنجليزية تظهر في منصّة عربية */
    public function test_a_missing_page_shows_an_arabic_404(): void
    {
        $this->get('/no-such-page-' . uniqid())
            ->assertNotFound()
            ->assertSee('الصفحة غير موجودة')
            ->assertDontSee('Not Found');
    }

    /** كان الرابط يُبنى من ترويسة Host — طلب مزوّر يُرسل للضحية رابطاً إلى نطاق المهاجم */
    public function test_the_reset_link_is_built_from_app_url_not_the_host_header(): void
    {
        config(['app.url' => 'https://takharruj.example']);
        $this->app['request']->headers->set('Host', 'evil.example');
        $this->app['url']->forceRootUrl('https://evil.example');

        $student = Student::first();
        $mail = (new ResetPasswordNotify('token123', 'student'))->toMail($student);

        $this->assertStringStartsWith('https://takharruj.example/', $mail->actionUrl);
        $this->assertStringNotContainsString('evil.example', $mail->actionUrl);

        $this->app['url']->forceRootUrl(null);
    }

    /** الخروج كان يترك الجلسة ورمز CSRF صالحين */
    public function test_logout_ends_the_session(): void
    {
        $student = Student::first();

        $this->actingAs($student, 'student')->get(route('student.dashboard'))->assertOk();
        $tokenBefore = session()->token();

        $this->post(route('logout'))->assertRedirect(route('site.home'));

        $this->assertGuest('student');
        $this->assertNotSame($tokenBefore, session()->token(), 'رمز CSRF لم يتجدّد بعد الخروج.');
        $this->get(route('student.dashboard'))->assertRedirect();
    }
}
