<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SupervisorsExport;
use App\Http\Controllers\Controller;
use App\Support\EditRecord;
use App\Http\Requests\Admin\SupervisorRequest;
use App\Http\Requests\UploadExcelFileRequest;
use App\Imports\SupervisorsImport;
use App\Models\Semester;
use App\Models\Specialize;
use App\Models\Supervisor;
use App\Support\TeamHealth;
use App\Models\Project;
use App\Support\Audit;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SupervisorController extends Controller
{

    public function checkRequiredTables()
    {
        // \u200Eactive()\u200E لا \u200Ecount()\u200E: تخصصات كلها موقوفة = لا مكان لمشرف جديد
        if (Specialize::active()->count() == 0) {
            return redirect()->route('admin.specialize.index')->with('fail', 'الرجاء ادخال تخصص نشط أو أكثر');
        }
    }

    public function index()
    {
        // كانت قيمة الإرجاع مُهمَلة، فتُفتح الصفحة بلا تخصصات ونموذج
        // الإضافة بقائمة فارغة — فيفشل الحفظ بلا سبب مفهوم
        if ($redirect = $this->checkRequiredTables()) {
            return $redirect;
        }

        $semesterId = Semester::current()->id;

        // قائمتان لا واحدة: الإضافة على تخصص موقوف ممنوعة، لكن البحث
        // عن مشرفيه لا بدّ أن يبقى ممكناً — وإلا اختفوا كلهم
        $allSpecializes = Specialize::select('id', 'name', 'archived_at')->orderBy('name')->get();

        $data['specializes'] = $allSpecializes->reject->isArchived()->values();
        $data['filterSpecializes'] = $allSpecializes;
        $data['editSpecializes'] = $allSpecializes;
        $data['semesterId'] = $semesterId;

        // عدّادات التبويبات: من يستطيع استقبال مجموعة أخرى، ومن تجاوز
        // حدّه أصلاً. كان الحدّ \u200Emax_group\u200E مُخزَّناً ولا يُعرض في أي
        // مكان، فالأدمن يوزّع المشاريع بلا أن يرى السقف.
        $data['currentLoad'] = in_array(request('load'), ['free', 'full', 'over'], true) ? request('load') : null;
        $data['currentSpecialize'] = request()->filled('specialize') ? (int) request('specialize') : null;

        // العدّادات تحترم فلتر التخصص، وإلا قالت الترويسة «٣٠٠ مشرفاً»
        // بينما الجدول يعرض ١٠٥ — والقادم من بطاقة تخصص يصل إلى صفحة
        // تناقض نفسها
        $scope = fn () => Supervisor::query()
            ->when($data['currentSpecialize'], fn ($q) => $q->where('specialize_id', $data['currentSpecialize']));

        $counts = [];
        foreach (['free', 'full', 'over'] as $bucket) {
            $counts[$bucket] = $scope()->whereRaw(
                Supervisor::loadExpression() . ' ' . Supervisor::loadOperator($bucket) . ' supervisors.max_group',
                [$semesterId]
            )->count();
        }

        $data['countAll'] = $scope()->count();
        $data['countFree'] = $counts['free'];
        $data['countFull'] = $counts['full'];
        $data['countOver'] = $counts['over'];

        $data['currentSpecializeName'] = $data['currentSpecialize']
            ? $allSpecializes->firstWhere('id', $data['currentSpecialize'])?->name
            : null;

        return view('dashboard.admin.supervisor.index', $data);
    }

    /** تصدير كشف المشرفين — يحترم الفلاتر المطبَّقة على الجدول */
    public function export()
    {
        return Excel::download(
            new SupervisorsExport(
                request('load'),
                request()->filled('specialize') ? (int) request('specialize') : null
            ),
            'supervisors_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function getData()
    {
        $semesterId = Semester::current()->id;

        // \u200Eselect()\u200E قبل \u200EwithCount()\u200E لا بعده: كان بعده فيستبدل الأعمدة كلّها،
        // فتسقط العدّادات ويظهر عبء كل مشرف ٠ مهما أشرف
        $supervisors = Supervisor::query()
            ->select('supervisors.*')
            ->with(['specialize:id,name'])
            ->withCount(['projects' => function ($q) use ($semesterId) {
                $q->whereIn('status', ['accept', 'complete'])->where('semester_id', $semesterId);
            }])
            // ما ينتظر مراجعته، وأقدمه — المشرف البطيء في الردّ كان لا يُرى
            ->withCount(['pendingReviews' => fn ($q) => $q->where('projects.semester_id', $semesterId)])
            ->withMin(['pendingReviews as oldest_review_at' => fn ($q) => $q->where('projects.semester_id', $semesterId)], 'project_milestones.updated_at');

        // الفلاتر تُطبَّق على الخادم فلا يُحمَّل الكشف كاملاً إلى المتصفّح
        if (in_array(request('load'), ['free', 'full', 'over'], true)) {
            $supervisors->whereRaw(
                Supervisor::loadExpression() . ' ' . Supervisor::loadOperator(request('load')) . ' supervisors.max_group',
                [$semesterId]
            );
        }

        if (request()->filled('specialize')) {
            $supervisors->where('specialize_id', (int) request('specialize'));
        }

        return DataTables::of($supervisors)
            ->addIndexColumn()

            ->editColumn('gender', function ($row) {
                return __('site.' . $row->gender);
            })

            // الهوية في خليّة واحدة: الاسم هو ما تبحث عنه العين وهي
            // تمسح، والبريد بيانات تُستخرج عند الحاجة
            ->addColumn('identity', function ($row) {
                // الصورة إن رفعها المشرف، وإلا أحرفه الأولى — و«د.»
                // تُسقَط منها في \u200EHasAvatar\u200E لأنها بادئة على كل اسم
                // تقريباً فلا تميّز أحداً
                return '<div class="cell-identity">'
                    . \App\Support\Avatar::html($row)
                    . '<span class="cell-identity-body">'
                    . '<span class="cell-name">' . e($row->name) . '</span>'
                    . '<span class="cell-sub" dir="ltr">' . e($row->email) . '</span>'
                    . '</span>'
                    . '</div>';
            })

            ->editColumn('university_id', fn ($row) => '<span class="cell-num" dir="ltr">' . e($row->university_id) . '</span>')

            ->editColumn('phone', fn ($row) => '<span class="cell-num" dir="ltr">' . e($row->phone) . '</span>')

            // عبء الإشراف: كانت شارة تقول «٢ مجموعة» ولا تقول من أصل
            // كم — وهو السؤال الوحيد الذي تُفتح الصفحة من أجله حين
            // يُوزَّع مشروع جديد.
            ->editColumn('projects_count', function ($row) {
                $used = (int) $row->projects_count;
                $max = (int) $row->max_group;

                $state = match (true) {
                    $max > 0 && $used > $max => 'is-over',
                    $max > 0 && $used === $max => 'is-full',
                    default => 'is-free',
                };

                $label = match ($state) {
                    'is-over' => 'تجاوز الحد',
                    'is-full' => 'مكتمل',
                    default => 'متاح',
                };

                $pct = $max > 0 ? min(100, round($used / $max * 100)) : 0;

                return '<a href="' . route('admin.supervisors.groups', $row->id) . '"'
                    . ' class="load-cell ' . $state . '" title="' . e($label) . '">'
                    . '<span class="load-figure">' . e($used) . '<small>/' . e($max) . '</small></span>'
                    . '<span class="load-bar"><span style="width: ' . $pct . '%"></span></span>'
                    . '</a>';
            })

            // التسليمات بانتظاره: «—» حين لا شيء، ولون تحذير إن طال انتظار أقدمها
            ->editColumn('pending_reviews_count', function ($row) {
                $n = (int) $row->pending_reviews_count;

                if ($n === 0) {
                    return '<span class="text-secondary small">—</span>';
                }

                $days = $row->oldest_review_at
                    ? (int) \Illuminate\Support\Carbon::parse($row->oldest_review_at)->startOfDay()->diffInDays(today())
                    : 0;
                $late = $days > \App\Support\TeamHealth::REVIEW_DAYS;

                return '<span class="review-cell' . ($late ? ' is-late' : '') . '"'
                    . ' title="أقدمها منذ ' . e($days) . ' يوماً">'
                    . '<b>' . e($n) . '</b>'
                    . '<small>' . match (true) { $days === 0 => 'اليوم', $days === 1 => 'منذ أمس', default => 'منذ ' . e($days) . ' يوماً' } . '</small>'
                    . '</span>';
            })

            ->addColumn('actions', function ($row) {

                $editBtn = "<button type='button' class='btn-action btn-edit' data-bs-toggle='offcanvas' data-bs-target='#editDrawer'"
                    . " data-record='" . EditRecord::attr(EditRecord::supervisor($row)) . "'"
                    . " title='تعديل' aria-label='تعديل " . e($row->name) . "'><i class='ti ti-pencil'></i></button>";

                $deleteBtn = "<button type='button' class='btn-action btn-action--danger btn-delete' data-bs-toggle='modal' data-bs-target='#deleteModal'
                              data-id='" . e($row->id) . "' data-name='" . e($row->name) . "' title='حذف'>
                              <i class='ti ti-trash'></i>
                          </button>";

                $actionBtn = '<div class="btn-group">' . $deleteBtn . $editBtn . '</div>';

                return $actionBtn;
            })
            ->rawColumns(['identity', 'university_id', 'phone', 'projects_count', 'pending_reviews_count', 'actions'])
            ->make(true);
    }

    public function store(SupervisorRequest $request)
    {
        try {
            // dd($request->all());
            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            $created = Supervisor::create($data);

            // «حفظ وإضافة آخر»: الدرج يُعاد فتحه فارغاً للتالي
            return redirect()->route("admin.supervisors.index")
                ->with('success', "تمت إضافة «{$created->name}»")
                ->with('reopen_create', $request->boolean('another'));

        } catch (\Exception $ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

    }

    public function update(SupervisorRequest $request)
    {
        // return $request;
        try {
            $admin = Supervisor::where('id', $request->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            $admin->update($data);

            // مخرج الأدمن من صورة غير لائقة، بلا تدخّل في القاعدة
            if ($request->boolean('remove_avatar')) {
                $admin->deleteAvatar();
            }

            return redirect()->back()->with('success', "تم تعديل السجل بنجاح");

        } catch (\Exception $ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    public function destroy($id)
    {
        try {
            $admin = Supervisor::where('id', request()->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            // حماية: لا حذف لمشرف لديه مشاريع قائمة — وإلا تُصبح مجموعاته بلا مشرف بصمت
            $hasActiveProjects = \App\Models\Project::where('supervisor_id', $admin->id)
                ->where('status', '!=', 'reject')
                ->exists();
            if ($hasActiveProjects) {
                return redirect()->back()->with('fail',
                    'لا يمكن حذف المشرف — لديه مشاريع/مجموعات قائمة. انقل مجموعاته لمشرف آخر من صفحة المجموعات أولاً.');
            }

            // قبل الحذف: بعده يفقد المشرف اسمه ورقمه الجامعي
            Audit::record('supervisor.deleted', $admin);

            $admin->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception $ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    /**
     * استيراد متزامن بتقرير: كان في الطابور بلا تحقّق، يقول «بدأت عملية الرفع
     * بنجاح» ثم يفشل بصمت إن لم يعمل عامل أو كان في الملف صفّ معطوب.
     */
    /** قالب الاستيراد بأعمدته الصحيحة وصفّ مثال — انظر \App\Exports\ImportTemplate */
    public function template()
    {
        return Excel::download(new \App\Exports\ImportTemplate('supervisor'), 'supervisors_template.xlsx');
    }

    public function import(UploadExcelFileRequest $request)
    {
        $import = new SupervisorsImport(auth('admin')->id());
        Excel::import($import, $request->file('attachment'));

        $report = $import->report();
        $skipped = count($report['skipped']) + $report['errored'];

        // العدد والمعدود متوافقان: «أُضيف ٣ طلاب — وتُخطّي صفّان» لا «٣ طالب … ٢ صفّاً»
        $count = fn (int $n, array $w) => match (true) {
            $n === 1 => $w[0],
            $n === 2 => $w[1],
            $n <= 10 => "{$n} {$w[2]}",
            default => "{$n} {$w[3]}",
        };

        $message = $report['added']
            ? 'أُضيف ' . $count($report['added'], ['مشرف واحد', 'مشرفان', 'مشرفين', 'مشرفاً'])
            : 'لم يُضف أحد';
        if ($skipped) {
            $message .= ' — وتُخطّي ' . $count($skipped, ['صفّ واحد', 'صفّان', 'صفوف', 'صفّاً']) . '، التفاصيل أعلى الصفحة.';
        }

        return redirect()->back()
            ->with('import_report', $report)
            ->with($report['added'] ? 'success' : 'fail', $message . ($skipped ? '' : '.'));
    }

    /**
     * مجموعات مشرف في الفصل الحالي.
     *
     * كان المتحكّم يحمّل مشاريع الفصل في \u200Eprojects\u200E والعرض يمرّ على
     * \u200EprojectsAccept\u200E (كل الفصول) تحت عنوان الفصل الحالي، وعمود «التخصص»
     * يعرض عنوان المشروع، ورقم مشرف مجهول يُسقط الصفحة بخطأ 500.
     */
    public function groups($id)
    {
        $semester = Semester::current();
        $supervisor = Supervisor::with('specialize')->findOrFail($id);

        // المراحل تُحمَّل مع المشاريع (قليلة لكل مجموعة): التقدّم والمرحلة التالية
        // منها بلا استعلام لكل مشروع. والمشكلات بتعريف TeamHealth نفسه.
        $projects = $semester
            ? $supervisor->projectsAccept()
                ->where('semester_id', $semester->id)
                ->with(['project_type', 'group.student.specialize', 'milestones'])
                ->latest()
                ->get()
            : collect();

        $projects->each(function ($p) {
            $total = $p->milestones->count();
            $done = $p->milestones->where('is_done', true)->count();
            $p->setAttribute('stages_total', $total);
            $p->setAttribute('stages_done', $done);
            $p->setAttribute('progress', $total ? (int) round($done * 100 / $total) : 0);
            $p->setAttribute('next_stage', $p->milestones->first(fn ($m) => ! $m->is_done));
            $p->setAttribute('issues', TeamHealth::issuesFor($p));
        });

        // الفصول السابقة: عدد مجموعاته في كل فصل، ورابط للجدول مُصفّى
        $past = Project::where('supervisor_id', $supervisor->id)
            ->whereIn('status', ['accept', 'complete'])
            ->when($semester, fn ($q) => $q->where('semester_id', '!=', $semester->id))
            ->selectRaw('semester_id, count(*) as n')
            ->groupBy('semester_id')
            ->with('semester')
            ->get()
            ->sortByDesc(fn ($row) => $row->semester->created_at)
            ->values();

        return view('dashboard.admin.supervisor.groups', [
            'supervisor' => $supervisor,
            'semester' => $semester,
            'projects' => $projects,
            'past' => $past,
            'summary' => [
                'groups' => $projects->count(),
                'students' => $projects->sum(fn ($p) => $p->group->count()),
                'progress' => $projects->count() ? (int) round($projects->avg('progress')) : null,
                'issues' => $projects->filter(fn ($p) => $p->issues)->count(),
            ],
        ]);
    }
}
