<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Supervisor;
use Tests\TestCase;

/**
 * جدول المجموعات بعد تحويله إلى DataTables.
 *
 * للقراءة فقط — \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة، فأي
 * \u200ERefreshDatabase\u200E هنا يمسح قاعدة التطوير.
 */
class GroupsDataTableTest extends TestCase
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
            ->getJson(route('admin.groups.getData', $query + [
                'draw' => 1,
                'start' => 0,
                'length' => 15,
            ]));

        $response->assertOk();

        return $response->json();
    }

    /** الصفحة تُفتح ولا تعتمد على \u200E$projects\u200E بعد إزالة الترقيم الخادمي */
    public function test_the_index_page_loads(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.groups.index'))
            ->assertOk()
            ->assertSee('بيانات المجموعات')
            ->assertSee('dataTable-1', false);
    }

    /** مصدر البيانات يردّ بهيكل DataTables المتوقَّع */
    public function test_the_data_endpoint_returns_a_datatables_payload(): void
    {
        $payload = $this->fetch();

        foreach (['draw', 'recordsTotal', 'recordsFiltered', 'data'] as $key) {
            $this->assertArrayHasKey($key, $payload);
        }
    }

    /** كل صفّ يحمل الأعمدة السبعة التي يعلنها الجدول */
    public function test_each_row_carries_every_declared_column(): void
    {
        $payload = $this->fetch();

        if (empty($payload['data'])) {
            $this->markTestSkipped('لا توجد مشاريع في الفصل الحالي.');
        }

        $expected = ['identity', 'leader', 'team', 'supervisor_name', 'status', 'grade', 'actions'];

        foreach ($expected as $column) {
            $this->assertArrayHasKey($column, $payload['data'][0], "العمود {$column} مفقود.");
        }
    }

    /** العدّ من نقطة البيانات يطابق العدّ المحسوب في الصفحة */
    public function test_the_row_count_matches_the_semester(): void
    {
        $semesterId = Semester::current()->id;

        $payload = $this->fetch(['semester' => $semesterId]);

        $this->assertSame(
            Project::where('semester_id', $semesterId)->count(),
            $payload['recordsTotal']
        );
    }

    /** فلتر الحالة يُطبَّق على الخادم لا في المتصفّح */
    public function test_the_status_filter_is_applied_server_side(): void
    {
        $semesterId = Semester::current()->id;

        foreach (array_keys(config('statuses.map')) as $status) {
            $payload = $this->fetch(['semester' => $semesterId, 'status' => $status]);

            $this->assertSame(
                Project::where('semester_id', $semesterId)->where('status', $status)->count(),
                $payload['recordsTotal'],
                "الحالة {$status}"
            );
        }
    }

    /** حالة غير معروفة تُتجاهَل بدل أن تُفرّغ الجدول */
    public function test_an_unknown_status_is_ignored(): void
    {
        $semesterId = Semester::current()->id;

        $this->assertSame(
            $this->fetch(['semester' => $semesterId])['recordsTotal'],
            $this->fetch(['semester' => $semesterId, 'status' => 'nonsense'])['recordsTotal']
        );
    }

    /** فلتر المشرف */
    public function test_the_supervisor_filter_is_applied_server_side(): void
    {
        $semesterId = Semester::current()->id;

        $supervisor = Supervisor::whereHas('projects', fn ($q) => $q->where('semester_id', $semesterId))->first()
            ?? Supervisor::first();

        if (! $supervisor) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        $payload = $this->fetch(['semester' => $semesterId, 'supervisor' => $supervisor->id]);

        $this->assertSame(
            Project::where('semester_id', $semesterId)->where('supervisor_id', $supervisor->id)->count(),
            $payload['recordsTotal']
        );
    }

    /** البحث يُصفّي على عنوان المشروع */
    public function test_search_filters_by_project_title(): void
    {
        $semesterId = Semester::current()->id;
        $project = Project::where('semester_id', $semesterId)->first();

        if (! $project) {
            $this->markTestSkipped('لا توجد مشاريع.');
        }

        $needle = mb_substr($project->title, 0, 8);

        $payload = $this->fetch([
            'semester' => $semesterId,
            'search' => ['value' => $needle, 'regex' => 'false'],
        ]);

        $this->assertGreaterThan(0, $payload['recordsFiltered']);
        $this->assertLessThanOrEqual($payload['recordsTotal'], $payload['recordsFiltered']);
    }

    /**
     * مشروع بلا قائد فريق لا يُسقط الاستجابة.
     * مفتاح الطالب \u200EnullOnDelete\u200E، فالحالة واقعية لا افتراضية.
     */
    public function test_rows_survive_a_project_without_a_leader(): void
    {
        $payload = $this->fetch();

        foreach ($payload['data'] as $row) {
            $this->assertIsString($row['leader']);
            $this->assertNotSame('', $row['leader']);
        }
    }

    /** الترميز في الخلايا مُهرَّب — لا حقن عبر عنوان مشروع */
    public function test_project_titles_are_escaped_in_the_payload(): void
    {
        $payload = $this->fetch();

        foreach ($payload['data'] as $row) {
            $this->assertStringNotContainsString('<script', strtolower($row['identity']));
            $this->assertStringNotContainsString('<script', strtolower($row['actions']));
        }
    }

    /** نقطة البيانات محميّة كبقية لوحة الأدمن */
    public function test_the_data_endpoint_requires_authentication(): void
    {
        $this->get(route('admin.groups.getData'))->assertRedirect();
    }
}
