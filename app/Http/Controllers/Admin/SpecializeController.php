<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecializeRequest;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialize;
use App\Support\Audit;

class SpecializeController extends Controller
{

    /**
     * كانت الصفحة جدول DataTables خادمياً لأربعة صفوف — آلة أكبر من
     * حاجتها — يعرض عموداً واحداً: عدد أنواع المشاريع، بعبارة «٣ مشروع»
     * وهي ليست مشاريع بل أنواعاً.
     *
     * والتخصص ليس اسماً في قائمة: هو سلسلة شروط. الطالب لا يستطيع
     * تسجيل مشروع إلا إذا كان لتخصصه نوع مشروع ومشرف متاح
     * (\u200EStudent\DashboardController::createProject\u200E). فتخصص ينقصه أحدهما
     * طريق مسدود أمام طلابه، ولم يكن شيء في الشاشة يقول ذلك.
     *
     * وحارس الحذف يمنع حذف تخصص عليه طلاب أو مشرفون — والصفحة لم تكن
     * تعرض عددهم، فالمنع يأتي مفاجأة.
     */
    public function index()
    {
        $semesterId = Semester::current()->id;
        // المشروع الجاري: مقبول أو مكتمل في الفصل الحالي
        // الأعمدة مؤهَّلة بجدولها: الشرط نفسه يُستعمل مع ربط جدولَي المشرفين والأنواع
        $running = fn ($q) => $q->whereIn('projects.status', ['accept', 'complete'])->where('projects.semester_id', $semesterId);

        $specializes = Specialize::withCount([
            'students',
            'supervisors',
            'supervisorsAvailable',
            'projects',
            // «كم طالباً ما زال بلا فريق؟» — وكانت البطاقة لا تجيبه. التعريف نفسه
            // الذي يرشّح به جدول الطلاب (\u200Egroup=none\u200E) فيطابق الرقمُ القائمةَ التي يفتحها
            'students as students_in_team_count' => fn ($q) => $q->whereHas('groups.project',
                fn ($p) => $p->whereIn('status', ['accept', 'complete'])),
        ])
            // سعة الإشراف: مجموع حدود المشرفين، تقابلها المقاعد المشغولة أدناه
            ->withSum('supervisors as seats_total', 'max_group')
            // الأسماء نفسها لا عددها، وكم مشروعاً جارياً على كلٍّ منها
            ->with(['projects' => fn ($q) => $q->select('id', 'specialize_id', 'name', 'min', 'max')
                ->withCount(['projects as current_count' => $running])
                ->orderBy('name')])
            // النشطة أولاً: الموقوفة أرشيف يُراجَع لا عملٌ يومي
            ->orderByRaw('archived_at is not null')
            ->orderBy('name')
            ->get();

        // استعلاما تجميع لكل التخصصات معاً — لا استعلام لكل بطاقة
        $seatsUsed = Project::query()->tap($running)
            ->join('supervisors', 'supervisors.id', '=', 'projects.supervisor_id')
            ->selectRaw('supervisors.specialize_id, COUNT(*) as total')
            ->groupBy('supervisors.specialize_id')
            ->pluck('total', 'specialize_id');

        $runningProjects = Project::query()->tap($running)
            ->join('specialize_projects', 'specialize_projects.id', '=', 'projects.specialize_project_id')
            ->selectRaw('specialize_projects.specialize_id, COUNT(*) as total')
            ->groupBy('specialize_projects.specialize_id')
            ->pluck('total', 'specialize_id');

        foreach ($specializes as $spec) {
            $spec->seats_total = (int) $spec->seats_total;
            $spec->seats_used = (int) ($seatsUsed[$spec->id] ?? 0);
            $spec->running_count = (int) ($runningProjects[$spec->id] ?? 0);
        }

        $showArchived = request('view') === 'archived';
        $active = $specializes->reject->isArchived();

        return view('dashboard.admin.setting.specialize.index', [
            'specializes' => $showArchived ? $specializes->filter->isArchived() : $active,
            'countActive' => $active->count(),
            'countArchived' => $specializes->filter->isArchived()->count(),
            'showArchived' => $showArchived,
            // الملخّص من المجموعات نفسها — للنشطة وحدها
            'summary' => [
                'students' => $active->sum('students_count'),
                'withoutTeam' => $active->sum(fn ($s) => $s->students_count - $s->students_in_team_count),
                'seatsFree' => $active->sum(fn ($s) => max(0, $s->seats_total - $s->seats_used)),
                'seatsTotal' => $active->sum('seats_total'),
                'running' => $active->sum('running_count'),
                'ready' => $active->filter(fn ($s) => $s->projects_count > 0 && $s->supervisors_available_count > 0)->count(),
            ],
        ]);
    }

