<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Project;
use App\Models\Student;
use App\Notifications\AdminChangeGroupNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * أخطاء كشفتها مراجعة التجربة بلقطات فعلية.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة: ما يُنشأ يُحذف بمعرّفه،
 * و\u200Eread_at\u200E يُستعاد — في \u200Efinally\u200E وباستعلام مباشر.
 */
class UxFixesTest extends TestCase
{
    /** كان المشروع المقبول يعرض «موافقة المشرف» خطوةً حالية */
    public function test_an_accepted_project_shows_execution_as_the_current_step(): void
    {
        $student = Student::whereHas('groups.project', fn ($q) => $q->where('status', 'accept'))->first();

        if (! $student) {
            $this->markTestSkipped('لا طالب مشروعه مقبول.');
        }

        $html = $this->actingAs($student, 'student')->get(route('student.dashboard'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/rail-step is-current">.*?التنفيذ والمتابعة/su', $html);
        $this->assertMatchesRegularExpression('/rail-step is-done">.*?موافقة المشرف/su', $html);
    }

    public function test_the_student_notifications_page_is_a_list_that_marks_read(): void
    {
        $student = Student::has('unreadNotifications')->first() ?? Student::has('notifications')->first();

        if (! $student) {
            $this->markTestSkipped('لا طالب له إشعارات.');
        }

        $unread = $student->unreadNotifications()->pluck('id')->all();

        try {
            $this->actingAs($student, 'student')
                ->get(route('student.showNotification'))
                ->assertOk()
                ->assertDontSee('<table', false)
                ->assertSee('notif-brief', false);

            $this->assertSame(0, $student->unreadNotifications()->count());
        } finally {
            DB::table('notifications')->whereIn('id', $unread)->update(['read_at' => null]);
        }
    }

    public function test_a_student_without_notifications_sees_an_empty_state(): void
    {
        $student = Student::doesntHave('notifications')->first();

        if (! $student) {
            $this->markTestSkipped('كل الطلاب لهم إشعارات.');
        }

        $this->actingAs($student, 'student')
            ->get(route('student.showNotification'))
            ->assertOk()
            ->assertSee('لا إشعارات بعد');
    }

    /** كان النصّ «تم اضافة 4 طلاب للمجموعة (2300000159,2300000040,…)» */
    public function test_adding_members_notifies_with_names_not_university_ids(): void
    {
        Notification::fake();

        $project = Project::whereNotNull('supervisor_id')->where('status', 'accept')->has('group')
            ->with('supervisor')->first();
        $newcomer = $project
            ? Student::availableForTeam($project->supervisor->specialize_id)->first()
            : null;

        if (! $newcomer) {
            $this->markTestSkipped('لا مشروع مقبول بطالب متاح من تخصصه.');
        }

        $groupMark = Group::max('id') ?? 0;
        $auditMark = AuditLog::max('id') ?? 0;

        try {
            $this->actingAs(Admin::first(), 'admin')
                ->post(route('admin.groups.update'), [
                    'id' => $project->id,
                    'student_ids' => [$newcomer->university_id],
                ])
                ->assertRedirect();

            Notification::assertSentTo($project->supervisor, AdminChangeGroupNotify::class,
                function ($notification) use ($newcomer) {
                    $msg = $notification->toArray($newcomer)['msg'] ?? '';

                    return str_contains($msg, $newcomer->name)
                        && ! str_contains($msg, (string) $newcomer->university_id);
                });
        } finally {
            Group::where('id', '>', $groupMark)->delete();
            AuditLog::where('id', '>', $auditMark)->delete();
        }

        $this->assertSame(0, Group::where('id', '>', $groupMark)->count(), 'العضو المضاف لم يُحذف.');
    }
}
