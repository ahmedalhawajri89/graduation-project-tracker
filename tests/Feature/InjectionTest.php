<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\SpecializeProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * حقن السكربت والإسناد الجماعي.
 *
 * ‎phpunit.xml‎ لا يضبط قاعدة اختبار منفصلة: الرسائل المُنشأة تُحذف بمعرّفها،
 * ونوع المشروع يُستعاد باستعلام مباشر في ‎finally‎.
 */
class InjectionTest extends TestCase
{
    private int $contactMark = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->contactMark = (int) (Contact::max('id') ?? 0);
    }

    protected function tearDown(): void
    {
        Contact::where('id', '>', $this->contactMark)->delete();

        parent::tearDown();
    }

    /** كان ‎.html()‎ يعيد النصّ المفكوك من السمة HTML حيّاً في جلسة الأدمن */
    public function test_the_delete_modal_inserts_names_as_text(): void
    {
        $js = file_get_contents(resource_path('views/dashboard/component/delete_modal.blade.php'));

        $this->assertStringContainsString(".text(button.data('name'))", $js);
        $this->assertStringNotContainsString(".html(button.data(", $js);
    }

    /** ‎$request->all()‎ كان يكتب ‎is_read‎ فتصل رسالة المُرسِل مقروءةً فلا تُرى */
    public function test_a_public_message_cannot_mark_itself_read(): void
    {
        $this->post('/send', [
            'name' => 'زائر',
            'email' => 'visitor@example.test',
            'subject' => 'استفسار',
            'message' => 'نصّ',
            'is_read' => 1,
        ])->assertRedirect();

        $contact = Contact::where('id', '>', $this->contactMark)->first();
        $this->assertNotNull($contact, 'لم تُحفظ الرسالة.');
        $this->assertFalse((bool) $contact->is_read);
    }

    public function test_public_message_fields_have_length_limits(): void
    {
        $this->post('/send', [
            'name' => 'زائر',
            'email' => 'visitor@example.test',
            'subject' => str_repeat('س', 201),
            'message' => 'نصّ',
        ])->assertSessionHasErrors('subject');

        $this->assertSame(0, Contact::where('id', '>', $this->contactMark)->count());
    }

    /** نقل نوعٍ مستعمَل إلى تخصص آخر كان ممكناً عبر ‎$request->all()‎ */
    public function test_updating_a_project_type_cannot_move_it_to_another_specialization(): void
    {
        $type = SpecializeProject::first();
        $otherSpec = DB::table('specializes')->where('id', '!=', $type?->specialize_id)->value('id');

        if (! $type || ! $otherSpec) {
            $this->markTestSkipped('لا نوع مشروع أو لا تخصص ثانٍ.');
        }

        $original = DB::table('specialize_projects')->where('id', $type->id)->first();

        try {
            $this->actingAs(Admin::first(), 'admin')
                ->put(route('admin.specialize.projects.update', $type->specialize_id), [
                    'id' => $type->id,
                    'name' => $type->name,
                    'specialize_id' => $otherSpec,
                    'min' => $type->min,
                    'max' => $type->max,
                ]);

            $this->assertSame((int) $original->specialize_id, (int) DB::table('specialize_projects')->where('id', $type->id)->value('specialize_id'));
        } finally {
            DB::table('specialize_projects')->where('id', $type->id)->update((array) $original);
        }
    }
}
