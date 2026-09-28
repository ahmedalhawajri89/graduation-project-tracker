<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\GroupRole;
use App\Models\MilestoneSubmission;
use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\ProjectMilestone;
use App\Models\Student;
use App\Support\TeamHealth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * الإدارة ترى ما يجري في الفرق: التدقيق، والمراحل وتسليماتها، والأدوار،
 * والفرق المتعثّرة، والمشرف الذي لم يراجع.
 *
 * بُنيت اللوحة قبل خطط المراحل والتسليم والأدوار، فكانت لا ترى منها شيئاً.
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class AdminOversightTest extends TestCase
{
    private Admin $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $admin = Admin::first();
        $project = Project::where('status', 'accept')->whereNull('grade')->whereNotNull('supervisor_id')
            ->whereHas('group')->first();

        if (! $admin || ! $project) {
            $this->markTestSkipped('لا أدمن أو لا مشروع جارٍ بفريق.');
        }

        $this->admin = $admin;
        $this->project = $project;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function member(): Student
    {
        return Student::find($this->project->group()->value('student_id'));
    }

    private function milestone(array $attrs = []): ProjectMilestone
    {
        return $this->project->milestones()->create($attrs + [
            'title' => 'مرحلة رقابة الإدارة',
            'due_date' => today()->addWeek(),
        ]);
    }

    /** جولة تسليم بعمر محدَّد — القرار فارغ ما لم يُعطَ */
    private function submission(ProjectMilestone $milestone, int $daysAgo, array $attrs = []): MilestoneSubmission
    {
        $sub = MilestoneSubmission::create($attrs + [
            'milestone_id' => $milestone->id,
            'student_id' => $this->member()->id,
            'round' => 1,
            'note' => 'تسليم للاختبار',
        ]);

        DB::table('milestone_submissions')->where('id', $sub->id)
            ->update(['created_at' => now()->subDays($daysAgo)]);
        DB::table('project_milestones')->where('id', $milestone->id)
            ->update(['updated_at' => now()->subDays($daysAgo)]);

        return $sub->fresh();
    }

    private function hasIssue(string $issue): bool
    {
        return TeamHealth::apply(Project::whereKey($this->project->id), $issue)->exists();
    }

    /* ==================== سجلّ التدقيق ==================== */

    /** كل حدث يسجّله الكود له اسم عربي — كانت خمسة تظهر مفاتيح خاماً */
    public function test_every_recorded_audit_action_has_a_label(): void
    {
        $actions = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(app_path())) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            preg_match_all('/Audit::record\(\s*([^,]+),/', file_get_contents($file), $calls);

            foreach ($calls[1] as $arg) {
                preg_match_all("/'([a-z]+\.[a-zA-Z]+)'/", $arg, $keys);
                array_push($actions, ...$keys[1]);
            }
        }

        $this->assertNotEmpty($actions);
        $this->assertSame([], array_values(array_diff(array_unique($actions), array_keys(AuditLog::LABELS))),
            'أحداث بلا تسمية في AuditLog::LABELS.');
    }

    public function test_the_work_tab_filters_to_workflow_events(): void
    {
        $query = AuditLog::applyScope(AuditLog::query(), 'work');

        $this->assertTrue($query->get()->every(fn ($log) => str_starts_with($log->action, 'milestone.')
            || in_array($log->action, ['team.roles', 'file.note', 'stage.deleted'], true)));

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit.index', ['scope' => 'work']))
            ->assertOk()
            ->assertSee('سير العمل');
    }

    /* ==================== صفحة المجموعة ==================== */

    public function test_the_group_page_shows_a_revision_and_its_reason(): void
    {
        $milestone = $this->milestone(['status' => ProjectMilestone::REVISION]);
        $this->submission($milestone, 2, [
            'decision' => ProjectMilestone::REVISION,
            'feedback' => 'ينقص فصل الدراسات السابقة.',
            'reviewed_at' => now()->subDay(),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.groups.show', $this->project->id))
            ->assertOk()
            ->assertSee('مطلوب تعديل')
            ->assertSee('ينقص فصل الدراسات السابقة.')
            ->assertSee('طلب المشرف تعديلاً')
            // الإدارة لم تطلب شيئاً — لا تُخاطَب بلسان المشرف
            ->assertDontSee('طلبتَ تعديلاً');
    }

    public function test_the_group_page_says_how_long_a_submission_has_waited(): void
    {
        $milestone = $this->milestone(['status' => ProjectMilestone::SUBMITTED]);
        $this->submission($milestone, 5);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.groups.show', $this->project->id))
            ->assertSee('بانتظار المشرف')
            ->assertSee('5 أيام');
    }

    public function test_the_group_page_shows_roles_or_their_absence(): void
    {
        $group = $this->project->group()->first();
        GroupRole::where('group_id', $group->id)->delete();

        $page = fn () => $this->actingAs($this->admin, 'admin')->get(route('admin.groups.show', $this->project->id));

        $page()->assertSee('بلا دور');

        GroupRole::create(['group_id' => $group->id, 'role_key' => 'custom', 'label' => 'مسؤول التوثيق']);

        $page()->assertSee('مسؤول التوثيق');
    }

    /** الخصوصية كما وُعد بها: نقاش الفريق لا يصل الإدارة */
    public function test_the_group_page_never_shows_the_team_channel(): void
    {
        $this->project->teamComments()->create([
            'body' => 'رسالة داخلية لا يراها غير الفريق',
            'channel' => ProjectComment::TEAM,
            'author_type' => Student::class,
            'author_id' => $this->member()->id,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.groups.show', $this->project->id))
            ->assertOk()
            ->assertDontSee('رسالة داخلية لا يراها غير الفريق');
    }

    public function test_the_group_page_query_count_stays_bounded(): void
    {
        foreach (range(1, 4) as $i) {
            $this->submission($this->milestone(['title' => "مرحلة {$i}", 'status' => ProjectMilestone::SUBMITTED]), $i);
        }

        $this->actingAs($this->admin, 'admin');
        DB::enableQueryLog();
        $this->get(route('admin.groups.show', $this->project->id))->assertOk();
        $queries = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThan(40, $queries, "صفحة المجموعة نفّذت {$queries} استعلاماً.");
    }

    /**
     * المقيَّم لا يتناقض: كانت خطوة «التقييم» جارية و«المتبقّي ١٢ يوماً»
     * لمشروع انتهى، و«بلا دور» بلون إنذار على كل عضو في مشروع مؤرشف.
     */
    public function test_a_graded_project_reads_as_finished(): void
    {
        $project = Project::where('status', 'complete')->whereNotNull('grade')->whereHas('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع مقيَّم.');
        }

        GroupRole::whereIn('group_id', $project->group()->pluck('id'))->delete();

        $html = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.groups.show', $project->id))
            ->assertOk()
            ->assertSee('اكتمل وقُيّم')
            ->assertDontSee('المتبقّي')
            ->assertDontSee('بلا دور')
            ->assertDontSee('يحتاج انتباهاً')
            ->getContent();

        // الخطوات الأربع منجزة، ولا خطوة جارية
        preg_match('/<ol class="pg-steps">(.*?)<\/ol>/s', $html, $m);
        $this->assertSame(4, substr_count($m[1] ?? '', 'is-done'));
        $this->assertStringNotContainsString('is-current', $m[1] ?? '');
    }

    /** شريط الانتباه بتعريفات «متابعة الفرق» — ويختفي حين تزول المشكلة */
    public function test_the_attention_bar_follows_team_health(): void
    {
        // مراحل البيانات التجريبية الفائتة تُؤجَّل — لتبقى هذه وحدها سبب المؤشّر
        $this->project->milestones()->whereIn('status', [ProjectMilestone::OPEN, ProjectMilestone::REVISION])
            ->update(['due_date' => today()->addMonth()]);
        $milestone = $this->milestone(['title' => 'مرحلة فائتة', 'due_date' => today()->subDays(2)]);
        $page = fn () => $this->actingAs($this->admin, 'admin')->get(route('admin.groups.show', $this->project->id));

        $page()->assertSee('يحتاج انتباهاً')->assertSee('مرحلة فات موعدها');

        $milestone->update(['status' => ProjectMilestone::SUBMITTED]);
        $this->submission($milestone, 1);

        $page()->assertDontSee('مرحلة فات موعدها');
    }

    public function test_a_healthy_project_has_no_attention_bar(): void
    {
        $this->project->milestones()->whereIn('status', [ProjectMilestone::OPEN, ProjectMilestone::REVISION])
            ->update(['due_date' => today()->addMonth()]);
        $this->project->milestones()->where('status', ProjectMilestone::SUBMITTED)->delete();
        foreach ($this->project->group()->pluck('id') as $groupId) {
            GroupRole::create(['group_id' => $groupId, 'role_key' => 'custom', 'label' => 'منسّق']);
        }
        $this->submission($this->milestone(['title' => 'نشاط حديث', 'status' => ProjectMilestone::APPROVED, 'is_done' => true]), 1);

        $this->assertSame([], TeamHealth::issuesFor($this->project->fresh()));

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.groups.show', $this->project->id))
            ->assertDontSee('يحتاج انتباهاً');
    }

    /** قبل القبول لا تبويبات ولا مراحل — لوح يقول السبب */
    public function test_a_pending_or_rejected_project_explains_why_there_is_no_work(): void
    {
        foreach (['request' => 'بانتظار رد المشرف', 'reject' => 'المشروع مرفوض'] as $status => $text) {
            $project = Project::where('status', $status)->first();

            if (! $project) {
                continue;
            }

            $this->actingAs($this->admin, 'admin')
                ->get(route('admin.groups.show', $project->id))
                ->assertOk()
                ->assertSee($text)
                ->assertDontSee('role="tablist"', false);
        }

        $this->assertTrue(true);
    }

    /** الفصل بصيغته المرتّبة لا اسمه الخام بشرطة مائلة */
    public function test_the_semester_is_shown_tidy(): void
    {
        $parts = $this->project->semester->parts();

        $html = $this->actingAs($this->admin, 'admin')->get(route('admin.groups.show', $this->project->id))->getContent();

        // سطر هوية المشروع وحده: الشريط الجانبي يعرض الفصل الحالي بصيغته
        $this->assertSame(1, preg_match('/<div class="pg-meta[^"]*">(.*?)<\/div>/s', $html, $m));
        $this->assertStringContainsString(e($parts['term']), $m[1]);

        if ($parts['year']) {
            $this->assertStringContainsString($parts['year'], $m[1]);
            $this->assertStringNotContainsString(e($this->project->semester->name), $m[1]);
        }
    }

    /* ==================== متابعة الفرق ==================== */

    public function test_a_stage_past_its_date_marks_the_team_late(): void
    {
        $this->project->milestones()->whereIn('status', [ProjectMilestone::OPEN, ProjectMilestone::REVISION])
            ->whereDate('due_date', '<', today())->update(['due_date' => today()->addMonth()]);
        $this->assertFalse($this->hasIssue('late'));

        $this->milestone(['due_date' => today()->subDay()]);
        $this->assertTrue($this->hasIssue('late'));
    }

    /** المسلَّمة بانتظار المشرف ليست تأخّراً على الفريق */
    public function test_a_submitted_stage_is_not_late(): void
    {
        $this->project->milestones()->whereDate('due_date', '<', today())->delete();
        $this->submission($this->milestone(['due_date' => today()->subDays(3), 'status' => ProjectMilestone::SUBMITTED]), 4);

        $this->assertFalse($this->hasIssue('late'));
    }

    public function test_a_submission_waiting_over_three_days_needs_review(): void
    {
        $this->project->milestones()->where('status', ProjectMilestone::SUBMITTED)->delete();

        $this->submission($this->milestone(['status' => ProjectMilestone::SUBMITTED]), 1);
        $this->assertFalse($this->hasIssue('review'), 'تسليم عمره يوم عُدّ متأخّر المراجعة.');

        $this->submission($this->milestone(['title' => 'أقدم', 'status' => ProjectMilestone::SUBMITTED]), 5);
        $this->assertTrue($this->hasIssue('review'));
    }

    public function test_a_team_without_roles_is_flagged(): void
    {
        GroupRole::whereIn('group_id', $this->project->group()->pluck('id'))->delete();
        $this->assertTrue($this->hasIssue('roles'));

        GroupRole::create(['group_id' => $this->project->group()->value('id'), 'role_key' => 'custom', 'label' => 'منسّق']);
        $this->assertFalse($this->hasIssue('roles'));
    }

    public function test_a_team_silent_for_two_weeks_is_idle(): void
    {
        DB::table('projects')->where('id', $this->project->id)->update(['created_at' => now()->subMonth()]);
        $milestone = $this->milestone();
        MilestoneSubmission::whereIn('milestone_id', $this->project->milestones()->pluck('id'))
            ->where('created_at', '>=', now()->subDays(TeamHealth::IDLE_DAYS))->delete();

        $this->assertTrue($this->hasIssue('idle'));

        $this->submission($milestone, 2);
        $this->assertFalse($this->hasIssue('idle'), 'فريق سلّم قبل يومين عُدّ متوقّفاً.');
    }

    /** المقيَّم مؤرشف — لا يُعدّ متعثّراً مهما بقي فيه */
    public function test_a_graded_project_is_never_flagged(): void
    {
        $this->milestone(['due_date' => today()->subDay()]);
        $this->project->update(['grade' => 80]);

        foreach (array_keys(TeamHealth::issues()) as $issue) {
            $this->assertFalse($this->hasIssue($issue), "المشروع المقيَّم عُدّ «{$issue}».");
        }
    }

    public function test_the_dashboard_counts_link_to_the_same_teams(): void
    {
        $this->milestone(['due_date' => today()->subDay()]);
        $semesterId = $this->project->semester_id;
        $count = TeamHealth::counts($semesterId)['late'];

        $this->assertGreaterThan(0, $count);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.groups.index', ['issue' => 'late', 'semester' => $semesterId]))
            ->assertOk()
            ->assertSee('مرحلة فات موعدها')
            ->assertSee($count . ' مجموعة ضمن التصفية الحالية');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('متابعة الفرق')
            ->assertSee(route('admin.groups.index', ['issue' => 'late']), false);
    }

    public function test_the_filtered_table_returns_only_matching_teams(): void
    {
        $this->milestone(['due_date' => today()->subDay()]);

        $json = $this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.groups.getData', ['issue' => 'late', 'semester' => $this->project->semester_id]))
            ->assertOk()
            ->json('data');

        $ids = TeamHealth::apply(Project::where('semester_id', $this->project->semester_id), 'late')->pluck('id');

        $this->assertCount($ids->count(), $json);
        $this->assertStringContainsString(e($this->project->title), collect($json)->pluck('identity')->implode(''));
    }

    /**
     * رابط بيانات الجدول يحمل كل الفلاتر: كان يُطبع مُهرَّباً فيصير الفاصل
     * كياناً، ويصل كل فلتر بعد الأول باسم خاطئ — فتعرض القائمة كل المشاريع.
     */
    public function test_the_table_data_url_keeps_every_filter(): void
    {
        $html = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.groups.index', ['issue' => 'late', 'status' => 'accept']))
            ->getContent();

        // سطر الجدول وحده: روابط HTML في الصفحة تُهرَّب بحقّ
        $this->assertSame(1, preg_match('/ajax: ("[^"]*")/', $html, $m));
        $url = json_decode($m[1]);

        $this->assertStringNotContainsString('&amp;', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertSame('late', $query['issue'] ?? null);
        $this->assertSame('accept', $query['status'] ?? null);
    }

    /* ==================== جدول المشرفين ==================== */

    public function test_the_supervisors_table_shows_pending_reviews(): void
    {
        $this->project->update(['semester_id' => \App\Models\Semester::current()->id]);
        $this->submission($this->milestone(['status' => ProjectMilestone::SUBMITTED]), 6);

        $expected = $this->project->supervisor->pendingReviews()
            ->where('projects.semester_id', $this->project->semester_id)->count();

        $row = collect($this->actingAs($this->admin, 'admin')
            ->getJson(route('admin.supervisors.getData', ['length' => 1000]))
            ->assertOk()
            ->json('data'))
            ->firstWhere('id', $this->project->supervisor_id);

        $this->assertNotNull($row);
        $this->assertStringContainsString('<b>' . $expected . '</b>', $row['pending_reviews_count']);
        $this->assertStringContainsString('is-late', $row['pending_reviews_count'], 'تسليم عمره ستة أيام لم يُلوَّن.');
    }
}
