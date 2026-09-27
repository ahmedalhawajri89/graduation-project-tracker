<?php

namespace Tests\Feature;

use App\Models\DiscussionRead;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Supervisor;
use App\Support\Discussion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * النقاش في تبويب مستقلّ وعدّاد غير المقروء.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة: التعليقات المُنشأة تُحذف
 * بمعرّفها، وصفوف \u200Ediscussion_reads\u200E تُستعاد من لقطتها — في \u200Efinally\u200E
 * وباستعلام مباشر.
 */
class DiscussionTest extends TestCase
{
    private int $commentMark = 0;

    /** @var array<int, array> */
    private array $readsSnapshot = [];

    private ?Project $project = null;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->commentMark = (int) (ProjectComment::max('id') ?? 0);
    }

    protected function tearDown(): void
    {
        ProjectComment::where('id', '>', $this->commentMark)->delete();

        if ($this->project) {
            DB::table('discussion_reads')->where('project_id', $this->project->id)->delete();
            DB::table('discussion_reads')->insert($this->readsSnapshot);
        }

        parent::tearDown();
    }

    /** مشروع مقبول في الفصل الحالي بمشرف وطالب — أي ما تعرضه صفحتا النقاش */
    private function project(): Project
    {
        $project = Project::where('status', 'accept')
            ->where('semester_id', Semester::current()?->id)
            ->whereNotNull('supervisor_id')
            ->has('group')
            ->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع مقبول في الفصل الحالي.');
        }

        $this->project = $project;
        $this->readsSnapshot = DB::table('discussion_reads')->where('project_id', $project->id)
            ->get()->map(fn ($r) => (array) $r)->all();

        return $project;
    }

    private function studentOf(Project $project): Student
    {
        return Student::findOrFail($project->group()->value('student_id'));
    }

    private function unread($reader, Project $project): int
    {
        return Discussion::unreadFor($reader, [$project->id])[$project->id] ?? 0;
    }

    // ═══ العدّاد ═══

    public function test_a_student_message_raises_the_supervisor_count_by_one(): void
    {
        $project = $this->project();
        $supervisor = $project->supervisor;
        $before = $this->unread($supervisor, $project);

        $this->actingAs($this->studentOf($project), 'student')
            ->post(route('student.comments.store', $project->id), ['body' => 'سؤال اختبار'])
            ->assertRedirect();

        $this->assertSame($before + 1, $this->unread($supervisor, $project));
    }

    /** ما يكتبه المرء لا يُعدّ عليه — ويُعلِّم ما قبله مقروءاً */
    public function test_your_own_message_is_never_unread(): void
    {
        $project = $this->project();
        $student = $this->studentOf($project);

        $this->actingAs($student, 'student')
            ->post(route('student.comments.store', $project->id), ['body' => 'رسالتي'])
            ->assertRedirect();

        $this->assertSame(0, $this->unread($student, $project));
    }

    public function test_opening_the_thread_clears_that_project_only(): void
    {
        $project = $this->project();
        $supervisor = $project->supervisor;

        $project->comments()->create([
            'body' => 'رسالة من الطالب',
            'author_type' => Student::class,
            'author_id' => $this->studentOf($project)->id,
        ]);
        $this->assertGreaterThan(0, $this->unread($supervisor, $project));

        // مشروع آخر للمشرف نفسه يبقى عدّاده كما هو
        $others = Discussion::projectsFor($supervisor)->pluck('id')->reject(fn ($id) => $id === $project->id);
        $othersBefore = Discussion::unreadFor($supervisor, $others);

        $this->actingAs($supervisor, 'supervisor')
            ->get(route('supervisor.discussion', $project->id))
            ->assertOk()
            ->assertSee('رسائل جديدة');

        $this->assertSame(0, $this->unread($supervisor, $project));
        $this->assertSame($othersBefore, Discussion::unreadFor($supervisor, $others));
    }

    /** القائمة وحدها لا تُعلِّم شيئاً مقروءاً: على الجوال لا يرى المشرف إلا القائمة */
    public function test_the_inbox_without_a_thread_marks_nothing_read(): void
    {
        $project = $this->project();
        $supervisor = $project->supervisor;

        $project->comments()->create([
            'body' => 'رسالة لم تُفتح',
            'author_type' => Student::class,
            'author_id' => $this->studentOf($project)->id,
        ]);
        $before = $this->unread($supervisor, $project);

        $this->actingAs($supervisor, 'supervisor')
            ->get(route('supervisor.discussion'))
            ->assertOk();

        $this->assertSame($before, $this->unread($supervisor, $project));
    }

    /** الإشعار أُلغي: العدّاد يحلّ محلّه، وبقاؤه يعدّ الرسالة مرّتين */
    public function test_a_message_sends_no_notification(): void
    {
        $project = $this->project();

        $this->actingAs($this->studentOf($project), 'student')
            ->post(route('student.comments.store', $project->id), ['body' => 'بلا إشعار']);
        $this->actingAs($project->supervisor, 'supervisor')
            ->post(route('supervisor.comments.store', $project->id), ['body' => 'بلا إشعار']);

        Notification::assertNothingSent();
    }

    // ═══ الصلاحيات ═══

    public function test_a_supervisor_cannot_open_another_supervisors_thread(): void
    {
        $project = $this->project();
        $other = Supervisor::where('id', '!=', $project->supervisor_id)->first();

        $this->actingAs($other, 'supervisor')
            ->get(route('supervisor.discussion', $project->id))
            ->assertForbidden();

        $this->assertSame(0, DiscussionRead::where('project_id', $project->id)
            ->where('reader_type', Supervisor::class)->where('reader_id', $other->id)->count());
    }

    public function test_both_pages_require_login(): void
    {
        $this->get(route('student.discussion'))->assertRedirect();
        $this->get(route('supervisor.discussion'))->assertRedirect();
    }

    // ═══ الصفحات ═══

    public function test_the_student_page_shows_the_thread(): void
    {
        $project = $this->project();

        $this->actingAs($this->studentOf($project), 'student')
            ->get(route('student.discussion'))
            ->assertOk()
            ->assertSee('chat-stream', false)
            ->assertSee(route('student.comments.store', $project->id), false);
    }

    /** النقاش خرج من اللوحة وصفحة المشروع — لا مكانان للكتابة */
    public function test_the_discussion_left_the_dashboard_and_the_project_page(): void
    {
        $project = $this->project();

        $this->actingAs($this->studentOf($project), 'student')
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee('cmt-form', false);

        $this->actingAs($project->supervisor, 'supervisor')
            ->get(route('supervisor.projects.show', $project->id))
            ->assertOk()
            ->assertDontSee('cmt-form', false)
            ->assertSee(route('supervisor.discussion', $project->id), false);
    }

    /**
     * نغمات Bootstrap أُزيلت من صفحة إدارة المشروع وأجزائها.
     * الملفات لا الصفحة المعروضة: ترويسة اللايوت خارج هذا النطاق.
     */
    public function test_the_project_page_uses_no_bootstrap_tones(): void
    {
        $views = [
            'dashboard/supervisor/project.blade.php',
            'dashboard/supervisor/discussion.blade.php',
            'dashboard/student/discussion.blade.php',
            'dashboard/discussion/_thread.blade.php',
            'dashboard/project/_milestones.blade.php',
            'dashboard/project/_files.blade.php',
        ];

        foreach ($views as $view) {
            $this->assertDoesNotMatchRegularExpression(
                '/\bbg-[a-z]+-lt\b/',
                file_get_contents(resource_path('views/' . $view)),
                "نغمة Bootstrap في {$view}"
            );
        }
    }
}
