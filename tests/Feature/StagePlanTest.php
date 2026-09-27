<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\SupervisorStage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * خطة المراحل: المرحلة تُعرَّف مرّة بقالبها فتصل إلى كل المجموعات.
 *
 * داخل معاملة تُرجَع في \u200EtearDown\u200E، والقرص الخاص مزيَّف.
 */
class StagePlanTest extends TestCase
{
    private Supervisor $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Storage::fake('local');
        DB::beginTransaction();

        // مشرف له مجموعة مقبولة غير مقيَّمة وأخرى معلّقة في الفصل الحالي
        $supervisor = Supervisor::whereHas('projects', fn ($q) => $q->where('status', 'accept')->whereNull('grade'))
            ->whereHas('projects', fn ($q) => $q->where('status', 'request'))
            ->first();

        if (! $supervisor) {
            $this->markTestSkipped('لا مشرف بمجموعة مقبولة وطلب معلّق.');
        }

        $this->supervisor = $supervisor;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function addStage(array $extra = [])
    {
        return $this->actingAs($this->supervisor, 'supervisor')
            ->post(route('supervisor.plan.store'), array_merge([
                'title' => 'الفصل الأول — المقدمة',
                'due_date' => now()->addWeeks(3)->toDateString(),
                'instructions' => 'اتبعوا القالب المرفق.',
                'template' => UploadedFile::fake()->create('قالب الفصل الأول.docx', 40),
            ], $extra));
    }

    private function stage(): SupervisorStage
    {
        return SupervisorStage::where('supervisor_id', $this->supervisor->id)->latest('id')->firstOrFail();
    }

    private function activeProjects()
    {
        return $this->supervisor->projects()->where('status', 'accept')->whereNull('grade')
            ->where('semester_id', \App\Models\Semester::current()->id)->get();
    }

    public function test_a_new_stage_reaches_every_active_group_and_only_them(): void
    {
        $this->addStage()->assertRedirect(route('supervisor.plan'))->assertSessionHas('success');
        $stage = $this->stage();

        $active = $this->activeProjects();
        $this->assertNotEmpty($active);
        foreach ($active as $project) {
            $this->assertTrue(ProjectMilestone::where('project_id', $project->id)->where('stage_id', $stage->id)->exists(),
                "لم تصل المرحلة إلى المجموعة {$project->id}.");
        }

        // لا تصل إلى المعلّق ولا إلى المقيَّم
        $others = $this->supervisor->projects()->where(fn ($q) => $q->where('status', '!=', 'accept')->orWhereNotNull('grade'))->pluck('id');
        $this->assertSame(0, ProjectMilestone::whereIn('project_id', $others)->where('stage_id', $stage->id)->count());

        Storage::disk('local')->assertExists($stage->template_path);
    }

    /** مجموعة تُقبل لاحقاً تأخذ الخطة كاملة */
    public function test_accepting_a_request_applies_the_plan(): void
    {
        $this->addStage();
        $stage = $this->stage();
        $pending = $this->supervisor->projects()->where('status', 'request')->first();
        DB::table('supervisors')->where('id', $this->supervisor->id)->update(['max_group' => 99]);

        $this->actingAs($this->supervisor->fresh(), 'supervisor')
            ->post(route('supervisor.replay.project', $pending->id), ['btnAccept' => 'accept'])
            ->assertSessionHas('success');

        $this->assertTrue(ProjectMilestone::where('project_id', $pending->id)->where('stage_id', $stage->id)->exists());
    }

    /** تعديل الموعد يسري على غير المنجز، والمنجز سجلّ لا يُعاد كتابته */
    public function test_editing_updates_open_milestones_but_not_done_ones(): void
    {
        $this->addStage();
        $stage = $this->stage();
        [$done, $open] = [
            ProjectMilestone::where('stage_id', $stage->id)->first(),
            ProjectMilestone::where('stage_id', $stage->id)->skip(1)->first(),
        ];
        $done->update(['is_done' => true, 'done_at' => now()]);

        $newDue = now()->addWeeks(6)->toDateString();
        $this->actingAs($this->supervisor, 'supervisor')
            ->post(route('supervisor.plan.update', $stage->id), ['title' => 'الفصل الأول (معدّل)', 'due_date' => $newDue])
            ->assertSessionHas('success');

        $this->assertNotSame('الفصل الأول (معدّل)', $done->fresh()->title, 'المنجز أُعيدت كتابته.');
        if ($open) {
            $this->assertSame('الفصل الأول (معدّل)', $open->fresh()->title);
            $this->assertSame($newDue, $open->fresh()->due_date->toDateString());
        }
    }

    /** الحذف يُزيل غير المنجز، والمنجز يبقى مرحلةً خاصة بمجموعته */
    public function test_deleting_keeps_done_milestones(): void
    {
        $this->addStage();
        $stage = $this->stage();
        $done = ProjectMilestone::where('stage_id', $stage->id)->first();
        $done->update(['is_done' => true, 'done_at' => now()]);
        $openCount = ProjectMilestone::where('stage_id', $stage->id)->where('is_done', false)->count();

        $this->actingAs($this->supervisor, 'supervisor')
            ->delete(route('supervisor.plan.destroy', $stage->id))
            ->assertSessionHas('success');

        $this->assertNull(SupervisorStage::find($stage->id));
        $this->assertNotNull($done->fresh(), 'المنجز حُذف.');
        $this->assertNull($done->fresh()->stage_id);
        $this->assertSame(0, ProjectMilestone::where('stage_id', $stage->id)->count());
        $this->assertGreaterThanOrEqual(0, $openCount);
        Storage::disk('local')->assertMissing($stage->template_path);
    }

    // ═══ القالب ═══

    public function test_a_team_member_downloads_the_template_and_an_outsider_cannot(): void
    {
        $this->addStage();
        $stage = $this->stage();
        $project = $this->activeProjects()->first();
        $member = Student::find($project->group()->value('student_id'));

        $this->actingAs($member, 'student')->get(route('stages.template', $stage->id))->assertOk();

        $this->app['auth']->forgetGuards();
        $outsider = Student::whereDoesntHave('groups.project', fn ($q) => $q->where('supervisor_id', $this->supervisor->id))->first();
        $this->actingAs($outsider, 'student')->get(route('stages.template', $stage->id))->assertForbidden();
    }

    public function test_the_template_shows_inside_the_students_milestone(): void
    {
        $this->addStage();
        $stage = $this->stage();
        $project = $this->activeProjects()->first();
        $member = Student::find($project->group()->value('student_id'));

        $this->actingAs($member, 'student')
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('قالب المشرف')
            ->assertSee(route('stages.template', $stage->id), false);
    }

    public function test_another_supervisor_cannot_touch_the_stage(): void
    {
        $this->addStage();
        $stage = $this->stage();
        $other = Supervisor::where('id', '!=', $this->supervisor->id)->first();

        $this->app['auth']->forgetGuards();
        $this->actingAs($other, 'supervisor')->post(route('supervisor.plan.update', $stage->id), ['title' => 'x'])->assertForbidden();
        $this->actingAs($other, 'supervisor')->delete(route('supervisor.plan.destroy', $stage->id))->assertForbidden();
        $this->assertNotNull(SupervisorStage::find($stage->id));
    }

    public function test_a_dangerous_template_type_is_refused(): void
    {
        $this->addStage(['template' => UploadedFile::fake()->create('evil.php', 1)])->assertSessionHasErrors('template');
        $this->assertSame(0, SupervisorStage::where('supervisor_id', $this->supervisor->id)->count());
    }
}
