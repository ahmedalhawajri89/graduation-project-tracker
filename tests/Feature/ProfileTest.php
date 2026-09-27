<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * الملف الشخصي — للأدوار الثلاثة.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة، فكل اختبار يُعدّل يُعيد
 * القيمة الأصلية في \u200Efinally\u200E. لا إنشاء ولا حذف لأي سجلّ.
 *
 * كلمة المرور لا تُغيَّر في أي اختبار: كلمة المرور الحالية غير معروفة
 * في قاعدة التطوير، فنكتفي بإثبات أن الطلب يُرفض.
 */
class ProfileTest extends TestCase
{
    private function admin(): Admin
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن في قاعدة التطوير.');
        }

        return $admin;
    }

    /** يُعيد الحقول الأصلية مهما فشل التأكيد */
    private function restoring($model, array $fields, callable $check): void
    {
        $original = collect($fields)->mapWithKeys(fn ($f) => [$f => $model->{$f}])->all();

        try {
            $check();
        } finally {
            // استعلام مباشر لا \u200E$model->save()\u200E: النموذج قد يحمل القيمة
            // الأصلية في الذاكرة، فإسنادها إليه يُبقي \u200EisDirty()\u200E كاذبة
            // و\u200Esave()\u200E لا يُصدر استعلاماً — فلا يُستعاد شيء.
            // (هذا بالضبط ما وقع في اختبار كلمة المرور وغيّر القاعدة.)
            $model::where($model->getKeyName(), $model->getKey())->update($original);
        }

        foreach ($original as $field => $value) {
            $this->assertSame($value, $model->fresh()->{$field}, "الحقل {$field} لم يُستعد.");
        }
    }

    private function payload(Admin $admin, array $overrides = []): array
    {
        return array_merge([
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => $admin->phone ?: '0590000000',
            'gender' => $admin->gender,
        ], $overrides);
    }

    // ═══ ما كان متعذّراً ═══

    /**
     * المسؤول كان لا يُعدّل من ملفه إلا رقم الجوال — بينما يُعدّل اسم
     * وبريد أي مسؤول آخر من صفحة المسؤولين. معكوس.
     */
    public function test_an_admin_can_finally_edit_their_own_name(): void
    {
        $admin = $this->admin();

        $this->restoring($admin, ['name'], function () use ($admin) {
            $this->actingAs($admin, 'admin')
                ->put(route('admin.profile.update'), $this->payload($admin, ['name' => 'اسم مُعدَّل']))
                ->assertRedirect();

            $this->assertSame('اسم مُعدَّل', $admin->fresh()->name);
        });
    }

    public function test_an_admin_can_edit_their_phone(): void
    {
        $admin = $this->admin();

        $this->restoring($admin, ['phone'], function () use ($admin) {
            $this->actingAs($admin, 'admin')
                ->put(route('admin.profile.update'), $this->payload($admin, ['phone' => '0591234567']))
                ->assertRedirect();

            $this->assertSame('0591234567', $admin->fresh()->phone);
        });
    }

    // ═══ البريد هوية الدخول ═══

    /** تغييره بلا كلمة المرور الحالية يُرفض */
    public function test_changing_the_email_without_the_current_password_is_refused(): void
    {
        $admin = $this->admin();
        $original = $admin->email;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), $this->payload($admin, ['email' => 'moved@example.test']))
            ->assertRedirect();

        $this->assertSame($original, $admin->fresh()->email, 'تغيّر البريد بلا كلمة المرور الحالية.');
    }

    /** وبكلمة مرور حالية خاطئة كذلك */
    public function test_changing_the_email_with_a_wrong_password_is_refused(): void
    {
        $admin = $this->admin();
        $original = $admin->email;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), $this->payload($admin, [
                'email' => 'moved@example.test',
                'current_password' => 'definitely-not-the-password',
            ]))
            ->assertRedirect();

        $this->assertSame($original, $admin->fresh()->email);
    }

    // ═══ التحقّق ═══

    /** \u200Ephone\u200E كان \u200Estring|max:20\u200E — فيمكن ضبطه على أي نصّ */
    public function test_a_non_numeric_phone_is_refused(): void
    {
        $admin = $this->admin();
        $original = $admin->phone;

        foreach (['hello world', '059', '05912345678'] as $bad) {
            $this->actingAs($admin, 'admin')
                ->put(route('admin.profile.update'), $this->payload($admin, ['phone' => $bad]))
                ->assertSessionHasErrors('phone');
        }

        $this->assertSame($original, $admin->fresh()->phone);
    }

    /** كلمة المرور كانت \u200Emin:6\u200E بينما صارت \u200Emin:8\u200E في إنشاء المسؤولين */
    public function test_a_short_new_password_is_refused(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), $this->payload($admin, [
                'password' => 'sevench',
                'password_confirmation' => 'sevench',
                'current_password' => 'whatever',
            ]))
            ->assertSessionHasErrors('password');
    }

    public function test_a_mismatched_confirmation_is_refused(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), $this->payload($admin, [
                'password' => 'eightchars',
                'password_confirmation' => 'eightcharz',
                'current_password' => 'whatever',
            ]))
            ->assertSessionHasErrors('password');
    }

    /** كلمة مرور حالية خاطئة لا تُغيّر شيئاً */
    public function test_a_wrong_current_password_changes_nothing(): void
    {
        $admin = $this->admin();
        $hash = $admin->password;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.profile.update'), $this->payload($admin, [
                'password' => 'a-new-strong-password',
                'password_confirmation' => 'a-new-strong-password',
                'current_password' => 'definitely-not-the-password',
            ]))
            ->assertRedirect();

        $this->assertSame($hash, $admin->fresh()->password);
        $this->assertFalse(Hash::check('a-new-strong-password', $admin->fresh()->password));
    }

    // ═══ ما تصدره الجامعة ═══

    /** الطالب لا يغيّر بريده ولا رقمه الجامعي ولو أرسلهما */
    public function test_a_student_cannot_change_institutional_fields(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        $this->restoring($student, ['phone'], function () use ($student) {
            $this->actingAs($student, 'student')
                ->put(route('student.profile.update'), [
                    'phone' => '0591112222',
                    'email' => 'hijack@example.test',
                    'university_id' => '9999999999',
                    'name' => 'اسم مُنتحَل',
                    'specialize_id' => 999,
                ])
                ->assertRedirect();

            $fresh = $student->fresh();
            $this->assertSame('0591112222', $fresh->phone, 'الجوال لم يُحفظ.');
            $this->assertNotSame('hijack@example.test', $fresh->email, 'الطالب غيّر بريده.');
            $this->assertNotSame('9999999999', $fresh->university_id, 'الطالب غيّر رقمه الجامعي.');
            $this->assertNotSame('اسم مُنتحَل', $fresh->name, 'الطالب غيّر اسمه.');
        });
    }

    /** والمشرف كذلك */
    public function test_a_supervisor_cannot_change_institutional_fields(): void
    {
        $supervisor = Supervisor::first();

        if (! $supervisor) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        $this->restoring($supervisor, ['phone'], function () use ($supervisor) {
            $this->actingAs($supervisor, 'supervisor')
                ->put(route('supervisor.profile.update'), [
                    'phone' => '0593334444',
                    'email' => 'hijack@example.test',
                    'max_group' => 99,
                ])
                ->assertRedirect();

            $fresh = $supervisor->fresh();
            $this->assertSame('0593334444', $fresh->phone);
            $this->assertNotSame('hijack@example.test', $fresh->email);
            $this->assertNotSame(99, (int) $fresh->max_group, 'المشرف رفع حدّ مجموعاته بنفسه.');
        });
    }

    // ═══ الصفحات والتنقّل ═══

    public function test_each_role_can_open_its_profile(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.profile.edit'))
            ->assertOk()
            ->assertSee('الملف الشخصي');

        if ($student = Student::first()) {
            $this->actingAs($student, 'student')->get(route('student.profile.edit'))->assertOk();
        }

        if ($supervisor = Supervisor::first()) {
            $this->actingAs($supervisor, 'supervisor')->get(route('supervisor.profile.edit'))->assertOk();
        }
    }

    /**
     * الملف الشخصي في قائمة الأفاتار وحدها. السايدبار يعدّد ما تُديره،
     * والملف الشخصي ليس شيئاً تُديره.
     */
    public function test_the_profile_link_left_the_sidebar(): void
    {
        // الصفحة ما زالت تحمل الرابط — في قائمة الأفاتار ولوحة البحث
        $html = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('user-menu', $html, 'قائمة الأفاتار غائبة.');
        $this->assertStringContainsString(route('admin.profile.edit'), $html);

        // والسايدبار وحده لم يعد يحمله. فحصٌ على المصدر لأن تمييز
        // السايدبار من بقية الصفحة في HTML مُصيَّر غير موثوق.
        foreach (['admin', 'student', 'supervisor'] as $role) {
            $sidebar = file_get_contents(
                resource_path("views/layouts/admin/inc/sidebar/{$role}.blade.php")
            );

            $this->assertStringNotContainsString(
                "{$role}.profile.edit",
                $sidebar,
                "سايدبار {$role} ما زال يحمل رابط الملف الشخصي."
            );
        }
    }
}
