<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Defense;
use App\Models\DefenseMember;
use App\Models\DefenseRoom;
use App\Models\Project;
use App\Models\Supervisor;
use App\Notifications\ProjectActivityNotify;
use App\Support\DefenseScheduler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * مخطِّط المناقشات: اقتراح خانة وقاعة وممتحن لكل مشروع منتظر ضمن أوقات
 * الدوام وبلا تعارض، جدولة بنقرة بالاقتراح، و«جدولة الكل» معاينةً ثم اعتماداً.
 */
class DefensePlannerTest extends TestCase
{
    private Admin $admin;
    /** @var \Illuminate\Support\Collection<int, Project> */
    private $projects;
    private DefenseRoom $room;

    protected function setUp(): void
    {
        parent::setUp();

        if (! DefenseScheduler::enabled()) {
            $this->markTestSkipped('جداول المناقشات غير موجودة في قاعدة الاختبار.');
        }

        Notification::fake();
        DB::beginTransaction();

        // ثلاثة مشاريع بمشرفين مختلفين تنتظر الجدولة، وقاعة واحدة فقط
        $projects = Project::whereHas('group')->whereNotNull('supervisor_id')->with('project_type')
            ->get()->unique('supervisor_id')->take(3)->values();
        $admin = Admin::first();
        if ($projects->count() < 3 || ! $admin) {
            $this->markTestSkipped('لا ثلاثة مشاريع بمشرفين مختلفين.');
        }

        Defense::query()->delete();
        DefenseRoom::query()->update(['is_active' => false]);
        foreach ($projects as $p) {
            $p->update(['status' => 'complete', 'grade' => null, 'grade_locked_at' => null]);
        }

        $this->admin = $admin;
        $this->projects = $projects->map->fresh('project_type');
        $this->room = DefenseRoom::create(['name' => 'قاعة المخطِّط ' . uniqid(), 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    public function test_suggestion_is_inside_working_hours_and_passes_the_rules(): void
    {
        $project = $this->projects->first();
        $s = DefenseScheduler::suggestSlot($project);

        $this->assertNotNull($s);
        $this->assertContains($s['starts_at']->dayOfWeek, config('defenses.days'));
        $this->assertGreaterThanOrEqual(config('defenses.start'), $s['starts_at']->format('H:i'));
        $this->assertLessThanOrEqual(config('defenses.end'), $s['starts_at']->copy()->addMinutes($s['duration_minutes'])->format('H:i'));
        $this->assertTrue($s['starts_at']->gte(today()->addDays(config('defenses.lead_days'))));
        $this->assertNotSame($project->supervisor_id, $s['examiner_id']);

        // الاقتراح نفسه يمرّ بقواعد الحفظ
        $this->assertSame([], DefenseScheduler::conflicts($project, $s + ['mode' => 'in_person']));
    }

    public function test_suggestion_avoids_a_busy_room_and_a_busy_supervisor(): void
    {
        [$a, $b] = [$this->projects[0], $this->projects[1]];
        $first = DefenseScheduler::suggestSlot($a);

        // مناقشة قائمة في الخانة الأولى بالقاعة الوحيدة
        $d = Defense::create(['project_id' => $a->id, 'starts_at' => $first['starts_at'], 'duration_minutes' => 45,
            'mode' => 'in_person', 'room_id' => $this->room->id, 'status' => 'scheduled']);
        DefenseMember::create(['defense_id' => $d->id, 'supervisor_id' => $a->supervisor_id, 'role' => 'supervisor']);
        DefenseMember::create(['defense_id' => $d->id, 'supervisor_id' => $first['examiner_id'], 'role' => 'examiner']);

        $next = DefenseScheduler::suggestSlot($b);
        $this->assertTrue($next['starts_at']->gte($first['starts_at']->copy()->addMinutes(45)), 'القاعة مشغولة في الخانة الأولى');
        $this->assertSame([], DefenseScheduler::conflicts($b, $next + ['mode' => 'in_person']));
    }

    public function test_plan_for_all_has_no_clashes_between_its_own_suggestions(): void
    {
        DefenseRoom::create(['name' => 'قاعة ثانية ' . uniqid(), 'is_active' => true]);
        $plan = collect(DefenseScheduler::planAll($this->projects))->filter();

        $this->assertCount(3, $plan);

        // لا قاعة ولا شخص في خانتين متداخلتين
        $seen = [];
        foreach ($plan as $pid => $s) {
            $project = $this->projects->firstWhere('id', $pid);
            foreach (['room:' . $s['room_id'], 'person:' . $project->supervisor_id, 'person:' . $s['examiner_id']] as $key) {
                $slot = $key . '@' . $s['starts_at']->format('Y-m-d H:i');
                $this->assertArrayNotHasKey($slot, $seen, $slot);
                $seen[$slot] = true;
            }
        }

        $this->assertSame(0, Defense::count(), 'الاقتراح لا يحفظ شيئاً');
    }

    public function test_no_rooms_means_no_suggestion(): void
    {
        $this->room->update(['is_active' => false]);

        $this->assertNull(DefenseScheduler::suggestSlot($this->projects->first()));
    }

    public function test_one_click_schedules_with_the_suggestion(): void
    {
        $project = $this->projects->first();
        $s = DefenseScheduler::suggestSlot($project);

        $this->actingAs($this->admin, 'admin')->post(route('admin.defenses.store'), [
            'project_id' => $project->id,
            'date' => $s['starts_at']->format('Y-m-d'),
            'time' => $s['starts_at']->format('H:i'),
            'duration_minutes' => $s['duration_minutes'],
            'mode' => 'in_person',
            'room_id' => $s['room_id'],
            'examiner_id' => $s['examiner_id'],
        ])->assertSessionHas('success');

        $this->assertSame('scheduled', Defense::where('project_id', $project->id)->value('status'));
    }

    public function test_approving_the_plan_schedules_everything_and_notifies(): void
    {
        DefenseRoom::create(['name' => 'قاعة ثانية ' . uniqid(), 'is_active' => true]);
        $plan = collect(DefenseScheduler::planAll($this->projects))->filter();

        $items = $plan->map(fn ($s) => [
            'date' => $s['starts_at']->format('Y-m-d'), 'time' => $s['starts_at']->format('H:i'),
            'room_id' => $s['room_id'], 'examiner_id' => $s['examiner_id'],
        ])->all();

        $this->actingAs($this->admin, 'admin')->post(route('admin.defenses.plan.store'), ['items' => $items])
            ->assertSessionHas('success');

        $this->assertSame(3, Defense::active()->count());
        foreach ($plan as $s) {
            Notification::assertSentTo(Supervisor::find($s['examiner_id']), ProjectActivityNotify::class);
        }
        $this->assertSame(0, DefenseScheduler::awaiting()->whereIn('id', $this->projects->pluck('id'))->count());
    }

    public function test_a_clashing_item_in_the_plan_is_skipped_not_forced(): void
    {
        [$a, $b] = [$this->projects[0], $this->projects[1]];
        $s = DefenseScheduler::suggestSlot($a);
        $same = ['date' => $s['starts_at']->format('Y-m-d'), 'time' => $s['starts_at']->format('H:i'), 'room_id' => $s['room_id']];
        $otherExaminer = Supervisor::whereNotIn('id', [$a->supervisor_id, $b->supervisor_id, $s['examiner_id']])->firstOrFail();

        // البندان في القاعة نفسها والوقت نفسه: الأول يُجدول والثاني يُتخطّى
        $this->actingAs($this->admin, 'admin')->post(route('admin.defenses.plan.store'), ['items' => [
            $a->id => $same + ['examiner_id' => $s['examiner_id']],
            $b->id => $same + ['examiner_id' => $otherExaminer->id],
        ]])->assertSessionHas('success');

        $this->assertTrue(Defense::where('project_id', $a->id)->exists());
        $this->assertFalse(Defense::where('project_id', $b->id)->exists());
    }

    public function test_page_shows_the_pipeline_suggestions_and_the_week_planner(): void
    {
        $res = $this->actingAs($this->admin, 'admin')->get(route('admin.defenses.index'))->assertOk();

        $this->assertGreaterThanOrEqual(3, $res->viewData('pipeline')['awaiting']);
        $res->assertSee('جدولة بهذا الاقتراح')
            ->assertSee('df-cal', false)
            ->assertSee('is-ghost', false)
            ->assertSee('جدولة الكل تلقائياً');

        // الأسبوع المعروض هو أسبوع أول اقتراح
        $first = collect($res->viewData('plan'))->filter()->min(fn ($s) => $s['starts_at']);
        $this->assertTrue($res->viewData('week')['start']->isSameDay($first->copy()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY)));
    }
}
