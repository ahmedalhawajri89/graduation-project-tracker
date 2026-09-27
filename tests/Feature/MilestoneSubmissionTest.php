<?php

namespace Tests\Feature;

use App\Models\MilestoneSubmission;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * تسليم المراحل: الفريق يسلّم، والمشرف يعتمد أو يطلب تعديلاً، فيعيد الفريق.
 *
 * داخل معاملة تُرجَع في \u200EtearDown\u200E، والقرص الخاص مزيَّف.
 */
class MilestoneSubmissionTest extends TestCase
{
    private Project $project;
    private ProjectMilestone $milestone;
    private Student $member;
    private Supervisor $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Storage::fake('local');
        DB::beginTransaction();

        $project = Project::where('status', 'accept')->whereNull('grade')->whereNotNull('supervisor_id')
            ->whereHas('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع جارٍ بفريق ومشرف.');
        }

        $this->project = $project;
        $this->member = Student::find($project->group()->value('student_id'));
        $this->supervisor = $project->supervisor;
        $this->milestone = $project->milestones()->create([
            'title' => 'مرحلة اختبار التسليم',
            'due_date' => today()->addWeek(),
        ]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function submit(array $data = [], ?Student $as = null)
    {
        return $this->actingAs($as ?? $this->member, 'student')
            ->post(route('student.milestones.submit', $this->milestone->id), $data + [
                'note' => 'أنهينا الفصل الأول.',
                'file' => UploadedFile::fake()->create('الفصل الأول.pdf', 120, 'application/pdf'),
            ]);
    }

    private function review(string $decision, ?string $feedback = null)
    {
        $this->app['auth']->forgetGuards();

        return $this->actingAs($this->supervisor, 'supervisor')
            ->post(route('supervisor.milestones.review', $this->milestone->id), array_filter([
                'decision' => $decision,
                'feedback' => $feedback,
            ]));
    }

    public function test_a_member_submits_and_the_stage_awaits_review(): void
    {
        $this->submit()->assertSessionHas('success');

        $this->milestone->refresh();
        $this->assertSame(ProjectMilestone::SUBMITTED, $this->milestone->status);
        $this->assertFalse($this->milestone->is_done);

        $sub = $this->milestone->submissions()->first();
        $this->assertSame(1, $sub->round);
        Storage::disk('local')->assertExists($sub->file_path);
    }

    public function test_an_empty_submission_is_refused(): void
    {
        $this->submit(['note' => '', 'file' => null])->assertSessionHasErrors('note');
        $this->assertSame(ProjectMilestone::OPEN, $this->milestone->fresh()->status);
    }

    public function test_an_outsider_cannot_submit(): void
    {
        $outsider = Student::whereDoesntHave('groups', fn ($q) => $q->where('project_id', $this->project->id))->first();

        $this->submit([], $outsider)->assertForbidden();
        $this->assertSame(0, $this->milestone->submissions()->count());
    }

    public function test_a_submitted_stage_cannot_be_submitted_twice(): void
    {
        $this->submit();
        $this->submit()->assertSessionHas('fail');

        $this->assertSame(1, $this->milestone->submissions()->count());
    }

    /** طلب التعديل بلا سبب لا يُقبل — الفريق يحتاج ما يعدّله */
    public function test_a_revision_needs_feedback(): void
    {
        $this->submit();
        $this->review('revision')->assertSessionHasErrors('feedback');

        $this->assertSame(ProjectMilestone::SUBMITTED, $this->milestone->fresh()->status);
    }

    /** الدورة كاملة: تسليم ← تعديل ← إعادة ← اعتماد */
    public function test_the_full_revision_cycle(): void
    {
        $this->submit();
        $this->review('revision', 'ينقص مخطط الكيانات.')->assertSessionHas('success');

        $this->milestone->refresh();
        $this->assertSame(ProjectMilestone::REVISION, $this->milestone->status);
        $first = $this->milestone->submissions()->first();
        $this->assertSame('revision', $first->decision);
        $this->assertSame('ينقص مخطط الكيانات.', $first->feedback);

        // الملاحظة تظهر للفريق في صفحته
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->member, 'student')->get(route('student.dashboard'))
            ->assertOk()->assertSee('ينقص مخطط الكيانات.')->assertSee('إعادة التسليم بعد التعديل');

        $this->submit(['note' => 'أضفنا المخطط.'])->assertSessionHas('success');
        $this->assertSame(2, $this->milestone->submissions()->first()->round);

        $this->review('approve')->assertSessionHas('success');
        $this->milestone->refresh();
        $this->assertSame(ProjectMilestone::APPROVED, $this->milestone->status);
        $this->assertTrue($this->milestone->is_done);
        $this->assertNotNull($this->milestone->done_at);
    }

    public function test_an_approved_stage_takes_no_new_submission(): void
    {
        $this->submit();
        $this->review('approve');

        $this->app['auth']->forgetGuards();
        $this->submit()->assertSessionHas('fail');
        $this->assertSame(1, $this->milestone->submissions()->count());
    }

    public function test_another_supervisor_cannot_review(): void
    {
        $this->submit();
        $other = Supervisor::where('id', '!=', $this->supervisor->id)->first();

        $this->app['auth']->forgetGuards();
        $this->actingAs($other, 'supervisor')
            ->post(route('supervisor.milestones.review', $this->milestone->id), ['decision' => 'approve'])
            ->assertForbidden();
    }

    /** بانتظار المراجعة ليست متأخّرة: الانتظار على المشرف */
    public function test_a_submitted_stage_past_due_is_not_late(): void
    {
        $this->milestone->update(['due_date' => today()->subDays(3)]);
        $this->assertTrue($this->milestone->fresh()->isLate());

        $this->submit();
        $this->assertFalse($this->milestone->fresh()->isLate());
    }

    public function test_the_file_downloads_for_the_team_and_supervisor_only(): void
    {
        $this->submit();
        $sub = MilestoneSubmission::where('milestone_id', $this->milestone->id)->first();

        $this->actingAs($this->member, 'student')->get(route('submissions.file', $sub->id))->assertOk();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->supervisor, 'supervisor')->get(route('submissions.file', $sub->id))->assertOk();

        $this->app['auth']->forgetGuards();
        $outsider = Student::whereDoesntHave('groups', fn ($q) => $q->where('project_id', $this->project->id))->first();
        $this->actingAs($outsider, 'student')->get(route('submissions.file', $sub->id))->assertForbidden();
    }

    /** الإنجاز المباشر من المشرف يعتمد التسليم المعلّق معه */
    public function test_toggling_done_approves_a_pending_submission(): void
    {
        $this->submit();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->supervisor, 'supervisor')
            ->post(route('supervisor.milestones.toggle', $this->milestone->id));

        $this->assertSame(ProjectMilestone::APPROVED, $this->milestone->fresh()->status);
        $this->assertSame('approved', $this->milestone->submissions()->first()->decision);
    }

    /** المشرف يرى ما ينتظر مراجعته في «ما يحتاجني الآن» */
    public function test_the_supervisor_dashboard_lists_pending_reviews(): void
    {
        $this->submit();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->supervisor, 'supervisor')->get(route('supervisor.dashboard'))
            ->assertOk()
            ->assertSee('بانتظار مراجعتك');
    }
}
