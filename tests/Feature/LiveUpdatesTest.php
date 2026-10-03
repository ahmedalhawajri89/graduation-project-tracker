<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\Student;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * التحديث الحيّ (/live): العدّادات نفسها التي ترسمها الصفحة، وقائمة الجرس،
 * والإشعارات التي وصلت بعد آخر سؤال — دون أن يُعلَّم شيء مقروءاً.
 */
class LiveUpdatesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function live($user, string $guard, array $query = [])
    {
        return $this->actingAs($user, $guard)->getJson(route('live', $query));
    }

    /** إشعار مخزَّن مباشرة (بلا طابور) بتاريخ محدّد */
    private function notify(Student $student, string $msg, $at): void
    {
        $student->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => ProjectActivityNotify::class,
            'data' => ['project' => 'مشروع تجريبي', 'supervisor_name' => 'الإدارة', 'msg' => $msg],
            'created_at' => $at,
            'updated_at' => $at,
        ]);
    }

    public function test_guests_get_401(): void
    {
        $this->getJson(route('live'))->assertUnauthorized();
    }

    public function test_student_gets_counts_menu_and_fresh_notifications(): void
    {
        $student = Student::firstOrFail();
        $since = now()->subMinute();
        $this->notify($student, 'إشعار قديم', now()->subHour());
        $this->notify($student, 'وصل للتوّ', now());

        $res = $this->live($student, 'student', ['since' => $since->toIso8601String()])->assertOk();

        $res->assertJsonPath('counts.notifications', $student->unreadNotifications()->count())
            ->assertJsonPath('counts.contacts', null)
            ->assertJsonPath('counts.requests', null);
        $this->assertIsInt($res->json('counts.discussion'));

        // التنبيه المنبثق: ما بعد since وحده
        $fresh = collect($res->json('fresh'))->pluck('text');
        $this->assertContains('وصل للتوّ', $fresh);
        $this->assertNotContains('إشعار قديم', $fresh);

        $this->assertStringContainsString('notif-menu-head', $res->json('menu'));
        $this->assertNotNull($res->json('now'));

        // قراءة فقط
        $this->assertSame(0, $student->notifications()->whereNotNull('read_at')->where('data->msg', 'وصل للتوّ')->count());
    }

    public function test_without_since_nothing_pops_up(): void
    {
        $student = Student::firstOrFail();
        $this->notify($student, 'وصل للتوّ', now());

        $this->assertSame([], $this->live($student, 'student')->assertOk()->json('fresh'));
    }

    public function test_admin_counts_unread_contact_messages(): void
    {
        $admin = Admin::firstOrFail();
        $before = $this->live($admin, 'admin')->json('counts.contacts');

        Contact::create(['name' => 'زائر', 'email' => 'visitor@example.com', 'subject' => 'سؤال', 'message' => 'نص الرسالة', 'is_read' => 0]);

        $this->assertSame($before + 1, $this->live($admin, 'admin')->json('counts.contacts'));
    }

    public function test_the_page_carries_the_live_hooks(): void
    {
        $student = Student::firstOrFail();

        $this->actingAs($student, 'student')->get(route('student.showNotification'))
            ->assertSee('data-live-endpoint', false)
            ->assertSee('data-live-badge', false)
            ->assertSee('data-live-count="notifications"', false);
    }
}
