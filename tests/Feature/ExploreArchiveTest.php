<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Supervisor;
use Tests\TestCase;

/**
 * «مشاريع منجزة» للطالب، و«أرشيف مشاريعي» للمشرف.
 *
 * قراءة فقط — لا شيء يُكتب في القاعدة، فلا شيء يُستعاد.
 */
class ExploreArchiveTest extends TestCase
{
    private function studentWithoutProject(): Student
    {
        $student = Student::whereDoesntHave('groups')->first();

        if (! $student) {
            $this->markTestSkipped('لا طالب بلا مشروع.');
        }

        return $student;
    }

    private function studentWithProject(): Student
    {
        $student = Student::whereHas('groups.project', fn ($q) => $q->where('status', '!=', 'reject'))->first();

        if (! $student) {
            $this->markTestSkipped('لا طالب له مشروع.');
        }

        return $student;
    }

    // ═══ المستكشف ═══

    /** كان يعرض \u200Eaccept\u200E أيضاً: أفكار زملاء جارية تحت اسم «السابقة» */
    public function test_the_explorer_shows_completed_projects_only(): void
    {
        $projects = $this->actingAs($this->studentWithoutProject(), 'student')
            ->get(route('student.projects.explore', ['specialize' => 'all']))
            ->assertOk()
            ->viewData('projects');

        $this->assertGreaterThan(0, $projects->total(), 'لا مشاريع مكتملة في البيانات.');
        $this->assertTrue(collect($projects->items())->every(fn ($p) => $p->status === 'complete'));
        $this->assertSame(Project::where('status', 'complete')->count(), $projects->total());
    }

    public function test_the_explorer_defaults_to_the_students_specialization(): void
    {
        $student = $this->studentWithoutProject();

        $response = $this->actingAs($student, 'student')
            ->get(route('student.projects.explore'))
            ->assertOk();

        $this->assertSame((int) $student->specialize_id, $response->viewData('specializeId'));

        foreach ($response->viewData('projects')->items() as $project) {
            $this->assertSame((int) $student->specialize_id, (int) $project->project_type->specialize_id);
        }
    }

    /** بلا درجات: المستكشف لسؤال «ماذا نُفّذ» لا «كم أخذوا» */
    public function test_the_explorer_does_not_show_grades(): void
    {
        $this->actingAs($this->studentWithoutProject(), 'student')
            ->get(route('student.projects.explore', ['specialize' => 'all']))
            ->assertOk()
            ->assertDontSee('grade-pill', false);
    }

    // ═══ التشابه ═══

    public function test_similar_finds_a_completed_project_by_its_title(): void
    {
        $project = Project::where('status', 'complete')->with('project_type')->first();
        $student = Student::where('specialize_id', $project?->project_type?->specialize_id)->first();

        if (! $project || ! $student) {
            $this->markTestSkipped('لا مشروع مكتمل بطالب من تخصصه.');
        }

        $results = $this->actingAs($student, 'student')
            ->getJson(route('student.projects.similar', ['title' => $project->title]))
            ->assertOk()
            ->json('results');

        $this->assertContains($project->title, array_column($results, 'title'));
    }

    /** المقارنة بالمنجز وحده — فكرة زميل جارية لا تُكشف عبر التلميح */
    public function test_similar_never_returns_an_in_progress_project(): void
    {
        $running = Project::where('status', 'accept')->with('project_type')->first();

        if (! $running) {
            $this->markTestSkipped('لا مشروع جارٍ.');
        }

        $student = Student::where('specialize_id', $running->project_type?->specialize_id)->first()
            ?? $this->studentWithoutProject();

        $titles = array_column($this->actingAs($student, 'student')
            ->getJson(route('student.projects.similar', ['title' => $running->title]))
            ->assertOk()
            ->json('results'), 'title');

        $completed = Project::where('status', 'complete')->pluck('title')->all();
        foreach ($titles as $title) {
            $this->assertContains($title, $completed);
        }
    }

    public function test_similar_ignores_short_input_and_requires_a_student(): void
    {
        $this->actingAs($this->studentWithoutProject(), 'student')
            ->getJson(route('student.projects.similar', ['title' => 'ab']))
            ->assertOk()
            ->assertExactJson(['results' => []]);

        $this->app['auth']->forgetGuards();
        $this->getJson(route('student.projects.similar', ['title' => 'التنبؤ']))->assertUnauthorized();
    }

    // ═══ الشريط الجانبي ═══

    public function test_the_sidebar_link_shows_only_before_a_project(): void
    {
        // رابط الشريط تحديداً: قائمة Ctrl+K تُرسم بالعنوان نفسه وتبقى دائماً
        $link = 'class="nav-link" href="' . route('student.projects.explore') . '"';

        $this->actingAs($this->studentWithoutProject(), 'student')
            ->get(route('student.dashboard'))
            ->assertSee($link, false);

        $this->app['auth']->forgetGuards();

        $this->actingAs($this->studentWithProject(), 'student')
            ->get(route('student.dashboard'))
            ->assertDontSee($link, false);
    }

    // ═══ الأرشيف ═══

    public function test_the_archive_excludes_the_current_semester_and_other_supervisors(): void
    {
        $current = Semester::current()->id;
        $supervisor = Supervisor::whereHas('projects', fn ($q) => $q->where('semester_id', '!=', $current)
            ->whereIn('status', ['accept', 'complete']))->first();

        if (! $supervisor) {
            $this->markTestSkipped('لا مشرف له مشاريع في فصول سابقة.');
        }

        $projects = $this->actingAs($supervisor, 'supervisor')
            ->get(route('supervisor.projects.archive'))
            ->assertOk()
            ->viewData('bySemester')
            ->flatten();

        $this->assertNotEmpty($projects);
        foreach ($projects as $project) {
            $this->assertNotSame($current, (int) $project->semester_id, 'مشروع من الفصل الحالي في الأرشيف.');
            $this->assertSame((int) $supervisor->id, (int) $project->supervisor_id, 'مشروع مشرف آخر في الأرشيف.');
        }
    }

    /** المشرف يُسأل عن طالب قديم باسمه لا بعنوان مشروعه */
    public function test_the_archive_searches_by_student_name(): void
    {
        $current = Semester::current()->id;
        $project = Project::where('semester_id', '!=', $current)
            ->whereIn('status', ['accept', 'complete'])
            ->whereNotNull('supervisor_id')
            ->has('group')
            ->with('group.student', 'supervisor')
            ->first();

        $name = $project?->group->first()?->student?->name;

        if (! $name) {
            $this->markTestSkipped('لا مشروع سابق بطالب.');
        }

        $found = $this->actingAs($project->supervisor, 'supervisor')
            ->get(route('supervisor.projects.archive', ['q' => $name]))
            ->assertOk()
            ->viewData('bySemester')
            ->flatten()
            ->pluck('id');

        $this->assertContains($project->id, $found);
    }

    public function test_neither_page_uses_bootstrap_tones(): void
    {
        foreach (['dashboard/student/explore.blade.php', 'dashboard/supervisor/archive.blade.php'] as $view) {
            $this->assertDoesNotMatchRegularExpression(
                '/\bbg-[a-z]+-lt\b/',
                file_get_contents(resource_path('views/' . $view)),
                "نغمة Bootstrap في {$view}"
            );
        }
    }
}
