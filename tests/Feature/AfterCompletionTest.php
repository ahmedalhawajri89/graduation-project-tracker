<?php

namespace Tests\Feature;

use App\Models\MilestoneSubmission;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * بعد اكتمال المشروع لا تُطلب مراحل: لا تسليم (الخادم يرفض)، ولا «متأخّرة»
 * ولا زر تسليم في لوحة الطالب — التالي المناقشة والدرجة. والمشروع الجاري
 * يسلّم كما كان.
 */
class AfterCompletionTest extends TestCase
{
    private Project $project;
    private Student $student;
    private ProjectMilestone $milestone;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        DB::beginTransaction();

        $project = Project::where('status', 'accept')->whereNull('grade')->whereHas('group')->whereNotNull('supervisor_id')->first();
        if (! $project) {
            $this->markTestSkipped('لا مشروع جارٍ بفريق.');
        }

        $this->project = $project;
        $this->student = Student::findOrFail($project->group()->value('student_id'));
        // مرحلة مفتوحة فات موعدها — ما كان يظهر «متأخّرة»
        $this->milestone = ProjectMilestone::create([
            'project_id' => $project->id, 'title' => 'مرحلة اختبار الاكتمال',
            'due_date' => today()->subDays(5), 'status' => ProjectMilestone::OPEN, 'is_done' => false,
        ]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function submit()
    {
        return $this->actingAs($this->student, 'student')
            ->post(route('student.milestones.submit', $this->milestone->id), ['note' => 'تسليم للاختبار']);
    }

    public function test_a_running_project_still_submits(): void
    {
        $this->submit()->assertSessionHas('success');

        $this->assertTrue(MilestoneSubmission::where('milestone_id', $this->milestone->id)->exists());
    }

    public function test_a_completed_project_refuses_submissions(): void
    {
        $this->project->update(['status' => 'complete']);

        $this->submit()->assertSessionHas('fail');

        $this->assertFalse(MilestoneSubmission::where('milestone_id', $this->milestone->id)->exists());
        $this->assertSame(ProjectMilestone::OPEN, $this->milestone->fresh()->status);
    }

    public function test_dashboard_of_a_completed_project_shows_no_late_stage_or_submit_button(): void
    {
        $running = $this->actingAs($this->student, 'student')->get(route('student.dashboard'))->assertOk();
        $running->assertSee('مرحلة اختبار الاكتمال')->assertSee('متأخّرة');

        $this->project->update(['status' => 'complete']);

        $this->actingAs($this->student, 'student')->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('اكتمل المشروع')
            ->assertSee('لا تسليمات بعد الاكتمال')
            ->assertDontSee('متأخّرة')
            ->assertDontSee('تسليم المرحلة');
    }
}
