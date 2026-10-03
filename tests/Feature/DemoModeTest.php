<?php

namespace Tests\Feature;

use App\Models\DefenseRoom;
use App\Models\Student;
use App\Support\Demo;
use App\Support\DemoSnapshot;
use App\Support\DefenseScheduler;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * وضع العرض التجريبي: مطفأً لا أثر له أبداً؛ مفعّلاً دخول بنقرة، وتبديل
 * الدور، وحماية من التخريب، ولقطة تعود بتواريخ مُزاحة بأسابيع كاملة.
 */
class DemoModeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function demo(): void
    {
        config(['demo.enabled' => true]);
    }

    public function test_off_by_default_leaves_no_trace(): void
    {
        $this->post(route('demo.enter', 'student'))->assertNotFound();
        $this->get(route('login'))->assertDontSee('demo-try', false);
        $this->artisan('demo:reset')->assertFailed();
    }

    public function test_the_sign_in_page_offers_three_roles(): void
    {
        $this->demo();

        $this->get(route('login'))->assertOk()
            ->assertSee(route('demo.enter', 'student'), false)
            ->assertSee(route('demo.enter', 'supervisor'), false)
            ->assertSee(route('demo.enter', 'admin'), false);
    }

    public function test_one_click_enters_each_dashboard_and_switches_roles(): void
    {
        $this->demo();
        foreach (Demo::ROLES as $role) {
            if (! Demo::account($role)) {
                $this->markTestSkipped("لا حساب {$role} في البيانات.");
            }
        }

        $this->post(route('demo.enter', 'student'))->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticatedAs(Demo::account('student'), 'student');

        // التبديل يُخرج من الدور السابق
        $this->post(route('demo.enter', 'admin'))->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs(Demo::account('admin'), 'admin');
        $this->assertGuest('student');

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('demo-bar', false);
    }

    public function test_destructive_actions_are_refused(): void
    {
        $this->demo();
        $student = Demo::account('student') ?? $this->markTestSkipped('لا طالب.');
        $email = $student->email;

        $this->actingAs($student, 'student')->from(route('student.profile.edit'))
            ->put(route('student.profile.update'), ['email' => 'hijack@example.com'])
            ->assertRedirect(route('student.profile.edit'))->assertSessionHas('fail');
        $this->assertSame($email, $student->fresh()->email);

        $admin = Demo::account('admin');
        $victim = Student::whereKeyNot($student->id)->firstOrFail();
        $this->app['auth']->forgetGuards();
        $this->actingAs($admin, 'admin')->delete(route('admin.students.destroy', $victim->id))->assertSessionHas('fail');
        $this->assertNotNull(Student::find($victim->id));

        // حساب التجربة نفسه لا يُعدَّل من الإدارة
        $this->put(route('admin.students.update', $student->id), ['name' => 'x'])->assertSessionHas('fail');
    }

    public function test_big_uploads_are_refused_but_normal_work_goes_on(): void
    {
        $this->demo();
        $student = Demo::account('student') ?? $this->markTestSkipped('لا طالب.');
        $project = $student->groups()->first()->project;

        $this->actingAs($student, 'student')->from(route('student.dashboard'))
            ->post(route('student.files.store', $project->id), ['title' => 'كبير', 'file' => UploadedFile::fake()->create('big.pdf', 3 * 1024, 'application/pdf')])
            ->assertSessionHas('fail');

        $this->postJson(route('student.comments.store', $project->id), ['body' => 'رسالة من زائر التجربة'])->assertOk();
    }

    public function test_the_snapshot_restores_and_shifts_dates_by_whole_weeks(): void
    {
        if (! DefenseScheduler::enabled()) {
            $this->markTestSkipped('لا جداول مناقشات.');
        }
        $path = storage_path('framework/testing/demo-' . uniqid());
        $snap = new DemoSnapshot($path);

        $room = DefenseRoom::create(['name' => 'قاعة اللقطة ' . uniqid(), 'is_active' => true]);
        $created = $room->fresh()->created_at;
        $snap->capture(['defense_rooms'], false);

        $room->update(['name' => 'عبث زائر']);
        DefenseRoom::create(['name' => 'قاعة أضافها زائر ' . uniqid(), 'is_active' => true]);

        $r = $snap->restore(now()->addDays(16), false);

        $this->assertSame(14, $r['shifted_days']);
        $back = DefenseRoom::find($room->id);
        $this->assertNotSame('عبث زائر', $back->name);
        $this->assertSame($created->copy()->addDays(14)->toDateTimeString(), $back->created_at->toDateTimeString());
        $this->assertFalse(DefenseRoom::where('name', 'like', 'قاعة أضافها زائر%')->exists());

        File::deleteDirectory($path);
    }
}
