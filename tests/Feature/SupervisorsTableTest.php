<?php

namespace Tests\Feature;

use App\Exports\SupervisorsExport;
use App\Models\Admin;
use App\Models\Semester;
use App\Models\Specialize;
use App\Models\Supervisor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

/**
 * جدول المشرفين وعبء الإشراف.
 *
 * للقراءة فقط — \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة، فأي
 * \u200ERefreshDatabase\u200E هنا يمسح قاعدة التطوير.
 */
class SupervisorsTableTest extends TestCase
{
    private function admin(): Admin
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن في قاعدة التطوير.');
        }

        return $admin;
    }

    private function fetch(array $query = []): array
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->getJson(route('admin.supervisors.getData', $query + [
                'draw' => 1,
                'start' => 0,
                'length' => 25,
            ]));

        $response->assertOk();

        return $response->json();
    }

    /** عدد المشرفين في كل دلو يساوي المجموع — لا تداخل ولا فجوة */
    private function loadCount(string $bucket): int
    {
        return Supervisor::whereRaw(
            Supervisor::loadExpression() . ' ' . Supervisor::loadOperator($bucket) . ' supervisors.max_group',
            [Semester::current()->id]
        )->count();
    }

    public function test_the_index_page_loads(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.supervisors.index'))
            ->assertOk()
            ->assertSee('بيانات المشرفين')
            ->assertSee('عبء الإشراف');
    }

    public function test_each_row_carries_every_declared_column(): void
    {
        $payload = $this->fetch();

        if (empty($payload['data'])) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        foreach (['identity', 'university_id', 'specialize', 'phone', 'projects_count', 'actions'] as $column) {
            $this->assertArrayHasKey($column, $payload['data'][0], "العمود {$column} مفقود.");
        }
    }

    /** الدلاء الثلاثة تقسّم الكشف قسمة تامّة */
    public function test_the_three_load_buckets_partition_every_supervisor(): void
    {
        $this->assertSame(
            Supervisor::count(),
            $this->loadCount('free') + $this->loadCount('full') + $this->loadCount('over'),
            'مشرف خارج الدلاء الثلاثة — أو محسوب مرتين.'
        );
    }

    /** فلتر العبء يُطبَّق على الخادم */
    public function test_the_load_filter_is_applied_server_side(): void
    {
        foreach (['free', 'full', 'over'] as $bucket) {
            $this->assertSame(
                $this->loadCount($bucket),
                $this->fetch(['load' => $bucket])['recordsTotal'],
                "الدلو {$bucket}"
            );
        }
    }

    /** قيمة عبء غير معروفة تُتجاهَل بدل أن تُفرّغ الجدول */
    public function test_an_unknown_load_filter_is_ignored(): void
    {
        $this->assertSame(
            $this->fetch()['recordsTotal'],
            $this->fetch(['load' => 'nonsense'])['recordsTotal']
        );
    }

    /** المتجاوزون يحملون فعلاً مجموعات أكثر من حدّهم */
    public function test_over_capacity_supervisors_really_exceed_their_limit(): void
    {
        $semesterId = Semester::current()->id;

        $over = Supervisor::withCount(['projects' => fn ($q) => $q
            ->whereIn('status', ['accept', 'complete'])
            ->where('semester_id', $semesterId)])
            ->whereRaw(
                Supervisor::loadExpression() . ' > supervisors.max_group',
                [$semesterId]
            )
            ->get();

        if ($over->isEmpty()) {
            $this->markTestSkipped('لا يوجد مشرف تجاوز حدّه.');
        }

        foreach ($over as $supervisor) {
            $this->assertGreaterThan(
                $supervisor->max_group,
                $supervisor->projects_count,
                $supervisor->name
            );
        }
    }

    /** خليّة العبء تعرض السقف لا العدد وحده — وهذا هو جوهر التحسين */
    public function test_the_load_cell_shows_the_limit_not_just_the_count(): void
    {
        $payload = $this->fetch();

        if (empty($payload['data'])) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        foreach ($payload['data'] as $row) {
            $this->assertStringContainsString('load-cell', $row['projects_count']);
            $this->assertMatchesRegularExpression('#<small>/\d+</small>#', $row['projects_count']);
        }
    }

    /** فلتر التخصص */
    public function test_the_specialize_filter_is_applied_server_side(): void
    {
        $specialize = Specialize::whereHas('supervisors')->first() ?? Specialize::first();

        if (! $specialize) {
            $this->markTestSkipped('لا توجد تخصصات.');
        }

        $this->assertSame(
            Supervisor::where('specialize_id', $specialize->id)->count(),
            $this->fetch(['specialize' => $specialize->id])['recordsTotal']
        );
    }

    /** التصدير يعمل، ويمرّ بالوسيط الذي كان يكسر كل تنزيل */
    public function test_export_returns_a_downloadable_spreadsheet(): void
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.supervisors.export'));

        $response->assertOk();
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        $this->assertGreaterThan(1000, $response->baseResponse->getFile()->getSize());
    }

    /** التصدير يحترم الفلتر: يصدّر ما يراه الأدمن لا كل شيء */
    public function test_export_respects_the_load_filter(): void
    {
        foreach (['free', 'full', 'over'] as $bucket) {
            $this->assertSame(
                $this->loadCount($bucket),
                (new SupervisorsExport($bucket))->collection()->count(),
                "الدلو {$bucket}"
            );
        }
    }

    /** عدد أعمدة كل صفّ في الكشف يطابق عدد العناوين */
    public function test_each_exported_row_matches_the_heading_count(): void
    {
        $export = new SupervisorsExport();
        $rows = $export->collection();

        if ($rows->isEmpty()) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        $this->assertCount(count($export->headings()), $export->map($rows->first()));
    }

    /** الترميز مُهرَّب — لا حقن عبر اسم مشرف */
    public function test_supervisor_names_are_escaped(): void
    {
        foreach ($this->fetch()['data'] as $row) {
            $this->assertStringNotContainsString('<script', strtolower($row['identity']));
            $this->assertStringNotContainsString('<script', strtolower($row['actions']));
        }
    }

    public function test_the_data_endpoint_requires_authentication(): void
    {
        $this->get(route('admin.supervisors.getData'))->assertRedirect();
        $this->get(route('admin.supervisors.export'))->assertRedirect();
    }
}
