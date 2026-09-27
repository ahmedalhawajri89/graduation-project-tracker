<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SemesterRequest;
use App\Models\Project;
use App\Models\Semester;

class semesterController extends Controller
{

    /**
     * كانت جدول DataTables خادمياً بعمودين لحفنة صفوف، وزرّ التفعيل
     * نموذجاً محشوراً في خليّة يسأل بـ \u200Econfirm()\u200E المتصفّح.
     *
     * والتفعيل أخطر مفتاح في النظام: ينقل التقديم واللوحات والإحصائيات
     * كلها إلى فصل آخر. ولم تكن الصفحة تقول أي فصل يعمل النظام عليه
     * الآن، ولا كم مشروعاً في كل فصل — وهو كل قيمة الأرشيف.
     */
    public function index()
    {
        $semesters = Semester::withCount('projects')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $active = $semesters->firstWhere('is_active', true);

        // توزيع حالات الفصل الحالي — من نفس مصدر بقية اللوحة
        $statusCounts = $active
            ? Project::where('semester_id', $active->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        return view('dashboard.admin.setting.semester.index', [
            'semesters' => $semesters,
            'active' => $active,
            'archive' => $semesters->where('is_active', false)->values(),
            'statusCounts' => $statusCounts,
        ]);
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

            // \u200E$request->all()\u200E و\u200Eis_active\u200E قابل للإسناد الجماعي: طلبٌ
            // مُلفَّق كان يُفعّل فصلاً بلا إلغاء تفعيل الباقي، فيصير في
            // النظام فصلان نشطان و\u200ESemester::current()\u200E تختار أحدهما
            // اعتباطاً. التفعيل له مساره الخاص وحده.
            $semester->update($request->only('name'));
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

            // حماية الأرشيف: لا حذف لفصل يحتوي مشاريع مسجلة — وإلا تفقد المشاريع ربطها بالفصل.
            // \u200EwithTrashed()\u200E مقصودة: المشروع المحذوف حذفاً ناعماً قابل
            // للاسترجاع، وحذف فصله يُرجعه بلا فصل.
            if (Project::withTrashed()->where('semester_id', $semester->id)->exists()) {
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
