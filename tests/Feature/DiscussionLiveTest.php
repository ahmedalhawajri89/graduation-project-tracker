<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Student;
use App\Models\Supervisor;
use App\Support\Discussion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * النقاش الحيّ: ما وصل بعد آخر رسالة معروضة، والإرسال والحذف بلا تحميل —
 * بالصلاحيات نفسها: الطالب عضو الفريق للقناتين، والمشرف لقناته وحدها.
 */
class DiscussionLiveTest extends TestCase
{
    private Project $project;
    private Student $student;
    private Supervisor $supervisor;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        DB::beginTransaction();

        $project = Project::whereHas('group')->whereNotNull('supervisor_id')->where('status', '!=', 'reject')->first();
        if (! $project) {
            $this->markTestSkipped('لا مشروع بفريق ومشرف.');
        }
        $this->project = $project;
        $this->student = Student::findOrFail($project->group()->value('student_id'));
        $this->supervisor = Supervisor::findOrFail($project->supervisor_id);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function say($author, string $body, string $channel = ProjectComment::SUPERVISOR): ProjectComment
    {
        return ProjectComment::create([
            'project_id' => $this->project->id, 'channel' => $channel, 'body' => $body,
            'author_type' => $author::class, 'author_id' => $author->id,
        ]);
    }

    private function live($user, string $guard, array $q = [])
    {
        $this->app['auth']->forgetGuards();

        return $this->actingAs($user, $guard)->getJson(route('discussion.live', ['project' => $this->project->id] + $q));
    }

    public function test_student_gets_only_what_arrived_after_the_last_id(): void
    {
        $old = $this->say($this->supervisor, 'رسالة قديمة');
        $new = $this->say($this->supervisor, 'رسالة وصلت للتوّ');

        $res = $this->live($this->student, 'student', ['after' => $old->id])->assertOk();

        $this->assertStringContainsString('رسالة وصلت للتوّ', $res->json('html'));
        $this->assertStringNotContainsString('رسالة قديمة', $res->json('html'));
        $this->assertSame($new->id, $res->json('last_id'));
        $this->assertContains($old->id, $res->json('ids'));
        $this->assertArrayHasKey('team', $res->json('unread'));
    }

    public function test_reading_live_clears_the_unread_count(): void
    {
        $this->say($this->supervisor, 'هل أنهيتم الفصل الثالث؟');
        $this->assertGreaterThan(0, Discussion::unreadFor($this->student, [$this->project->id])[$this->project->id] ?? 0);

        $this->live($this->student, 'student', ['read' => 1])->assertOk();

        $this->assertSame(0, Discussion::unreadFor($this->student, [$this->project->id])[$this->project->id] ?? 0);
    }

    public function test_the_supervisor_never_sees_the_team_channel(): void
    {
        $this->say($this->student, 'سرّ الفريق', ProjectComment::TEAM);

        $this->live($this->supervisor, 'supervisor', ['channel' => 'team'])->assertNotFound();
        $res = $this->live($this->supervisor, 'supervisor')->assertOk();
        $this->assertStringNotContainsString('سرّ الفريق', $res->json('html'));
        $this->assertArrayHasKey('projects', $res->json('unread'));
    }

    public function test_outsiders_are_refused(): void
    {
        $outsider = Student::whereNotIn('id', $this->project->group()->pluck('student_id'))->firstOrFail();
        $this->live($outsider, 'student')->assertForbidden();

        $other = Supervisor::whereKeyNot($this->project->supervisor_id)->firstOrFail();
        $this->live($other, 'supervisor')->assertForbidden();

        $this->app['auth']->forgetGuards();
        $this->getJson(route('discussion.live', $this->project->id))->assertUnauthorized();
    }

    public function test_sending_by_ajax_returns_the_new_message(): void
    {
        $before = $this->say($this->supervisor, 'سؤال المشرف');

        $res = $this->actingAs($this->student, 'student')
            ->postJson(route('student.comments.store', $this->project->id), ['body' => 'ردّ الطالب بلا تحميل', 'after' => $before->id])
            ->assertOk();

        $this->assertStringContainsString('ردّ الطالب بلا تحميل', $res->json('html'));
        $this->assertTrue(ProjectComment::where('body', 'ردّ الطالب بلا تحميل')->exists());

        // النموذج العادي (بلا JavaScript) ما زال يعيد التوجيه
        $this->post(route('student.comments.store', $this->project->id), ['body' => 'إرسال عادي'])
            ->assertRedirect();
    }

    public function test_an_empty_message_is_a_422_for_ajax(): void
    {
        $this->actingAs($this->supervisor, 'supervisor')
            ->postJson(route('supervisor.comments.store', $this->project->id), ['body' => ''])
            ->assertStatus(422)->assertJsonValidationErrors('body');
    }

    public function test_deleting_by_ajax(): void
    {
        $mine = $this->say($this->supervisor, 'ملاحظة للحذف');

        $this->actingAs($this->supervisor, 'supervisor')
            ->deleteJson(route('supervisor.comments.destroy', $mine->id))
            ->assertOk()->assertJson(['ok' => true]);

        $this->assertNull(ProjectComment::find($mine->id));
    }
}
