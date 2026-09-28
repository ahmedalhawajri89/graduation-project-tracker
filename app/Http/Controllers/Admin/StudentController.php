<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\EditRecord;
use App\Http\Requests\Admin\StudentRequest;
use App\Exports\StudentsExport;
use App\Http\Requests\UploadExcelFileRequest;
use App\Imports\StudentsImport;
use App\Models\Specialize;
use App\Models\Student;
use App\Support\Audit;
use DataTables;
use Excel;

class StudentController extends Controller
{

    public function checkRequiredTables()
    {
        // \u200Eactive()\u200E لا \u200Ecount()\u200E: تخصصات كلها موقوفة = لا مكان لطالب جديد
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

        // قائمتان لا واحدة: كان \u200E$specializes\u200E يغذّي نوافذ الإضافة
        // وقائمة التصفية معاً. الإضافة على تخصص موقوف ممنوعة، لكن
        // البحث عن طلابه لا بدّ أن يبقى ممكناً — وإلا اختفوا كلهم.
        $specializes = Specialize::select('id', 'name', 'archived_at')->orderBy('name')->get();

        $data['specializes'] = $specializes->reject->isArchived()->values();
        $data['filterSpecializes'] = $specializes;
        $data['editSpecializes'] = $specializes;
        $data['currentGroupFilter'] = in_array(request('group'), ['in', 'none'], true) ? request('group') : null;
        $data['currentSpecialize'] = request()->filled('specialize') ? (int) request('specialize') : null;

        // العدّادات كانت تحسب كل الطلاب دائماً، فتقول الترويسة «٥٠٠
        // طالباً» بينما الجدول يعرض ١٧٩ — والقادم من بطاقة تخصص يصل
        // إلى صفحة تناقض نفسها. الفلتر يُطبَّق عليها كما يُطبَّق على
        // الجدول. (تبويبات الانضمام لا تدخل هنا: كل تبويب يعرض عدده.)
        $scope = fn () => Student::query()
            ->when($data['currentSpecialize'], fn ($q) => $q->where('specialize_id', $data['currentSpecialize']));

        $data['countAll'] = $scope()->count();
        $data['countInGroup'] = $scope()
            ->whereHas('groups.project', fn ($q) => $q->whereIn('status', ['accept', 'complete']))
            ->count();
        $data['countNoGroup'] = $data['countAll'] - $data['countInGroup'];

        $data['currentSpecializeName'] = $data['currentSpecialize']
            ? $specializes->firstWhere('id', $data['currentSpecialize'])?->name
            : null;

        return view('dashboard.admin.student.index', $data);
    }

