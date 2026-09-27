<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * تعديل المجموعة عند الأدمن، وقيد العضوية في القاعدة.
 *
 * على قاعدة ‎*_testing‎، وما يُنشأ يُحذف بمعرّفه في ‎tearDown‎.
 */
class GroupUpdateTest extends TestCase
{
    private int $groupMark = 0;

    private int $auditMark = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->groupMark = (int) (Group::max('id') ?? 0);
        $this->auditMark = (int) (AuditLog::max('id') ?? 0);
    }

    protected function tearDown(): void
    {
        Group::where('id', '>', $this->groupMark)->delete();
        AuditLog::where('id', '>', $this->auditMark)->delete();

        parent::tearDown();
    }

    private function update(Project $project, array $ids)
    {
        return $this->actingAs(Admin::first(), 'admin')
            ->post(route('admin.groups.update'), ['id' => $project->id, 'student_ids' => $ids]);
    }

    /** مشروع مقبول له مقعد في فريقه، وطالب متاح من تخصصه */
    private function roomyProject(): array
    {
        $project = Project::where('status', 'accept')->with(['project_type', 'group'])->get()
            ->first(fn ($p) => $p->project_type && $p->group->count() < $p->project_type->max);
        $free = $project ? Student::availableForTeam($project->project_type->specialize_id)->first() : null;

        if (! $free) {
            $this->markTestSkipped('لا مشروع بمقعد أو لا طالب متاح.');
        }

        return [$project, $free];
    }

    /** كان الحدّ الأقصى يُفحص في JavaScript وحده */
    public function test_the_team_maximum_is_enforced_on_the_server(): void
    {
        $project = Project::where('status', 'accept')->with(['project_type', 'group'])->get()
            ->first(fn ($p) => $p->project_type && $p->group->count() >= $p->project_type->max);
        $free = $project ? Student::availableForTeam($project->project_type->specialize_id)->first() : null;

        if (! $free) {
            $this->markTestSkipped('لا مشروع ممتلئ أو لا طالب متاح.');
        }

        $this->update($project, [$free->university_id])->assertSessionHas('fail');
        $this->assertSame(0, Group::where('id', '>', $this->groupMark)->count());
    }

    public function test_a_member_from_another_specialization_is_refused(): void
    {
        [$project] = $this->roomyProject();
        $outsider = Student::where('specialize_id', '!=', $project->project_type->specialize_id)
            ->whereDoesntHave('groups')->first();

        if (! $outsider) {
            $this->markTestSkipped('لا طالب متاح من تخصص آخر.');
        }

        $this->update($project, [$outsider->university_id])->assertSessionHas('fail');
        $this->assertSame(0, Group::where('id', '>', $this->groupMark)->count());
    }

    /** إضافة العضو كانت لا تترك أثراً في سجلّ التدقيق */
    public function test_adding_a_member_is_audited(): void
    {
        [$project, $free] = $this->roomyProject();

        $this->update($project, [$free->university_id])->assertSessionHas('success');

        $this->assertSame(1, Group::where('id', '>', $this->groupMark)->where('student_id', $free->id)->count());
        $this->assertNotNull(AuditLog::where('id', '>', $this->auditMark)->where('action', 'project.memberAdded')->first());
    }

    public function test_a_failed_update_says_update_not_force_delete(): void
    {
        [$project] = $this->roomyProject();
        $busy = Student::whereHas('groups.project', fn ($q) => $q->where('status', 'accept'))
            ->where('specialize_id', $project->project_type->specialize_id)->first();

        if (! $busy) {
            $this->markTestSkipped('لا طالب مشغول في التخصص.');
        }

        $response = $this->update($project, [$busy->university_id])->assertSessionHas('fail');
        $this->assertStringNotContainsString('الحذف النهائي', session('fail'));
    }

    /** الطالب مرّة واحدة في المشروع — في القاعدة لا في PHP وحده */
    public function test_the_database_refuses_a_duplicate_membership(): void
    {
        $row = Group::first();

        $this->expectException(QueryException::class);
        Group::create(['student_id' => $row->student_id, 'project_id' => $row->project_id, 'type' => 'member']);
    }
}
