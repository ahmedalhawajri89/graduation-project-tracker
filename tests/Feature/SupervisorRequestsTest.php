<?php

namespace Tests\Feature;

use App\Models\Supervisor;
use App\Notifications\SuperVisorRequestProjectNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * طلبات الإشراف.
 *
 * كانت تُقرأ من الإشعارات غير المقروءة، وزرّا القرار مطويّان في أكورديون.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة: حالة المشروع و\u200Eread_at\u200E
 * و\u200Emax_group\u200E تُستعاد في \u200Efinally\u200E باستعلام مباشر.
 */
class SupervisorRequestsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // لا إشعار يُرسل للطلاب في أي اختبار
        Notification::fake();
    }

    /** مشرف له طلب معلّق — ومعه لقطة لكل ما قد يتغيّر */
    private function supervisorWithRequest(): array
    {
        $supervisor = Supervisor::has('pendingRequests')->first();

        if (! $supervisor) {
            $this->markTestSkipped('لا مشرف له طلب معلّق.');
        }

        $project = $supervisor->pendingRequests()->first();

        return [$supervisor, $project, [
            'max_group' => $supervisor->max_group,
            'statuses' => $supervisor->projects()->pluck('status', 'id')->all(),
            'unread' => DB::table('notifications')->where('notifiable_id', $supervisor->id)
                ->where('notifiable_type', Supervisor::class)->whereNull('read_at')->pluck('id')->all(),
        ]];
    }

    private function restore(Supervisor $supervisor, array $snap): void
    {
        DB::table('supervisors')->where('id', $supervisor->id)->update(['max_group' => $snap['max_group']]);

        foreach ($snap['statuses'] as $id => $status) {
            DB::table('projects')->where('id', $id)->update(['status' => $status]);
        }

        DB::table('notifications')->whereIn('id', $snap['unread'])->update(['read_at' => null]);
    }

    /** «هل نُفّذت الفكرة؟» — مشروع مكتمل بالعنوان نفسه يُنبَّه إليه في بطاقة الطلب */
    public function test_a_request_shows_similar_completed_projects(): void
    {
        [$supervisor, $project, $snap] = $this->supervisorWithRequest();
        $done = \App\Models\Project::where('status', 'complete')->first();

        if (! $done) {
            $this->markTestSkipped('لا مشروع مكتمل للمقارنة.');
        }

        $title = $done->title;
        DB::table('projects')->where('id', $done->id)->update(['title' => $project->title]);

        try {
            $this->actingAs($supervisor, 'supervisor')
                ->get(route('supervisor.showNotification'))
                ->assertOk()
                ->assertSee('يشبه');
        } finally {
            DB::table('projects')->where('id', $done->id)->update(['title' => $title]);
            $this->restore($supervisor, $snap);
        }
    }

    /** ما قرّره هذا الفصل بجانب الطلبات: المقبول والمرفوض */
    public function test_the_page_lists_this_semesters_decisions(): void
    {
        [$supervisor, , $snap] = $this->supervisorWithRequest();
        $decided = $supervisor->projects()->where('semester_id', \App\Models\Semester::current()->id)
            ->whereIn('status', ['accept', 'complete', 'reject'])->first();

        try {
            $response = $this->actingAs($supervisor, 'supervisor')
                ->get(route('supervisor.showNotification'))
                ->assertOk()
                ->assertSee('قراراتك هذا الفصل');

            if ($decided) {
                $response->assertSee($decided->title);
            }
        } finally {
            $this->restore($supervisor, $snap);
        }
    }

    /** الإشعار المقروء كان يُخفي الطلب وزرّيه — والمشروع ما زال معلّقاً */
    public function test_a_request_stays_visible_after_its_notification_is_read(): void
    {
        [$supervisor, $project, $snap] = $this->supervisorWithRequest();

        try {
            DB::table('notifications')->where('notifiable_id', $supervisor->id)
                ->where('type', SuperVisorRequestProjectNotify::class)->update(['read_at' => now()]);

            $this->actingAs($supervisor, 'supervisor')
                ->get(route('supervisor.showNotification'))
                ->assertOk()
                ->assertSee('id="request-' . $project->id . '"', false)
                ->assertSee(route('supervisor.replay.project', $project->id), false);
        } finally {
            $this->restore($supervisor, $snap);
        }
    }

    /** شارة الشريط تعدّ الطلبات المعلّقة وحدها، لا كل الإشعارات */
    public function test_the_sidebar_badge_counts_pending_requests_only(): void
    {
        [$supervisor] = $this->supervisorWithRequest();
        $pending = $supervisor->pendingRequests()->count();

        $html = $this->actingAs($supervisor, 'supervisor')
            ->get(route('supervisor.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/طلبات الإشراف<\/span>\s*(?:\{\{--.*?--\}\}\s*)?<span class="sidebar-count">' . $pending . '<\/span>/su',
            $html
        );
    }

    public function test_accepting_marks_only_that_requests_notification_read(): void
    {
        [$supervisor, $project, $snap] = $this->supervisorWithRequest();

        try {
            DB::table('supervisors')->where('id', $supervisor->id)->update(['max_group' => 99]);

            $this->actingAs($supervisor->fresh(), 'supervisor')
                ->post(route('supervisor.replay.project', $project->id), ['btnAccept' => 'accept'])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertSame('accept', $project->fresh()->status);

            // الإشعارات الأخرى غير المقروءة تبقى كما هي
            $stillUnread = DB::table('notifications')->whereIn('id', $snap['unread'])->whereNull('read_at')->count();
            $this->assertSame(count($snap['unread']) - 1, $stillUnread);
        } finally {
            $this->restore($supervisor, $snap);
        }
    }

    /** لا قبول بلا مقعد — كان يُقبل فوق الحدّ */
    public function test_accepting_is_refused_when_no_seat_is_left(): void
    {
        [$supervisor, $project, $snap] = $this->supervisorWithRequest();

        try {
            DB::table('supervisors')->where('id', $supervisor->id)->update(['max_group' => 0]);

            $this->actingAs($supervisor->fresh(), 'supervisor')
                ->post(route('supervisor.replay.project', $project->id), ['btnAccept' => 'accept'])
                ->assertRedirect()
                ->assertSessionHas('fail');

            $this->assertSame('request', $project->fresh()->status);
        } finally {
            $this->restore($supervisor, $snap);
        }
    }

    /** آخر مقعد يرفض الباقي — بعدّ الفصل الحالي لا كل الفصول */
    public function test_taking_the_last_seat_rejects_the_rest(): void
    {
        $supervisor = Supervisor::has('pendingRequests', '>=', 2)->first();

        if (! $supervisor) {
            $this->markTestSkipped('لا مشرف بطلبين معلّقين.');
        }

        [$supervisor, $project, $snap] = [$supervisor, $supervisor->pendingRequests()->first(), [
            'max_group' => $supervisor->max_group,
            'statuses' => $supervisor->projects()->pluck('status', 'id')->all(),
            'unread' => DB::table('notifications')->where('notifiable_id', $supervisor->id)
                ->whereNull('read_at')->pluck('id')->all(),
        ]];

        try {
            $taken = (int) $supervisor->max_group - $supervisor->seatsLeft();
            DB::table('supervisors')->where('id', $supervisor->id)->update(['max_group' => $taken + 1]);

            $this->actingAs($supervisor->fresh(), 'supervisor')
                ->post(route('supervisor.replay.project', $project->id), ['btnAccept' => 'accept'])
                ->assertSessionHas('success');

            $this->assertSame(0, $supervisor->fresh()->pendingRequests()->count());
        } finally {
            $this->restore($supervisor, $snap);
        }
    }

    public function test_a_supervisor_cannot_answer_another_supervisors_request(): void
    {
        [$supervisor, $project, $snap] = $this->supervisorWithRequest();
        $other = Supervisor::where('id', '!=', $supervisor->id)->first();

        try {
            $this->actingAs($other, 'supervisor')
                ->post(route('supervisor.replay.project', $project->id), ['btnAccept' => 'accept'])
                ->assertSessionHas('fail');

            $this->assertSame('request', $project->fresh()->status);
        } finally {
            $this->restore($supervisor, $snap);
        }
    }

    /** إشعار طلب رُدّ عليه كان يبقى غير مقروء فيعدّه الجرس بلا طلب خلفه */
    public function test_opening_the_page_clears_notifications_of_answered_requests(): void
    {
        [$supervisor, $project, $snap] = $this->supervisorWithRequest();

        try {
            // كأنّ الأدمن غيّر حالته من خارج صفحة الطلبات
            DB::table('projects')->where('id', $project->id)->update(['status' => 'accept']);

            $this->actingAs($supervisor, 'supervisor')->get(route('supervisor.showNotification'))->assertOk();

            $stale = $supervisor->unreadNotifications()
                ->where('type', SuperVisorRequestProjectNotify::class)->get()
                ->filter(fn ($n) => (int) ($n->data['project_id'] ?? 0) === (int) $project->id);

            $this->assertCount(0, $stale);
        } finally {
            $this->restore($supervisor, $snap);
        }
    }

    public function test_the_page_uses_no_bootstrap_tones(): void
    {
        foreach (['dashboard/supervisor/requestProjects.blade.php', 'dashboard/supervisor/_request-card.blade.php'] as $view) {
            $this->assertDoesNotMatchRegularExpression(
                '/\bbg-[a-z]+-lt\b/',
                file_get_contents(resource_path('views/' . $view)),
                "نغمة Bootstrap في {$view}"
            );
        }
    }
}
