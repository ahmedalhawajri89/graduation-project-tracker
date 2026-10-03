<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * الرفع بشريط تقدّم: الطلب الخفي (X-Upload) يأخذ عنوان الصفحة التالية JSON
 * بدل أن يتبع التوجيه — فتبقى رسالة النجاح أو أخطاء التحقّق للصفحة نفسها.
 */
class UploadResponseTest extends TestCase
{
    private Project $project;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        Storage::fake('local');
        DB::beginTransaction();

        $project = Project::whereHas('group')->whereIn('status', ['accept', 'complete'])->whereNull('grade_locked_at')->first();
        if (! $project) {
            $this->markTestSkipped('لا مشروع مقبول بفريق.');
        }
        $project->update(['grade' => null, 'status' => 'accept']);
        $this->project = $project;
        $this->student = Student::findOrFail($project->group()->value('student_id'));
    }

    protected function tearDown(): void
    {
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }
        parent::tearDown();
    }

    private function upload(array $data, array $headers = [])
    {
        return $this->actingAs($this->student, 'student')
            ->from(route('student.dashboard'))
            ->withHeaders($headers)
            ->post(route('student.files.store', $this->project->id), $data);
    }

    public function test_a_progress_upload_gets_the_next_page_and_keeps_the_flash(): void
    {
        $res = $this->upload(
            ['title' => 'تقرير المرحلة', 'file' => UploadedFile::fake()->create('report.pdf', 800, 'application/pdf')],
            ['X-Upload' => '1', 'Accept' => 'text/html']
        );

        $res->assertOk()->assertJsonStructure(['redirect']);
        $this->assertTrue($this->project->files()->where('title', 'تقرير المرحلة')->exists());
        // التوجيه لم يُتبع: الرسالة ما زالت تنتظر الصفحة التالية
        $res->assertSessionHas('success');
    }

    public function test_validation_errors_also_come_back_as_a_redirect(): void
    {
        $res = $this->upload(
            ['title' => '', 'file' => UploadedFile::fake()->create('virus.exe', 10)],
            ['X-Upload' => '1', 'Accept' => 'text/html']
        );

        $res->assertOk()->assertJsonStructure(['redirect']);
        $res->assertSessionHasErrors(['title', 'file']);
    }

    public function test_a_normal_form_post_is_untouched(): void
    {
        $this->upload(['title' => 'ملف عادي', 'file' => UploadedFile::fake()->create('a.pdf', 50, 'application/pdf')])
            ->assertRedirect(route('student.dashboard'));
    }
}
