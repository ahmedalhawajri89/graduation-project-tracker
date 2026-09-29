<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialize;
use App\Models\SpecializeProject;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * صفحة التخصصات لوحة جاهزية: كم بلا فريق، وكم مقعداً متاحاً، وكم مشروعاً يجري،
 * وما يجري على كل نوع — بأرقام تطابق القوائم التي تفتحها.
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class SpecializePageTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $admin = Admin::first();
        if (! $admin || ! Specialize::active()->exists()) {
            $this->markTestSkipped('لا أدمن أو تخصص نشط.');
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
        return $this->actingAs($this->admin, 'admin')->get(route('admin.specialize.index'))->assertOk();
    }

    private function spec(Specialize $s)
    {
        return $this->page()->viewData('specializes')->firstWhere('id', $s->id);
    }

    /** «بلا فريق» بتعريف جدول الطلاب نفسه — فيطابق الرقمُ القائمةَ التي يفتحها */
    public function test_students_without_a_team_match_the_students_filter(): void
    {
        $specialize = Specialize::active()->has('students')->first();
        $spec = $this->spec($specialize);

        $expected = Student::where('specialize_id', $specialize->id)
            ->whereDoesntHave('groups.project', fn ($q) => $q->whereIn('status', ['accept', 'complete']))
            ->count();

        $this->assertSame($expected, $spec->students_count - $spec->students_in_team_count);
        // مهرَّباً كما في HTML: الفاصل بين المعاملات \u200E&amp;\u200E
        $this->page()->assertSee(route('admin.students.index', ['specialize' => $specialize->id, 'group' => 'none']));
    }

    /** المقاعد: مجموع حدود المشرفين، والمشغول ما يجري لهم هذا الفصل */
    public function test_seats_are_capacity_minus_running_projects(): void
    {
        $specialize = Specialize::active()->has('supervisors')->first();
        $spec = $this->spec($specialize);
        $semesterId = Semester::current()->id;

        $this->assertSame((int) Supervisor::where('specialize_id', $specialize->id)->sum('max_group'), $spec->seats_total);
        $this->assertSame(
            Project::whereIn('status', ['accept', 'complete'])->where('semester_id', $semesterId)
                ->whereHas('supervisor', fn ($q) => $q->where('specialize_id', $specialize->id))->count(),
            $spec->seats_used
        );
    }

    /** المشاريع الجارية على كل نوع — ومجموعها للتخصص */
    public function test_running_projects_are_counted_per_type(): void
    {
        $specialize = Specialize::active()->has('projects')->first();
        $spec = $this->spec($specialize);
        $semesterId = Semester::current()->id;

        foreach ($spec->projects as $type) {
            $this->assertSame(
                Project::where('specialize_project_id', $type->id)->whereIn('status', ['accept', 'complete'])
                    ->where('semester_id', $semesterId)->count(),
                $type->current_count
            );
        }
        $this->assertSame($spec->projects->sum('current_count'), $spec->running_count);
    }

    /** تخصص بلا نوع مشروع «غير مكتمل» بمخرجه — ويصير جاهزاً بعد إضافة نوع */
    public function test_an_incomplete_specialization_says_so_and_how_to_fix_it(): void
    {
        $specialize = Specialize::create(['name' => 'تخصص للاختبار']);
        Supervisor::where('id', Supervisor::value('id'))->update(['specialize_id' => $specialize->id, 'max_group' => 3]);

        $this->page()->assertSee('غير مكتمل')->assertSee('أضف أول نوع مشروع');
        $this->assertSame('incomplete', $this->spec($specialize)->projects_count === 0 ? 'incomplete' : 'ready');

        SpecializeProject::create(['specialize_id' => $specialize->id, 'name' => 'نوع للاختبار', 'min' => 1, 'max' => 3]);

        $spec = $this->spec($specialize);
        $this->assertSame(1, $spec->projects_count);
        $this->assertGreaterThan(0, $spec->supervisors_available_count);
    }

    /** الحذف في القائمة لتخصص فارغ وحده — والمأهول يُوقَف (حارس المتحكّم نفسه) */
    public function test_delete_is_offered_only_for_an_empty_specialization(): void
    {
        $empty = Specialize::create(['name' => 'تخصص فارغ للاختبار']);
        $html = $this->page()->getContent();

        preg_match('/data-id="' . $empty->id . '" data-name="تخصص فارغ للاختبار">\s*<i class="ti ti-trash/', $html, $m);
        $this->assertNotEmpty($m, 'لا حذف لتخصص فارغ.');

        $busy = Specialize::active()->has('students')->first();
        $this->assertDoesNotMatchRegularExpression('/btn-delete" data-bs-toggle="modal"\s+data-bs-target="#deleteModal" data-id="' . $busy->id . '"/', $html);
    }

    public function test_the_page_query_count_stays_bounded(): void
    {
        $this->actingAs($this->admin, 'admin');
        DB::enableQueryLog();
        $this->get(route('admin.specialize.index'))->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(25, $queries, "صفحة التخصصات نفّذت {$queries} استعلاماً.");
    }
}
