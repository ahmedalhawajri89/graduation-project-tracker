<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Project;
use App\Models\Semester;
use App\Models\SpecializeProject;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * تقديم المقترح — نقطة الدخول التي كانت تحفظ الطلب كاملاً.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة: كل مشروع يُنشأ يُحذف نهائياً
 * مع صفوف فريقه في \u200EtearDown\u200E بمعرّفه، و\u200Emax_group\u200E يُستعاد باستعلام مباشر.
 */
class ProposalTest extends TestCase
{
    private int $projectMark = 0;

    private int $groupMark = 0;

    /** @var array<int, int|null> */
    private array $maxGroups = [];

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->projectMark = (int) (Project::withTrashed()->max('id') ?? 0);
        $this->groupMark = (int) (Group::max('id') ?? 0);
    }

    protected function tearDown(): void
    {
        Group::where('id', '>', $this->groupMark)->delete();
        Project::withTrashed()->where('id', '>', $this->projectMark)->forceDelete();

        foreach ($this->maxGroups as $id => $max) {
            DB::table('supervisors')->where('id', $id)->update(['max_group' => $max]);
        }

        parent::tearDown();
    }

    /**
     * سيناريو صالح: نوع مشروع، ومشرف بمقعد، وطلاب متاحون بقدر الحدّ الأدنى
     * — من القواعد نفسها التي يستعملها الكود.
     *
     * @return array{leader: Student, mates: \Illuminate\Support\Collection, type: SpecializeProject, supervisor: Supervisor}
     */
    private function scenario(): array
    {
        foreach (SpecializeProject::orderBy('min')->get() as $type) {
            $supervisor = Supervisor::where('specialize_id', $type->specialize_id)
                ->where('max_group', '>', 0)->get()
                ->first(fn ($s) => $s->seatsLeft() > 0);

            $available = Student::availableForTeam($type->specialize_id)->take(max(2, $type->min))->get();

            if ($supervisor && $available->count() >= max(2, $type->min)) {
                return [
                    'leader' => $available->first(),
                    'mates' => $available->slice(1, max(1, $type->min - 1))->values(),
                    'type' => $type,
                    'supervisor' => $supervisor,
                ];
            }
        }

        $this->markTestSkipped('لا سيناريو صالح في البيانات.');
    }

    private function payload(array $s, array $extra = []): array
    {
        return array_merge([
            'title' => 'مقترح اختبار ' . uniqid(),
            'description' => 'وصف قصير',
            'supervisor_id' => $s['supervisor']->id,
            'specialize_project_id' => $s['type']->id,
            'student_ids' => array_merge(
                [$s['leader']->university_id],
                $s['mates']->pluck('university_id')->all()
            ),
        ], $extra);
    }

    private function newProject(): ?Project
    {
        return Project::withTrashed()->where('id', '>', $this->projectMark)->first();
    }

    // ═══ الثغرة الحرجة: الطلب لا يكتب الحالة ولا الدرجة ═══

    public function test_forged_status_and_grade_are_ignored(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $this->payload($s, [
                'status' => 'complete',
                'grade' => 100,
                'grade_locked_at' => '2026-01-01 00:00:00',
                'graded_by' => $s['supervisor']->id,
                'date_line' => '2030-01-01',
            ]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $project = $this->newProject();
        $this->assertNotNull($project, 'لم يُنشأ المشروع.');
        $this->assertSame('request', $project->status);
        $this->assertNull($project->grade);
        $this->assertNull($project->grade_locked_at);
        $this->assertNull($project->graded_by);
        $this->assertNull($project->date_line);
    }

    public function test_the_semester_comes_from_the_server(): void
    {
        $s = $this->scenario();
        $other = Semester::where('id', '!=', Semester::current()->id)->value('id');

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $this->payload($s, ['semester_id' => $other]))
            ->assertSessionHas('success');

        $this->assertSame(Semester::current()->id, (int) $this->newProject()->semester_id);
    }

    // ═══ الفريق ═══

    /** كان يمكن تسجيل زملاء في مشروع لم يوافقوا عليه دون أن يكون المُرسِل منهم */
    public function test_the_sender_must_be_in_the_team(): void
    {
        $s = $this->scenario();
        $outsider = Student::availableForTeam($s['type']->specialize_id)
            ->whereNotIn('id', $s['mates']->pluck('id')->push($s['leader']->id))
            ->first();

        if (! $outsider) {
            $this->markTestSkipped('لا طالب ثالث متاح.');
        }

        $payload = $this->payload($s);
        $payload['student_ids'] = array_values(array_diff($payload['student_ids'], [$s['leader']->university_id]));
        $payload['student_ids'][] = $outsider->university_id;

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $payload)
            ->assertSessionHas('fail');

        $this->assertNull($this->newProject(), 'أُنشئ مشروع لا يضمّ مُرسِله.');
    }

    /** القيم الفارغة كانت تُعدّ في الحدّ الأدنى ولا تصير أعضاءً */
    public function test_empty_ids_do_not_count_towards_the_minimum(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $this->payload($s, [
                'student_ids' => [$s['leader']->university_id, null, ''],
            ]))
            ->assertSessionHasErrors();

        $this->assertNull($this->newProject());
    }

    public function test_the_leader_is_the_sender(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $this->payload($s))
            ->assertSessionHas('success');

        $leaders = Group::where('project_id', $this->newProject()->id)->where('type', 'leader')->pluck('student_id');
        $this->assertSame([(int) $s['leader']->id], $leaders->map(fn ($id) => (int) $id)->all());
    }

    public function test_a_busy_student_cannot_join_a_second_team(): void
    {
        $s = $this->scenario();
        $busy = Student::where('specialize_id', $s['type']->specialize_id)
            ->whereHas('groups.project', fn ($q) => $q->where('status', 'accept'))
            ->first();

        if (! $busy) {
            $this->markTestSkipped('لا طالب في مشروع قائم في هذا التخصص.');
        }

        $payload = $this->payload($s);
        $payload['student_ids'][] = $busy->university_id;

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $payload)
            ->assertSessionHas('fail');

        $this->assertNull($this->newProject());
    }

    // ═══ الوصف والمقاعد ═══

    /** العمود كان NOT NULL فيفشل كل مقترح بلا وصف، والنموذج يقول «اختياري» */
    public function test_a_proposal_without_a_description_is_accepted(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $this->payload($s, ['description' => '']))
            ->assertSessionHas('success');

        $this->assertNull($this->newProject()->description);
    }

    /** كان يعدّ مجموعات «الفصل ٠» فلا يمنع مشرفاً مكتملاً أبداً */
    public function test_a_full_supervisor_is_refused_at_submission(): void
    {
        $s = $this->scenario();

        // مشرف من التخصص نفسه له مجموعة قائمة هذا الفصل: يُضبط حدّه على عدد
        // مجموعاته فيكتمل — لا حدّ صفر، فذاك يُخرجه من القائمة بفحص آخر
        $sup = Supervisor::where('specialize_id', $s['type']->specialize_id)->where('max_group', '>', 0)->get()
            ->first(fn ($x) => ((int) $x->max_group - $x->seatsLeft()) >= 1);

        if (! $sup) {
            $this->markTestSkipped('لا مشرف في التخصص له مجموعة قائمة.');
        }

        $s['supervisor'] = $sup;
        $this->maxGroups[$sup->id] = $sup->max_group;
        DB::table('supervisors')->where('id', $sup->id)->update(['max_group' => (int) $sup->max_group - $sup->seatsLeft()]);
        $this->assertSame(0, Supervisor::find($sup->id)->seatsLeft());

        $this->actingAs($s['leader'], 'student')
            ->post(route('student.project.create'), $this->payload($s))
            ->assertSessionHas('fail');

        $this->assertNull($this->newProject());
    }

    public function test_nothing_leaked(): void
    {
        $this->assertSame(0, Project::withTrashed()->where('id', '>', $this->projectMark)->count());
    }
}
