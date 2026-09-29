<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Supervisor;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * لوحات الأدوار الثلاثة بلغة ألوان واحدة: الحبر للنصوص، والأزرق للنشط
 * والمنجَز والتقدّم — كالموقع وصفحة الدخول. واسم الفصل بسنة لا تنقلب.
 * داخل معاملة تُرجَع في tearDown.
 */
class DashboardColorsTest extends TestCase
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

    private function css(): string
    {
        return file_get_contents(public_path('css/dashboard.css'));
    }

    /** لا خلفية ولا حدّ بالحبر الأسود: كانت أزراراً ومؤشّرات سوداء في اللوحات */
    public function test_no_ink_backgrounds_or_borders_in_the_dashboard_styles(): void
    {
        $css = $this->css();

        $this->assertDoesNotMatchRegularExpression('/background(-color)?:\s*var\(--ds-ink\)/', $css);
        $this->assertDoesNotMatchRegularExpression('/border-color:\s*var\(--ds-ink\)/', $css);
    }

    /** لون الفعل مُعرَّف مرّة واحدة وهو أزرق العلامة */
    public function test_accent_token_is_the_brand_blue(): void
    {
        $css = $this->css();

        $this->assertStringContainsString('--ds-accent: var(--ds-brand-600);', $css);
        $this->assertStringContainsString('--ds-brand-600: #2563eb;', $css);
        $this->assertMatchesRegularExpression('/\.btn-primary\s*\{[^}]*--ds-brand-600|\.btn-login\s*\{[^}]*--ds-brand-600/s', $css);
    }

    /** شعار الشريط الجانبي بلاطة زرقاء لا سوداء */
    public function test_sidebar_logo_is_blue(): void
    {
        $sidebar = file_get_contents(resource_path('views/layouts/admin/inc/sidebar.blade.php'));

        $this->assertStringNotContainsString('#09090b', $sidebar);
        $this->assertStringContainsString('#1d4ed8', $sidebar);
    }

    /** تلميح مخطط الاتجاه فاتح بحدّ، لا صندوق أسود */
    public function test_trend_chart_tooltip_is_light(): void
    {
        $chart = file_get_contents(resource_path('views/components/trend-chart.blade.php'));

        $this->assertStringContainsString('backgroundColor: surface', $chart);
        $this->assertStringNotContainsString('backgroundColor: ink', $chart);
    }

    /** «الفصل الأول · 2022–23» بسنة معزولة، لا «2023\2022» مقلوبة */
    public function test_semester_label_isolates_the_year(): void
    {
        $semester = new Semester(['name' => 'الفصل الدراسي الأول 2022\\2023']);

        $this->assertSame("الفصل الأول · \u{2066}2022–23\u{2069}", $semester->label);
        $this->assertSame('فصل صيفي', (new Semester(['name' => 'فصل صيفي']))->label);
    }

    /** لوحة الإدارة ورأس مشروع المشرف يعرضان الاسم المرتّب لا الخام */
    public function test_admin_dashboard_and_supervisor_project_show_the_label(): void
    {
        $semester = Semester::current();
        $admin = Admin::first();
        if (! $semester || ! $admin) {
            $this->markTestSkipped('لا فصل أو أدمن.');
        }

        $this->actingAs($admin, 'admin')->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($semester->label);

        $project = Project::whereNotNull('supervisor_id')->whereIn('status', ['accept', 'complete'])->with('semester')->first();
        if (! $project || ! $project->semester) {
            return;
        }

        $this->actingAs(Supervisor::find($project->supervisor_id), 'supervisor')
            ->get(route('supervisor.projects.show', $project->id))
            ->assertOk()
            ->assertSee($project->semester->label)
            ->assertDontSee($project->semester->name);
    }
}
