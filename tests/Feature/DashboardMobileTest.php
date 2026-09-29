<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * اللوحات على الجوال: الفلاتر تُطوى، والجداول بطاقات بعناوين أعمدتها،
 * وأهداف اللمس لا تقلّ عن 40px — ومعها اسم الفصل المرتّب في القوائم.
 * داخل معاملة تُرجَع في tearDown.
 */
class DashboardMobileTest extends TestCase
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

    /** سكربت الجوال يُحمَّل في قشرة اللوحات برقم إصدار */
    public function test_layout_loads_the_mobile_script_versioned(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.groups.index'))
            ->assertOk()
            ->assertSee('js/dashboard-mobile.js?v=', false);

        $this->assertFileExists(public_path('js/dashboard-mobile.js'));
    }

    /** كل رسم للجدول يضع عناوين الأعمدة على الخلايا */
    public function test_datatables_label_cells_after_each_draw(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.groups.index'))
            ->assertOk()
            ->assertSee('drawCallback', false)
            ->assertSee('window.dtLabelCells', false);

        $js = file_get_contents(public_path('js/dashboard-mobile.js'));
        $this->assertStringContainsString("td.setAttribute('data-label'", $js);
        $this->assertStringContainsString("'filter-toggle'", $js);
    }

    /** قواعد الجوال: طيّ الفلاتر، البطاقات، وأهداف لمس 40px */
    public function test_mobile_rules_exist(): void
    {
        $css = file_get_contents(public_path('css/dashboard.css'));

        $this->assertStringContainsString('.filter-form.is-collapsible:not(.is-open) [data-filter-more] { display: none !important; }', $css);
        $this->assertStringContainsString('content: attr(data-label);', $css);
        $this->assertMatchesRegularExpression('/@media \(pointer: coarse\)\s*\{\s*\.btn-action \{ min-width: 40px; min-height: 40px; \}/', $css);
    }

    /** قائمة الفصل في فلتر المجموعات بالاسم المرتّب لا «2023\2022» */
    public function test_group_filter_lists_semesters_by_label(): void
    {
        $semester = Semester::current();
        if (! $semester) {
            $this->markTestSkipped('لا فصل.');
        }

        $this->actingAs($this->admin, 'admin')->get(route('admin.groups.index'))
            ->assertOk()
            ->assertSee('>' . e($semester->label) . '</option>', false);
    }
}
