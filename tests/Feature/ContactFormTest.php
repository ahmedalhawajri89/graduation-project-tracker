<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * نموذج التواصل في الصفحة العامة.
 *
 * كان يُرسل فتُعاد الصفحة من رأسها — إلى الهيرو — فلا يرى المرسل أن رسالته
 * وصلت. صار يُرسل في الخلفية بجواب JSON، وبلا JavaScript يعود إلى قسمه.
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class ContactFormTest extends TestCase
{
    private array $message = [
        'name' => 'زائر',
        'email' => 'visitor@example.test',
        'subject' => 'سؤال عن المواعيد',
        'message' => 'متى تُفتح خطة المراحل؟',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    public function test_a_background_send_answers_with_json(): void
    {
        $this->postJson(route('site.send'), $this->message)
            ->assertOk()
            ->assertJson(['message' => 'sent']);

        $this->assertTrue(Contact::where('email', 'visitor@example.test')->where('subject', 'سؤال عن المواعيد')->exists());
    }

    /** أخطاء التحقّق مفصّلة بالحقل — تُعرض تحت حقلها لا في رأس الصفحة */
    public function test_a_background_send_returns_field_errors(): void
    {
        $this->postJson(route('site.send'), ['email' => 'not-an-email'] + $this->message)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    /** بلا JavaScript: الرجوع إلى قسم التواصل، لا إلى رأس الصفحة */
    public function test_without_javascript_the_page_returns_to_the_contact_section(): void
    {
        $this->from(route('site.home'))
            ->post(route('site.send'), $this->message)
            ->assertRedirect(route('site.home') . '#contact')
            ->assertSessionHas('success');
    }

    public function test_a_failed_validation_also_returns_to_the_contact_section(): void
    {
        $this->from(route('site.home') . '#services')
            ->post(route('site.send'), ['subject' => ''] + $this->message)
            ->assertRedirect(route('site.home') . '#contact')
            ->assertSessionHasErrors('subject');
    }
}
