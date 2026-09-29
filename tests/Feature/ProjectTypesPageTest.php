<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialize;
use App\Models\SpecializeProject;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * أنواع المشاريع: الحدّان مقابل أحجام الفرق الفعلية، والخارج عنهما يُقال،
 * وتعديل الحدّين يحذّر بما سيصير خارجهما قبل الحفظ.
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class ProjectTypesPageTest extends TestCase
{
    private Admin $admin;
    private SpecializeProject $type;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $admin = Admin::first();
        $type = SpecializeProject::whereHas('projects', fn ($q) => $q->where('status', '!=', 'reject')->has('group'))->first();
        if (! $admin || ! $type) {
            $this->markTestSkipped('لا أدمن أو نوع مستعمَل.');
        }
        $this->admin = $admin;
        $this->type = $type;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function page()
    {
        return $this->actingAs($this->admin, 'admin')
            ->get(route('admin.specialize.projects.index', $this->type->specialize_id))
            ->assertOk();
    }

    private function typeRow()
    {
        return $this->page()->viewData('types')->firstWhere('id', $this->type->id);
    }

    /** التوزيع يطابق عدّ أعضاء كل مشروع غير مرفوض */
    public function test_the_size_distribution_matches_the_teams(): void
    {
        $expected = Project::where('specialize_project_id', $this->type->id)->where('status', '!=', 'reject')
            ->withCount('group')->get()
            ->filter(fn ($p) => $p->group_count > 0)
            ->countBy('group_count')->sortKeys()->all();

        $this->assertEquals($expected, $this->typeRow()->sizes);
    }

    /** تضييق الحدّين يجعل الفرق الأكبر خارجهما — تُعدّ ويُقال ذلك */
    public function test_teams_outside_the_limits_are_counted_and_flagged(): void
    {
        $largest = max(array_keys($this->typeRow()->sizes));
        $this->type->update(['min' => 1, 'max' => max(1, $largest - 1)]);

        $row = $this->typeRow();
        $this->assertGreaterThan(0, $row->outside_count);
        $this->page()->assertSee('خارج الحدود الحالية');

        $this->type->update(['max' => $largest]);
        $this->assertSame(0, $this->typeRow()->outside_count);
    }

    /** زرّ التعديل يحمل أحجام الفرق — للتحذير الحيّ قبل الحفظ */
    public function test_the_edit_button_carries_the_sizes(): void
    {
        $html = $this->page()->getContent();

        $this->assertMatchesRegularExpression('/data-id="' . $this->type->id . '"[^>]*data-sizes="\{[^"]+\}"/', $html);
    }

    public function test_running_projects_are_counted_for_the_current_semester(): void
    {
        $this->assertSame(
            Project::where('specialize_project_id', $this->type->id)->whereIn('status', ['accept', 'complete'])
                ->where('semester_id', Semester::current()->id)->count(),
            $this->typeRow()->current_count
        );
    }

    /** الحذف في القائمة للنوع غير المستعمل وحده — والحارس في المتحكّم يبقى */
    public function test_delete_is_offered_only_for_an_unused_type(): void
    {
        $unused = SpecializeProject::create(['specialize_id' => $this->type->specialize_id, 'name' => 'نوع غير مستعمل', 'min' => 1, 'max' => 2]);

        $html = $this->page()->getContent();
        $this->assertMatchesRegularExpression('/btn-delete"[^>]*data-id="' . $unused->id . '"/', $html);
        $this->assertDoesNotMatchRegularExpression('/btn-delete"[^>]*data-id="' . $this->type->id . '"/', $html);

        $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.specialize.projects.destroy', $this->type->id), ['id' => $this->type->id])
            ->assertSessionHas('fail');
        $this->assertNotNull($this->type->fresh());
    }

    /** التنقّل بين التخصصات — والحالي مُبرَز */
    public function test_the_specialization_tabs_mark_the_current_one(): void
    {
        if (Specialize::active()->count() < 2) {
            $this->markTestSkipped('تخصص واحد.');
        }

        $this->page()->assertSee('aria-current="page"', false)
            ->assertSee(route('admin.specialize.projects.index', Specialize::active()->where('id', '!=', $this->type->specialize_id)->value('id')));
    }

    /** مقطع بطاقة النوع من الصفحة — لفحص رسمه وخلاصته وحده */
    private function card(): string
    {
        $html = $this->page()->getContent();
        $start = strpos($html, 'data-id="' . $this->type->id . '" data-name=');
        $this->assertNotFalse($start);
        $from = strrpos(substr($html, 0, $start), '<article class="pt-card');

        return substr($html, $from, strpos($html, '</article>', $start) - $from);
    }

    /** الخلاصة تذكر الحجم الأكثر شيوعاً ونسبته — والحدود تحتويها كلّها */
    public function test_the_insight_names_the_most_common_size(): void
    {
        $sizes = $this->typeRow()->sizes;
        $this->type->update(['min' => min(array_keys($sizes)), 'max' => max(array_keys($sizes)) + 1]);

        $top = array_search(max($sizes), $sizes);
        $pct = (int) round($sizes[$top] / array_sum($sizes) * 100);

        $card = $this->card();
        $this->assertStringContainsString('الأكثر شيوعاً: فريق من ' . $top, $card);
        $this->assertStringContainsString($pct . '%', $card);
    }

    /** كل الفرق بالحدّ الأعلى: الخلاصة تقترح رفعه */
    public function test_all_teams_at_the_upper_limit_suggest_raising_it(): void
    {
        $sizes = $this->typeRow()->sizes;
        if (count($sizes) !== 1) {
            // فريق واحد الحجم: نُبقي مشاريع النوع كلّها بحجم واحد بحذف ما عداه داخل المعاملة
            $keep = array_search(max($sizes), $sizes);
            Project::where('specialize_project_id', $this->type->id)->withCount('group')->get()
                ->filter(fn ($p) => $p->group_count !== $keep)
                ->each(fn ($p) => $p->update(['status' => 'reject']));
            $sizes = [$keep => 1];
        }
        $size = array_key_first($sizes);
        $this->type->update(['min' => max(1, $size - 1), 'max' => $size]);

        if ($size === 1) {
            $this->markTestSkipped('الحجم 1 لا حدّ أدنى دونه.');
        }

        $this->assertStringContainsString('كل الفرق بالحدّ الأعلى', $this->card());
    }

    /** المدى المسموح مظلّل: الأعمدة داخله تحمل \u200Eis-allowed\u200E بعدد أحجامه */
    public function test_the_chart_shades_the_allowed_range(): void
    {
        $this->type->update(['min' => 2, 'max' => 3]);

        $card = $this->card();
        preg_match('/<div class="pt-chart".*?<\/div>\s*<div class="pt-axis"/s', $card, $m);
        $this->assertSame(2, substr_count($m[0] ?? '', 'is-allowed'));
    }

    public function test_the_summary_adds_up_teams_and_outside(): void
    {
        $types = $this->page()->viewData('types');

        $this->page()
            ->assertSeeInOrder(['pt-sum', (string) $types->sum(fn ($t) => array_sum($t->sizes)), 'فريقاً مسجّلاً'], false)
            ->assertSeeInOrder(['pt-sum', (string) $types->sum('outside_count')], false);
    }

    public function test_the_page_query_count_stays_bounded(): void
    {
        $this->actingAs($this->admin, 'admin');
        DB::enableQueryLog();
        $this->get(route('admin.specialize.projects.index', $this->type->specialize_id))->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(20, $queries, "صفحة الأنواع نفّذت {$queries} استعلاماً.");
    }
}
