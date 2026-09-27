<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\ResetPasswordNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * استرجاع كلمة المرور.
 *
 * \u200ENotification::fake()\u200E يمنع أي إرسال فعلي.
 *
 * ولا شيء يبقى في القاعدة: الرموز تُمسح قبل كل طلب وبعده، وكلمات
 * المرور تُستعاد باستعلام مباشر — لأن \u200Ephpunit.xml\u200E لا يضبط قاعدة
 * اختبار منفصلة.
 */
class PasswordResetTest extends TestCase
{
    /** @var array<int, array{0: string, 1: string}> جداول ورسائل بريد نُظّفها */
    private array $issued = [];

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    protected function tearDown(): void
    {
        foreach ($this->issued as [$table, $email]) {
            DB::table($table)->where('email', $email)->delete();
        }

        parent::tearDown();
    }

    /**
     * يطلب رابطاً بعد مسح أي رمز سابق.
     *
     * \u200EsendResetLink\u200E يخنق الطلبات المتتالية لنفس البريد (\u200Ethrottle\u200E ٦٠
     * ثانية في \u200Econfig/auth.php\u200E)، فاختباران متتاليان لنفس الحساب
     * يفشل ثانيهما بصمت — لا لخلل بل لأن الخنق يعمل.
     */
    private function ask(string $identify, string $table, string $email)
    {
        DB::table($table)->where('email', $email)->delete();
        $this->issued[] = [$table, $email];

        return $this->post(route('password.email'), ['identify' => $identify]);
    }

    private function student(): Student
    {
        $student = Student::whereNotNull('email')->first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        return $student;
    }

    // ═══ طلب الرابط ═══

    public function test_the_forgot_page_loads_and_login_links_to_it(): void
    {
        $this->get(route('password.request'))->assertOk()->assertSee('نسيت كلمة السر');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(route('password.request'), false);
    }

    /** البريد يصل إلى صاحبه بالدور الصحيح */
    public function test_a_student_receives_a_reset_link_by_email(): void
    {
        $student = $this->student();

        $this->ask($student->email, 'password_reset_students', $student->email)
            ->assertRedirect();

        Notification::assertSentTo(
            $student,
            ResetPasswordNotify::class,
            fn ($notification) => $notification->guard === 'student'
        );
    }

    /** والرقم الجامعي يعمل كالبريد، كما في تسجيل الدخول */
    public function test_a_student_can_ask_with_their_university_id(): void
    {
        $student = $this->student();

        $this->ask($student->university_id, 'password_reset_students', $student->email)
            ->assertRedirect();

        Notification::assertSentTo($student, ResetPasswordNotify::class);
    }

    public function test_a_supervisor_gets_the_supervisor_guard(): void
    {
        $supervisor = Supervisor::whereNotNull('email')->first();

        if (! $supervisor) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        $this->ask($supervisor->email, 'password_reset_supervisors', $supervisor->email);

        Notification::assertSentTo(
            $supervisor,
            ResetPasswordNotify::class,
            fn ($notification) => $notification->guard === 'supervisor'
        );
    }

