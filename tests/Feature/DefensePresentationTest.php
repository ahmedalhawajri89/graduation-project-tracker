<?php

namespace Tests\Feature;

use App\Models\Defense;
use App\Models\DefenseMember;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\ProjectActivityNotify;
use App\Support\DefenseScheduler;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * العرض التقديمي للمناقشة: يرفعه القائد حتى بدئها، يُستبدل ولا يتكرّر،
 * تراه اللجنة أعلى صفحتها وتُشعَر به، ويُذكَّر الفريق إن لم يرفعه.
 */
class DefensePresentationTest extends TestCase
{
    private Project $project;
    private Defense $defense;
    private Supervisor $examiner;
    private Student $leader;

    protected function setUp(): void
    {
        parent::setUp();

        if (! DefenseScheduler::enabled()) {
            $this->markTestSkipped('جداول المناقشات غير موجودة في قاعدة الاختبار.');
        }

        Notification::fake();
        Storage::fake('local');
        DB::beginTransaction();

        $project = Project::whereHas('group', fn ($q) => $q->where('type', 'leader'))->has('group', '>=', 2)
            ->whereNotNull('supervisor_id')->first();
        if (! $project) {
            $this->markTestSkipped('لا مشروع بفريق له قائد وعضو.');
        }
        $project->update(['status' => 'complete', 'grade' => null, 'grade_locked_at' => null]);
        Defense::where('project_id', $project->id)->delete();
        ProjectFile::where('project_id', $project->id)->where('path', 'like', ProjectFile::PRESENTATION_DIR . '/%')->delete();

        $this->project = $project->fresh();
        $this->leader = Student::findOrFail($project->group()->where('type', 'leader')->value('student_id'));
        $this->examiner = Supervisor::whereKeyNot($project->supervisor_id)->firstOrFail();
        $this->defense = Defense::create([
            'project_id' => $project->id, 'starts_at' => now()->addDay()->setTime(10, 0), 'duration_minutes' => 45,
            'mode' => 'online', 'meeting_url' => 'https://meet.google.com/abc-defg-hij', 'status' => 'scheduled',
        ]);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $project->supervisor_id, 'role' => 'supervisor']);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $this->examiner->id, 'role' => 'examiner']);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function upload(Student $who, ?UploadedFile $file = null)
    {
        return $this->actingAs($who, 'student')->post(route('student.presentation.store', $this->defense->id), [
            'presentation' => $file ?? UploadedFile::fake()->create('slides.pdf', 300, 'application/pdf'),
        ]);
    }

    public function test_leader_uploads_and_the_committee_is_notified_and_can_download(): void
    {
        $this->upload($this->leader)->assertSessionHas('success');

        $slides = $this->project->fresh()->presentation;
        $this->assertNotNull($slides);
        $this->assertTrue($slides->isPresentation());
        Storage::disk('local')->assertExists($slides->path);
        Notification::assertSentTo($this->examiner, ProjectActivityNotify::class, fn ($n) => str_contains($n->toArray($this->examiner)['msg'], 'العرض التقديمي'));

        $this->actingAs($this->examiner, 'supervisor')->get(route('supervisor.defenses.show', $this->defense->id))
            ->assertOk()->assertSee(route('files.download', $slides->id), false);
        $this->assertSame(200, $this->actingAs($this->examiner, 'supervisor')->get(route('files.download', $slides->id))->baseResponse->getStatusCode());
    }

    public function test_a_new_upload_replaces_the_old_one(): void
    {
        $this->upload($this->leader);
        $old = $this->project->fresh()->presentation;

        $this->upload($this->leader, UploadedFile::fake()->create('v2.pptx', 500, 'application/vnd.openxmlformats-officedocument.presentationml.presentation'))
            ->assertSessionHas('success');

        $all = ProjectFile::where('project_id', $this->project->id)->where('path', 'like', ProjectFile::PRESENTATION_DIR . '/%')->get();
        $this->assertCount(1, $all);
        $this->assertNotSame($old->id, $all->first()->id);
        Storage::disk('local')->assertMissing($old->path);
    }

    public function test_only_the_leader_uploads_and_only_a_presentation_file(): void
    {
        $member = Student::findOrFail($this->project->group()->where('type', '!=', 'leader')->value('student_id'));
        $this->upload($member)->assertSessionHas('fail');

        $outsider = Student::whereNotIn('id', $this->project->group()->pluck('student_id'))->firstOrFail();
        $this->app['auth']->forgetGuards();
        $this->upload($outsider)->assertForbidden();

        $this->app['auth']->forgetGuards();
        $this->upload($this->leader, UploadedFile::fake()->create('notes.zip', 100, 'application/zip'))->assertSessionHasErrors('presentation');

        $this->assertNull($this->project->fresh()->presentation);
    }

    public function test_upload_closes_when_the_defense_starts(): void
    {
        $this->defense->update(['starts_at' => now()->subMinutes(5)]);

        $this->upload($this->leader)->assertSessionHas('fail');
        $this->assertNull($this->project->fresh()->presentation);
    }

    public function test_the_student_card_shows_the_upload_then_the_file(): void
    {
        $this->actingAs($this->leader, 'student')->get(route('student.dashboard'))
            ->assertOk()->assertSee('رفع العرض')->assertSee(route('student.presentation.store', $this->defense->id), false);

        $this->upload($this->leader);

        $this->actingAs($this->leader, 'student')->get(route('student.dashboard'))
            ->assertOk()->assertSee('استبدال')->assertSee('تراه اللجنة');
    }

    public function test_day_before_reminder_nudges_a_team_without_a_presentation(): void
    {
        $this->artisan('defenses:remind')->assertSuccessful();

        Notification::assertSentTo($this->leader, ProjectActivityNotify::class, fn ($n) => str_contains($n->toArray($this->leader)['msg'], 'لم يُرفع العرض التقديمي'));
        Notification::assertNotSentTo($this->examiner, ProjectActivityNotify::class, fn ($n) => str_contains($n->toArray($this->examiner)['msg'], 'لم يُرفع العرض التقديمي'));
    }

    public function test_no_nudge_when_the_presentation_is_there(): void
    {
        $this->upload($this->leader);
        Notification::fake();

        $this->artisan('defenses:remind')->assertSuccessful();

        Notification::assertNotSentTo($this->leader, ProjectActivityNotify::class, fn ($n) => str_contains($n->toArray($this->leader)['msg'], 'لم يُرفع العرض التقديمي'));
    }
}
