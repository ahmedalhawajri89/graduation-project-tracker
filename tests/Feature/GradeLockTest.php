<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * اعتماد الدرجة.
 *
 * كان المشرف يضع الدرجة ويغيّرها وحده، بلا حدّ، إلى الأبد.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة، فكل حقل يُعدَّل يُستعاد في
 * \u200Efinally\u200E **باستعلام مباشر لا \u200E$model->save()\u200E** — النموذج في الذاكرة
 * قد يحمل القيمة الأصلية فتبقى \u200EisDirty()\u200E كاذبة ولا يُكتب شيء.
 */
class GradeLockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // التقييم يُشعر الفريق — لا رسالة تُرسل في أي اختبار
        Notification::fake();
    }

    private function admin(): Admin
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن.');
        }

        return $admin;
    }

    /** مشروع مكتمل يملك مشرفاً — شرط \u200Eevaluate()\u200E */
    private function project(): Project
    {
        $project = Project::where('status', 'complete')->whereNotNull('supervisor_id')->first();

        if (! $project) {
            $this->markTestSkipped('لا يوجد مشروع مكتمل.');
        }

        return $project;
    }

    /** يُعيد حقول المشروع وسطور التدقيق التي أنشأها الاختبار */
    private function restoring(Project $project, callable $check): void
    {
        $fields = ['grade', 'evaluation_note', 'evaluated_at', 'grade_locked_at', 'graded_by'];
        $original = collect($fields)->mapWithKeys(fn ($f) => [$f => $project->{$f}])->all();
        $logsBefore = AuditLog::max('id') ?? 0;

        try {
            $check();
        } finally {
            Project::where('id', $project->id)->update($original);
            AuditLog::where('id', '>', $logsBefore)->delete();
        }

        $this->assertSame(
            $original['grade_locked_at']?->toDateTimeString(),
            $project->fresh()->grade_locked_at?->toDateTimeString(),
            'حالة القفل لم تُستعد.'
        );
    }

    private function evaluate(Project $project, $grade)
    {
        return $this->actingAs($project->supervisor, 'supervisor')
            ->post(route('supervisor.project.evaluate', ['project' => $project->id]), [
                'grade' => $grade,
                'evaluation_note' => 'ملاحظة اختبار',
            ]);
    }

    // ═══ ما دامت غير معتمدة ═══

    public function test_a_supervisor_can_set_and_change_an_unlocked_grade(): void
    {
        $project = $this->project();

        $this->restoring($project, function () use ($project) {
            Project::where('id', $project->id)->update(['grade_locked_at' => null]);

            $this->evaluate($project, 77)->assertRedirect();
            $this->assertEquals(77, $project->fresh()->grade);

            $this->evaluate($project, 88)->assertRedirect();
            $this->assertEquals(88, $project->fresh()->grade);
        });
    }

    /** ومَن وضعها يُسجَّل: المشرف قد يتغيّر بعد التقييم */
    public function test_the_grader_is_recorded(): void
    {
        $project = $this->project();

        $this->restoring($project, function () use ($project) {
            Project::where('id', $project->id)->update(['grade_locked_at' => null]);

            $this->evaluate($project, 70);

            $this->assertSame($project->supervisor_id, $project->fresh()->graded_by);
        });
    }

    // ═══ الاعتماد ═══

    public function test_locking_an_empty_grade_is_refused(): void
    {
        $project = $this->project();

        $this->restoring($project, function () use ($project) {
            Project::where('id', $project->id)->update(['grade' => null, 'grade_locked_at' => null]);

            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.project.grade.lock', ['project' => $project->id]))
                ->assertRedirect();

            $this->assertNull($project->fresh()->grade_locked_at, 'اعتُمدت درجة غير موضوعة.');
        });
    }

    /**
     * جوهر الميزة: بعد الاعتماد يُردّ الطلب **على الخادم**، لا بإخفاء
     * النموذج وحده — إخفاؤه لا يمنع طلباً مُلفَّقاً.
     */
    public function test_a_locked_grade_cannot_be_changed_even_by_a_direct_request(): void
    {
        $project = $this->project();

        $this->restoring($project, function () use ($project) {
            Project::where('id', $project->id)->update(['grade' => 60, 'grade_locked_at' => null]);

            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.project.grade.lock', ['project' => $project->id]))
                ->assertRedirect();

            $this->assertNotNull($project->fresh()->grade_locked_at);

            // الطلب المباشر بعد القفل
            $this->evaluate($project, 99)->assertRedirect();

            $this->assertEquals(60, $project->fresh()->grade, 'تغيّرت درجة معتمدة.');
        });
    }

    // ═══ فكّ الاعتماد ═══

    public function test_unlocking_without_a_reason_is_refused(): void
    {
        $project = $this->project();

        $this->restoring($project, function () use ($project) {
            Project::where('id', $project->id)->update(['grade' => 60, 'grade_locked_at' => now()]);

            $this->actingAs($this->admin(), 'admin')
                ->post(route('admin.groups.grade.unlock', $project->id), ['reason' => ''])
                ->assertSessionHasErrors('reason');

            // وسبب قصير كذلك: حقل شكلي لا يُقرأ عند التنازع
            $this->actingAs($this->admin(), 'admin')
                ->post(route('admin.groups.grade.unlock', $project->id), ['reason' => 'خطأ'])
                ->assertSessionHasErrors('reason');

            $this->assertNotNull($project->fresh()->grade_locked_at, 'فُكّ الاعتماد بلا سبب.');
        });
    }

    public function test_an_admin_can_unlock_with_a_reason_and_the_supervisor_can_edit_again(): void
    {
        $project = $this->project();

        $this->restoring($project, function () use ($project) {
            Project::where('id', $project->id)->update(['grade' => 60, 'grade_locked_at' => now()]);

            $this->actingAs($this->admin(), 'admin')
                ->post(route('admin.groups.grade.unlock', $project->id), [
                    'reason' => 'خطأ في احتساب درجة المناقشة النهائية',
                ])
                ->assertRedirect();

            $this->assertNull($project->fresh()->grade_locked_at);

            $this->evaluate($project, 95)->assertRedirect();
            $this->assertEquals(95, $project->fresh()->grade);
        });
    }

    // ═══ الصلاحيات ═══

    /** الطالب لا يعتمد درجة مشروعه */
    public function test_a_student_cannot_lock_a_grade(): void
    {
        $project = $this->project();
        $student = Student::first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        $this->restoring($project, function () use ($project, $student) {
            Project::where('id', $project->id)->update(['grade' => 60, 'grade_locked_at' => null]);

            $this->actingAs($student, 'student')
                ->post(route('supervisor.project.grade.lock', ['project' => $project->id]))
                ->assertRedirect();

            $this->assertNull($project->fresh()->grade_locked_at);
        });
    }

    /** والمشرف لا يفكّ اعتماداً: المسار تحت \u200Eauth:admin\u200E */
    public function test_a_supervisor_cannot_unlock(): void
    {
        $project = $this->project();
        $supervisor = Supervisor::first();

        if (! $supervisor) {
            $this->markTestSkipped('لا يوجد مشرفون.');
        }

        $this->restoring($project, function () use ($project, $supervisor) {
            Project::where('id', $project->id)->update(['grade' => 60, 'grade_locked_at' => now()]);

            $this->actingAs($supervisor, 'supervisor')
                ->post(route('admin.groups.grade.unlock', $project->id), [
                    'reason' => 'أريد تعديل درجتي بنفسي',
                ])
                ->assertRedirect();

            $this->assertNotNull($project->fresh()->grade_locked_at, 'المشرف فكّ اعتماداً.');
        });
    }
}
