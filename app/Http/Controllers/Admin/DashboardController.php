<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialize;
use App\Models\SpecializeProject;
use App\Models\StatSnapshot;
use App\Models\Student;
use App\Models\Supervisor;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {

        $last_semester = Semester::current();
        $data['semester'] = $last_semester;
        $data['student_count'] = Student::count();
        $data['supervisor_count'] = Supervisor::count();
        // كان يستعمل with() داخل whereHas — و with يحمّل ولا يُرشِّح، فلم
        // يكن شرط الفصل والحالة يُطبَّق، والرقم يعدّ كل طالب انضمّ لأي
        // مجموعة في أي فصل. الشرط الآن داخل whereHas فعلياً.
        $data['has_group'] = Student::whereHas('groups.project', function ($q) use ($last_semester) {
            $q->where('status', '!=', 'reject')->where('semester_id', $last_semester->id);
        })->count();

        $data['not_has_group'] = Student::whereDoesntHave('groups.project', function ($q) use ($last_semester) {
            $q->where('status', '!=', 'reject')->where('semester_id', $last_semester->id);
        })->count();
        $data['project_count'] = Project::whereIn('status', ['accept', 'complete'])->where('semester_id', $last_semester->id)->count();
        $data['admin_count'] = Admin::count();
        $data['msg_count'] = Contact::count();
        // توزيع حالات المشاريع للفصل الحالي (لرسم الدونات)
        $data['project_status'] = Project::where('semester_id', $last_semester->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // أحدث الطلبات/النشاطات لهذا الفصل (لودجت النشاط)
        $data['recent_projects'] = Project::with('supervisor')
            ->where('semester_id', $last_semester->id)
            ->latest()
            ->take(6)
            ->get();

        // آخر رسائل الاستفسار
        $data['recent_messages'] = Contact::latest()->take(5)->get();

        // مؤشّرات الاتجاه: مقارنة الأرقام الحالية بآخر لقطة قبل اليوم
        $prev = StatSnapshot::whereDate('date', '<', now()->toDateString())
            ->orderByDesc('date')
            ->first();

        $pct = function ($current, $old) {
            if ($old === null || (int) $old === 0) {
                return null; // لا يوجد أساس للمقارنة بعد
            }
            return (int) round((($current - $old) / $old) * 100);
        };

        $data['trends'] = $prev ? [
            'students'    => $pct($data['student_count'], $prev->students),
            'supervisors' => $pct($data['supervisor_count'], $prev->supervisors),
            'groups'      => $pct($data['project_count'], $prev->groups),
            'messages'    => $pct($data['msg_count'], $prev->messages),
        ] : [];

        // سلسلة زمنية للرسم: جدول stat_snapshots كان يُستعمل لحساب سهم
        // النسبة فقط ثم يُرمى، رغم أنه لقطة يومية بستة عدّادات. الخط
        // يجيب ما لا يجيبه الرقم: إلى أين تتجه الأرقام، لا أين هي.
        // الأحدث ستّون ثم تُعاد تصاعدية للرسم. كان ‎orderBy('date')->take(60)‎
        // يأخذ أقدم ستّين، فيتجمّد الخطّ بعد شهرين من التشغيل
        $data['trendSeries'] = StatSnapshot::orderByDesc('date')
            ->take(60)
            ->get(['date', 'groups', 'not_has_group'])
            ->reverse()
            ->values();

        // الموقوف يبقى هنا: ما زال يحمل طلاباً، وإخفاؤه يُنقص المجموع
        // بلا تفسير. يُميَّز في العرض وحده.
        $data['specializes'] = Specialize::select('id', 'name', 'archived_at')->withCount('students')->get();
        $data['project_types'] = SpecializeProject::select('id', 'name')
            ->withCount(['projects' => function ($q) use ($last_semester) {
                $q->whereIn('status', ['accept', 'complete'])->where('semester_id', $last_semester->id);
            }])
            ->with(['projects' => function ($q) use ($last_semester) {
                $q->select('specialize_project_id')->whereIn('status', ['accept', 'complete'])->where('semester_id', $last_semester->id)->withCount('group');
            }])->get();

        return view('dashboard.admin.index', $data);
    }
}
