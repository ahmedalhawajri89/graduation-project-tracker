<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * مجموعات المشرف في لوحة الإدارة: سعته، وملخّص، وبطاقة لكل مجموعة
 * بتقدّمها ومشكلاتها — ثم الفصول السابقة. ومعها الملف الشخصي بأرقام
 * غربية وبلا نقاط كنصّ إرشادي، والأحرف الأولى اللاتينية كبيرة.
 * داخل معاملة تُرجَع في tearDown.
 */
class SupervisorGroupsPageTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $admin = Admin::first();
        if (! $admin || ! Semester::current()) {
            $this->markTestSkipped('لا أدمن أو فصل.');
        }
        $this->admin = $admin;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function supervisorWithGroups(): ?int
    {
        return Project::where('semester_id', Semester::current()->id)
            ->whereIn('status', ['accept', 'complete'])->value('supervisor_id');
    }

    public function test_page_shows_label_summary_and_a_card_per_group(): void
    {
        $id = $this->supervisorWithGroups();
        if (! $id) {
            $this->markTestSkipped('لا مشرف بمجموعات هذا الفصل.');
        }

        $res = $this->actingAs($this->admin, 'admin')->get(route('admin.supervisors.groups', $id))->assertOk();
        $projects = $res->viewData('projects');

        $res->assertSee(Semester::current()->label)
            ->assertDontSee(Semester::current()->name);
        $this->assertSame($projects->count(), $res->viewData('summary')['groups']);

        foreach ($projects as $p) {
            $done = $p->milestones->where('is_done', true)->count();
            $total = $p->milestones->count();
            $this->assertSame($total ? (int) round($done * 100 / $total) : 0, $p->progress, $p->title);
            $res->assertSee(route('admin.groups.show', $p->id), false);
        }
        $res->assertSee('sg-card', false);
    }

    public function test_issues_match_team_health(): void
    {
        $id = $this->supervisorWithGroups();
        if (! $id) {
            $this->markTestSkipped('لا مشرف بمجموعات هذا الفصل.');
        }

        $projects = $this->actingAs($this->admin, 'admin')->get(route('admin.supervisors.groups', $id))->viewData('projects');

        foreach ($projects as $p) {
            $this->assertSame(array_keys(\App\Support\TeamHealth::issuesFor($p)), array_keys($p->issues), $p->title);
        }
    }

    public function test_past_semesters_link_to_the_filtered_table(): void
    {
        $row = Project::whereIn('status', ['accept', 'complete'])
            ->where('semester_id', '!=', Semester::current()->id)->first(['supervisor_id', 'semester_id']);
        if (! $row) {
            $this->markTestSkipped('لا مجموعات في فصول سابقة.');
        }

        $this->actingAs($this->admin, 'admin')->get(route('admin.supervisors.groups', $row->supervisor_id))
            ->assertOk()
            ->assertSee('الفصول السابقة')
            ->assertSee(e(route('admin.groups.index', ['supervisor' => $row->supervisor_id, 'semester' => $row->semester_id])), false);
    }

    public function test_profile_uses_western_digits_and_no_dot_placeholders(): void
    {
        $html = $this->actingAs($this->admin, 'admin')->get(route('admin.profile.edit'))->assertOk()->getContent();
        // التعليقات لا تصل إلى الصفحة، فأي رقم هندي هنا نصّ يراه المستخدم
        $this->assertDoesNotMatchRegularExpression('/[\x{0660}-\x{0669}]/u', $html);
        $this->assertStringNotContainsString('placeholder="••', $html);
    }

    public function test_latin_initials_are_two_capitals_and_arabic_unchanged(): void
    {
        $this->assertSame('AA', (new Admin(['name' => 'admin admin']))->initials);
        $this->assertSame('JD', (new Admin(['name' => 'john doe']))->initials);
        $this->assertSame('ره', (new Student(['name' => 'رهام الخطيب']))->initials);
        $this->assertSame('يو', (new Student(['name' => 'د. يوسف الزاملي']))->initials);
    }
}
