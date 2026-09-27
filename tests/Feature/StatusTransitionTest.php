<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * انتقالات الحالة عند المشرف.
 *
 * كان الإكمال يقبل أي حالة، وأدوات المشرف تعمل على مشروع معلّق أو مرفوض.
 *
 * ‎phpunit.xml‎ لا يضبط قاعدة اختبار منفصلة: الحالة تُستعاد باستعلام مباشر
 * في ‎finally‎، وما يُنشأ من مراحل وسجلّات تدقيق يُحذف بمعرّفه.
 */
class StatusTransitionTest extends TestCase
{
    private int $auditMark = 0;

    private int $milestoneMark = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->auditMark = (int) (AuditLog::max('id') ?? 0);
        $this->milestoneMark = (int) (ProjectMilestone::max('id') ?? 0);
    }

    protected function tearDown(): void
    {
        AuditLog::where('id', '>', $this->auditMark)->delete();
        ProjectMilestone::where('id', '>', $this->milestoneMark)->delete();

        parent::tearDown();
    }

    private function projectIn(string $status): Project
    {
        $project = Project::where('status', $status)->whereNotNull('supervisor_id')->whereNull('grade')
            ->with('supervisor')->first();

        if (! $project) {
            $this->markTestSkipped("لا مشروع بحالة {$status}.");
        }

        return $project;
    }

    /** يشغّل ‎$check‎ ثم يعيد الحالة كما كانت — ولو فشل */
    private function restoring(Project $project, callable $check): void
    {
        $original = $project->status;

        try {
            $check();
        } finally {
            DB::table('projects')->where('id', $project->id)->update(['status' => $original]);
        }
    }

    // ═══ الإكمال ═══

    public function test_a_pending_project_cannot_be_completed(): void
    {
        $project = $this->projectIn('request');

        $this->restoring($project, function () use ($project) {
            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.project.complete', $project->id))
                ->assertSessionHas('fail');

            $this->assertSame('request', $project->fresh()->status);
        });
    }

    /** كان يُحيي المرفوض — وطلابه ربما في مشروع جديد فيصيرون في مشروعين */
    public function test_a_rejected_project_cannot_be_revived_by_completing_it(): void
    {
        $project = $this->projectIn('reject');

        $this->restoring($project, function () use ($project) {
            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.project.complete', $project->id))
                ->assertSessionHas('fail');

            $this->assertSame('reject', $project->fresh()->status);
        });
    }

    public function test_an_accepted_project_completes_and_is_audited(): void
    {
        $project = $this->projectIn('accept');

        $this->restoring($project, function () use ($project) {
            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.project.complete', $project->id))
                ->assertSessionHas('success');

            $this->assertSame('complete', $project->fresh()->status);

            $log = AuditLog::where('id', '>', $this->auditMark)->where('action', 'project.statusChanged')->first();
            $this->assertNotNull($log, 'الإكمال لم يُسجَّل.');
            $this->assertSame('complete', $log->changes['status']['to'] ?? null);
        });
    }

    // ═══ أدوات المشرف على مشروع غير مقبول ═══

    public function test_milestones_cannot_be_added_to_a_pending_or_rejected_project(): void
    {
        foreach (['request', 'reject'] as $status) {
            $project = $this->projectIn($status);

            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.milestones.store', $project->id), ['title' => 'مرحلة'])
                ->assertSessionHas('fail');

            $this->assertSame(0, ProjectMilestone::where('id', '>', $this->milestoneMark)->count(), "أُضيفت مرحلة لمشروع {$status}.");
        }
    }

    public function test_the_deadline_cannot_be_set_on_a_pending_project(): void
    {
        $project = $this->projectIn('request');
        $original = $project->date_line;

        try {
            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.deadline.update', $project->id), ['date_line' => now()->addMonth()->toDateString()])
                ->assertSessionHas('fail');

            $this->assertEquals($original, $project->fresh()->date_line);
        } finally {
            DB::table('projects')->where('id', $project->id)->update(['date_line' => $original]);
        }
    }

    /** وعلى المقبول تعمل كما كانت — القيد على الحالة لا على الأداة */
    public function test_milestones_still_work_on_an_accepted_project(): void
    {
        $project = $this->projectIn('accept');

        $this->actingAs($project->supervisor, 'supervisor')
            ->post(route('supervisor.milestones.store', $project->id), ['title' => 'مرحلة اختبار'])
            ->assertSessionHas('success');

        $this->assertSame(1, ProjectMilestone::where('id', '>', $this->milestoneMark)->count());
    }

    // ═══ القبول والرفض ═══

    /** القرار كان لا يترك أثراً في سجلّ التدقيق */
    public function test_rejecting_a_request_is_audited(): void
    {
        $project = $this->projectIn('request');
        $unread = DB::table('notifications')->whereNull('read_at')->pluck('id')->all();

        try {
            $this->actingAs($project->supervisor, 'supervisor')
                ->post(route('supervisor.replay.project', $project->id), ['btnReject' => 'reject', 'reason' => 'سبب'])
                ->assertSessionHas('success');

            $this->assertSame('reject', $project->fresh()->status);
            $this->assertNotNull(
                AuditLog::where('id', '>', $this->auditMark)->where('action', 'project.statusChanged')->first(),
                'الرفض لم يُسجَّل.'
            );
        } finally {
            DB::table('projects')->where('id', $project->id)->update(['status' => 'request']);
            DB::table('notifications')->whereIn('id', $unread)->update(['read_at' => null]);
        }
    }

    /** طلب رُدّ عليه لا يُردّ عليه ثانيةً */
    public function test_a_request_cannot_be_answered_twice(): void
    {
        $project = $this->projectIn('request');
        $unread = DB::table('notifications')->whereNull('read_at')->pluck('id')->all();

        try {
            $as = $this->actingAs($project->supervisor, 'supervisor');
            $as->post(route('supervisor.replay.project', $project->id), ['btnReject' => 'reject'])->assertSessionHas('success');
            $as->post(route('supervisor.replay.project', $project->id), ['btnAccept' => 'accept'])->assertSessionHas('fail');

            $this->assertSame('reject', $project->fresh()->status);
        } finally {
            DB::table('projects')->where('id', $project->id)->update(['status' => 'request']);
            DB::table('notifications')->whereIn('id', $unread)->update(['read_at' => null]);
        }
    }
}
