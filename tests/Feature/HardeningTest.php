<?php

namespace Tests\Feature;

use App\Exports\SafeValueBinder;
use App\Exports\StudentsExport;
use App\Models\Admin;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelType;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * تحصين: الصيغ في التصدير، وترويسات الأمان، والملفات اليتيمة.
 *
 * داخل معاملة تُرجَع في ‎tearDown‎.
 */
class HardeningTest extends TestCase
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

    /** اسم يبدأ بـ «=» كان يُخزَّن صيغةً تُنفَّذ حين يفتح الأدمن الملف */
    public function test_a_formula_in_user_data_is_exported_as_plain_text(): void
    {
        $this->assertSame(SafeValueBinder::class, config('excel.value_binder.default'));

        $student = Student::first();
        $payload = '=HYPERLINK("http://evil.example","اضغط")';
        DB::table('students')->where('id', $student->id)->update(['name' => $payload]);

        $path = tempnam(sys_get_temp_dir(), 'exp') . '.xlsx';
        file_put_contents($path, Excel::raw(new StudentsExport(), ExcelType::XLSX));
        $sheet = IOFactory::load($path)->getActiveSheet();

        $cell = null;
        foreach ($sheet->getRowIterator() as $row) {
            foreach ($row->getCellIterator() as $c) {
                if ($c->getValue() === $payload) {
                    $cell = $c;
                    break 2;
                }
            }
        }

        $this->assertNotNull($cell, 'الاسم لم يظهر في التصدير كما كُتب.');
        $this->assertSame(DataType::TYPE_STRING, $cell->getDataType(), 'الاسم خُزّن صيغةً.');
        @unlink($path);
    }

    public function test_responses_carry_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /** حذف الحساب كان يترك صورته يتيمة في public/uploads */
    public function test_deleting_an_account_deletes_its_avatar_file(): void
    {
        $student = Student::whereDoesntHave('groups')->first();
        $name = 'test-orphan-' . uniqid() . '.jpg';
        Storage::disk('avatars')->put($name, 'x');
        DB::table('students')->where('id', $student->id)->update(['avatar' => $name]);

        $student->fresh()->delete();

        $this->assertFalse(Storage::disk('avatars')->exists($name), 'الصورة بقيت بعد حذف الحساب.');
    }

    public function test_the_unused_api_user_route_is_gone(): void
    {
        $this->getJson('/api/user')->assertNotFound();
    }

    public function test_a_negative_group_limit_is_refused(): void
    {
        $spec = DB::table('specializes')->whereNull('archived_at')->value('id');

        $this->actingAs(Admin::first(), 'admin')
            ->post(route('admin.supervisors.store'), [
                'name' => 'مشرف', 'university_id' => '799999941', 'email' => 'sup41@example.test',
                'specialize_id' => $spec, 'gender' => 'male', 'password' => 'long-enough-1', 'max_group' => -2,
            ])
            ->assertSessionHasErrors('max_group');
    }
}
