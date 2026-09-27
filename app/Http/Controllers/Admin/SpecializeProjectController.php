<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecializeProjectRequest;
use App\Models\Specialize;
use App\Models\SpecializeProject;

class SpecializeProjectController extends Controller
{
    /**
     * كانت الصفحة جدول DataTables خادمياً — بترقيم وبحث وفرز عبر ajax —
     * لنوعين اثنين. ونصّ البحث كان «ابحث بالاسم أو الرقم أو البريد»
     * مسرَّباً من صفحة الطلاب، ولا بريد هنا أصلاً.
     *
     * والعدد الوحيد الذي يُبنى عليه قرار هنا لم يكن معروضاً: كم مشروعاً
     * يستعمل هذا النوع فعلاً. من دونه، تعديل حدود الفريق أو حذف النوع
     * يقع في العتمة.
     */
    public function index($specialize_id)
    {
        $specialize = Specialize::where('id', $specialize_id)->first();
        if (!$specialize) {
            return redirect()->route('admin.specialize.index')->with('fail', 'لا توجد بيانات!!!');
        }

        $types = SpecializeProject::where('specialize_id', $specialize->id)
            ->withCount('projects')
            ->orderBy('name')
            ->get();

        return view('dashboard.admin.setting.specialize.project.index', compact('specialize', 'types'));
    }

    public function store(SpecializeProjectRequest $request)
    {
        try {
            // dd($request->all());

            SpecializeProject::create($request->only(['name', 'specialize_id', 'min', 'max']));
            return redirect()->route("admin.specialize.projects.index", $request->specialize_id)->with('success', "تم اضافة السجل بنجاح");

        } catch (\Exception$ex) {

            \Illuminate\Support\Facades\Log::error('فشل إضافة نوع مشروع', ['exception' => $ex]);

            return back()->with('fail', 'تعذّرت إضافة النوع. تأكّد أن الاسم غير مستعمل في هذا التخصص.');
        }

    }

    public function update(SpecializeProjectRequest $request)
    {
        try {
            $specialize = SpecializeProject::where('id', $request->id)->first();
            if (!$specialize) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            // بلا \u200Especialize_id\u200E: نقل نوعٍ مستعمَل إلى تخصص آخر يكسر حدود فرقه القائمة
            $specialize->update($request->only(['name', 'min', 'max']));
            return redirect()->back()->with('success', "تم تعديل السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    public function destroy($id)
    {
        try {
            $specialize = SpecializeProject::where('id', request()->id)->first();
            if (!$specialize) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            // حماية: \u200Eprojects.specialize_project_id\u200E مفتاح \u200EnullOnDelete\u200E،
            // فحذف نوع مستعمَل يترك مشاريع قائمة بلا نوع — وصفحة
            // المجموعات تقرأ \u200Eproject_type->name\u200E و\u200E->max\u200E لحجم الفريق،
            // فينكسر عرضها ويسقط التحقّق من حجم الفرق.
            $inUse = $specialize->projects()->count();
            if ($inUse > 0) {
                return redirect()->back()->with('fail',
                    "لا يمكن حذف «{$specialize->name}» — يستعمله {$inUse} مشروعاً. المشاريع القائمة ستفقد نوعها وحدود فريقها.");
            }

            $specialize->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }
}