    /**
     * إيقاف التخصص.
     *
     * البديل عن حذفٍ ممنوع: لا يُسجَّل عليه أحد جديد، وطلابه ومشرفوه
     * ومشاريعه تبقى تعمل كما كانت.
     */
    public function archive($id)
    {
        $specialize = Specialize::find($id);

        if (! $specialize) {
            return redirect()->back()->with('fail', __('لا توجد بيانات!!!'));
        }

        if ($specialize->isArchived()) {
            return redirect()->back()->with('fail', __('التخصص موقوف أصلاً.'));
        }

        $specialize->update(['archived_at' => now()]);

        Audit::record('specialize.archived', $specialize);

        return redirect()->back()->with('success', __('تم إيقاف «:name» — لن يُسجَّل عليه أحد جديد.', ['name' => $specialize->name]));
    }

    /** استئناف تخصص موقوف */
    public function restore($id)
    {
        $specialize = Specialize::find($id);

        if (! $specialize) {
            return redirect()->back()->with('fail', __('لا توجد بيانات!!!'));
        }

        $specialize->update(['archived_at' => null]);

        Audit::record('specialize.restored', $specialize);

        return redirect()->back()->with('success', __('تم استئناف «:name».', ['name' => $specialize->name]));
    }

    public function store(SpecializeRequest $request)
    {
        try {
            // dd($request->all());

            // لا \u200E$request->all()\u200E: \u200Earchived_at\u200E قابل للإسناد، والأرشفة فعلٌ له مساره
            Specialize::create($request->only('name'));
            return redirect()->route("admin.specialize.index")->with('success', __('تم اضافة السجل بنجاح'));

        } catch (\Exception$ex) {

            return back()->with('fail', __('حدث خطأ .. الرجاء المحاولة مرة أخرى'));
        }

    }

    public function update(SpecializeRequest $request)
    {
        try {
            $specialize = Specialize::where('id', $request->id)->first();
            if (!$specialize) {
                return redirect()->back()->with('fail', __('لا توجد بيانات!!!'));
            }

            $specialize->update($request->only('name'));
            return redirect()->back()->with('success', __('تم تعديل السجل بنجاح'));

        } catch (\Exception$ex) {

            return back()->with('fail', __('حدث خطأ .. الرجاء المحاولة مرة أخرى'));

        }

    }

    public function destroy($id)
    {
        try {
            $specialize = Specialize::where('id', request()->id)->first();
            if (!$specialize) {
                return redirect()->back()->with('fail', __('لا توجد بيانات!!!'));
            }

            // حماية: لا حذف لتخصص مرتبط بطلاب أو مشرفين — وإلا يفقدون تصنيفهم
            $hasUsers = \App\Models\Student::where('specialize_id', $specialize->id)->exists()
                || \App\Models\Supervisor::where('specialize_id', $specialize->id)->exists();
            if ($hasUsers) {
                return redirect()->back()->with('fail',
                    __('لا يمكن حذف التخصص — يوجد طلاب أو مشرفون مسجلون عليه. انقلهم لتخصص آخر أولاً.'));
            }

            $specialize->delete();
            return redirect()->back()->with('success', __('تم حذف السجل بنجاح'));

        } catch (\Exception$ex) {

            return back()->with('fail', __('حدث خطأ .. الرجاء المحاولة مرة أخرى'));

        }

    }
}
