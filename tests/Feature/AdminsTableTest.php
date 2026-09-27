<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\AdministratorsRequest;
use App\Models\Admin;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * جدول مسؤولي النظام — والحارس الذي يحمي كل لوحة الأدمن.
 *
 * للقراءة فقط: \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة، فأي
 * \u200ERefreshDatabase\u200E هنا يمسح قاعدة التطوير. الاختبارات التي تجرّب
 * الحذف تتأكّد أن الطلب رُدّ وأن الحساب باقٍ.
 */
class AdminsTableTest extends TestCase
{
    private function admin(): Admin
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن في قاعدة التطوير.');
        }

        return $admin;
    }

    private function fetch(array $query = []): array
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->getJson(route('admin.administrators.getData', $query + [
                'draw' => 1,
                'start' => 0,
                'length' => 25,
            ]));

        $response->assertOk();

        return $response->json();
    }

    // ═══ الثغرة ═══

    /**
     * الحارس الخارجي \u200Eauth:student,supervisor,admin\u200E يمرّ إن نجح أيّ من
     * الثلاثة، و\u200EAdminController\u200E كان بلا حارس خاص — فطالب مسجَّل دخوله
     * يفتح الصفحة ويُنشئ حساب أدمن لنفسه.
     */
    public function test_a_logged_in_student_cannot_reach_the_admins_page(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        $this->actingAs($student, 'student')
            ->get(route('admin.administrators.index'))
            ->assertRedirect();
    }

    /** وهذا هو التصعيد نفسه: طالب يمنح نفسه صلاحية كاملة */
    public function test_a_logged_in_student_cannot_create_an_admin_account(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        $before = Admin::count();

        $this->actingAs($student, 'student')
            ->post(route('admin.administrators.store'), [
                'name' => 'حساب مُصعَّد',
                'email' => 'escalation-probe@example.test',
                'phone' => '0590000000',
                'password' => 'password1234',
                'password_confirmation' => 'password1234',
                'gender' => 'male',
            ])
            ->assertRedirect();

        $this->assertSame($before, Admin::count(), 'أُنشئ حساب أدمن من جلسة طالب.');
        $this->assertNull(Admin::where('email', 'escalation-probe@example.test')->first());
    }

    /** والمشرف كذلك */
    public function test_a_logged_in_supervisor_cannot_reach_the_admins_page(): void
    {
        $supervisor = Supervisor::first();

        if (! $supervisor) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        $this->actingAs($supervisor, 'supervisor')
            ->get(route('admin.administrators.index'))
            ->assertRedirect();
    }

    /** الحارس يغطّي بقية اللوحة لا صفحة المسؤولين وحدها */
    public function test_the_guard_covers_the_whole_admin_panel(): void
    {
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        foreach (['admin.students.index', 'admin.supervisors.index', 'admin.groups.index', 'admin.contact.index'] as $route) {
            $this->actingAs($student, 'student')
                ->get(route($route))
                ->assertRedirect();
        }
    }

    // ═══ قفل النظام ═══

    /** حذف حسابك يُخرجك من نظامك أثناء استعماله */
    public function test_an_admin_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.administrators.destroy', $admin->id), ['id' => $admin->id])
            ->assertRedirect();

        $this->assertNotNull(Admin::find($admin->id), 'الأدمن حذف حسابه هو.');
    }

    /**
     * وحذف الأخير يقفل اللوحة على الجميع بلا رجعة — لا استرجاع ولا
     * «نسيت كلمة المرور» لحساب لم يعد موجوداً.
     */
    public function test_the_last_admin_account_cannot_be_deleted(): void
    {
        if (Admin::count() > 1) {
            $this->markTestSkipped('يوجد أكثر من حساب — الحارس الآخر (ليس أنت) يغطّي الحالة.');
        }

        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.administrators.destroy', $admin->id), ['id' => $admin->id])
            ->assertRedirect();

        $this->assertSame(1, Admin::count(), 'حُذف آخر حساب مسؤول.');
    }

    // ═══ الجدول ═══

    public function test_the_index_page_loads(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.administrators.index'))
            ->assertOk()
            ->assertSee('مسؤولو النظام');
    }

    /** كان حسابك مستثنى، فالجدول فارغ تماماً بحساب واحد */
    public function test_an_admin_sees_their_own_account_marked(): void
    {
        $admin = $this->admin();
        $payload = $this->fetch();

        $this->assertSame(Admin::count(), $payload['recordsTotal']);

        $mine = collect($payload['data'])
            ->first(fn ($row) => str_contains($row['identity'], e($admin->email)));

        $this->assertNotNull($mine, 'حساب الأدمن الحالي غائب عن القائمة.');
        $this->assertStringContainsString('cell-you', $mine['identity']);
    }

    /** ولا زرّ حذف على صفّك */
    public function test_no_delete_button_on_your_own_row(): void
    {
        $admin = $this->admin();

        $mine = collect($this->fetch()['data'])
            ->first(fn ($row) => str_contains($row['identity'], e($admin->email)));

        $this->assertNotNull($mine);
        $this->assertStringNotContainsString('btn-delete', $mine['actions']);
        $this->assertStringContainsString('btn-edit', $mine['actions']);
    }

    public function test_each_row_carries_every_declared_column(): void
    {
        $payload = $this->fetch();

        foreach (['identity', 'phone', 'created_at', 'actions'] as $column) {
            $this->assertArrayHasKey($column, $payload['data'][0], "العمود {$column} مفقود.");
        }
    }

    public function test_names_are_escaped(): void
    {
        foreach ($this->fetch()['data'] as $row) {
            $this->assertStringNotContainsString('<script', strtolower($row['identity']));
            $this->assertStringNotContainsString('<script', strtolower($row['actions']));
        }
    }

    // ═══ كلمة المرور ═══

    /** ستة أحرف كانت تكفي لحساب يملك كل شيء */
    public function test_a_short_password_is_rejected(): void
    {
        $this->assertTrue($this->passwordFails('sevench'));
        $this->assertFalse($this->passwordFails('eightchars'));
    }

    /** وخطأ مطبعي بلا تأكيد كان يقفل الحساب الجديد صامتاً */
    public function test_a_mismatched_confirmation_is_rejected(): void
    {
        $this->assertTrue($this->passwordFails('eightchars', 'eightcharz'));
    }

    /** يقرأ القواعد الحقيقية من الطلب نفسه، لا نسخة منها في الاختبار */
    private function passwordFails(string $password, ?string $confirmation = null): bool
    {
        $payload = [
            'name' => 'مسؤول',
            'email' => 'probe@example.test',
            'phone' => '0590000000',
            'gender' => 'male',
            'password' => $password,
            'password_confirmation' => $confirmation ?? $password,
        ];

        $rules = AdministratorsRequest::create('/', 'POST', $payload)->rules();

        return Validator::make($payload, $rules)->fails();
    }
}
