<?php

namespace Tests\Feature;

use App\Models\FileNote;
use App\Models\Group;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\Student;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * ملاحظات على ملفات المشروع: تنبّه عضواً، وتُغلق حين تُعالَج.
 *
 * داخل معاملة تُرجَع في \u200EtearDown\u200E، والإشعارات مزيّفة.
 */
class FileNoteTest extends TestCase
{
    private Project $project;
    private ProjectFile $file;
    private Student $leader;
    private Student $member;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        DB::beginTransaction();

        $project = Project::where('status', 'accept')->whereNull('grade')->whereNotNull('supervisor_id')
            ->whereHas('group', fn ($q) => $q->where('type', 'leader'))
            // عضوان على الأقل غير القائد: اختبار «عضو ثالث لا يغلقها» يحتاجهما
            ->whereHas('group', fn ($q) => $q->where('type', 'member'), '>=', 2)
            ->first();

        if (! $project) {
            $this->markTestSkipped('لا مشروع جارٍ بقائد وأعضاء.');
        }

        $this->project = $project;
        $this->leader = Student::find($project->group()->where('type', 'leader')->value('student_id'));
        $this->member = Student::find($project->group()->where('type', 'member')->value('student_id'));

        // ملف رفعه العضو
        $this->file = $project->files()->create([
            'title' => 'الفصل الأول للاختبار',
            'path' => 'project_files/test.pdf',
            'size' => 1000,
            'uploader_type' => Student::class,
            'uploader_id' => $this->member->id,
        ]);
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function note(array $data = [], $as = null, string $guard = 'student')
    {
        return $this->actingAs($as ?? $this->leader, $guard)
            ->post(route('files.notes.store', $this->file->id), $data + [
                'body' => 'صفحة ٣ ينقصها المرجع.',
                'mentioned_id' => $this->member->id,
            ]);
    }

    public function test_the_leader_notes_a_file_and_the_mentioned_member_is_notified(): void
    {
        $this->note()->assertSessionHas('success');

        $note = FileNote::where('project_file_id', $this->file->id)->first();
        $this->assertTrue($note->isOpen());
        $this->assertSame($this->member->id, (int) $note->mentioned_id);
        Notification::assertSentTo($this->member, ProjectActivityNotify::class);

        // يظهر للعضو في «ماذا عليّ الآن» وعلى الملف
        $this->app['auth']->forgetGuards();
        $this->actingAs($this->member, 'student')->get(route('student.dashboard'))->assertOk()
            ->assertSee('تنتظر تعديلك')
            ->assertSee('تعديل مطلوب منك')
            ->assertSee('صفحة ٣ ينقصها المرجع.');
    }

    /** بلا منبَّه: رافع الملف يُنبَّه إن لم يكن الكاتب */
    public function test_without_a_mention_the_uploader_is_notified(): void
    {
        $this->note(['mentioned_id' => null]);

        Notification::assertSentTo($this->member, ProjectActivityNotify::class);
    }

    public function test_the_supervisor_can_note_and_an_outsider_cannot(): void
    {
        $this->note([], $this->project->supervisor, 'supervisor')->assertSessionHas('success');

        $this->app['auth']->forgetGuards();
        $outsider = Student::whereDoesntHave('groups', fn ($q) => $q->where('project_id', $this->project->id))->first();
        $this->note([], $outsider)->assertForbidden();
    }

    public function test_a_student_outside_the_team_cannot_be_mentioned(): void
    {
        $outsider = Student::whereDoesntHave('groups', fn ($q) => $q->where('project_id', $this->project->id))->first();

        $this->note(['mentioned_id' => $outsider->id])->assertSessionHasErrors('mentioned_id');
        $this->assertSame(0, FileNote::where('project_file_id', $this->file->id)->count());
    }

    /** المنبَّه يعالجها، فيصل الكاتبَ إشعار */
    public function test_the_mentioned_member_resolves_and_the_author_is_notified(): void
    {
        $this->note();
        $note = FileNote::where('project_file_id', $this->file->id)->first();
        Notification::fake();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->member, 'student')->post(route('files.notes.toggle', $note->id))->assertSessionHas('success');

        $this->assertFalse($note->fresh()->isOpen());
        $this->assertSame(Student::class, $note->fresh()->resolved_by_type);
        Notification::assertSentTo($this->leader, ProjectActivityNotify::class);

        // وإعادة الفتح
        $this->actingAs($this->member, 'student')->post(route('files.notes.toggle', $note->id));
        $this->assertTrue($note->fresh()->isOpen());
    }

    /** عضو ثالث (لا كاتب ولا منبَّه ولا قائد) لا يغلقها */
    public function test_an_unrelated_member_cannot_resolve(): void
    {
        $third = Group::where('project_id', $this->project->id)->where('type', 'member')
            ->where('student_id', '!=', $this->member->id)->value('student_id');

        if (! $third) {
            $this->markTestSkipped('الفريق عضوان فقط.');
        }

        $this->note();
        $note = FileNote::where('project_file_id', $this->file->id)->first();

        $this->app['auth']->forgetGuards();
        $this->actingAs(Student::find($third), 'student')->post(route('files.notes.toggle', $note->id))->assertForbidden();
    }

    public function test_only_the_author_or_the_supervisor_deletes(): void
    {
        $this->note();
        $note = FileNote::where('project_file_id', $this->file->id)->first();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->member, 'student')->delete(route('files.notes.destroy', $note->id))->assertForbidden();

        $this->app['auth']->forgetGuards();
        $this->actingAs($this->project->supervisor, 'supervisor')->delete(route('files.notes.destroy', $note->id));
        $this->assertNull(FileNote::find($note->id));
    }

    public function test_a_locked_project_takes_no_new_notes(): void
    {
        DB::table('projects')->where('id', $this->project->id)->update(['grade' => 90, 'status' => 'complete']);

        $this->note()->assertSessionHas('fail');
        $this->assertSame(0, FileNote::where('project_file_id', $this->file->id)->count());
    }
}
