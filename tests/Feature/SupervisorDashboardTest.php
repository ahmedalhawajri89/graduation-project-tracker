<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Semester;
use App\Models\Supervisor;
use App\Models\SupervisorStage;
use App\Support\StagePlan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * لوحة المشرف: المؤشرات، والمرحلة الحالية، والقادم، وآخر النشاط.
 *
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class SupervisorDashboardTest extends TestCase
{
    private Supervisor $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $supervisor = Supervisor::whereHas('projects', fn ($q) => $q->where('status', 'accept')
            ->where('semester_id', Semester::current()->id))->first();

        if (! $supervisor) {
            $this->markTestSkipped('لا مشرف بمجموعة مقبولة في الفصل الحالي.');
        }

        $this->supervisor = $supervisor;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function dashboard()
    {
        return $this->actingAs($this->supervisor, 'supervisor')->get(route('supervisor.dashboard'))->assertOk();
    }

    private function activeProject(): Project
    {
        return $this->supervisor->projects()->where('status', 'accept')
            ->where('semester_id', Semester::current()->id)->firstOrFail();
    }

    private function addStage(string $title, int $inDays): SupervisorStage
    {
        $stage = SupervisorStage::create([
            'supervisor_id' => $this->supervisor->id,
            'semester_id' => Semester::current()->id,
            'title' => $title,
            'due_date' => today()->addDays($inDays)->toDateString(),
        ]);
        StagePlan::propagate($stage);

        return $stage;
    }

    public function test_without_a_plan_it_invites_to_create_one(): void
    {
        $this->supervisor->stages()->delete();

        $this->dashboard()
            ->assertSee('ارسم خطة الفصل مرّة واحدة')
            ->assertSee(route('supervisor.plan', ['new' => 1]), false);
    }

    /** القادم: مرحلة خلال أسبوعين تظهر، والبعيدة لا */
    public function test_upcoming_lists_stages_due_within_two_weeks(): void
    {
        $this->addStage('مرحلة قريبة للاختبار', 5);
        $this->addStage('مرحلة بعيدة للاختبار', 40);

        $this->dashboard()
            ->assertSee('مرحلة قريبة للاختبار')
            ->assertDontSee('مرحلة بعيدة للاختبار')
            ->assertDontSee('ارسم خطة الفصل مرّة واحدة');
    }

    /** المرحلة الحالية في البطاقة: أول غير منجزة */
    public function test_the_card_shows_the_groups_current_stage(): void
    {
        $project = $this->activeProject();
        $project->milestones()->update(['is_done' => true, 'done_at' => now()]);
        $project->milestones()->create(['title' => 'المرحلة الجارية للاختبار', 'due_date' => today()->addDays(3), 'is_done' => false]);

        $this->dashboard()->assertSee('المرحلة الجارية للاختبار');
    }

    /** النشاط من مجموعاته وحدها — لا رسائل مجموعات مشرف آخر */
    public function test_activity_shows_only_the_supervisors_groups(): void
    {
        $mine = $this->activeProject();
        $student = $mine->students()->first();
        ProjectComment::create([
            'project_id' => $mine->id,
            'author_type' => $student::class,
            'author_id' => $student->id,
            'body' => 'رسالة من مجموعتي للاختبار',
        ]);

        $other = Project::where('supervisor_id', '!=', $this->supervisor->id)->where('status', 'accept')->first();
        if ($other) {
            ProjectComment::create([
                'project_id' => $other->id,
                'author_type' => $student::class,
                'author_id' => $student->id,
                'body' => 'رسالة مجموعة غريبة للاختبار',
            ]);
        }

        $this->dashboard()
            ->assertSee('رسالة من مجموعتي للاختبار')
            ->assertDontSee('رسالة مجموعة غريبة للاختبار');
    }

    /** رسالة طالب غير مقروءة تُعدّ في المؤشر وعلى زرّ نقاش البطاقة */
    public function test_unread_messages_are_counted(): void
    {
        $project = $this->activeProject();
        $student = $project->students()->first();
        DB::table('discussion_reads')->where('reader_type', Supervisor::class)->where('reader_id', $this->supervisor->id)->delete();
        ProjectComment::create([
            'project_id' => $project->id,
            'author_type' => $student::class,
            'author_id' => $student->id,
            'body' => 'جديد',
        ]);

        $this->dashboard()->assertSee('في النقاش — افتحه');
    }

    /** الاستعلامات لا تتضاعف بعدد المجموعات */
    public function test_the_page_runs_a_bounded_number_of_queries(): void
    {
        $this->actingAs($this->supervisor, 'supervisor');

        DB::enableQueryLog();
        $this->get(route('supervisor.dashboard'))->assertOk();
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(40, $count, "الصفحة نفّذت {$count} استعلاماً.");
    }
}
