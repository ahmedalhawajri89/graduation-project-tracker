<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Semester;
use App\Models\StatSnapshot;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * صفحات كانت تسقط، وحالات كانت تحجب مستخدمين.
 *
 * كل اختبار داخل معاملة تُرجَع في ‎tearDown‎: الحالات المؤقتة (حذف الفصول،
 * حذف مشروع حذفاً مرناً) لا تبقى — وطلبات HTTP في الاختبار ترى المعاملة نفسها.
 */
class RobustnessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        parent::tearDown();
    }

    // ═══ مجموعات المشرف (الأدمن) ═══

    public function test_an_unknown_supervisor_gives_404_not_500(): void
    {
        $this->actingAs(Admin::first(), 'admin')
            ->get(route('admin.supervisors.groups', 999999))
            ->assertNotFound();
    }

    /** كان العرض يمرّ على كل الفصول تحت عنوان الفصل الحالي */
    public function test_the_supervisor_groups_page_shows_the_current_semester_only(): void
    {
        $current = Semester::current()->id;
        $supervisor = Supervisor::whereHas('projectsAccept', fn ($q) => $q->where('semester_id', '!=', $current))->first();

        if (! $supervisor) {
            $this->markTestSkipped('لا مشرف له مشاريع في فصل سابق.');
        }

        $projects = $this->actingAs(Admin::first(), 'admin')
            ->get(route('admin.supervisors.groups', $supervisor->id))
            ->assertOk()
            ->viewData('projects');

        $this->assertTrue($projects->every(fn ($p) => (int) $p->semester_id === $current));
    }

    // ═══ لا فصل دراسي ═══

    /** تثبيت جديد قبل الفصل الأول: كانت لوحة الأدمن أول ما يُفتح بعد الدخول — بخطأ 500 */
    public function test_without_any_semester_pages_guide_instead_of_crashing(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::table('semesters')->delete();
        $this->assertNull(Semester::current());

        $this->actingAs(Admin::first(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.semesters.index'));

        $this->actingAs(Admin::first(), 'admin')
            ->get(route('admin.semesters.index'))
            ->assertOk();

        $this->app['auth']->forgetGuards();
        $this->actingAs(Student::first(), 'student')
            ->get(route('student.dashboard'))
            ->assertStatus(503)
            ->assertSee('لم يُفتح فصل دراسي بعد');
    }

    // ═══ رسم الاتجاه ═══

    /** كان يأخذ أقدم ستّين لقطة فيتجمّد الخطّ بعد شهرين */
    public function test_the_trend_shows_the_newest_snapshots(): void
    {
        DB::table('stat_snapshots')->delete();
        foreach (range(1, 70) as $i) {
            StatSnapshot::create(['date' => now()->subDays(70 - $i)->toDateString(), 'groups' => $i, 'not_has_group' => 0]
                + array_fill_keys(array_diff((new StatSnapshot())->getFillable(), ['date', 'groups', 'not_has_group']), 0));
        }

        $series = $this->actingAs(Admin::first(), 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->viewData('trendSeries');

        $this->assertCount(60, $series);
        $this->assertSame(now()->toDateString(), \Illuminate\Support\Carbon::parse($series->last()->date)->toDateString());
        $this->assertTrue($series->first()->date < $series->last()->date, 'السلسلة ليست تصاعدية.');
    }

    // ═══ كلمة السر ═══

    /** الدخول كان يرفض ما فوق ٣٠ حرفاً والملف الشخصي يسمح بستّين — فيُقفل صاحبه خارج حسابه */
    public function test_a_long_password_set_elsewhere_can_log_in(): void
    {
        $student = Student::first();
        $long = str_repeat('طويلة', 9); // ٤٥ حرفاً
        DB::table('students')->where('id', $student->id)->update(['password' => Hash::make($long)]);

        $this->post(route('login.check'), ['identify' => $student->university_id, 'password' => $long])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($student->fresh(), 'student');
    }

    public function test_a_new_account_needs_eight_characters(): void
    {
        $spec = DB::table('specializes')->whereNull('archived_at')->value('id');

        $this->actingAs(Admin::first(), 'admin')
            ->post(route('admin.students.store'), [
                'name' => 'طالب', 'university_id' => '2309999931', 'email' => 's31@example.test',
                'specialize_id' => $spec, 'gender' => 'male', 'password' => '123456',
            ])
            ->assertSessionHasErrors('password');

        $this->assertNull(Student::where('university_id', '2309999931')->first());
    }

    // ═══ الملفات والمشروع المحذوف ═══

    private function fileOnDisk(): ProjectFile
    {
        $file = ProjectFile::with('project.group')->get()
            ->first(fn ($f) => $f->project && $f->project->group->isNotEmpty() && Storage::disk('local')->exists($f->path));

        if (! $file) {
            $this->markTestSkipped('لا ملف مشروع موجود على القرص.');
        }

        return $file;
    }

    /** كان ‎$file->project‎ null لمشروع محذوف فيسقط التنزيل بخطأ 500 */
    public function test_a_file_of_a_deleted_project_is_404_for_a_student(): void
    {
        $file = $this->fileOnDisk();
        $student = Student::find($file->project->group->first()->student_id);
        $file->project->delete();

        $this->actingAs($student, 'student')
            ->get(route('files.download', $file->id))
            ->assertNotFound();
    }

    /** عنوان فيه «/» كان يُفشل التنزيل دائماً */
    public function test_a_title_with_a_slash_still_downloads(): void
    {
        $file = $this->fileOnDisk();
        DB::table('project_files')->where('id', $file->id)->update(['title' => 'تقرير/نهائي']);

        $response = $this->actingAs(Admin::first(), 'admin')
            ->get(route('files.download', $file->id))
            ->assertOk();

        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
    }

    /** الطالب المحجوز بمشروع محذوف كان يرى نموذجاً يُرفض بلا تفسير */
    public function test_a_student_held_by_a_deleted_project_sees_why(): void
    {
        $group = Group::whereHas('project', fn ($q) => $q->where('status', 'accept'))->where('type', 'member')->first();
        $student = Student::find($group->student_id);
        Project::find($group->project_id)->delete();

        $this->actingAs($student, 'student')
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('موقوف لدى الإدارة')
            ->assertDontSee(route('student.project.create'), false);
    }
}
