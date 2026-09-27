<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * توزيع الأدوار: القائد يوزّع، والجميع يرى، والعضو يُشعَر بدوره.
 *
 * داخل معاملة تُرجَع في \u200EtearDown\u200E، والإشعارات مزيّفة.
 */
class TeamRolesTest extends TestCase
{
    private Project $project;
    private Group $leader;
    private Group $member;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        DB::beginTransaction();

        // مشروع جارٍ بقائد وعضو واحد على الأقل
        $project = Project::where('status', 'accept')->whereNull('grade')
            ->whereHas('group', fn ($q) => $q->where('type', 'leader'))
            ->whereHas('group', fn ($q) => $q->where('type', 'member'))
            ->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع جارٍ بقائد وأعضاء.');
        }

        $this->project = $project;
        $this->leader = $project->group()->where('type', 'leader')->first();
        $this->member = $project->group()->where('type', 'member')->first();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function save(array $members, ?Student $as = null)
    {
        return $this->actingAs($as ?? Student::find($this->leader->student_id), 'student')
            ->post(route('student.roles.update', $this->project->id), ['members' => $members]);
    }

    public function test_the_leader_assigns_roles_and_everyone_sees_them(): void
    {
        $this->save([
            $this->member->id => ['roles' => ['docs', 'custom:الأمن والصلاحيات'], 'responsibility' => 'التوثيق وصلاحيات المستخدمين'],
            $this->leader->id => ['roles' => ['coordination'], 'responsibility' => ''],
        ])->assertRedirect(route('student.team'))->assertSessionHas('success');

        $this->assertEqualsCanonicalizing(['التوثيق والكتابة', 'الأمن والصلاحيات'], $this->member->roles()->pluck('label')->all());
        $this->assertSame('التوثيق وصلاحيات المستخدمين', $this->member->fresh()->responsibility);

        // العضو يرى دوره في صفحة الفريق — بلا أدوات التعديل
        $this->app['auth']->forgetGuards();
        $this->actingAs(Student::find($this->member->student_id), 'student')
            ->get(route('student.team'))->assertOk()
            ->assertSee('الأمن والصلاحيات')
            ->assertSee('التوثيق وصلاحيات المستخدمين')
            ->assertDontSee('حفظ التوزيع');

        // والمشرف في بطاقة الفريق بصفحة المشروع
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->project->supervisor, 'supervisor')
            ->get(route('supervisor.projects.show', $this->project->id))->assertOk()
            ->assertSee('الأمن والصلاحيات');
    }

    public function test_a_member_who_is_not_the_leader_cannot_assign(): void
    {
        $this->save([$this->member->id => ['roles' => ['docs']]], Student::find($this->member->student_id))
            ->assertForbidden();

        $this->assertSame(0, $this->member->roles()->count());
    }

    public function test_an_outsider_cannot_assign(): void
    {
        $outsider = Student::whereDoesntHave('groups', fn ($q) => $q->where('project_id', $this->project->id))->first();

        $this->save([$this->member->id => ['roles' => ['docs']]], $outsider)->assertForbidden();
    }

    /** عضو من فريق آخر لا يُمسّ عبر هذا المشروع */
    public function test_a_member_of_another_team_is_refused(): void
    {
        $foreign = Group::where('project_id', '!=', $this->project->id)->whereNotNull('project_id')->first();

        $this->save([$foreign->id => ['roles' => ['docs']]])->assertSessionHasErrors('members');
        $this->assertSame(0, $foreign->roles()->count());
    }

    public function test_more_than_the_limit_or_an_unknown_role_is_refused(): void
    {
        $this->save([$this->member->id => ['roles' => ['docs', 'presentation', 'coordination', 'custom:أ', 'custom:ب']]])
            ->assertSessionHasErrors();

        $this->save([$this->member->id => ['roles' => ['not-a-role']]])->assertSessionHasErrors('members');

        $this->assertSame(0, $this->member->roles()->count());
    }

    public function test_a_locked_project_is_refused(): void
    {
        DB::table('projects')->where('id', $this->project->id)->update(['grade' => 90, 'status' => 'complete']);

        $this->save([$this->member->id => ['roles' => ['docs']]])->assertSessionHas('fail');
        $this->assertSame(0, $this->member->roles()->count());
    }

    /** الإشعار لمن تغيّرت أدواره وحده — لا لكل الفريق عند كل حفظ */
    public function test_only_members_whose_roles_changed_are_notified(): void
    {
        $this->save([$this->member->id => ['roles' => ['docs']]]);
        Notification::assertSentTo(Student::find($this->member->student_id), \App\Notifications\ProjectActivityNotify::class);

        Notification::fake();
        $this->save([$this->member->id => ['roles' => ['docs'], 'responsibility' => 'تعديل المسؤولية وحدها']]);
        Notification::assertNothingSent();
    }

    /** «أعضاء بلا دور» للقائد وحده */
    public function test_the_leader_is_prompted_to_assign_roles(): void
    {
        $this->actingAs(Student::find($this->leader->student_id), 'student')
            ->get(route('student.dashboard'))->assertOk()
            ->assertSee(route('student.team'), false)
            ->assertSee('وزّع الأدوار على الفريق');

        $this->app['auth']->forgetGuards();
        $this->actingAs(Student::find($this->member->student_id), 'student')
            ->get(route('student.dashboard'))->assertOk()
            ->assertDontSee('وزّع الأدوار على الفريق');
    }
}
