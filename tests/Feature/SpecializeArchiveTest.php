<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\StudentRequest;
use App\Imports\StudentsImport;
use App\Imports\SupervisorsImport;
use App\Models\Admin;
use App\Models\Specialize;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * إيقاف التخصص بدل حذفه.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة، فالاختبارات التي تُوقف
 * تخصصاً تستأنفه في \u200Efinally\u200E — تعود القاعدة كما كانت مهما فشل التأكيد.
 * ولا يُنشأ ولا يُحذف أي سجلّ.
 */
class SpecializeArchiveTest extends TestCase
{
    private function admin(): Admin
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن في قاعدة التطوير.');
        }

        return $admin;
    }

    private function aSpecialize(): Specialize
    {
        $specialize = Specialize::active()->first();

        if (! $specialize) {
            $this->markTestSkipped('لا توجد تخصصات نشطة.');
        }

        return $specialize;
    }

    /** يُوقف التخصص، يُنفّذ الفحص، ثم يستأنفه مهما حدث */
    private function whileArchived(Specialize $specialize, callable $check): void
    {
        $specialize->update(['archived_at' => now()]);

        try {
            $check();
        } finally {
            $specialize->update(['archived_at' => null]);
        }

        $this->assertNull($specialize->fresh()->archived_at, 'التخصص لم يُستأنف بعد الاختبار.');
    }

    // ═══ النطاقات ═══

    public function test_scopes_split_active_from_archived(): void
    {
        $specialize = $this->aSpecialize();

        $this->whileArchived($specialize, function () use ($specialize) {
            $this->assertTrue($specialize->fresh()->isArchived());
            $this->assertFalse(Specialize::active()->where('id', $specialize->id)->exists());
            $this->assertTrue(Specialize::archived()->where('id', $specialize->id)->exists());
        });
    }

    // ═══ أين يختفي وأين يبقى ═══

    /** نموذج الإضافة: النشط فقط. قائمة التصفية: الجميع. */
    public function test_an_archived_specialize_leaves_the_create_list_but_stays_in_the_filter(): void
    {
        $specialize = $this->aSpecialize();

        $this->whileArchived($specialize, function () use ($specialize) {
            $response = $this->actingAs($this->admin(), 'admin')->get(route('admin.students.index'));
            $response->assertOk();

            $forForms = $response->viewData('specializes');
            $forFilter = $response->viewData('filterSpecializes');

            $this->assertNull(
                $forForms->firstWhere('id', $specialize->id),
                'تخصص موقوف ما زال معروضاً في نموذج الإضافة.'
            );
            $this->assertNotNull(
                $forFilter->firstWhere('id', $specialize->id),
                'تخصص موقوف اختفى من التصفية — فاختفى طلابه معه.'
            );
        });
    }

    /**
     * نافذة التعديل تعرض الجميع: لولا ذلك، طالبٌ في تخصص موقوف تُفتح
     * نافذته فلا يجد الـ select قيمته، فيُحفظ على أول خيار — نقلٌ صامت.
     */
    public function test_the_edit_list_keeps_archived_options(): void
    {
        $specialize = $this->aSpecialize();

        $this->whileArchived($specialize, function () use ($specialize) {
            $response = $this->actingAs($this->admin(), 'admin')->get(route('admin.supervisors.index'));
            $response->assertOk();

            $this->assertNotNull(
                $response->viewData('editSpecializes')->firstWhere('id', $specialize->id)
            );
        });
    }

    /** طلاب التخصص الموقوف لا يتأثّرون */
    public function test_students_of_an_archived_specialize_are_untouched(): void
    {
        $specialize = Specialize::active()->has('students')->first();

        if (! $specialize) {
            $this->markTestSkipped('لا يوجد تخصص نشط عليه طلاب.');
        }

        $before = Student::where('specialize_id', $specialize->id)->count();

        $this->whileArchived($specialize, function () use ($specialize, $before) {
            $this->assertSame(
                $before,
                Student::where('specialize_id', $specialize->id)->count(),
                'إيقاف التخصص مسّ طلابه.'
            );

            $payload = $this->actingAs($this->admin(), 'admin')
                ->getJson(route('admin.students.getData', [
                    'specialize' => $specialize->id, 'draw' => 1, 'start' => 0, 'length' => 10,
                ]))
                ->assertOk()
                ->json();

            $this->assertSame($before, $payload['recordsTotal']);
        });
    }

    // ═══ الاستيراد ═══

    /** اسم تخصص موقوف يُعامَل كاسم مجهول */
    public function test_imports_ignore_an_archived_specialize_name(): void
    {
        $specialize = $this->aSpecialize();
        $name = $specialize->name;

        // نشط: يُطابَق
        $this->assertSame(
            $specialize->id,
            (new StudentsImport(1))->model($this->row($name))->specialize_id
        );

        $this->whileArchived($specialize, function () use ($name) {
            $this->assertNull(
                (new StudentsImport(1))->model($this->row($name))->specialize_id,
                'الاستيراد أدخل طالباً إلى تخصص موقوف.'
            );
            $this->assertNull(
                (new SupervisorsImport(1))->model($this->row($name))->specialize_id
            );
        });
    }

    // ═══ التحقّق من المدخلات ═══

    /** الإضافة ترفض الموقوف، والتعديل يقبله */
    public function test_validation_blocks_archived_on_create_but_allows_it_on_update(): void
    {
        $specialize = $this->aSpecialize();

        $this->whileArchived($specialize, function () use ($specialize) {
            $this->assertTrue(
                $this->studentValidationFails($specialize->id, null),
                'أُضيف طالب جديد إلى تخصص موقوف.'
            );

            $existing = Student::first();

            if ($existing) {
                $this->assertFalse(
                    $this->studentValidationFails($specialize->id, $existing->id),
                    'تعذّر حفظ طالب قائم في تخصص أُوقف — وهو ليس خطأه.'
                );
            }
        });
    }

    // ═══ الحارس والمسارات ═══

    /** الحذف ما زال ممنوعاً على تخصص عليه أشخاص */
    public function test_a_specialize_with_people_still_cannot_be_deleted(): void
    {
        $specialize = Specialize::has('students')->first();

        if (! $specialize) {
            $this->markTestSkipped('لا يوجد تخصص عليه طلاب.');
        }

        $this->actingAs($this->admin(), 'admin')
            ->delete(route('admin.specialize.destroy', $specialize->id), ['id' => $specialize->id])
            ->assertRedirect();

        $this->assertNotNull(Specialize::find($specialize->id), 'حُذف تخصص عليه طلاب.');
    }

    /** الإيقاف والاستئناف عبر المسارين */
    public function test_archive_then_restore_through_the_routes(): void
    {
        $specialize = $this->aSpecialize();
        $admin = $this->admin();

        try {
            $this->actingAs($admin, 'admin')
                ->post(route('admin.specialize.archive', $specialize->id))
                ->assertRedirect();

            $this->assertTrue($specialize->fresh()->isArchived());
        } finally {
            $this->actingAs($admin, 'admin')
                ->post(route('admin.specialize.restore', $specialize->id));
        }

        $this->assertFalse($specialize->fresh()->isArchived());
    }

    /** المسارات محميّة كبقية اللوحة */
    public function test_the_archive_routes_require_an_admin(): void
    {
        $specialize = $this->aSpecialize();

        $this->post(route('admin.specialize.archive', $specialize->id))->assertRedirect();
        $this->assertFalse($specialize->fresh()->isArchived());
    }

    // ═══ أدوات ═══

    private function row(string $specialization): array
    {
        return [
            'name' => 'مستخدم تجريبي',
            'university_id' => '2300009999',
            'email' => 'probe@student.test',
            'phone' => '592050879',
            'gender' => 'male',
            'specialization' => $specialization,
            'password' => 'secret123',
            'max_group' => 2,
        ];
    }

    private function studentValidationFails(int $specializeId, ?int $studentId): bool
    {
        $payload = [
            'university_id' => '2300009999',
            'specialize_id' => $specializeId,
            'name' => 'طالب',
            'email' => 'probe-validation@student.test',
            'phone' => '0590000000',
            'gender' => 'male',
            'password' => 'secret123',
        ];

        if ($studentId) {
            $payload['id'] = $studentId;
        }

        $rules = StudentRequest::create('/', 'POST', $payload)->rules();

        return Validator::make($payload, ['specialize_id' => $rules['specialize_id']])->fails();
    }
}
