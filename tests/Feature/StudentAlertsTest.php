<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\ProjectActivityNotify;
use App\Support\NotificationView;
use Illuminate\Http\UploadedFile;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * ما يصل الطالب خارج المنصّة: البريد لما ينتظر تصرّفه، وتذكير المواعيد.
 *
 * كان كل شيء في الجرس وحده، والطالب لا يفتح المنصّة كل يوم — فيعلم بطلب
 * التعديل أو بقرب الموعد بعد فواته. داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class StudentAlertsTest extends TestCase
{
    private Project $project;
    private Supervisor $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Storage::fake('local');
        DB::beginTransaction();

        $project = Project::where('status', 'accept')->whereNull('grade')->whereNotNull('supervisor_id')
            ->whereHas('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع جارٍ بفريق ومشرف.');
        }

        $this->project = $project;
        $this->supervisor = $project->supervisor;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function milestone(array $attrs = []): ProjectMilestone
    {
        return $this->project->milestones()->create($attrs + [
            'title' => 'مرحلة اختبار التنبيهات',
            'due_date' => today()->addWeek(),
        ]);
    }

    private function member(): Student
    {
        return Student::find($this->project->group()->value('student_id'));
    }

    /** ما وصل الطالب من إشعارات عن هذه المرحلة */
    private function sentAbout(Student $student, ProjectMilestone $milestone)
    {
        return Notification::sent($student, ProjectActivityNotify::class)
            ->filter(fn ($n) => str_contains($n->toArray($student)['msg'] ?? '', '«' . $milestone->title . '»'));
    }

    private function channels(ProjectActivityNotify $n, Student $student): array
    {
        return $n->via($student);
    }

    private function submitAndReview(ProjectMilestone $milestone, string $decision, ?string $feedback = null): void
    {
        $this->actingAs($this->member(), 'student')
            ->post(route('student.milestones.submit', $milestone->id), [
                'note' => 'أنهينا الفصل.',
                'file' => UploadedFile::fake()->create('الفصل.pdf', 80, 'application/pdf'),
            ])->assertSessionHas('success');

        $this->app['auth']->forgetGuards();

        $this->actingAs($this->supervisor, 'supervisor')
            ->post(route('supervisor.milestones.review', $milestone->id), array_filter([
                'decision' => $decision,
                'feedback' => $feedback,
            ]))->assertSessionHas('success');
    }

    /* ==================== البريد لما ينتظر تصرّفاً ==================== */

    public function test_a_revision_request_is_emailed_with_a_link_to_the_stage(): void
    {
        $milestone = $this->milestone();
        $this->submitAndReview($milestone, 'revision', 'ينقص مخطط الكيانات.');

        foreach ($this->project->students() as $student) {
            $sent = $this->sentAbout($student, $milestone);
            $this->assertCount(1, $sent, 'لم يصل طلب التعديل لكل الفريق.');

            $n = $sent->first();
            $this->assertSame($student->email ? ['database', 'mail'] : ['database'], $this->channels($n, $student));

            if ($student->email) {
                $mail = $n->toMail($student);
                $this->assertStringContainsString('مطلوب تعديل', $mail->subject);
                $this->assertStringEndsWith('#milestone-' . $milestone->id, $mail->actionUrl);
            }
        }
    }

    /** الاعتماد خبر سارّ لا يستدعي تصرّفاً — الجرس يكفي، والبريد لكل شيء ضجيج */
    public function test_an_approval_stays_in_app(): void
    {
        $milestone = $this->milestone();
        $this->submitAndReview($milestone, 'approve');

        $student = $this->member();
        $student->email ??= 'member@example.test';

        $this->assertSame(['database'], $this->channels($this->sentAbout($student, $milestone)->first(), $student));
    }

    public function test_the_grade_is_emailed(): void
    {
        $project = Project::where('status', 'complete')->whereNotNull('supervisor_id')->whereHas('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع مكتمل.');
        }

        $project->update(['grade' => null, 'grade_locked_at' => null]);

        $this->actingAs($project->supervisor, 'supervisor')
            ->post(route('supervisor.project.evaluate', $project->id), ['grade' => 88])
            ->assertSessionHas('success');

        $student = $project->students()->first();
        $n = Notification::sent($student, ProjectActivityNotify::class)->last();

        $this->assertNotNull($n);
        $student->email ??= 'member@example.test';
        $this->assertSame(['database', 'mail'], $n->via($student));
        $this->assertStringContainsString('درجة', $n->toMail($student)->subject);
    }

    /** الملفات والمراحل الجديدة وسائر النشاط في الجرس وحده */
    public function test_ordinary_activity_is_not_emailed(): void
    {
        $student = $this->member();
        $student->email ??= 'member@example.test';

        $n = new ProjectActivityNotify(['project' => 'مشروع', 'msg' => 'رفع المشرف ملفاً جديداً: خطة']);

        $this->assertSame(['database'], $n->via($student));
    }

    /* ==================== تذكير المواعيد ==================== */

    public function test_a_team_is_reminded_two_days_before_the_deadline(): void
    {
        $milestone = $this->milestone(['due_date' => today()->addDays(2)]);

        $this->artisan('milestones:remind')->assertSuccessful();

        foreach ($this->project->students() as $student) {
            $sent = $this->sentAbout($student, $milestone);
            $this->assertCount(1, $sent, 'لم يُذكَّر كل الفريق.');

            $data = $sent->first()->toArray($student);
            $this->assertSame('reminder', $data['kind']);
            $this->assertStringContainsString('بعد يومين', $data['msg']);
            $this->assertSame($milestone->id, $data['milestone_id']);
        }

        $this->assertTrue($milestone->fresh()->reminded_on->isToday());
    }

    public function test_the_last_day_says_today(): void
    {
        $milestone = $this->milestone(['due_date' => today()]);

        $this->artisan('milestones:remind');

        $student = $this->member();
        $n = $this->sentAbout($student, $milestone)->first();

        $this->assertNotNull($n);
        $this->assertStringContainsString('اليوم', $n->toArray($student)['msg']);
        $student->email ??= 'member@example.test';
        $this->assertStringStartsWith('آخر يوم', $n->toMail($student)->subject);
    }

    /** الأمر قد يُشغَّل مرّتين في اليوم — التذكير لا يتكرّر */
    public function test_a_second_run_the_same_day_sends_nothing(): void
    {
        $milestone = $this->milestone(['due_date' => today()->addDays(2)]);

        $this->artisan('milestones:remind');
        $this->artisan('milestones:remind');

        $this->assertCount(1, $this->sentAbout($this->member(), $milestone));
    }

    /** المسلَّمة بانتظار المشرف، والبعيدة، والمعتمدة — لا تذكير */
    public function test_only_stages_still_owed_by_the_team_are_reminded(): void
    {
        $submitted = $this->milestone(['title' => 'مرحلة مسلَّمة', 'due_date' => today()->addDays(2), 'status' => ProjectMilestone::SUBMITTED]);
        $far = $this->milestone(['title' => 'مرحلة بعيدة', 'due_date' => today()->addDays(5)]);
        $done = $this->milestone(['title' => 'مرحلة معتمدة', 'due_date' => today(), 'status' => ProjectMilestone::APPROVED, 'is_done' => true]);

        $this->artisan('milestones:remind');

        foreach ([$submitted, $far, $done] as $milestone) {
            $this->assertCount(0, $this->sentAbout($this->member(), $milestone), "ذُكِّرت «{$milestone->title}».");
            $this->assertNull($milestone->fresh()->reminded_on);
        }
    }

    /** المطلوب تعديلها ما زالت على الفريق — تُذكَّر، بنصّ إعادة التسليم */
    public function test_a_stage_under_revision_is_reminded_to_resubmit(): void
    {
        $milestone = $this->milestone(['due_date' => today()->addDays(2), 'status' => ProjectMilestone::REVISION]);

        $this->artisan('milestones:remind');

        $student = $this->member();
        $this->assertStringContainsString('إعادة تسليم', $this->sentAbout($student, $milestone)->first()->toArray($student)['msg']);
    }

    /** المشروع المقيَّم مؤرشف — مراحله لا تُذكَّر */
    public function test_a_graded_project_is_not_reminded(): void
    {
        $milestone = $this->milestone(['due_date' => today()->addDays(2)]);
        $this->project->update(['grade' => 90]);

        $this->artisan('milestones:remind');

        $this->assertCount(0, $this->sentAbout($this->member(), $milestone));
    }

    /** في صفحة الإشعارات: تذكير من المنصّة، ورابطه إلى المرحلة نفسها */
    public function test_a_reminder_is_presented_as_a_reminder(): void
    {
        $n = new DatabaseNotification();
        $n->type = ProjectActivityNotify::class;
        $n->data = ['kind' => 'reminder', 'msg' => 'تذكير: آخر موعد لتسليم مرحلة «التحليل» اليوم.', 'milestone_id' => 42];

        $view = NotificationView::present($n);

        $this->assertSame('reminder', $view['cat']);
        $this->assertSame('تخرُّج', $view['sender']);
        $this->assertStringEndsWith('#milestone-42', $view['href']);
    }
}
