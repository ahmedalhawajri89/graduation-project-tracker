<?php

namespace Tests\Feature;

use App\Exports\StudentsExport;
use App\Models\Admin;
use App\Models\Specialize;
use App\Models\Student;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * تصدير كشف الطلاب.
 *
 * الاختبار للقراءة فقط عمداً: \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار
 * منفصلة (سطرا sqlite معلَّقان)، فأي \u200ERefreshDatabase\u200E هنا يمسح
 * قاعدة التطوير الحقيقية. لا كتابة ولا هجرات — نقرأ ما هو قائم.
 *
 * ما يحرسه أساساً: \u200EPreventBackHistory\u200E كانت تنادي \u200E$response->header()\u200E
 * وهي موجودة على \u200EIlluminate\Http\Response\u200E وحدها — أما التنزيلات فترث
 * \u200EBinaryFileResponse\u200E من Symfony بلا تلك الدالة، فكان كل تصدير يسقط بـ
 * \u200ECall to undefined method\u200E. هذا الاختبار يمرّ بالوسيط كاملاً.
 */
class StudentsExportTest extends TestCase
{
    private function admin(): Admin
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن في قاعدة التطوير.');
        }

        return $admin;
    }

    /** التنزيل يمرّ بالوسيط بلا سقوط — هذا هو العطل المُبلَّغ عنه */
    public function test_export_route_returns_a_downloadable_spreadsheet(): void
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.students.export'));

        $response->assertOk();

        $this->assertInstanceOf(
            BinaryFileResponse::class,
            $response->baseResponse,
            'التصدير يجب أن يكون تنزيل ملف لا صفحة.'
        );

        $response->assertHeader(
            'content-type',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);

        // الملف المُولَّد موجود وغير فارغ
        $file = $response->baseResponse->getFile();
        $this->assertTrue($file->isFile());
        $this->assertGreaterThan(1000, $file->getSize(), 'الملف أصغر من أن يحوي كشفاً.');
    }

    /** الوسيط نفسه ضبط ترويسات المنع بلا أن يكسر الاستجابة */
    public function test_prevent_back_history_headers_are_applied_to_downloads(): void
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.students.export'));

        $response->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('cache-control'));
        $this->assertSame('no-cache', $response->headers->get('pragma'));
    }

    /** لا يُصدَّر إلا لأدمن مسجَّل */
    public function test_export_requires_authentication(): void
    {
        $this->get(route('admin.students.export'))->assertRedirect();
    }

    /** الفلاتر تُمرَّر من الرابط إلى الكشف، فيُصدَّر ما يراه الأدمن */
    public function test_export_respects_the_group_filter(): void
    {
        $admin = $this->admin();

        foreach (['in', 'none'] as $group) {
            $this->actingAs($admin, 'admin')
                ->get(route('admin.students.export', ['group' => $group]))
                ->assertOk();
        }

        $inGroup = new StudentsExport('in');
        $noGroup = new StudentsExport('none');
        $all = new StudentsExport();

        $this->assertSame(
            $all->collection()->count(),
            $inGroup->collection()->count() + $noGroup->collection()->count(),
            'المنضمّون + غير المنضمّين يجب أن يساويا الكل.'
        );
    }

    /** قيمة فلتر غير معروفة لا تُغيّر شيئاً بدل أن تُفرّغ الكشف */
    public function test_unknown_group_filter_is_ignored(): void
    {
        $this->assertSame(
            (new StudentsExport())->collection()->count(),
            (new StudentsExport('whatever'))->collection()->count()
        );
    }

    /** فلتر التخصص */
    public function test_export_respects_the_specialize_filter(): void
    {
        $specialize = Specialize::first();

        if (! $specialize) {
            $this->markTestSkipped('لا توجد تخصصات.');
        }

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.students.export', ['specialize' => $specialize->id]))
            ->assertOk();

        $rows = (new StudentsExport(null, $specialize->id))->collection();

        $this->assertSame(
            Student::where('specialize_id', $specialize->id)->count(),
            $rows->count()
        );
    }

    /** عدد الأعمدة في كل صفّ يطابق عدد العناوين — وإلا انزاح الكشف */
    public function test_each_row_matches_the_heading_count(): void
    {
        $export = new StudentsExport();
        $headings = $export->headings();

        $student = Student::with(['specialize:id,name', 'groups.project:id,title'])->first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        $this->assertCount(count($headings), $export->map($student));
    }

    /** طالب بلا تخصص أو بلا فريق لا يُسقط التصدير بـ null */
    public function test_map_survives_missing_relations(): void
    {
        $export = new StudentsExport();

        $orphan = new Student([
            'name' => 'طالب بلا علاقات',
            'university_id' => '2300000000',
            'email' => 'orphan@student.com',
            'phone' => '0590000000',
            'gender' => 'male',
        ]);
        $orphan->setRelation('specialize', null);
        $orphan->setRelation('groups', collect());

        $row = $export->map($orphan);

        $this->assertCount(count($export->headings()), $row);
        $this->assertSame('—', $row[2], 'التخصص المفقود يجب أن يظهر شرطة لا فراغاً.');
        $this->assertSame('بلا فريق', $row[6]);
    }
}
