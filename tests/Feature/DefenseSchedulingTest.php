<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Defense;
use App\Models\DefenseRoom;
use App\Models\Project;
use App\Models\Supervisor;
use App\Notifications\ProjectActivityNotify;
use App\Support\DefenseScheduler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * جدولة المناقشات: لجنة (المشرف + ممتحن) وموعد وقاعة أو رابط لكل مشروع
 * مكتمل، بلا تعارض في القاعة ولا في الأعضاء، مع إشعار الفريق واللجنة
 * وتسجيل الحدث وتذكير اليوم السابق. داخل معاملة تُرجَع في tearDown.
 */
class DefenseSchedulingTest extends TestCase
{
    private Admin $admin;
    private Project $project;
    private Supervisor $examiner;
    private DefenseRoom $room;

    protected function setUp(): void
    {
        parent::setUp();

        if (! DefenseScheduler::enabled()) {
            $this->markTestSkipped('جداول المناقشات غير موجودة في قاعدة الاختبار — شغّل migrate ثم test:prepare.');
        }

        Notification::fake();
        DB::beginTransaction();

        $admin = Admin::first();
        $project = Project::whereIn('status', ['accept', 'complete'])->whereNull('grade_locked_at')
            ->whereHas('group')->whereNotNull('supervisor_id')->with('project_type')->first();
        if (! $admin || ! $project) {
            $this->markTestSkipped('لا أدمن أو مشروع بفريق.');
        }

        // المشروع مكتمل ولا مناقشة له — حالة «بانتظار الجدولة»
        $project->update(['status' => 'complete', 'grade' => null]);
        Defense::where('project_id', $project->id)->delete();

        $this->admin = $admin;
        $this->project = $project->fresh('project_type');
        $this->examiner = Supervisor::whereKeyNot($project->supervisor_id)->firstOrFail();
        $this->room = DefenseRoom::create(['name' => 'قاعة اختبار ' . uniqid(), 'is_active' => true]);
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function payload(array $over = []): array
    {
        return $over + [
            'project_id' => $this->project->id,
            'date' => now()->addDays(3)->format('Y-m-d'),
            'time' => '10:00',
            'duration_minutes' => 45,
            'mode' => 'in_person',
            'room_id' => $this->room->id,
            'examiner_id' => $this->examiner->id,
        ];
    }

    private function schedule(array $over = [])
    {
        return $this->actingAs($this->admin, 'admin')->post(route('admin.defenses.store'), $this->payload($over));
    }

    /** مشروع آخر مكتمل بمشرف غير الممتحن — لاختبار التعارض */
    private function otherProject(): Project
    {
        $other = Project::whereKeyNot($this->project->id)->whereHas('group')
            ->whereNotIn('supervisor_id', [$this->examiner->id])->whereNotNull('supervisor_id')->firstOrFail();
        $other->update(['status' => 'complete', 'grade' => null, 'grade_locked_at' => null]);
        Defense::where('project_id', $other->id)->delete();

        return $other;
    }

    public function test_scheduling_creates_the_defense_with_its_committee_and_notifies_everyone(): void
    {
        $this->schedule()->assertRedirect()->assertSessionHas('success');

        $defense = Defense::where('project_id', $this->project->id)->with('members')->firstOrFail();
        $this->assertSame('scheduled', $defense->status);
        $this->assertSame('10:00', $defense->starts_at->format('H:i'));
        $this->assertEqualsCanonicalizing(
            [$this->project->supervisor_id, $this->examiner->id],
            $defense->members->pluck('supervisor_id')->all()
        );
        $this->assertSame('examiner', $defense->members->firstWhere('supervisor_id', $this->examiner->id)->role);

        foreach ($this->project->students() as $student) {
            Notification::assertSentTo($student, ProjectActivityNotify::class, fn ($n) => $n->toArray($student)['kind'] === 'defense');
        }
        Notification::assertSentTo($this->examiner, ProjectActivityNotify::class);
        Notification::assertSentTo(Supervisor::find($this->project->supervisor_id), ProjectActivityNotify::class);

        $this->assertTrue(AuditLog::where('action', 'defense.scheduled')->where('subject_id', $this->project->id)->exists());
    }

    public function test_only_completed_projects_can_be_scheduled(): void
    {
        $this->project->update(['status' => 'accept']);

        $this->schedule()->assertSessionHasErrors('date');
        $this->assertFalse(Defense::where('project_id', $this->project->id)->exists());
    }

    public function test_the_project_supervisor_cannot_be_the_examiner(): void
    {
        $this->schedule(['examiner_id' => $this->project->supervisor_id])->assertSessionHasErrors('examiner_id');
    }

    public function test_a_room_cannot_host_two_overlapping_defenses(): void
    {
        $this->schedule()->assertSessionHas('success');
        $other = $this->otherProject();
        $examiner2 = Supervisor::whereNotIn('id', [$this->examiner->id, $other->supervisor_id, $this->project->supervisor_id])->firstOrFail();

        // 10:30 داخل 10:00–10:45 في القاعة نفسها
        $this->schedule(['project_id' => $other->id, 'time' => '10:30', 'examiner_id' => $examiner2->id])
            ->assertSessionHasErrors('room_id');

        // بعد انتهائها مباشرة مسموح
        $this->schedule(['project_id' => $other->id, 'time' => '10:45', 'examiner_id' => $examiner2->id])
            ->assertSessionHas('success');
    }

    public function test_an_examiner_cannot_sit_on_two_committees_at_once(): void
    {
        $this->schedule()->assertSessionHas('success');
        $other = $this->otherProject();
        $room2 = DefenseRoom::create(['name' => 'قاعة ثانية ' . uniqid(), 'is_active' => true]);

        $this->schedule(['project_id' => $other->id, 'room_id' => $room2->id, 'time' => '10:15'])
            ->assertSessionHasErrors('examiner_id');
    }

    /** ممتحنان آخران غير المشرف والممتحن الأول */
    private function moreExaminers(int $n = 1): array
    {
        $ids = Supervisor::whereNotIn('id', [$this->project->supervisor_id, $this->examiner->id])->limit($n)->pluck('id')->all();
        if (count($ids) < $n) {
            $this->markTestSkipped('لا مشرفين كفاية للجنة موسّعة.');
        }

        return $ids;
    }

    public function test_a_committee_can_have_several_examiners(): void
    {
        [$second] = $this->moreExaminers();

        $this->schedule(['examiner_id' => null, 'examiner_ids' => [$this->examiner->id, $second]])->assertSessionHas('success');

        $defense = Defense::where('project_id', $this->project->id)->with('members')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$this->project->supervisor_id, $this->examiner->id, $second],
            $defense->members->pluck('supervisor_id')->all()
        );
        $this->assertSame(2, $defense->members->where('role', 'examiner')->count());
        Notification::assertSentTo(Supervisor::find($second), ProjectActivityNotify::class);
    }

    public function test_examiners_must_be_distinct_within_the_limit_and_not_the_supervisor(): void
    {
        $this->schedule(['examiner_ids' => [$this->examiner->id, $this->examiner->id]])->assertSessionHasErrors('examiner_ids.0');
        $this->schedule(['examiner_ids' => [$this->examiner->id, $this->project->supervisor_id]])->assertSessionHasErrors('examiner_id');
        $this->schedule(['examiner_ids' => []])->assertSessionHasErrors('examiner_ids');

        config(['defenses.max_examiners' => 2]);
        $this->schedule(['examiner_ids' => [$this->examiner->id, ...$this->moreExaminers(2)]])->assertSessionHasErrors('examiner_ids');

        $this->assertFalse(Defense::where('project_id', $this->project->id)->exists());
    }

    public function test_a_busy_second_examiner_is_named_in_the_conflict(): void
    {
        [$second] = $this->moreExaminers();
        $other = $this->otherProject();
        $room2 = DefenseRoom::create(['name' => 'قاعة ثانية ' . uniqid(), 'is_active' => true]);
        if (in_array($other->supervisor_id, [$second, $this->project->supervisor_id], true)) {
            $this->markTestSkipped('المشروع الآخر يشارك عضواً.');
        }
        $this->schedule(['project_id' => $other->id, 'room_id' => $room2->id, 'examiner_id' => $second])->assertSessionHas('success');

        $this->schedule(['time' => '10:15', 'examiner_ids' => [$this->examiner->id, $second]])->assertSessionHasErrors('examiner_id');
        $this->assertStringContainsString(Supervisor::find($second)->name, session('errors')->first('examiner_id'));
    }

    public function test_rescheduling_swaps_committee_members(): void
    {
        [$second, $third] = $this->moreExaminers(2);
        $this->schedule(['examiner_ids' => [$this->examiner->id, $second]])->assertSessionHas('success');
        $d = Defense::where('project_id', $this->project->id)->firstOrFail();

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.defenses.update', $d->id), $this->payload(['examiner_ids' => [$this->examiner->id, $third]]))
            ->assertSessionHas('success');

        $this->assertEqualsCanonicalizing(
            [$this->project->supervisor_id, $this->examiner->id, $third],
            $d->members()->pluck('supervisor_id')->all()
        );
    }

    private function needChairColumn(): void
    {
        if (! \App\Models\DefenseMember::chairSupported()) {
            $this->markTestSkipped('عمود is_chair غير موجود — شغّل الترحيل ثم test:prepare --force.');
        }
    }

    public function test_the_chair_defaults_to_the_supervisor_and_can_be_an_examiner(): void
    {
        $this->needChairColumn();
        [$second] = $this->moreExaminers();

        $this->schedule(['examiner_ids' => [$this->examiner->id, $second]])->assertSessionHas('success');
        $d = Defense::where('project_id', $this->project->id)->with('members')->firstOrFail();
        $this->assertSame($this->project->supervisor_id, $d->chair()->supervisor_id);
        $this->assertSame(1, $d->members->where('is_chair', true)->count());

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.defenses.update', $d->id), $this->payload(['examiner_ids' => [$this->examiner->id, $second], 'chair_id' => $second]))
            ->assertSessionHas('success');
        $d = $d->fresh('members');
        $this->assertSame($second, $d->chair()->supervisor_id);
        $this->assertSame(1, $d->members->where('is_chair', true)->count());
    }

    public function test_the_chair_must_sit_on_the_committee(): void
    {
        [$outsider] = $this->moreExaminers();

        $this->schedule(['chair_id' => $outsider])->assertSessionHasErrors('chair_id');
        $this->assertFalse(Defense::where('project_id', $this->project->id)->exists());
    }

    public function test_online_defense_needs_a_valid_link_and_no_room(): void
    {
        $this->schedule(['mode' => 'online', 'room_id' => null])->assertSessionHasErrors('meeting_url');
        $this->schedule(['mode' => 'online', 'room_id' => null, 'meeting_url' => 'not a link'])->assertSessionHasErrors('meeting_url');

        $this->schedule(['mode' => 'online', 'room_id' => $this->room->id, 'meeting_url' => 'https://meet.google.com/abc-defg-hij'])
            ->assertSessionHas('success');

        $d = Defense::where('project_id', $this->project->id)->firstOrFail();
        $this->assertNull($d->room_id);
        $this->assertSame('https://meet.google.com/abc-defg-hij', $d->meeting_url);
        $this->assertStringContainsString('calendar.google.com/calendar/render', $d->googleCalendarUrl());
    }

    public function test_past_time_is_rejected(): void
    {
        $this->schedule(['date' => now()->subDay()->format('Y-m-d')])->assertSessionHasErrors('time');
    }

    public function test_rescheduling_updates_notifies_and_is_audited(): void
    {
        $this->schedule()->assertSessionHas('success');
        $d = Defense::where('project_id', $this->project->id)->firstOrFail();
        Notification::fake();

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.defenses.update', $d->id), $this->payload(['time' => '12:00']))
            ->assertSessionHas('success');

        $this->assertSame('12:00', $d->fresh()->starts_at->format('H:i'));
        Notification::assertSentTo($this->examiner, ProjectActivityNotify::class, fn ($n) => str_contains($n->toArray($this->examiner)['msg'], 'تغيّر موعد'));
        $this->assertTrue(AuditLog::where('action', 'defense.rescheduled')->where('subject_id', $this->project->id)->exists());
    }

    public function test_cancelling_returns_the_project_to_awaiting(): void
    {
        $this->schedule()->assertSessionHas('success');
        $d = Defense::where('project_id', $this->project->id)->firstOrFail();

        $this->actingAs($this->admin, 'admin')->post(route('admin.defenses.cancel', $d->id), ['reason' => 'تعارض'])
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $d->fresh()->status);
        $awaiting = $this->actingAs($this->admin, 'admin')->get(route('admin.defenses.index', ['tab' => 'awaiting']))
            ->assertOk()->viewData('awaiting');
        $this->assertContains($this->project->id, $awaiting->pluck('id'));
    }

    public function test_reminder_is_sent_once_for_tomorrow(): void
    {
        $this->schedule(['date' => now()->addDay()->format('Y-m-d')])->assertSessionHas('success');
        Notification::fake();

        $this->artisan('defenses:remind')->assertSuccessful();
        $this->artisan('defenses:remind')->assertSuccessful();

        Notification::assertSentToTimes($this->examiner, ProjectActivityNotify::class, 1);
        $this->assertTrue(Defense::where('project_id', $this->project->id)->first()->reminded_on->isToday());
    }

    public function test_page_lists_awaiting_projects_and_suggests_examiners_without_the_supervisor(): void
    {
        $res = $this->actingAs($this->admin, 'admin')->get(route('admin.defenses.index'))->assertOk();

        $this->assertContains($this->project->id, $res->viewData('awaiting')->pluck('id'));
        $res->assertSee('جدولة المناقشة')->assertSee('إنشاء Google Meet');

        $suggested = DefenseScheduler::suggestExaminers($this->project);
        $this->assertNotContains($this->project->supervisor_id, $suggested->pluck('id'));
    }

    public function test_room_with_history_is_deactivated_not_deleted(): void
    {
        $this->schedule()->assertSessionHas('success');

        $this->actingAs($this->admin, 'admin')->delete(route('admin.defenses.rooms.destroy', $this->room->id))
            ->assertSessionHas('fail');
        $this->assertNotNull($this->room->fresh());
    }
}
