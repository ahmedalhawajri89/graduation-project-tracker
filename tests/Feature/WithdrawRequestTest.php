<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Project;
use App\Models\Student;
use App\Notifications\SuperVisorRequestProjectNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * سحب طلب معلّق.
 *
 * كان الفريق الذي لا يردّ مشرفه عالقاً بلا مخرج. داخل معاملة تُرجَع في
 * ‎tearDown‎ — الحذف النهائي نفسه يُرجَع.
 */
class WithdrawRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** طلب معلّق بقائد وعضو واحد على الأقل */
    private function pending(): array
    {
        $project = Project::where('status', 'request')->has('group', '>=', 2)->with('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا طلب معلّق بفريق من عضوين.');
        }

        return [
            $project,
            Student::find($project->group->firstWhere('type', 'leader')->student_id),
            Student::find($project->group->firstWhere('type', 'member')->student_id),
        ];
    }

    public function test_the_leader_withdraws_and_the_team_is_freed(): void
    {
        [$project, $leader, $member] = $this->pending();
        $auditMark = (int) (AuditLog::max('id') ?? 0);

        $this->actingAs($leader, 'student')
            ->post(route('student.project.withdraw', $project->id))
            ->assertRedirect(route('student.dashboard'))
            ->assertSessionHas('success');

        $this->assertNull(Project::withTrashed()->find($project->id), 'المشروع لم يُحذف.');
        $this->assertSame(0, Group::where('project_id', $project->id)->count(), 'صفوف الفريق بقيت.');

        // الأعضاء تحرّروا: يظهرون متاحين لفريق جديد
        $available = Student::availableForTeam($leader->specialize_id)->pluck('id');
        $this->assertTrue($available->contains($leader->id) && $available->contains($member->id));

        $this->assertNotNull(AuditLog::where('id', '>', $auditMark)->where('action', 'project.withdrawn')->first());

        // إشعار الطلب عند المشرف زال — الطلب لم يعد قائماً
        $left = DB::table('notifications')->where('type', SuperVisorRequestProjectNotify::class)->get()
            ->filter(fn ($n) => (int) (json_decode($n->data, true)['project_id'] ?? 0) === (int) $project->id);
        $this->assertCount(0, $left);
    }

    public function test_a_member_who_is_not_the_leader_cannot_withdraw(): void
    {
        [$project, , $member] = $this->pending();

        $this->actingAs($member, 'student')
            ->post(route('student.project.withdraw', $project->id))
            ->assertForbidden();

        $this->assertSame('request', $project->fresh()->status);
    }

    public function test_a_student_outside_the_team_cannot_withdraw(): void
    {
        [$project] = $this->pending();
        $outsider = Student::whereNotIn('id', $project->group->pluck('student_id'))->first();

        $this->actingAs($outsider, 'student')
            ->post(route('student.project.withdraw', $project->id))
            ->assertForbidden();

        $this->assertNotNull($project->fresh());
    }

    /** بعد القبول يدير المشرف المشروع — لا سحب */
    public function test_an_accepted_project_cannot_be_withdrawn(): void
    {
        $project = Project::where('status', 'accept')->with('group')->first();
        $leader = Student::find($project->group->firstWhere('type', 'leader')->student_id);

        $this->actingAs($leader, 'student')
            ->post(route('student.project.withdraw', $project->id))
            ->assertSessionHas('fail');

        $this->assertSame('accept', $project->fresh()->status);
    }

    public function test_only_the_leader_sees_the_withdraw_button(): void
    {
        [$project, $leader, $member] = $this->pending();
        $action = route('student.project.withdraw', $project->id);

        $this->actingAs($leader, 'student')->get(route('student.dashboard'))->assertSee($action, false);

        $this->app['auth']->forgetGuards();
        $this->actingAs($member, 'student')->get(route('student.dashboard'))->assertDontSee($action, false);
    }
}
