<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\Student;
use App\Support\ContactTopic;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * رسائل الاستفسار: موضوع مستنتج لكل رسالة، ومن هو المُرسِل في المنصّة،
 * والتصفية حسب الموضوع، والرسائل السابقة، والتنقّل بين الرسائل.
 * داخل معاملة تُرجَع في tearDown.
 */
class ContactInboxTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();

        $admin = Admin::first();
        if (! $admin) {
            $this->markTestSkipped('لا أدمن.');
        }
        $this->admin = $admin;
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    private function message(array $attrs = []): Contact
    {
        $c = Contact::create($attrs + [
            'name' => 'زائر اختبار',
            'email' => 'visitor.' . uniqid() . '@example.com',
            'subject' => 'سؤال عام',
            'message' => 'نص الرسالة',
            'is_read' => 0,
        ]);
        // الأحدث أولاً في القائمة: الطابع الزمني صريح لا يعتمد على سرعة التنفيذ
        $c->forceFill(['created_at' => now()->addMinutes(Contact::count())])->save();

        return $c;
    }

    private function inbox(array $query = [])
    {
        return $this->actingAs($this->admin, 'admin')->get(route('admin.contact.index', $query))->assertOk();
    }

    public function test_topic_is_read_from_subject_before_body(): void
    {
        $of = fn ($s, $m = '') => ContactTopic::of(new Contact(['subject' => $s, 'message' => $m]));

        $this->assertSame('account', $of('مشكلة في تسجيل الدخول'));
        $this->assertSame('team', $of('طلب رفع الحد الأقصى للمجموعات'));
        $this->assertSame('feedback', $of('شكر وتقدير'));
        $this->assertSame('files', $of('الملف المرفوع لا يفتح'));
        // «لتسليمه» في النصّ لا تغلب عنواناً صريحاً عن الدرجات
        $this->assertSame('grades', $of('استفسار عن تصدير كشف الدرجات', 'أحتاجه لتسليمه للقسم'));
        $this->assertSame('other', $of('سؤال عام', 'نص بلا كلمات مفتاحية'));
    }

    public function test_topic_filter_lists_only_that_topic_and_counts_agree(): void
    {
        $login = $this->message(['subject' => 'مشكلة في تسجيل الدخول']);
        $grade = $this->message(['subject' => 'استفسار عن التقييم']);

        $res = $this->inbox(['topic' => 'account']);
        $ids = $res->viewData('messages')->getCollection()->pluck('id');

        $this->assertContains($login->id, $ids);
        $this->assertNotContains($grade->id, $ids);
        $ids->each(fn ($id) => $this->assertSame('account', ContactTopic::of(Contact::find($id))));

        // مجموع أعداد المواضيع = كل الرسائل
        $this->assertSame(Contact::count(), array_sum($this->inbox()->viewData('topicCounts')));
    }

    public function test_student_sender_is_recognised_with_a_link_to_the_project(): void
    {
        $student = Student::whereHas('groups.project', fn ($q) => $q->where('status', '!=', 'reject'))->with('groups.project')->first();
        if (! $student) {
            $this->markTestSkipped('لا طالب في مشروع.');
        }
        $m = $this->message(['name' => $student->name, 'email' => $student->email]);
        $project = $student->groups->pluck('project')->filter()->first(fn ($p) => $p->status !== 'reject');

        $this->inbox(['open' => $m->id])
            ->assertSee('cx-role-student', false)
            ->assertSee($student->university_id)
            ->assertSee(route('admin.groups.show', $project->id), false);
    }

    public function test_unknown_sender_is_a_guest(): void
    {
        $m = $this->message();

        $this->inbox(['open' => $m->id])
            ->assertSee('cx-role-guest', false)
            ->assertSee('لا حساب بهذا البريد في المنصّة');
    }

    public function test_previous_messages_from_the_same_sender_are_listed(): void
    {
        $older = $this->message(['email' => 'same@example.com', 'subject' => 'رسالة أولى قديمة']);
        $newer = $this->message(['email' => 'same@example.com', 'subject' => 'رسالة ثانية جديدة']);

        $res = $this->inbox(['open' => $newer->id]);
        $this->assertSame([$older->id], $res->viewData('history')->pluck('id')->all());
        $res->assertSee('رسائل سابقة من المُرسِل نفسه');
    }

    public function test_prev_and_next_follow_the_list_order(): void
    {
        $a = $this->message(['subject' => 'تجربة تنقّل أ']);
        $b = $this->message(['subject' => 'تجربة تنقّل ب']);
        $c = $this->message(['subject' => 'تجربة تنقّل ج']);

        // الأحدث أولاً: ج ثم ب ثم أ — والبحث يقصر القائمة على الثلاث
        $res = $this->inbox(['q' => 'تجربة تنقّل', 'open' => $b->id]);
        $this->assertSame($c->id, $res->viewData('prevId'));
        $this->assertSame($a->id, $res->viewData('nextId'));
    }

    public function test_opening_marks_read_and_reply_menu_offers_the_topic_reply_first(): void
    {
        $m = $this->message(['subject' => 'مشكلة في تسجيل الدخول']);

        $this->inbox(['open' => $m->id])
            ->assertSee('ردود جاهزة', false)
            ->assertSeeInOrder(['الدخول والحساب', 'مقترح'], false);

        $this->assertTrue((bool) $m->fresh()->is_read);
    }
}
