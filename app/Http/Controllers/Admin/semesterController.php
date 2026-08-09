<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SemesterRequest;
use App\Models\Semester;
use DataTables;

class semesterController extends Controller
{

    public function index()
    {
        return view('dashboard.admin.setting.semester.index');
    }

    public function getData()
    {
        // if (request()->ajax()) {

        $semesters = Semester::select('id', 'name', 'is_active');

        return DataTables::of($semesters)
            ->addIndexColumn()

            // اسم الفصل + شارة "النشط حالياً"
            ->editColumn('name', function ($row) {
                $badge = $row->is_active
                    ? " <span class='badge bg-green-lt text-green ms-2'><i class='ti ti-check me-1'></i>الفصل النشط</span>"
                    : '';

                return e($row->name) . $badge;
            })

            ->addColumn('actions', function ($row) {

                // زر التفعيل: يظهر فقط لغير النشط
                $activateBtn = '';
                if (! $row->is_active) {
                    $activateUrl = route('admin.semesters.activate', ['id' => $row->id]);
                    $csrf = csrf_field();
                    $activateBtn = "<form method='POST' action='{$activateUrl}' class='d-inline'
                                        onsubmit=\"return confirm('تفعيل هذا الفصل كفصل حالي؟ سينتقل النظام كاملاً (التقديم، اللوحات، الإحصائيات) للعمل عليه.')\">
                                        {$csrf}
                                        <button type='submit' class='btn mb-2 btn-outline-primary btn-sm' title='تفعيل كفصل حالي'>
                                            <i class='ti ti-player-play'></i> تفعيل
                                        </button>
                                    </form> ";
                }

                $editBtn = "<a class='btn mb-2 btn-success btn-sm btn-edit' data-bs-toggle='modal' data-bs-target='#editModal'
                              data-id='{$row->id}' data-name='{$row->name}'  title='تعديل'>
                              <i class='ti ti-pencil'></i>
                          </a>";
                $deleteBtn = "<button type='button' class='btn mb-2 btn-danger btn-sm btn-delete' data-bs-toggle='modal' data-bs-target='#deleteModal'
                              data-id='{$row->id}' data-name='{$row->name}' title='حذف'>
                              <i class='ti ti-trash'></i>
                          </button>";

                $actionBtn = $activateBtn . '<div class="btn-group">' . $deleteBtn . $editBtn . '</div>';

                return $actionBtn;
            })
            ->rawColumns(['name', 'actions'])
            ->make(true);

        //}

    }

    public function store(SemesterRequest $request)
    {
        try {
            // الفصل الجديد يُنشأ غير نشط — النظام لا ينتقل إليه إلا بتفعيل صريح من الأدمن.
            // استثناء: أول فصل في النظام يُفعّل تلقائياً.
            $semester = Semester::create($request->only('name'));

            if (! Semester::where('is_active', true)->where('id', '!=', $semester->id)->exists()) {
                $semester->update(['is_active' => true]);
                return redirect()->route('admin.semesters.index')
                    ->with('success', 'تم إضافة الفصل وتفعيله (أول فصل في النظام)');
            }

            return redirect()->route('admin.semesters.index')
                ->with('success', 'تم إضافة الفصل بنجاح — لن ينتقل النظام إليه حتى تضغط "تفعيل"');

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

    }

    /** تفعيل فصل كفصل حالي — ينتقل النظام كاملاً للعمل عليه */
    public function activate($id)
    {
        $semester = Semester::where('id', $id)->first();
        if (!$semester) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        try {
            \DB::transaction(function () use ($semester) {
                Semester::query()->update(['is_active' => false]);
                $semester->update(['is_active' => true]);
            });

            return redirect()->back()->with('success', "تم التفعيل — النظام الآن يعمل على: {$semester->name}");

        } catch (\Exception$ex) {
            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }
    }

    public function update(SemesterRequest $request)
    {
        try {
            $semester = Semester::where('id', $request->id)->first();
            if (!$semester) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $semester->update($request->all());
            return redirect()->back()->with('success', "تم تعديل السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    public function destroy($id)
    {
        try {
            $semester = Semester::where('id', request()->id)->first();
            if (!$semester) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            // حماية الأرشيف: لا حذف لفصل يحتوي مشاريع مسجلة — وإلا تفقد المشاريع ربطها بالفصل
            if (\App\Models\Project::where('semester_id', $semester->id)->exists()) {
                return redirect()->back()->with('fail',
                    'لا يمكن حذف هذا الفصل — يحتوي مشاريع مسجلة، وحذفه يدمر أرشيف النتائج.');
            }

            // لا حذف للفصل النشط — فعّل فصلاً آخر أولاً
            if ($semester->is_active) {
                return redirect()->back()->with('fail',
                    'لا يمكن حذف الفصل النشط — فعّل فصلاً آخر أولاً ثم احذف هذا.');
            }

            $semester->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }
}
