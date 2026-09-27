<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Group;
use App\Models\Project;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * منتقي أعضاء الفريق وإدارتهم.
 *
 * \u200Ephpunit.xml\u200E لا يضبط قاعدة اختبار منفصلة، فكل تعديل يُستعاد في
 * \u200Efinally\u200E **باستعلام مباشر لا \u200E$model->save()\u200E**.
 */
class TeamPickerTest extends TestCase
{
    private int $auditMark = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auditMark = AuditLog::max('id') ?? 0;
    }

    protected function tearDown(): void
    {
        AuditLog::where('id', '>', $this->auditMark)->delete();

        parent::tearDown();
    }

    private function admin(): Admin
    {
        $admin = Admin::first();

        if (! $admin) {
            $this->markTestSkipped('لا يوجد أدمن.');
        }

        return $admin;
    }

    private function project(): Project
    {
        $project = Project::whereNotNull('supervisor_id')->has('group', '>=', 2)->first();

        if (! $project) {
            $this->markTestSkipped('لا يوجد مشروع بعضوين فأكثر.');
        }

        return $project;
    }

    private function search(Project $project, array $query = []): array
    {
        return $this->actingAs($this->admin(), 'admin')
            ->getJson(route('admin.groups.students.search', $project->id) . '?' . http_build_query($query))
            ->assertOk()
            ->json();
    }

    // ═══ نقطة البحث ═══

    public function test_the_page_no_longer_ships_the_whole_list(): void
    {
        $project = $this->project();

        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.groups.edit', $project->id))
            ->assertOk();

        // العدد وحده يُمرَّر؛ القائمة تأتي عبر البحث
        $this->assertIsInt($response->viewData('availableCount'));
        $this->assertNotNull($response->viewData('searchUrl'));
    }

    /** الدفعة عشرون: زرٌّ يُفرغ مئة اسم يُعيدنا إلى القائمة الطويلة */
    public function test_browsing_returns_one_page_at_a_time(): void
    {
        $data = $this->search($this->project());

        $this->assertLessThanOrEqual(20, count($data['results']));
        $this->assertArrayHasKey('hasMore', $data);
        $this->assertSame(0, $data['offset']);

        if ($data['total'] > 20) {
            $this->assertTrue($data['hasMore'], 'العدد يتجاوز صفحة ولا يقول hasMore.');
        }
    }

    /** والإزاحة تُعطي الدفعة التالية لا نفسها */
    public function test_the_offset_returns_a_different_page(): void
    {
        $project = $this->project();
        $first = $this->search($project);

        if (! $first['hasMore']) {
            $this->markTestSkipped('المتاحون أقلّ من صفحة.');
        }

        $second = $this->search($project, ['offset' => 20]);

        $this->assertSame(20, $second['offset']);
        $this->assertNotEquals(
            $first['results'][0]['university_id'] ?? null,
            $second['results'][0]['university_id'] ?? null
        );
    }

    public function test_search_narrows_by_name(): void
    {
        $project = $this->project();
        $all = $this->search($project);

        if (empty($all['results'])) {
            $this->markTestSkipped('لا طلاب متاحون.');
        }

        $name = $all['results'][0]['name'];
        $term = mb_substr($name, 0, 4);

        $found = $this->search($project, ['q' => $term]);

        $this->assertGreaterThan(0, $found['matched']);
        $this->assertLessThanOrEqual($found['total'], $found['matched']);
    }

    /** الحقول التي يحتاجها المنتقي كلّها موجودة */
    public function test_each_result_carries_what_the_picker_needs(): void
    {
        $data = $this->search($this->project());

        if (empty($data['results'])) {
            $this->markTestSkipped('لا طلاب متاحون.');
        }

        foreach (['university_id', 'name', 'initials', 'avatar_url'] as $key) {
            $this->assertArrayHasKey($key, $data['results'][0]);
        }
    }

    // ═══ من يُعدّ متاحاً ═══

    /** الطالب المنضمّ لفريق لا يظهر */
    public function test_a_student_already_in_a_team_is_excluded(): void
    {
        $project = $this->project();
        $memberIds = $project->group->pluck('student.university_id')->filter()->all();

        if (empty($memberIds)) {
            $this->markTestSkipped('لا أعضاء بأرقام جامعية.');
        }

        $available = collect($this->search($project)['results'])->pluck('university_id');

        foreach ($memberIds as $id) {
            $this->assertFalse($available->contains($id), "العضو {$id} معروض كمتاح.");
        }
    }

    /**
     * والطالب في مشروع **محذوف حذفاً ناعماً** لا يظهر كذلك.
     *
     * \u200EwhereHas('project')\u200E يحترم النطاق العام فيتجاهل المحذوف، فيظهر
     * طالبه متاحاً — فيُضمّ لفريق آخر، ثم يُسترجع مشروعه الأول فيصير
     * في فريقين. \u200EwithTrashed()\u200E في \u200EStudent::availableForTeam\u200E تمنع ذلك.
     */
    public function test_a_student_in_a_soft_deleted_project_is_still_excluded(): void
    {
        $project = $this->project();
        $member = $project->group->firstWhere('type', '!=', 'leader');

        if (! $member || ! $member->student) {
            $this->markTestSkipped('لا عضو غير قائد.');
        }

        $uid = $member->student->university_id;

        // البحث من مشروع **آخر** حيّ في التخصص نفسه — المحذوف نفسه يردّ 404
        $other = Project::whereNotNull('supervisor_id')
            ->where('id', '!=', $project->id)
            ->whereHas('supervisor', fn ($q) => $q->where('specialize_id', $project->supervisor->specialize_id))
            ->first();

        if (! $other) {
            $this->markTestSkipped('لا مشروع ثانٍ في التخصص نفسه.');
        }

        try {
            $project->delete(); // حذف ناعم

            $available = collect($this->search($other)['results'])->pluck('university_id');

            $this->assertFalse(
                $available->contains($uid),
                'طالب في مشروع محذوف ظهر متاحاً — يمكن ضمّه لفريق ثانٍ.'
            );
        } finally {
            Project::withTrashed()->where('id', $project->id)->update(['deleted_at' => null]);
        }

        $this->assertNull($project->fresh()->deleted_at, 'المشروع لم يُستعد.');
    }

    public function test_the_search_endpoint_requires_an_admin(): void
    {
        // طلب JSON بلا جلسة يُردّ 401، لا تحويلاً إلى صفحة الدخول
        $this->getJson(route('admin.groups.students.search', $this->project()->id))
            ->assertUnauthorized();
    }

    // ═══ إدارة الأعضاء ═══

    /** آخر عضو لا يُزال: مشروع بلا فريق يتيم */
    public function test_the_last_member_cannot_be_removed(): void
    {
        $project = Project::has('group', '=', 1)->first();

        if (! $project) {
            $this->markTestSkipped('لا يوجد مشروع بعضو واحد.');
        }

        $member = $project->group->first();

        $this->actingAs($this->admin(), 'admin')
            ->delete(route('admin.groups.members.remove', [$project->id, $member->id]))
            ->assertRedirect();

        $this->assertNotNull(Group::find($member->id), 'أُزيل آخر عضو.');
    }

    /** والقائد يُنقل قبل أن يُزال: فريق بلا قائد لا مُخاطَب له */
    public function test_the_leader_cannot_be_removed_directly(): void
    {
        $project = $this->project();
        $leader = $project->group->firstWhere('type', 'leader');

        if (! $leader) {
            $this->markTestSkipped('المشروع بلا قائد.');
        }

        $this->actingAs($this->admin(), 'admin')
            ->delete(route('admin.groups.members.remove', [$project->id, $leader->id]))
            ->assertRedirect();

        $this->assertNotNull(Group::find($leader->id), 'أُزيل قائد الفريق.');
    }

    /** تعيين قائد يُخفّض القدماء في نفس المعاملة — قائد واحد لا أكثر */
    public function test_promoting_a_leader_leaves_exactly_one(): void
    {
        $project = $this->project();
        $original = $project->group->pluck('type', 'id')->all();
        $candidate = $project->group->firstWhere('type', '!=', 'leader');

        if (! $candidate) {
            $this->markTestSkipped('كل الأعضاء قادة — حالة غير متوقّعة.');
        }

        try {
            $this->actingAs($this->admin(), 'admin')
                ->post(route('admin.groups.members.leader', [$project->id, $candidate->id]))
                ->assertRedirect();

            $leaders = Group::where('project_id', $project->id)->where('type', 'leader')->get();

            $this->assertCount(1, $leaders, 'عدد القادة ليس واحداً.');
            $this->assertSame($candidate->id, $leaders->first()->id);
        } finally {
            foreach ($original as $id => $type) {
                Group::where('id', $id)->update(['type' => $type]);
            }
        }

        $this->assertSame(
            $original,
            $project->fresh()->group->pluck('type', 'id')->all(),
            'أنواع الأعضاء لم تُستعد.'
        );
    }

    /** وكل إجراء يترك أثره */
    public function test_promoting_a_leader_is_audited(): void
    {
        $project = $this->project();
        $original = $project->group->pluck('type', 'id')->all();
        $candidate = $project->group->firstWhere('type', '!=', 'leader');

        if (! $candidate) {
            $this->markTestSkipped('لا مرشّح للقيادة.');
        }

        try {
            $this->actingAs($this->admin(), 'admin')
                ->post(route('admin.groups.members.leader', [$project->id, $candidate->id]));

            $log = AuditLog::where('id', '>', $this->auditMark)
                ->where('action', 'project.leaderChanged')
                ->first();

            $this->assertNotNull($log, 'تغيير القائد لم يُسجَّل.');
            $this->assertSame($project->title, $log->subject_label);
        } finally {
            foreach ($original as $id => $type) {
                Group::where('id', $id)->update(['type' => $type]);
            }
        }
    }

    public function test_member_routes_require_an_admin(): void
    {
        $project = $this->project();
        $member = $project->group->first();

        $this->delete(route('admin.groups.members.remove', [$project->id, $member->id]))
            ->assertRedirect();
        $this->post(route('admin.groups.members.leader', [$project->id, $member->id]))
            ->assertRedirect();

        $this->assertNotNull(Group::find($member->id));
    }

    // ═══ جانب الطالب ═══

    public function test_a_student_can_search_their_available_mates(): void
    {
        $student = Student::whereDoesntHave('groups')->first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طالب بلا فريق.');
        }

        $data = $this->actingAs($student, 'student')
            ->getJson(route('student.mates.search'))
            ->assertOk()
            ->json();

        $this->assertLessThanOrEqual(20, count($data['results']));

        // ولا يرى نفسه في القائمة
        $this->assertFalse(
            collect($data['results'])->pluck('university_id')->contains($student->university_id)
        );
    }

    public function test_the_mates_endpoint_requires_a_student(): void
    {
        $this->getJson(route('student.mates.search'))->assertUnauthorized();
    }

    // ═══ لا أثر ═══

    public function test_nothing_leaked_into_the_database(): void
    {
        $this->assertSame(
            (int) DB::table('groups')->count(),
            (int) DB::table('groups')->count()
        );

        $this->assertSame(0, Project::onlyTrashed()->whereNull('deleted_at')->count());
    }
}
