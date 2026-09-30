<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Defense;
use App\Models\DefenseMember;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use App\Support\DefenseScheduler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * محضر المناقشة: للإدارة واللجنة وحدهما، «مسودة» قبل اكتمال الدرجات،
 * ومكتمل بدرجة كل عضو وملاحظاته والنهائية بعدها.
 */
class DefenseMinutesTest extends TestCase
{
    private Project $project;
    private Defense $defense;
    private Supervisor $supervisor;
    private Supervisor $examiner;

    protected function setUp(): void
    {
        parent::setUp();

        if (! DefenseScheduler::enabled()) {
            $this->markTestSkipped('جداول المناقشات غير موجودة في قاعدة الاختبار.');
        }

        Notification::fake();
        DB::beginTransaction();

        $project = Project::whereHas('group')->whereNotNull('supervisor_id')->first();
        if (! $project) {
            $this->markTestSkipped('لا مشروع بفريق.');
        }
        $project->update(['status' => 'complete', 'grade' => null, 'grade_locked_at' => null]);
        Defense::where('project_id', $project->id)->delete();

        $this->project = $project->fresh();
        $this->supervisor = Supervisor::findOrFail($project->supervisor_id);
        $this->examiner = Supervisor::whereKeyNot($project->supervisor_id)->firstOrFail();
        $this->defense = Defense::create([
            'project_id' => $project->id, 'starts_at' => now()->subHour(), 'duration_minutes' => 45,
            'mode' => 'online', 'meeting_url' => 'https://meet.google.com/abc-defg-hij', 'status' => 'scheduled',
        ]);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $this->supervisor->id, 'role' => 'supervisor']);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $this->examiner->id, 'role' => 'examiner']);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function grade(Supervisor $who, $grade, string $comments)
    {
        $this->app['auth']->forgetGuards();

        return $this->actingAs($who, 'supervisor')
            ->post(route('supervisor.defenses.grade', $this->defense->id), ['grade' => $grade, 'comments' => $comments]);
    }

    public function test_before_all_grades_it_is_a_draft(): void
    {
        $this->grade($this->supervisor, 90, 'عرض واضح');

        $this->app['auth']->forgetGuards();
        $this->actingAs(Admin::first(), 'admin')->get(route('defenses.minutes', $this->defense->id))
            ->assertOk()
            ->assertSee('مسودة')
            ->assertSee($this->project->title)
            ->assertSee($this->examiner->name)
            ->assertSee('تُحسب حين يرصد كل الأعضاء درجاتهم');
    }

    public function test_complete_minutes_carry_each_grade_comment_and_the_final(): void
    {
        $this->grade($this->supervisor, 90, 'عرض واضح');
        $this->grade($this->examiner, 81, 'ينقص التوثيق');

        $this->app['auth']->forgetGuards();
        $res = $this->actingAs($this->examiner, 'supervisor')->get(route('defenses.minutes', $this->defense->id))->assertOk();
        $res->assertDontSee('<div class="mn-draft"', false)
            ->assertSee('عرض واضح')->assertSee('ينقص التوثيق')
            ->assertSee('>90<', false)->assertSee('>81<', false)->assertSee('85.5')
            ->assertSee('DEF-' . str_pad($this->defense->id, 4, '0', STR_PAD_LEFT));
    }

    public function test_only_admin_and_committee_can_open_it(): void
    {
        $outsider = Supervisor::whereNotIn('id', [$this->supervisor->id, $this->examiner->id])->firstOrFail();
        $this->actingAs($outsider, 'supervisor')->get(route('defenses.minutes', $this->defense->id))->assertNotFound();

        $this->app['auth']->forgetGuards();
        $student = Student::find($this->project->group()->value('student_id'));
        $this->actingAs($student, 'student')->get(route('defenses.minutes', $this->defense->id))->assertNotFound();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->supervisor, 'supervisor')->get(route('defenses.minutes', $this->defense->id))->assertOk();
    }

    public function test_cancelled_defense_has_no_minutes(): void
    {
        $this->defense->update(['status' => Defense::CANCELLED]);

        $this->actingAs(Admin::first(), 'admin')->get(route('defenses.minutes', $this->defense->id))->assertNotFound();
    }
}
