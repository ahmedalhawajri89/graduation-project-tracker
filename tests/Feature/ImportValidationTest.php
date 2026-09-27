<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Specialize;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

/**
 * الاستيراد عبر نقطة الرفع الحقيقية — بملفات Excel فعلية.
 *
 * كان في الطابور بلا تحقّق: صفّ معطوب يُفشل الدفعة كلّها بصمت، والأدمن يرى
 * «بدأت عملية الرفع بنجاح». على قاعدة ‎*_testing‎، وما يُنشأ يُحذف.
 */
class ImportValidationTest extends TestCase
{
    private const NEW_STUDENT = '2309999901';

    private const NEW_SUPERVISOR = '799999901';

    protected function tearDown(): void
    {
        Student::whereIn('university_id', [self::NEW_STUDENT, '2309999902'])->delete();
        Supervisor::where('university_id', self::NEW_SUPERVISOR)->delete();

        parent::tearDown();
    }

    /** ملف xlsx حقيقي بصفّ عناوين ثم الصفوف */
    private function xlsx(array $header, array $rows): UploadedFile
    {
        $sheet = (new Spreadsheet())->getActiveSheet();
        $sheet->fromArray([$header, ...$rows], null, 'A1', true);

        $path = tempnam(sys_get_temp_dir(), 'imp') . '.xlsx';
        (new Xlsx($sheet->getParent()))->save($path);

        return new UploadedFile($path, 'sheet.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    public function test_a_student_sheet_adds_good_rows_and_reports_bad_ones(): void
    {
        $spec = Specialize::active()->value('name');
        $existing = Student::first();
        $header = ['name', 'university_id', 'email', 'phone', 'gender', 'specialization'];

        $file = $this->xlsx($header, [
            ['طالب جديد', self::NEW_STUDENT, 'new.student@example.test', '592050879', 'Male', $spec],   // ٢: سليم
            ['مكرّر', self::NEW_STUDENT, 'dup@example.test', '', 'male', $spec],                       // ٣: مكرّر في الملف
            ['رقم خاطئ', '1234567890', 'bad.id@example.test', '', 'male', $spec],                      // ٤: لا يبدأ بـ 130/230
            ['تخصص مجهول', '2309999902', 'unknown.spec@example.test', '', 'male', 'تخصص لا وجود له'],  // ٥
            [null, null, null, null, null, null],                                                        // ٦: فارغ — يُتجاهل
            ['موجود', $existing->university_id, 'exists@example.test', '', 'male', $spec],             // ٧: موجود في القاعدة
        ]);

        $response = $this->actingAs(Admin::first(), 'admin')
            ->post(route('admin.students.import'), ['attachment' => $file])
            ->assertRedirect();

        $report = session('import_report');
        $response->assertSessionHas('success');

        $this->assertSame(1, $report['added']);
        $this->assertSame([3, 4, 5, 7], array_keys($report['skipped']), 'صفوف التخطّي غير المتوقّعة.');
        // المكرّر يُلتقط بتفرّد القاعدة (الصفّ ٢ حُفظ قبله) أو بفحص الملف — كلاهما سبب صحيح
        $this->assertMatchesRegularExpression('/مكرّر|مُستخدمة/u', $report['skipped'][3]);

        $created = Student::where('university_id', self::NEW_STUDENT)->first();
        $this->assertNotNull($created, 'الصفّ السليم لم يُضف.');
        $this->assertSame('0592050879', $created->phone, 'الصفر البادئ لم يُستعد.');
        $this->assertSame('male', $created->gender);
        $this->assertNull(Student::where('university_id', '2309999902')->first(), 'تخصص مجهول أُضيف.');
    }

    public function test_a_supervisor_sheet_is_validated_the_same_way(): void
    {
        $spec = Specialize::active()->value('name');
        $header = ['name', 'university_id', 'email', 'phone', 'gender', 'specialization', 'max_group'];

        $file = $this->xlsx($header, [
            ['مشرف جديد', self::NEW_SUPERVISOR, 'new.sup@example.test', '', 'female', $spec, 3],
            ['رقم قصير', '12345', 'short@example.test', '', 'male', $spec, 3],
            ['حدّ سالب', '799999902', 'neg@example.test', '', 'male', $spec, -2],
        ]);

        $this->actingAs(Admin::first(), 'admin')
            ->post(route('admin.supervisors.import'), ['attachment' => $file])
            ->assertSessionHas('success');

        $report = session('import_report');
        $this->assertSame(1, $report['added']);
        $this->assertSame([3, 4], array_keys($report['skipped']));
        $this->assertSame(3, (int) Supervisor::where('university_id', self::NEW_SUPERVISOR)->value('max_group'));
    }

    public function test_a_non_excel_file_is_refused(): void
    {
        $file = UploadedFile::fake()->create('sheet.pdf', 10, 'application/pdf');

        $this->actingAs(Admin::first(), 'admin')
            ->post(route('admin.students.import'), ['attachment' => $file])
            ->assertSessionHasErrors('attachment');
    }
}
