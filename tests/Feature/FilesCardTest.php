<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Student;
use Tests\TestCase;

/**
 * بطاقة ملفات المشروع — قراءة فقط.
 */
class FilesCardTest extends TestCase
{
    private function memberOf(Project $project): Student
    {
        return Student::find($project->group()->value('student_id'));
    }

    /** كانت الحالة الفارغة وزرّاً صغيراً معزولاً — صارت البطاقة كلّها منطقة إفلات */
    public function test_an_empty_unlocked_project_shows_the_drop_zone(): void
    {
        $project = Project::where('status', 'accept')->whereNull('grade')->doesntHave('files')->has('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع مقبول بلا ملفات.');
        }

        $this->actingAs($this->memberOf($project), 'student')
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('class="file-drop"', false)
            ->assertSee('data-files-card', false)
            ->assertDontSee('data-upload-empty', false);
    }

    /** كانت أيقونة رمادية واحدة لكل الأنواع */
    public function test_each_file_carries_a_type_badge_from_its_extension(): void
    {
        $project = Project::whereHas('files', fn ($q) => $q->where('path', 'like', '%.pdf'))->has('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع بملف PDF.');
        }

        $this->actingAs($this->memberOf($project), 'student')
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertSee('file-type is-pdf', false)
            ->assertSee('>PDF<', false);
    }

    /** بعد التقييم: لا منطقة إفلات ولا سحب */
    public function test_a_locked_project_has_no_upload(): void
    {
        $project = Project::whereNotNull('grade')->has('group')->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع مقيَّم.');
        }

        $html = $this->actingAs($this->memberOf($project), 'student')
            ->get(route('student.dashboard'))
            ->assertOk()
            ->assertDontSee('class="file-drop"', false)
            ->getContent();

        // السمة على البطاقة نفسها — النصّ يرد أيضاً في السكربت (querySelector)
        $this->assertDoesNotMatchRegularExpression('/<section[^>]*data-files-card/', $html);
    }
}
