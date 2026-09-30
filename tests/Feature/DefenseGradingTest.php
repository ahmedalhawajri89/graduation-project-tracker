<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Defense;
use App\Models\DefenseMember;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Supervisor;
use App\Notifications\ProjectActivityNotify;
use App\Support\DefenseScheduler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * رصد درجة لجنة المناقشة: كل عضو يرصد بعد بدئها ودون أن يرى درجة زميله،
 * والنهائية متوسطهما تُشعر الفريق وتُسجَّل، والتعديل حتى الاعتماد. والممتحن
 * يطّلع على ملفات المشروع، وغير العضو لا يرى المناقشة.
 */
class DefenseGradingTest extends TestCase
{
    private Project $project;
    private Supervisor $supervisor;
    private Supervisor $examiner;
    private Defense $defense;

    protected function setUp(): void
    {
        parent::setUp();

        if (! DefenseScheduler::enabled()) {
            $this->markTestSkipped('جداول المناقشات غير موجودة في قاعدة الاختبار.');
        }

        Notification::fake();
        DB::beginTransaction();

        // مشروع بملفات إن وُجد — ليُختبر اطّلاع الممتحن عليها
        $project = Project::whereHas('group')->whereNotNull('supervisor_id')->whereHas('files')->first()
            ?? Project::whereHas('group')->whereNotNull('supervisor_id')->first();
        if (! $project) {
            $this->markTestSkipped('لا مشروع بفريق.');
        }
        $project->update(['status' => 'complete', 'grade' => null, 'grade_locked_at' => null, 'evaluation_note' => null]);
        Defense::where('project_id', $project->id)->delete();

        $this->project = $project->fresh();
        $this->supervisor = Supervisor::findOrFail($project->supervisor_id);
        $this->examiner = Supervisor::whereKeyNot($project->supervisor_id)->firstOrFail();

        // مناقشة جرت قبل ساعة — يُفتح الرصد
        $this->defense = Defense::create([
            'project_id' => $project->id, 'starts_at' => now()->subHour(), 'duration_minutes' => 45,
            'mode' => 'online', 'meeting_url' => 'https://meet.google.com/abc-defg-hij', 'status' => 'scheduled',
        ]);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $this->supervisor->id, 'role' => 'supervisor']);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $this->examiner->id, 'role' => 'examiner']);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function grade(Supervisor $who, $grade, ?string $comments = null)
    {
        return $this->actingAs($who, 'supervisor')
            ->post(route('supervisor.defenses.grade', $this->defense->id), ['grade' => $grade, 'comments' => $comments]);
    }

    public function test_final_grade_is_the_committee_average_and_the_team_is_notified(): void
    {
        $this->grade($this->supervisor, 90, 'عرض واضح')->assertSessionHas('success');
        $this->assertNull($this->project->fresh()->grade, 'لا نهائية قبل اكتمال اللجنة');
        Notification::assertSentTo($this->examiner, ProjectActivityNotify::class);

        $this->grade($this->examiner, 81, 'ينقص التوثيق')->assertSessionHas('success');

        $project = $this->project->fresh();
        $this->assertSame(85.5, (float) $project->grade);
        $this->assertStringContainsString('ينقص التوثيق', $project->evaluation_note);
        $this->assertSame('done', $this->defense->fresh()->status);
        $this->assertTrue(AuditLog::where('action', 'grade.set')->where('subject_id', $project->id)->exists());
        foreach ($project->students() as $student) {
            Notification::assertSentTo($student, ProjectActivityNotify::class, fn ($n) => str_contains($n->toArray($student)['msg'], '85.5'));
        }
    }

    public function test_three_member_committee_waits_for_all_and_averages_them(): void
    {
        $third = Supervisor::whereNotIn('id', [$this->supervisor->id, $this->examiner->id])->firstOrFail();
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $third->id, 'role' => 'examiner']);

        $this->grade($this->supervisor, 90);
        $this->grade($this->examiner, 80);
        $this->assertNull($this->project->fresh()->grade, 'لا نهائية قبل الممتحن الثالث');
        $this->assertSame('scheduled', $this->defense->fresh()->status);

        $this->grade($third, 70)->assertSessionHas('success');
        $this->assertSame(80.0, (float) $this->project->fresh()->grade);
        $this->assertSame('done', $this->defense->fresh()->status);
    }

    public function test_a_supervisor_weight_splits_the_rest_between_examiners(): void
    {
        config(['defenses.supervisor_weight' => 40]);
        $third = Supervisor::whereNotIn('id', [$this->supervisor->id, $this->examiner->id])->firstOrFail();
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $third->id, 'role' => 'examiner']);

        $this->grade($this->supervisor, 90);
        $this->grade($this->examiner, 80);
        $this->grade($third, 70);

        // 90×40% + 80×30% + 70×30% = 81 (المتوسط العادي 80)
        $this->assertSame(81.0, (float) $this->project->fresh()->grade);
        $this->actingAs($this->supervisor, 'supervisor')->get(route('supervisor.defenses.show', $this->defense->id))
            ->assertSee('وزنه 40%')->assertSee('وزنه 30%');
    }

    public function test_an_invalid_weight_falls_back_to_the_average(): void
    {
        config(['defenses.supervisor_weight' => 150]);

        $this->grade($this->supervisor, 90);
        $this->grade($this->examiner, 80);

        $this->assertSame(85.0, (float) $this->project->fresh()->grade);
    }

    public function test_grading_opens_only_when_the_defense_starts(): void
    {
        $this->defense->update(['starts_at' => now()->addDay()]);

        $this->grade($this->supervisor, 90)->assertSessionHas('fail');
        $this->assertNull(DefenseMember::where('supervisor_id', $this->supervisor->id)->where('defense_id', $this->defense->id)->value('grade'));
    }

    public function test_a_member_does_not_see_the_other_grade_before_grading(): void
    {
        $this->grade($this->examiner, 77);

        $this->actingAs($this->supervisor, 'supervisor')->get(route('supervisor.defenses.show', $this->defense->id))
            ->assertOk()
            ->assertViewHas('revealed', false)
            ->assertDontSee('>77<', false);

        $this->grade($this->supervisor, 80);
        $this->actingAs($this->supervisor, 'supervisor')->get(route('supervisor.defenses.show', $this->defense->id))
            ->assertViewHas('revealed', true)
            ->assertSee('77');
    }

    public function test_changing_a_grade_recomputes_until_locked(): void
    {
        $this->grade($this->supervisor, 90);
        $this->grade($this->examiner, 80);
        $this->assertSame(85.0, (float) $this->project->fresh()->grade);

        $this->grade($this->examiner, 70)->assertSessionHas('success');
        $this->assertSame(80.0, (float) $this->project->fresh()->grade);
        $this->assertTrue(AuditLog::where('action', 'grade.changed')->where('subject_id', $this->project->id)->exists());

        $this->project->update(['grade_locked_at' => now()]);
        $this->grade($this->examiner, 100)->assertSessionHas('fail');
        $this->assertSame(80.0, (float) $this->project->fresh()->grade);
    }

    public function test_direct_evaluation_is_redirected_to_the_committee(): void
    {
        $this->actingAs($this->supervisor, 'supervisor')
            ->post(route('supervisor.project.evaluate', $this->project->id), ['grade' => 95])
            ->assertRedirect(route('supervisor.defenses.show', $this->defense->id));
        $this->assertNull($this->project->fresh()->grade);
    }

    public function test_non_members_cannot_open_or_grade(): void
    {
        $outsider = Supervisor::whereNotIn('id', [$this->supervisor->id, $this->examiner->id])->firstOrFail();

        $this->actingAs($outsider, 'supervisor')->get(route('supervisor.defenses.show', $this->defense->id))->assertNotFound();
        $this->grade($outsider, 50)->assertNotFound();
    }

    public function test_examiner_can_download_the_project_files(): void
    {
        $file = ProjectFile::where('project_id', $this->project->id)->first();
        if (! $file) {
            $this->markTestSkipped('لا ملفات للمشروع.');
        }

        // 403 ممنوع؛ 404 مقبول هنا (الملف غير موجود على القرص في بيئة الاختبار)
        $status = $this->actingAs($this->examiner, 'supervisor')->get(route('files.download', $file->id))->baseResponse->getStatusCode();
        $this->assertNotSame(403, $status);

        $outsider = Supervisor::whereNotIn('id', [$this->supervisor->id, $this->examiner->id])->firstOrFail();
        $this->actingAs($outsider, 'supervisor')->get(route('files.download', $file->id))->assertForbidden();
    }

    public function test_my_defenses_page_lists_what_awaits_my_grade(): void
    {
        $res = $this->actingAs($this->examiner, 'supervisor')->get(route('supervisor.defenses.index'))->assertOk();

        $this->assertContains($this->defense->id, $res->viewData('toGrade')->pluck('id'));
        $res->assertSee('رصد درجتي');
    }

    public function test_admin_cannot_reschedule_after_grading_started(): void
    {
        $this->grade($this->supervisor, 88);
        $admin = \App\Models\Admin::first();

        $this->actingAs($admin, 'admin')->post(route('admin.defenses.cancel', $this->defense->id))
            ->assertSessionHas('fail');
        $this->assertSame('scheduled', $this->defense->fresh()->status);
    }
}
