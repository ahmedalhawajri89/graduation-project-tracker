<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecializeRequest;
use App\Models\Specialize;
use DataTables;

class SpecializeController extends Controller
{

    public function index()
    {
        return view('dashboard.admin.setting.specialize.index');
    }

    public function getData()
    {
        // if (request()->ajax()) {

        $specializes = Specialize::select('id', 'name')
            ->withCount('projects');
        // ->select('specializes.*');

        return DataTables::of($specializes)
            ->addIndexColumn()

            ->editColumn('projects_count', function ($row) {
                return "<a class='btn btn-info' href='" . route('admin.specialize.projects.index', $row->id) . "'>{$row->projects_count} مشروع</a>";
            })

            ->addColumn('actions', function ($row) {

                $editBtn = "<a class='btn mb-2 btn-success btn-sm btn-edit' data-bs-toggle='modal' data-bs-target='#editModal'
                              data-id='{$row->id}' data-name='{$row->name}'  title='تعديل'>
                              <i class='ti ti-pencil'></i>
                          </a>";
                $deleteBtn = "<button type='button' class='btn mb-2 btn-danger btn-sm btn-delete' data-bs-toggle='modal' data-bs-target='#deleteModal'
                              data-id='{$row->id}' data-name='{$row->name}' title='حذف'>
                              <i class='ti ti-trash'></i>
                          </button>";

                $actionBtn = '<div class="btn-group">' . $deleteBtn . $editBtn . '</div>';

                return $actionBtn;
            })
            ->rawColumns(['projects_count', 'actions'])
            ->make(true);

        //}

    }

    public function store(SpecializeRequest $request)
    {
        try {
            // dd($request->all());

            Specialize::create($request->all());
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

            $specialize->update($request->all());
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
