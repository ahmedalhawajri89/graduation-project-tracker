<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Student;
use App\Notifications\ProjectActivityNotify;
use App\Support\Discussion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * نقاش الفريق: للأعضاء وحدهم — لا يصل المشرف ولا الإدارة من أيّ باب.
 *
 * داخل معاملة تُرجَع في \u200EtearDown\u200E، والإشعارات مزيّفة.
 */
class TeamChatTest extends TestCase
{
    private const SECRET = 'رسالة داخلية سرّية للفريق فقط';

    private Project $project;
    private Student $leader;
    private Student $member;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        DB::beginTransaction();

        $project = Project::where('status', 'accept')->whereNull('grade')->whereNotNull('supervisor_id')
            ->where('semester_id', \App\Models\Semester::current()->id)
            ->whereHas('group', fn ($q) => $q->where('type', 'leader'))
            ->whereHas('group', fn ($q) => $q->where('type', 'member'))
            ->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع جارٍ بقائد وأعضاء في الفصل الحالي.');
        }

        $this->project = $project;
        $this->leader = Student::find($project->group()->where('type', 'leader')->value('student_id'));
        $this->member = Student::find($project->group()->where('type', 'member')->value('student_id'));
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function send(array $data = [], ?Student $as = null)
    {
        return $this->actingAs($as ?? $this->leader, 'student')
            ->post(route('student.comments.store', $this->project->id), $data + [
                'body' => self::SECRET,
                'channel' => 'team',
            ]);
    }

    private function asSupervisor()
    {
        $this->app['auth']->forgetGuards();

        return $this->actingAs($this->project->supervisor, 'supervisor');
    }

    public function test_a_team_message_never_reaches_the_supervisor(): void
    {
        $before = Discussion::unreadFor($this->project->supervisor, [$this->project->id])[$this->project->id] ?? 0;

        $this->send()->assertRedirect(route('student.discussion', ['tab' => 'team']));
        $this->assertSame(ProjectComment::TEAM, ProjectComment::latest('id')->first()->channel);

        // عدّاده لا يتحرّك
        $this->assertSame($before, Discussion::unreadFor($this->project->supervisor, [$this->project->id])[$this->project->id] ?? 0);

        // خيطه، وصندوقه، ولوحته (آخر النشاط، «مجموعة تنتظر ردّك»)، وصفحة المشروع
        $this->asSupervisor()->get(route('supervisor.discussion', $this->project->id))->assertOk()->assertDontSee(self::SECRET);
        $this->get(route('supervisor.discussion'))->assertOk()->assertDontSee(self::SECRET);
        $this->get(route('supervisor.dashboard'))->assertOk()->assertDontSee(self::SECRET);
        $this->get(route('supervisor.projects.show', $this->project->id))->assertOk()->assertDontSee(self::SECRET);
    }

    public function test_the_administration_does_not_see_team_messages(): void
    {
        $this->send();

        $this->app['auth']->forgetGuards();
        $admin = \App\Models\Admin::first();
        $this->actingAs($admin, 'admin')
            ->get(route('admin.groups.show', $this->project->id))
            ->assertOk()
            ->assertDontSee(self::SECRET);
    }

    public function test_the_supervisor_cannot_delete_a_team_message(): void
    {
        $this->send();
        $comment = ProjectComment::latest('id')->first();

        $this->asSupervisor()->delete(route('supervisor.comments.destroy', $comment->id))->assertNotFound();
        $this->assertNotNull(ProjectComment::find($comment->id));
    }

    /** مؤشّر القراءة لكل قناة: فتح الفريق لا يعلّم قناة المشرف مقروءة */
    public function test_reads_are_tracked_per_channel(): void
    {
        ProjectComment::create([
            'project_id' => $this->project->id,
            'author_type' => \App\Models\Supervisor::class,
            'author_id' => $this->project->supervisor_id,
            'body' => 'رسالة من المشرف',
        ]);
        $this->send([], $this->member);

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->leader, 'student')->get(route('student.discussion', ['tab' => 'team']))
            ->assertOk()->assertSee(self::SECRET)->assertSee('خاص بالفريق');

        $this->assertSame(0, Discussion::unreadFor($this->leader, [$this->project->id], ProjectComment::TEAM)[$this->project->id] ?? 0);
        $this->assertGreaterThan(0, Discussion::unreadFor($this->leader, [$this->project->id])[$this->project->id] ?? 0);
    }

    public function test_the_supervisor_tab_does_not_show_team_messages(): void
    {
        $this->send();

        $this->actingAs($this->member, 'student')->get(route('student.discussion'))
            ->assertOk()->assertDontSee(self::SECRET);
    }

    /** الذكر: المذكور وحده يُشعَر، ولا إشعار بلا ذكر */
    public function test_only_mentioned_teammates_are_notified(): void
    {
        $this->send(['body' => '@' . $this->member->name . ' راجعي الفصل', 'mentions' => [$this->member->id]]);

        Notification::assertSentTo($this->member, ProjectActivityNotify::class);
        Notification::assertNotSentTo($this->leader, ProjectActivityNotify::class);
        $this->assertSame([$this->member->id], ProjectComment::latest('id')->first()->mentions);

        Notification::fake();
        $this->send(['body' => 'رسالة عادية بلا ذكر']);
        Notification::assertNothingSent();
    }

    public function test_a_student_outside_the_team_cannot_be_mentioned(): void
    {
        $outsider = Student::whereDoesntHave('groups', fn ($q) => $q->where('project_id', $this->project->id))->first();

        $this->send(['mentions' => [$outsider->id]])->assertSessionHasErrors('mentions.0');
        Notification::assertNothingSent();
    }

    public function test_an_outsider_cannot_write_in_the_team_channel(): void
    {
        $outsider = Student::whereDoesntHave('groups', fn ($q) => $q->where('project_id', $this->project->id))->first();

        $this->send([], $outsider)->assertForbidden();
    }
}
