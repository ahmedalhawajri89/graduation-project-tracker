<?php

namespace Tests\Feature;

use App\Models\MilestoneSubmission;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectMilestone;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * لوحة الطالب: بطاقة المشروع، والمرحلة الحالية، وآخر النشاط.
 *
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class StudentDashboardTest extends TestCase
{
    private Project $project;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $project = Project::where('status', 'accept')->whereNull('grade')->whereNotNull('supervisor_id')
            ->whereHas('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع جارٍ بفريق ومشرف.');
        }

        $this->project = $project;
        $this->student = Student::find($project->group()->value('student_id'));
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function dashboard()
    {
        return $this->actingAs($this->student, 'student')->get(route('student.dashboard'))->assertOk();
    }

    public function test_the_hero_shows_the_project_and_its_supervisor(): void
    {
        $this->dashboard()
            ->assertSee($this->project->title)
            ->assertSee($this->project->supervisor->name)
            ->assertSee('stu-hero', false);
    }

    /** بلا مراحل: حالة تشرح وتقود إلى النقاش — لا «—» ولا «لم تُحدَّد» */
    public function test_without_stages_the_spotlight_explains_and_links_to_the_discussion(): void
    {
        $this->project->milestones()->delete();

        $this->dashboard()
            ->assertSee('لم يضع مشرفك المراحل بعد')
            ->assertSee('بانتظار خطة المشرف')
            ->assertDontSee('لم تُحدَّد');
    }

    /** «مطلوب تعديل» تتقدّم على المرحلة المفتوحة، بملاحظة المشرف وزرّ إعادة التسليم */
    public function test_a_revision_request_takes_the_spotlight(): void
    {
        $this->project->milestones()->delete();
        $this->project->milestones()->create(['title' => 'مرحلة مفتوحة للاختبار', 'due_date' => today()->addDays(2)]);
        $revise = $this->project->milestones()->create([
            'title' => 'مرحلة بطلب تعديل للاختبار',
            'due_date' => today()->addDays(9),
            'status' => ProjectMilestone::REVISION,
        ]);
        MilestoneSubmission::create([
            'milestone_id' => $revise->id,
            'student_id' => $this->student->id,
            'round' => 1,
            'note' => 'تسليم',
            'decision' => ProjectMilestone::REVISION,
            'feedback' => 'ملاحظة تعديل للاختبار',
            'reviewed_at' => now(),
        ]);

        $html = $this->dashboard()->getContent();

        $spotlight = str($html)->after('class="spotlight')->before('</section>');
        $this->assertStringContainsString('مرحلة بطلب تعديل للاختبار', $spotlight);
        $this->assertStringContainsString('ملاحظة تعديل للاختبار', $spotlight);
        $this->assertStringContainsString('إعادة التسليم بعد التعديل', $spotlight);
    }

    /** النشاط من مشروع الطالب وحده */
    public function test_activity_shows_only_the_students_project(): void
    {
        ProjectComment::create([
            'project_id' => $this->project->id,
            'author_type' => Student::class,
            'author_id' => $this->student->id,
            'body' => 'رسالة من مشروعي للاختبار',
        ]);

        $other = Project::where('id', '!=', $this->project->id)->where('status', 'accept')->first();
        if ($other) {
            ProjectComment::create([
                'project_id' => $other->id,
                'author_type' => Student::class,
                'author_id' => $this->student->id,
                'body' => 'رسالة مشروع غريب للاختبار',
            ]);
        }

        $this->dashboard()
            ->assertSee('رسالة من مشروعي للاختبار')
            ->assertDontSee('رسالة مشروع غريب للاختبار');
    }

    public function test_the_page_runs_a_bounded_number_of_queries(): void
    {
        $this->actingAs($this->student, 'student');

        DB::enableQueryLog();
        $this->get(route('student.dashboard'))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(45, $count, "الصفحة نفّذت {$count} استعلاماً.");
    }
}