    /** تصدير كشف الطلاب — يحترم الفلاتر المطبَّقة على الجدول */
    public function export()
    {
        return Excel::download(
            new StudentsExport(
                request('group'),
                request()->filled('specialize') ? (int) request('specialize') : null
            ),
            'students_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function getData()
    {

        // if (request()->ajax()) {

        // كان يحمّل كل صفوف المجموعات لكل طالب ليختبر وجودها فقط.
        // نحمّل الآن المشروع نفسه (عمودين) لأن الأدمن يحتاج أن يعرف
        // أين الطالب لا أن يعرف «نعم/لا» فحسب.
        $active = fn ($q) => $q->whereIn('status', ['accept', 'complete']);

        $students = Student::select('students.*')
            ->with([
                'specialize:id,name',
                'groups' => fn ($q) => $q->whereHas('project', $active)
                    ->with('project:id,title')
                    ->limit(1),
            ]);

        // الفلاتر تُطبَّق على الخادم فلا يُحمَّل ٥٠٠ صفّ إلى المتصفح
        if (request('group') === 'in') {
            $students->whereHas('groups.project', $active);
        } elseif (request('group') === 'none') {
            $students->whereDoesntHave('groups.project', $active);
        }

        if (request()->filled('specialize')) {
            $students->where('specialize_id', (int) request('specialize'));
        }

        return DataTables::of($students)
            ->addIndexColumn()

            ->editColumn('gender', function ($row) {
                return __('site.' . $row->gender);
            })

            // الهوية في خليّة واحدة: الاسم هو ما تبحث عنه العين وهي
            // تمسح، والبريد بيانات تُستخرج عند الحاجة. الأحرف الأولى
            // تعطي العين مرساة تنزل عليها عبر مئات الصفوف.
            ->addColumn('identity', function ($row) {
                // الصورة إن رفعها الطالب، وإلا أحرفه الأولى. ومع
                // عائلات متكرّرة و«آية» خمس مرات، هي أسرع ما تميّز به
                // العين صفّاً عن صفّ.
                return '<div class="cell-identity">'
                    . \App\Support\Avatar::html($row)
                    . '<span class="cell-identity-body">'
                    . '<span class="cell-name">' . e($row->name) . '</span>'
                    . '<span class="cell-sub" dir="ltr">' . e($row->email) . '</span>'
                    . '</span>'
                    . '</div>';
            })

            // مُعرِّفات رقمية: tabular-nums تُصفّ خاناتها عمودياً فتُقارَن بالعين
            ->editColumn('university_id', function ($row) {
                return '<span class="cell-num" dir="ltr">' . e($row->university_id) . '</span>';
            })

            ->editColumn('phone', function ($row) {
                return '<span class="cell-num" dir="ltr">' . e($row->phone) . '</span>';
            })

            ->addColumn('isGroup', function ($row) {
                // كانت ✓ خضراء أو ✗ حمراء: تقول «نعم/لا» ولا تقول أين
                $project = $row->groups->first()?->project;

                if (! $project) {
                    return "<span class='no-team'>بلا فريق</span>";
                }

                return "<a href='" . route('admin.groups.show', $project->id) . "' class='team-link'>"
                    . e($project->title) . "</a>";
            })

            ->addColumn('actions', function ($row) {

                $editBtn = "<button type='button' class='btn-action btn-edit' data-bs-toggle='offcanvas' data-bs-target='#editDrawer'"
                    . " data-record='" . EditRecord::attr(EditRecord::student($row)) . "'"
                    . " title='تعديل' aria-label='تعديل " . e($row->name) . "'><i class='ti ti-pencil'></i></button>";

                $deleteBtn = "<button type='button' class='btn-action btn-action--danger btn-delete' data-bs-toggle='modal' data-bs-target='#deleteModal'
                              data-id='" . e($row->id) . "' data-name='" . e($row->name) . "' title='حذف'>
                              <i class='ti ti-trash'></i>
                          </button>";

                $actionBtn = '<div class="btn-group">' . $deleteBtn . $editBtn . '</div>';

                return $actionBtn;
            })
            ->rawColumns(['identity', 'university_id', 'phone', 'isGroup', 'actions'])
            ->make(true);

        //}

    }

    public function store(StudentRequest $request)
    {
        $this->checkRequiredTables();

        try {
            // dd($request->all());
            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            Student::create($data);
            return redirect()->route("admin.students.index")->with('success', "تم اضافة السجل بنجاح");

        } catch (\Exception $ex) {

            // نصّ الاستثناء يكشف الجدول والأعمدة والمضيف والمنفذ —
            // يُسجَّل للمطوّر ولا يُعرض للمستخدم
            \Illuminate\Support\Facades\Log::error('فشل إضافة طالب', ['exception' => $ex]);

            return back()->with('fail', 'تعذّرت إضافة الطالب. تأكّد أن الرقم الجامعي والبريد غير مستعملين.');
        }

    }

    public function update(StudentRequest $request)
    {

        try {
            $admin = Student::where('id', $request->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            $admin->update($data);

            // صورة واحدة غير لائقة يجب ألّا تعني تدخّلاً في قاعدة
            // البيانات: للأدمن مخرجٌ من نافذة التعديل نفسها
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
            $admin = Student::where('id', request()->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            // حماية: لا حذف لطالب منضم لفريق نشط — وإلا يبقى صف فارغ في فريقه
            $inActiveGroup = $admin->groups()
                ->whereHas('project', function ($q) {
                    $q->where('status', '!=', 'reject');
                })
                ->exists();
            if ($inActiveGroup) {
                return redirect()->back()->with('fail',
                    'لا يمكن حذف الطالب — منضم لفريق مشروع نشط. عالج وضع مجموعته من صفحة المجموعات أولاً.');
            }

            // قبل الحذف: بعده يفقد الطالب اسمه ورقمه الجامعي
            Audit::record('student.deleted', $admin);

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
    public function import(UploadExcelFileRequest $request)
    {
        $import = new StudentsImport(auth('admin')->id());
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
            ? 'أُضيف ' . $count($report['added'], ['طالب واحد', 'طالبان', 'طلاب', 'طالباً'])
            : 'لم يُضف أحد';
        if ($skipped) {
            $message .= ' — وتُخطّي ' . $count($skipped, ['صفّ واحد', 'صفّان', 'صفوف', 'صفّاً']) . '، التفاصيل أعلى الصفحة.';
        }

        return redirect()->back()
            ->with('import_report', $report)
            ->with($report['added'] ? 'success' : 'fail', $message . ($skipped ? '' : '.'));
    }

}
