<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Group;
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
