<?php

namespace Tests\Feature;

use App\Imports\StudentsImport;
use App\Models\Specialize;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * استيراد كشف الطلاب من Excel.
 *
 * كل ما هنا يختبر \u200Emodel()\u200E وحدها: تُرجع كياناً غير محفوظ، فلا شيء
 * يُكتب في قاعدة التطوير. (\u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة.)
 */
class StudentsImportTest extends TestCase
{
    private function import(): StudentsImport
    {
        return new StudentsImport(1);
    }

    private function row(array $overrides = []): array
    {
        return array_merge([
            'name' => 'طالب تجريبي',
            'university_id' => '2300009999',
            'email' => 'trial@student.com',
            'phone' => '592050879',
            'gender' => 'male',
            'specialization' => 'غير موجود',
            'password' => 'secret123',
        ], $overrides);
    }

    /** Excel يقرأ \u200E0592050879\u200E رقماً فيسقط الصفر البادئ — نعيده */
    public function test_a_phone_missing_its_leading_zero_gets_one(): void
    {
        $student = $this->import()->model($this->row(['phone' => '592050879']));

        $this->assertSame('0592050879', $student->phone);
    }

    /**
     * العطل: حين يكون العمود نصّاً في الملف يبقى الصفر، وكان الكود
     * يلصق صفراً ثانياً فينتج \u200E00592050879\u200E — رقم لا يعمل، ويُحفظ بلا
     * شكوى لأن العمود \u200Evarchar NULL\u200E.
     */
    public function test_a_phone_that_already_has_its_zero_is_not_doubled(): void
    {
        $student = $this->import()->model($this->row(['phone' => '0592050879']));

        $this->assertSame('0592050879', $student->phone);
        $this->assertStringStartsNotWith('00', $student->phone);
    }

    /** أرقام تأتي من Excel بفواصل أو مسافات أو شرطات */
    public function test_phone_separators_are_stripped(): void
    {
        foreach (['059-205-0879', '059 205 0879', ' 0592050879 '] as $raw) {
            $this->assertSame(
                '0592050879',
                $this->import()->model($this->row(['phone' => $raw]))->phone,
                "فشل على: {$raw}"
            );
        }
    }

    /** خليّة جوال فارغة تصير \u200Enull\u200E لا الصفر وحده */
    public function test_an_empty_phone_becomes_null(): void
    {
        foreach ([null, '', '   '] as $raw) {
            $this->assertNull($this->import()->model($this->row(['phone' => $raw]))->phone);
        }
    }

    /**
     * العطل: \u200Ebcrypt(trim(null))\u200E — تحذير إهمال في PHP 8.1+، ثم حساب
     * بكلمة مرور فارغة صالحة يستطيع أي أحد الدخول به.
     */
    public function test_a_missing_password_never_yields_an_empty_one(): void
    {
        foreach ([null, '', '   '] as $raw) {
            $student = $this->import()->model($this->row(['password' => $raw]));

            $this->assertNotEmpty($student->password);
            $this->assertFalse(
                Hash::check('', $student->password),
                'كلمة مرور فارغة تُنتج حساباً مفتوحاً للجميع.'
            );
        }
    }

    /** كلمة المرور تُخزَّن مُعمّاة لا نصّاً صريحاً */
    public function test_the_password_is_hashed(): void
    {
        $student = $this->import()->model($this->row(['password' => 'secret123']));

        $this->assertNotSame('secret123', $student->password);
        $this->assertTrue(Hash::check('secret123', $student->password));
    }

    /** التخصص يُطابَق بالاسم */
    public function test_a_known_specialization_is_matched_by_name(): void
    {
        $specialize = Specialize::first();

        if (! $specialize) {
            $this->markTestSkipped('لا توجد تخصصات.');
        }

        $student = $this->import()->model($this->row(['specialization' => $specialize->name]));

        $this->assertSame($specialize->id, $student->specialize_id);
    }

    /** ومسافة زائدة حول اسم التخصص لا تُفقده مطابقته */
    public function test_specialization_matching_tolerates_whitespace(): void
    {
        $specialize = Specialize::first();

        if (! $specialize) {
            $this->markTestSkipped('لا توجد تخصصات.');
        }

        $student = $this->import()->model($this->row(['specialization' => '  ' . $specialize->name . ' ']));

        $this->assertSame($specialize->id, $student->specialize_id);
    }

    /** تخصص غير معروف يترك الحقل فارغاً بدل أن يُسقط الاستيراد */
    public function test_an_unknown_specialization_leaves_the_field_null(): void
    {
        $student = $this->import()->model($this->row(['specialization' => 'تخصص لا وجود له']));

        $this->assertNull($student->specialize_id);
    }

    /** الجنس الافتراضي حين لا يُذكر في الملف */
    public function test_gender_defaults_to_male_when_absent(): void
    {
        $row = $this->row();
        unset($row['gender']);

        $this->assertSame('male', $this->import()->model($row)->gender);
    }

    /** المسافات حول الحقول النصّية تُقلَّم */
    public function test_text_fields_are_trimmed(): void
    {
        $student = $this->import()->model($this->row([
            'name' => '  دعاء الأغا  ',
            'university_id' => ' 2300000001 ',
            'email' => '  duaa@student.com ',
        ]));

        $this->assertSame('دعاء الأغا', $student->name);
        $this->assertSame('2300000001', $student->university_id);
        $this->assertSame('duaa@student.com', $student->email);
    }

    /** المستورِد يُنسب إلى الأدمن الذي رفع الملف */
    public function test_the_importing_admin_is_recorded(): void
    {
        $student = (new StudentsImport(7))->model($this->row());

        $this->assertSame(7, $student->admin_id);
    }

    /** لا شيء يُحفظ: \u200Emodel()\u200E تُرجع كياناً جديداً لا سجلاً */
    public function test_model_returns_an_unsaved_student(): void
    {
        $student = $this->import()->model($this->row());

        $this->assertFalse($student->exists);
    }
}
