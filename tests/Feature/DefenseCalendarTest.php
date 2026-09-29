<?php

namespace Tests\Feature;

use App\Exports\DefensesExport;
use App\Models\Admin;
use App\Models\Defense;
use App\Models\DefenseMember;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\ProjectActivityNotify;
use App\Support\DefenseIcs;
use App\Support\DefenseNotifier;
use App\Support\DefenseScheduler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * المرحلة الثالثة: ملف التقويم (.ics) مع البريد وللتنزيل، وبطاقة «مناقشتك»
 * وخطوة المناقشة في مسار الطالب، وتصدير جدول المناقشات.
 */
class DefenseCalendarTest extends TestCase
{
    private Project $project;
    private Defense $defense;
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
        $this->examiner = Supervisor::whereKeyNot($project->supervisor_id)->firstOrFail();
        $this->defense = Defense::create([
            'project_id' => $project->id, 'starts_at' => now()->addDays(2)->setTime(10, 0), 'duration_minutes' => 45,
            'mode' => 'hybrid', 'meeting_url' => 'https://meet.google.com/abc-defg-hij', 'status' => 'scheduled',
            'notes' => 'عرض 15 دقيقة، ثم أسئلة; أحضروا التقرير',
        ]);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $project->supervisor_id, 'role' => 'supervisor']);
        DefenseMember::create(['defense_id' => $this->defense->id, 'supervisor_id' => $this->examiner->id, 'role' => 'examiner']);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_ics_follows_the_standard(): void
    {
        $ics = DefenseIcs::make($this->defense);

        $this->assertStringStartsWith("BEGIN:VCALENDAR\r\n", $ics);
        $this->assertStringContainsString('UID:defense-' . $this->defense->id . '@', $ics);
        $this->assertStringContainsString('DTSTART:' . $this->defense->starts_at->copy()->utc()->format('Ymd\THis\Z'), $ics);
        $this->assertStringContainsString('DTEND:' . $this->defense->endsAt()->utc()->format('Ymd\THis\Z'), $ics);
        $this->assertStringContainsString('URL:https://meet.google.com/abc-defg-hij', $ics);
        $this->assertStringContainsString('STATUS:CONFIRMED', $ics);
        $this->assertStringContainsString('BEGIN:VALARM', $ics);

        // الفاصلة والفاصلة المنقوطة مهرّبتان، ولا سطر يتجاوز 75 بايتاً
        $unfolded = str_replace("\r\n ", '', $ics);
        $this->assertStringContainsString('ثم أسئلة\; أحضروا', $unfolded);
        foreach (explode("\r\n", $ics) as $line) {
            $this->assertLessThanOrEqual(75, strlen($line), $line);
        }

        $this->assertStringContainsString('STATUS:CANCELLED', DefenseIcs::make($this->defense, true));
    }

    public function test_ics_download_is_for_the_team_committee_and_admin_only(): void
    {
        // كل طلب بجلسة واحدة: تسجيلات الدخول في الاختبار نفسه تتراكم عبر الحُرّاس
        $as = function ($user, string $guard) {
            $this->app['auth']->forgetGuards();

            return $this->actingAs($user, $guard)->get(route('defenses.ics', $this->defense->id));
        };

        $student = Student::find($this->project->group()->value('student_id'));
        $as($student, 'student')->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $as($this->examiner, 'supervisor')->assertOk();
        $as(Admin::first(), 'admin')->assertOk();

        $outsider = Supervisor::whereNotIn('id', [$this->examiner->id, $this->project->supervisor_id])->firstOrFail();
        $as($outsider, 'supervisor')->assertNotFound();

        $otherStudent = Student::whereNotIn('id', $this->project->group()->pluck('student_id'))->firstOrFail();
        $as($otherStudent, 'student')->assertNotFound();
    }

    public function test_emails_carry_the_calendar_file(): void
    {
        DefenseNotifier::scheduled($this->defense);

        Notification::assertSentTo($this->examiner, ProjectActivityNotify::class, function ($n) {
            $mail = $n->toMail($this->examiner);

            return collect($mail->rawAttachments)->contains(fn ($a) => $a['name'] === DefenseIcs::filename($this->defense)
                && str_contains($a['data'], 'BEGIN:VEVENT'));
        });
    }

    public function test_student_sees_the_defense_card_and_the_defense_step(): void
    {
        $student = Student::find($this->project->group()->value('student_id'));

        $this->actingAs($student, 'student')->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('مناقشتك')
            ->assertSee($this->examiner->name)
            ->assertSee(route('defenses.ics', $this->defense->id), false)
            ->assertSeeInOrder(['التنفيذ والمتابعة', 'المناقشة', 'الدرجة']);
    }

    public function test_export_lists_the_defense_with_its_committee(): void
    {
        $rows = (new DefensesExport($this->project->semester_id))->collection();
        $this->assertContains($this->defense->id, $rows->pluck('id'));

        $row = (new DefensesExport())->map($this->defense->fresh(['project.group.student', 'project.project_type', 'members.supervisor', 'room']));
        $this->assertSame('10:00', $row[2]);
        $this->assertSame($this->examiner->name, $row[8]);
        $this->assertSame('مجدولة', $row[11]);

        $this->actingAs(Admin::first(), 'admin')->get(route('admin.defenses.export'))->assertOk();
    }
}
