<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Supervisor;
use App\Support\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * درج التعديل للطالب والمشرف والمسؤول.
 *
 * كانت نافذة تُغلق عند فشل التحقّق فتضيع رسالة الخطأ ويُظنّ الحفظ تمّ،
 * ونافذة الإضافة تمتلئ بقيم سجلّ آخر. داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class EditDrawerTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $admin = Admin::first();
        if (! $admin) {
            $this->markTestSkipped('لا أدمن.');
        }
        $this->admin = $admin;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function studentPayload(Student $s, array $over = []): array
    {
        return $over + [
            'id' => $s->id,
            'name' => $s->name,
            'university_id' => $s->university_id,
            'specialize_id' => $s->specialize_id,
            'email' => $s->email,
            'phone' => $s->phone,
            'gender' => $s->gender,
        ];
    }

    /** السجلّ JSON صالح ومهرَّب — اسم فيه اقتباس ووسم لا يكسر الخاصية */
    public function test_the_record_attribute_is_safe_json(): void
    {
        $student = Student::first();
        $student->name = "آية 'O\"Neil' </script><b>x</b>";

        $attr = EditRecord::attr(EditRecord::student($student));

        $this->assertStringNotContainsString("'", $attr);
        $this->assertStringNotContainsString('<', $attr);
        $this->assertSame($student->name, json_decode(html_entity_decode($attr, ENT_QUOTES), true)['name']);
    }

    public function test_the_student_table_carries_one_record_per_edit_button(): void
    {
        $row = collect($this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.students.getData', ['length' => 5]))
            ->assertOk()
            ->json('data'))->first();

        $this->assertMatchesRegularExpression("/data-record='[^']+'/", $row['actions']);
        preg_match("/data-record='([^']+)'/", $row['actions'], $m);
        $record = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertArrayHasKey('university_id', $record);
        $this->assertArrayHasKey('status', $record);
        $this->assertStringContainsString("data-bs-target='#editDrawer'", $row['actions']);
    }

    public function test_an_edit_saves(): void
    {
        $student = Student::first();

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.students.update', 'test'), $this->studentPayload($student, ['phone' => '0599000111']))
            ->assertSessionHas('success');

        $this->assertSame('0599000111', $student->fresh()->phone);
    }

    /** الطيّ يعطّل حقل كلمة السر فلا يُرسل — والغائبة لا تمسّ الحالية */
    public function test_leaving_the_password_closed_keeps_it(): void
    {
        $student = Student::first();
        $hash = $student->password;

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.students.update', 'test'), $this->studentPayload($student, ['name' => 'اسم معدّل']))
            ->assertSessionHas('success');

        $this->assertSame($hash, $student->fresh()->password);
    }

    public function test_a_new_password_is_set_when_given(): void
    {
        $supervisor = Supervisor::first();

        $this->actingAs($this->admin, 'admin')->put(route('admin.supervisors.update', 'test'), [
            'id' => $supervisor->id,
            'name' => $supervisor->name,
            'university_id' => $supervisor->university_id,
            'specialize_id' => $supervisor->specialize_id,
            'email' => $supervisor->email,
            'phone' => $supervisor->phone,
            'gender' => $supervisor->gender,
            'max_group' => $supervisor->max_group,
            'password' => 'Str0ng-Pass!2026',
        ])->assertSessionHas('success');

        $this->assertTrue(Hash::check('Str0ng-Pass!2026', $supervisor->fresh()->password));
    }

    /** فشل التحقّق يعيد فتح الدرج للسجلّ نفسه بقيمه — ونافذة الإضافة لا تأخذها */
    public function test_a_failed_edit_reopens_the_drawer_for_the_same_record(): void
    {
        [$student, $other] = Student::take(2)->get();

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.students.index'))
            ->put(route('admin.students.update', 'test'), $this->studentPayload($student, ['email' => $other->email]))
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHasErrors('email');

        $html = $this->get(route('admin.students.index'))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/data-reopen="([^"]+)"/', $html, $m), 'الدرج لا يُعاد فتحه.');
        $payload = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);
        $this->assertSame($student->id, $payload['record']['id']);
        $this->assertSame($other->email, $payload['old']['email']);

        // رسالة الخطأ في الدرج، لا في نافذة الإضافة
        preg_match('/id="createModal".*?<\/form>/s', $html, $create);
        $this->assertStringNotContainsString('is-invalid', $create[0] ?? '');
        $this->assertStringNotContainsString(e($other->email), $create[0] ?? '');
        $this->assertStringContainsString('invalid-feedback', substr($html, strpos($html, 'data-edit-drawer')));

        $this->assertNotSame($other->email, $student->fresh()->email);
    }

    /** درج الإضافة المعاد فتحه بعد فشل، أو بعد «حفظ وإضافة آخر» */
    private function createReopen(string $html): ?array
    {
        if (! preg_match('/id="createDrawer"[^>]*data-reopen="([^"]+)"/', $html, $m)) {
            return null;
        }

        return json_decode(html_entity_decode($m[1], ENT_QUOTES), true);
    }

    /** فشل الإضافة يعيد فتح درجها بقيمها — كانت النافذة تُغلق فتضيع الرسالة */
    public function test_a_failed_create_reopens_the_create_drawer(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.students.index'))
            ->post(route('admin.students.store'), ['name' => 'طالب بلا بيانات'])
            ->assertSessionHasErrors();

        $html = $this->get(route('admin.students.index'))->getContent();

        $this->assertSame('طالب بلا بيانات', $this->createReopen($html)['old']['name'] ?? null);
        // درج التعديل لا يُعاد فتحه لخطأ الإضافة
        $this->assertDoesNotMatchRegularExpression('/id="editDrawer"[^>]*data-reopen=/', $html);
    }

    /** «حفظ وإضافة آخر» يحفظ ويعيد فتح الدرج فارغاً للتالي */
    public function test_save_and_add_another_reopens_an_empty_drawer(): void
    {
        $specialize = \App\Models\Specialize::whereNull('archived_at')->first();

        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.students.index'))
            ->post(route('admin.students.store'), [
                'name' => 'طالب متتالٍ',
                'university_id' => '2300099911',
                'specialize_id' => $specialize->id,
                'email' => 'batch.one@student.test',
                'phone' => '0599000222',
                'gender' => 'male',
                'password' => 'Batch-Pass-2026',
                'another' => '1',
            ])
            ->assertSessionHas('success', 'تمت إضافة «طالب متتالٍ»')
            ->assertSessionHas('reopen_create', true);

        $this->assertTrue(Student::where('email', 'batch.one@student.test')->exists());

        $reopen = $this->createReopen($this->get(route('admin.students.index'))->getContent());
        $this->assertSame([], $reopen['record'] ?? null);
        $this->assertNull($reopen['old']);
    }

    /** خطأ ملف الاستيراد يعيد فتح نافذة الاستيراد، لا درج الإضافة */
    public function test_an_import_error_does_not_open_the_create_drawer(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->from(route('admin.students.index'))
            ->post(route('admin.students.import'), ['attachment' => \Illuminate\Http\UploadedFile::fake()->create('x.pdf', 10)])
            ->assertSessionHasErrors('attachment');

        $html = $this->get(route('admin.students.index'))->getContent();

        $this->assertNull($this->createReopen($html));
        $this->assertStringContainsString("getElementById('importModal')).show()", $html);
    }

    /** القالب لكل نوع بأعمدة المستورِد نفسها — كان ملفاً واحداً بلا \u200Emax_group\u200E */
    public function test_each_import_template_has_its_importers_columns(): void
    {
        \Maatwebsite\Excel\Facades\Excel::fake();
        $this->actingAs($this->admin, 'admin');

        $this->get(route('admin.students.template'))->assertOk();
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('students_template.xlsx', function (\App\Exports\ImportTemplate $t) {
            return $t->headings() === ['name', 'university_id', 'email', 'phone', 'specialization', 'gender', 'password']
                && \App\Models\Specialize::where('name', $t->array()[0][4])->exists();
        });

        $this->get(route('admin.supervisors.template'))->assertOk();
        \Maatwebsite\Excel\Facades\Excel::assertDownloaded('supervisors_template.xlsx', function (\App\Exports\ImportTemplate $t) {
            return in_array('max_group', $t->headings(), true);
        });
    }

    public function test_the_three_tables_open_the_drawer(): void
    {
        $this->actingAs($this->admin, 'admin');

        foreach (['admin.students.index', 'admin.supervisors.index', 'admin.administrators.index'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('id="editDrawer"', false)
                ->assertSee('js/edit-drawer.js', false);
        }
    }
}
