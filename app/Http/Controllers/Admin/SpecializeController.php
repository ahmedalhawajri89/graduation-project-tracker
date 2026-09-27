<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecializeRequest;
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
        $specializes = Specialize::withCount([
            'students',
            'supervisors',
            'supervisorsAvailable',
            'projects',
        ])
            // الأسماء نفسها لا عددها: «ما الأنواع المعرَّفة تحته؟» سؤال
            // كان يحتاج نقرة وانتقالاً إلى صفحة أخرى لكل تخصص
            ->with(['projects' => fn ($q) => $q->select('id', 'specialize_id', 'name', 'min', 'max')->orderBy('name')])
            // النشطة أولاً: الموقوفة أرشيف يُراجَع لا عملٌ يومي
            ->orderByRaw('archived_at is not null')
            ->orderBy('name')
            ->get();

        $showArchived = request('view') === 'archived';

        return view('dashboard.admin.setting.specialize.index', [
            'specializes' => $showArchived
                ? $specializes->filter->isArchived()
                : $specializes->reject->isArchived(),
            'countActive' => $specializes->reject->isArchived()->count(),
            'countArchived' => $specializes->filter->isArchived()->count(),
            'showArchived' => $showArchived,
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
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        if ($specialize->isArchived()) {
            return redirect()->back()->with('fail', 'التخصص موقوف أصلاً.');
        }

        $specialize->update(['archived_at' => now()]);

        Audit::record('specialize.archived', $specialize);

        return redirect()->back()->with('success', "تم إيقاف «{$specialize->name}» — لن يُسجَّل عليه أحد جديد.");
    }

    /** استئناف تخصص موقوف */
    public function restore($id)
    {
        $specialize = Specialize::find($id);

        if (! $specialize) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        $specialize->update(['archived_at' => null]);

        Audit::record('specialize.restored', $specialize);

        return redirect()->back()->with('success', "تم استئناف «{$specialize->name}».");
    }

    public function store(SpecializeRequest $request)
    {
        try {
            // dd($request->all());

            // لا \u200E$request->all()\u200E: \u200Earchived_at\u200E قابل للإسناد، والأرشفة فعلٌ له مساره
            Specialize::create($request->only('name'));
            return redirect()->route("admin.specialize.index")->with('success', "تم اضافة السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

    }

    public function update(SpecializeRequest $request)
    {
        try {
            $specialize = Specialize::where('id', $request->id)->first();
            if (!$specialize) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $specialize->update($request->only('name'));
            return redirect()->back()->with('success', "تم تعديل السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    public function destroy($id)
    {
        try {
            $specialize = Specialize::where('id', request()->id)->first();
            if (!$specialize) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            // حماية: لا حذف لتخصص مرتبط بطلاب أو مشرفين — وإلا يفقدون تصنيفهم
            $hasUsers = \App\Models\Student::where('specialize_id', $specialize->id)->exists()
                || \App\Models\Supervisor::where('specialize_id', $specialize->id)->exists();
            if ($hasUsers) {
                return redirect()->back()->with('fail',
                    'لا يمكن حذف التخصص — يوجد طلاب أو مشرفون مسجلون عليه. انقلهم لتخصص آخر أولاً.');
            }

            $specialize->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }
}
