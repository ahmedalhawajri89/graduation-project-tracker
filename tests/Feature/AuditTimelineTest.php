<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Project;
use App\Support\AuditPresenter;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * سجلّ التدقيق خطّاً زمنياً: لكل حدث فئته وأيقونته، وما تغيّر فيه شارات،
 * والمشروع رابط، والأحداث مجمّعة باليوم، والمدد السريعة ترشّح الصفحة والتصدير.
 * داخل معاملة تُرجَع في \u200EtearDown\u200E.
 */
class AuditTimelineTest extends TestCase
{
    private Admin $admin;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        DB::beginTransaction();

        $admin = Admin::first();
        $project = Project::first();
        if (! $admin || ! $project) {
            $this->markTestSkipped('لا أدمن أو مشروع.');
        }
        $this->admin = $admin;
        $this->project = $project;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function log(string $action, array $changes = [], ?\DateTimeInterface $at = null, ?Project $subject = null): AuditLog
    {
        $subject ??= $this->project;
        $log = AuditLog::create([
            'actor_type' => Admin::class, 'actor_id' => $this->admin->id,
            'actor_name' => 'فاعل الاختبار', 'actor_role' => 'admin',
            'action' => $action,
            'subject_type' => Project::class, 'subject_id' => $subject->id, 'subject_label' => $subject->title,
            'changes' => $changes,
        ]);

        if ($at) {
            DB::table('audit_logs')->where('id', $log->id)->update(['created_at' => $at]);
        }

        return $log->fresh();
    }

    /** كل حدث معروف له أيقونته — كان القلم لأغلبها */
    public function test_every_labelled_action_has_its_own_icon_and_category(): void
    {
        foreach (array_keys(AuditLog::LABELS) as $action) {
            $this->assertTrue(AuditPresenter::hasIcon($action), "لا أيقونة لـ {$action}.");
            $this->assertArrayHasKey(AuditPresenter::category($action), AuditPresenter::CATEGORIES);
        }

        $this->assertSame('danger', AuditPresenter::category('grade.unlocked'));
        $this->assertSame('work', AuditPresenter::category('milestone.revision'));
        $this->assertSame('grade', AuditPresenter::category('grade.locked'));
    }

    public function test_a_status_change_shows_both_states_in_arabic(): void
    {
        $chips = AuditPresenter::chips($this->log('project.statusChanged', ['status' => ['from' => 'accept', 'to' => 'complete']]));

        $this->assertSame(['label' => 'الحالة', 'from' => __('site.accept'), 'to' => __('site.complete')], $chips[0]);
    }

    public function test_a_revision_shows_its_stage_and_round(): void
    {
        $chips = collect(AuditPresenter::chips($this->log('milestone.revision', ['milestone' => ['to' => 'الفصل الثالث'], 'round' => ['to' => 2]])));

        $this->assertTrue($chips->contains(fn ($c) => $c['to'] === 'الفصل الثالث'));
        $this->assertTrue($chips->contains(fn ($c) => $c['to'] === 'الجولة 2'));
    }

    public function test_a_grade_change_shows_from_and_to(): void
    {
        $chip = AuditPresenter::chips($this->log('grade.changed', ['grade' => ['from' => 85, 'to' => 90.5]]))[0];

        $this->assertSame('85', $chip['from']);
        $this->assertSame('90.5 / 100', $chip['to']);
    }

    /** المشروع الموجود رابط، والمحذوف اسم بلا رابط */
    public function test_the_subject_links_only_while_the_project_exists(): void
    {
        $this->log('project.restored');

        $html = $this->actingAs($this->admin, 'admin')->get(route('admin.audit.index'))->assertOk()->getContent();
        $this->assertStringContainsString('href="' . route('admin.groups.show', $this->project->id) . '" class="at-subject"', $html);

        $this->project->delete(); // حذف ناعم
        $html = $this->get(route('admin.audit.index'))->getContent();
        $this->assertStringNotContainsString('href="' . route('admin.groups.show', $this->project->id) . '" class="at-subject"', $html);
        $this->assertStringContainsString(e($this->project->title), $html);
    }

    public function test_events_are_grouped_by_day(): void
    {
        // تصفية بحدث واحد: أحداث البيانات التجريبية الحديثة تملأ الصفحة الأولى
        AuditLog::where('action', 'project.restored')->delete();
        $this->log('project.restored', [], now());
        $this->log('project.restored', [], now()->subDay());

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit.index', ['action' => 'project.restored']))
            ->assertSeeInOrder(['at-day-head', 'اليوم', 'at-day-head', 'أمس'], false);
    }

    /** المدّة السريعة ترشّح الصفحة والتصدير بالشرط نفسه */
    public function test_a_quick_period_filters_the_page_and_the_export(): void
    {
        $old = $this->log('grade.set', ['grade' => ['to' => 77]], now()->subDays(10));
        $new = $this->log('grade.set', ['grade' => ['to' => 88]], now()->subDays(2));

        $ids = AuditLog::applyPeriod(AuditLog::query(), '7')->pluck('id');
        $this->assertContains($new->id, $ids);
        $this->assertNotContains($old->id, $ids);

        $ids = AuditLog::applyPeriod(AuditLog::query(), '30')->pluck('id');
        $this->assertContains($old->id, $ids);

        $export = new \App\Exports\AuditLogExport(null, null, null, null, null, '7');
        $exported = $export->collection()->pluck('id');
        $this->assertContains($new->id, $exported);
        $this->assertNotContains($old->id, $exported);

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit.index', ['period' => '7']))
            ->assertOk()
            ->assertSee('value="7" checked', false);
    }

    /** تاريخا المدى المخصّص لا يُطبَّقان مع مدّة سريعة */
    public function test_custom_dates_apply_only_to_the_custom_period(): void
    {
        AuditLog::where('action', 'grade.set')->delete();
        $this->log('grade.set', ['grade' => ['to' => 70]], now()->subDays(3));

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit.index', ['action' => 'grade.set', 'from' => now()->toDateString()]))
            ->assertDontSee('70 / 100');

        $this->get(route('admin.audit.index', ['action' => 'grade.set', 'period' => '7', 'from' => now()->toDateString()]))
            ->assertSee('70 / 100');
    }

    public function test_the_summary_counts_today(): void
    {
        $before = AuditLog::whereDate('created_at', today())->count();
        $this->log('project.restored', [], now());

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.audit.index'))
            ->assertSeeInOrder(['at-stat-n', (string) ($before + 1), 'حدثاً اليوم'], false);
    }
}