    public function test_an_admin_gets_the_admin_guard(): void
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن.');
        }

        $this->ask($admin->email, 'password_reset_admins', $admin->email);

        Notification::assertSentTo(
            $admin,
            ResetPasswordNotify::class,
            fn ($notification) => $notification->guard === 'admin'
        );
    }

    /**
     * الردّ واحد سواء وُجد الحساب أو لم يوجد: ردّ مختلف يحوّل النموذج
     * إلى أداة تُعدّد البُرد والأرقام الجامعية المسجّلة.
     */
    public function test_an_unknown_account_gets_the_same_answer(): void
    {
        $student = $this->student();

        $known = $this->ask($student->email, 'password_reset_students', $student->email);
        $unknown = $this->post(route('password.email'), ['identify' => 'nobody-here@example.test']);

        $knownMsg = $known->getSession()->get('success');
        $unknownMsg = $unknown->getSession()->get('success');

        $this->assertNotNull($knownMsg);
        $this->assertSame($knownMsg, $unknownMsg, 'الردّ يختلف، فيكشف وجود الحساب.');
    }

    /** ولا يُرسَل شيء لحساب لا وجود له */
    public function test_an_unknown_account_receives_nothing(): void
    {
        $this->post(route('password.email'), ['identify' => 'nobody-here@example.test'])
            ->assertRedirect();

        Notification::assertNothingSent();
    }

    // ═══ الجداول منفصلة ═══

    /**
     * الحُرّاس الثلاثة كانوا يتشاركون جدول \u200Epassword_resets\u200E واحداً
     * مفهرساً بالبريد، ورمز أحدهم يُبطل رمز الآخر.
     */
    public function test_each_guard_writes_to_its_own_table(): void
    {
        foreach (['students', 'supervisors', 'admins'] as $broker) {
            $table = config("auth.passwords.{$broker}.table");

            $this->assertSame("password_reset_{$broker}", $table);
            $this->assertTrue(
                Schema::hasTable($table),
                "الجدول {$table} غير موجود — شغّل php artisan migrate."
            );
        }

        $this->assertFalse(
            Schema::hasTable('password_resets'),
            'الجدول المشترك القديم ما زال موجوداً.'
        );
    }

    // ═══ صفحة التعيين ═══

    public function test_the_reset_form_loads_for_a_known_guard(): void
    {
        $this->get(route('password.reset', ['guard' => 'student', 'token' => 'anything']))
            ->assertOk()
            ->assertSee('كلمة سر جديدة');
    }

    public function test_an_unknown_guard_is_rejected(): void
    {
        $this->get(route('password.reset', ['guard' => 'hacker', 'token' => 'x']))
            ->assertNotFound();

        $this->post(route('password.update', 'hacker'), [])->assertNotFound();
    }

    // ═══ التعيين نفسه ═══

    /** رمز مزوّر لا يغيّر شيئاً */
    public function test_a_forged_token_changes_nothing(): void
    {
        $student = $this->student();
        $hash = $student->password;

        $this->post(route('password.update', 'student'), [
            'token' => 'forged-token',
            'email' => $student->email,
            'password' => 'a-new-strong-password',
            'password_confirmation' => 'a-new-strong-password',
        ])->assertRedirect();

        $this->assertSame($hash, $student->fresh()->password);
    }

    /** كلمة سر قصيرة تُرفض — ٨ أحرف كما في بقية النظام */
    public function test_a_short_password_is_rejected(): void
    {
        $student = $this->student();

        $this->post(route('password.update', 'student'), [
            'token' => 'whatever',
            'email' => $student->email,
            'password' => 'sevench',
            'password_confirmation' => 'sevench',
        ])->assertSessionHasErrors('password');
    }

    public function test_a_mismatched_confirmation_is_rejected(): void
    {
        $student = $this->student();

        $this->post(route('password.update', 'student'), [
            'token' => 'whatever',
            'email' => $student->email,
            'password' => 'eightchars',
            'password_confirmation' => 'eightcharz',
        ])->assertSessionHasErrors('password');
    }

    /** المسار كاملاً: رمز حقيقي يغيّر كلمة المرور فعلاً */
    public function test_a_real_token_resets_the_password(): void
    {
        $student = $this->student();
        $original = $student->password;

        DB::table('password_reset_students')->where('email', $student->email)->delete();
        $this->issued[] = ['password_reset_students', $student->email];

        try {
            $token = Password::broker('students')->createToken($student);

            $this->post(route('password.update', 'student'), [
                'token' => $token,
                'email' => $student->email,
                'password' => 'a-new-strong-password',
                'password_confirmation' => 'a-new-strong-password',
            ])->assertRedirect(route('login'));

            $this->assertTrue(Hash::check('a-new-strong-password', $student->fresh()->password));

            // الرمز يُستعمل مرة واحدة
            $this->assertSame(
                0,
                DB::table('password_reset_students')->where('email', $student->email)->count()
            );
        } finally {
            // استعلام مباشر لا \u200E$model->save()\u200E: النموذج في الذاكرة ما
            // زال يحمل التجزئة الأصلية، فإسنادها إليه يُبقي \u200EisDirty()\u200E
            // كاذبة و\u200Esave()\u200E لا يُصدر استعلاماً — فلا يُستعاد شيء.
            Student::where('id', $student->id)->update(['password' => $original]);
        }

        $this->assertSame(
            $original,
            $student->fresh()->password,
            'كلمة المرور لم تُستعد.'
        );
    }
}
