<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * صفحة الفصول: الفصل الحالي بمؤشّراته، وكل الفصول على خطّ زمني بأرقامها،
 * والتفعيل — أخطر مفتاح في النظام — يُسجَّل في سجلّ التدقيق.
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class SemesterPageTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $admin = Admin::first();
        if (! $admin || ! Semester::where('is_active', true)->exists()) {
            $this->markTestSkipped('لا أدمن أو فصل نشط.');
        }
        $this->admin = $admin;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function page()
    {
        return $this->actingAs($this->admin, 'admin')->get(route('admin.semesters.index'))->assertOk();
    }

    /** الإكمال من المقبولة، ومتوسط الدرجة — لكل فصل من استعلامَي تجميع */
    public function test_each_semester_carries_its_completion_and_average_grade(): void
    {
        $terms = $this->page()->viewData('semesters');

        foreach ($terms as $term) {
            $accepted = Project::where('semester_id', $term->id)->whereIn('status', ['accept', 'complete'])->count();
            $complete = Project::where('semester_id', $term->id)->where('status', 'complete')->count();
            $avg = Project::where('semester_id', $term->id)->whereNotNull('grade')->avg('grade');

            $this->assertSame($accepted ? (int) round($complete / $accepted * 100) : null, $term->completion, $term->name);
            $this->assertSame($avg === null ? null : round((float) $avg, 1), $term->avg_grade, $term->name);
        }
    }

    /** الاسم مرتّباً لا خاماً بشرطة مائلة */
    public function test_names_are_shown_tidy(): void
    {
        $active = Semester::where('is_active', true)->first();
        $parts = $active->parts();

        $html = $this->page()->getContent();

        $this->assertStringContainsString(e($parts['term']), $html);
        if ($parts['year']) {
            $this->assertStringContainsString($parts['year'], $html);
            preg_match('/<h2 class="tm-now-name">(.*?)<\/h2>/s', $html, $m);
            $this->assertStringNotContainsString('\\', $m[1] ?? '');
        }
    }

    /** التفعيل يُسجَّل بما كان وما صار — كان يقلب النظام بلا أثر */
    public function test_activation_is_audited_with_from_and_to(): void
    {
        $previous = Semester::where('is_active', true)->first();
        $next = Semester::create(['name' => 'الفصل الدراسي الصيفي 2099\\2100']);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.semesters.activate', $next->id))
            ->assertSessionHas('success');

        $this->assertTrue($next->fresh()->is_active);
        $this->assertFalse($previous->fresh()->is_active);

        $log = AuditLog::where('action', 'semester.activated')->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame(['from' => $previous->name, 'to' => $next->name], $log->changes['semester']);
        $this->assertSame('تفعيل فصل دراسي', $log->action_label);
    }

    /** الحذف في القائمة لفصل فارغ غير نشط وحده — كحارس المتحكّم */
    public function test_delete_is_offered_only_for_an_empty_inactive_semester(): void
    {
        $empty = Semester::create(['name' => 'فصل فارغ للاختبار']);

        $html = $this->page()->getContent();

        $this->assertMatchesRegularExpression('/btn-delete"[^>]*data-id="' . $empty->id . '"/', $html);

        $withProjects = Semester::has('projects')->where('is_active', false)->first();
        if ($withProjects) {
            $this->assertDoesNotMatchRegularExpression('/btn-delete"[^>]*data-id="' . $withProjects->id . '"/', $html);
        }

        $active = Semester::where('is_active', true)->first();
        $this->assertDoesNotMatchRegularExpression('/btn-delete"[^>]*data-id="' . $active->id . '"/', $html);
    }

    /** نافذة التفعيل تطلب تأكيداً صريحاً — الزرّ معطّل حتى يُعلَّم */
    public function test_the_activation_modal_requires_confirmation(): void
    {
        $this->page()
            ->assertSee('id="activate-confirm"', false)
            ->assertSee('id="activate-submit" disabled', false);
    }

    public function test_the_page_query_count_stays_bounded(): void
    {
        $this->actingAs($this->admin, 'admin');
        DB::enableQueryLog();
        $this->get(route('admin.semesters.index'))->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(20, $queries, "صفحة الفصول نفّذت {$queries} استعلاماً.");
    }
}
