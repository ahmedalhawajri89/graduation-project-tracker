<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\Specialize;
use App\Support\Audit;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * سجلّ التدقيق.
 *
 * سجلّ إلحاق فقط. ما يُنشئه الاختبار يُحذف في \u200Efinally\u200E باستعلام مباشر.
 */
class AuditLogTest extends TestCase
{
    private int $mark = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->mark = AuditLog::max('id') ?? 0;
    }

    protected function tearDown(): void
    {
        AuditLog::where('id', '>', $this->mark)->delete();

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

    /** السطور التي أنشأها هذا الاختبار وحده */
    private function fresh()
    {
        return AuditLog::where('id', '>', $this->mark)->get();
    }

    // ═══ التسجيل ═══

    public function test_an_event_records_the_actor_and_their_role(): void
    {
        $project = Project::first();

        if (! $project) {
            $this->markTestSkipped('لا توجد مشاريع.');
        }

        $this->actingAs($this->admin(), 'admin');

        Audit::record('project.deleted', $project);

        $log = $this->fresh()->first();

        $this->assertNotNull($log);
        $this->assertSame('project.deleted', $log->action);
        $this->assertSame('admin', $log->actor_role);
        $this->assertSame($this->admin()->name, $log->actor_name);
        $this->assertSame($project->title, $log->subject_label);
    }

    /**
     * اللقطات النصّية: سجلّ يشير إلى صفّ محذوف يصير سطوراً فارغة —
     * وهو أوّل ما يُحتاج إليه عند التنازع.
     */
    public function test_names_survive_as_text_not_relations(): void
    {
        $project = Project::first();

        if (! $project) {
            $this->markTestSkipped('لا توجد مشاريع.');
        }

        $this->actingAs($this->admin(), 'admin');
        Audit::record('project.forceDeleted', $project);

        $log = $this->fresh()->first();

        // العمودان نصّان مستقلّان لا مفاتيح
        $this->assertIsString($log->actor_name);
        $this->assertIsString($log->subject_label);
        $this->assertNotEmpty($log->subject_label);
    }

    public function test_changes_are_stored_as_an_array(): void
    {
        $this->actingAs($this->admin(), 'admin');

        Audit::record('grade.unlocked', Project::first(), [
            'grade' => ['from' => 80, 'to' => 90],
            'reason' => 'سبب مكتوب للاختبار',
        ]);

        $log = $this->fresh()->first();

        $this->assertIsArray($log->changes);
        $this->assertSame(80, $log->changes['grade']['from']);
        $this->assertSame('سبب مكتوب للاختبار', $log->changes['reason']);
    }

    /** كل فعل معروف له نصّ عربي — وإلا ظهر المفتاح الخام للمستخدم */
    public function test_every_action_key_has_an_arabic_label(): void
    {
        foreach (array_keys(AuditLog::LABELS) as $key) {
            $log = new AuditLog(['action' => $key]);

            $this->assertNotSame($key, $log->action_label, "المفتاح {$key} بلا نصّ عربي.");
        }
    }

    // ═══ التكامل مع الأفعال الحقيقية ═══

    /** إيقاف تخصص يُنتج سطراً واحداً — لا صفراً ولا اثنين */
    public function test_archiving_a_specialize_records_exactly_one_row(): void
    {
        $specialize = Specialize::active()->first();

        if (! $specialize) {
            $this->markTestSkipped('لا توجد تخصصات نشطة.');
        }

        try {
            $this->actingAs($this->admin(), 'admin')
                ->post(route('admin.specialize.archive', $specialize->id))
                ->assertRedirect();

            $rows = $this->fresh()->where('action', 'specialize.archived');

            $this->assertCount(1, $rows);
            $this->assertSame($specialize->name, $rows->first()->subject_label);
        } finally {
            Specialize::where('id', $specialize->id)->update(['archived_at' => null]);
        }

        $this->assertNull($specialize->fresh()->archived_at, 'التخصص لم يُستأنف.');
    }

    // ═══ السجلّ لا يُعدَّل ═══

    /** لا مسار إنشاء أو تعديل أو حذف. سجلّ يُعدَّل ليس سجلّاً. */
    public function test_no_route_mutates_the_log(): void
    {
        $mutating = collect(Route::getRoutes())
            ->filter(fn ($route) => str_contains($route->uri(), 'audit'))
            ->filter(fn ($route) => count(array_intersect($route->methods(), ['POST', 'PUT', 'PATCH', 'DELETE'])) > 0);

        $this->assertCount(0, $mutating, 'يوجد مسار يعدّل سجلّ التدقيق.');
    }

    public function test_the_model_has_no_updated_at(): void
    {
        $this->assertNull(AuditLog::UPDATED_AT);
    }

    // ═══ الصفحة ═══

    public function test_the_page_loads_for_an_admin(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('سجلّ التدقيق');
    }

    public function test_the_page_is_closed_to_non_admins(): void
    {
        $student = \App\Models\Student::first();

        if (! $student) {
            $this->markTestSkipped('لا يوجد طلاب.');
        }

        $this->actingAs($student, 'student')
            ->get(route('admin.audit.index'))
            ->assertRedirect();
    }

    public function test_the_scope_filter_narrows_the_list(): void
    {
        $this->actingAs($this->admin(), 'admin');

        Audit::record('grade.locked', Project::first());
        Audit::record('project.deleted', Project::first());

        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.audit.index', ['scope' => 'grade']))
            ->assertOk();

        $this->assertGreaterThanOrEqual(1, $response->viewData('countGrade'));
        $this->assertLessThanOrEqual(
            $response->viewData('countAll'),
            $response->viewData('countGrade')
        );
    }

    public function test_export_returns_a_spreadsheet(): void
    {
        $response = $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.audit.export'));

        $response->assertOk();
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\BinaryFileResponse::class,
            $response->baseResponse
        );
    }
}
