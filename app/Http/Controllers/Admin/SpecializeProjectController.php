<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecializeProjectRequest;
use App\Models\Specialize;
use App\Models\SpecializeProject;
use DataTables;
use Illuminate\Http\Request;

class SpecializeProjectController extends Controller
{
    public function index($specialize_id)
    {
        $specialize = Specialize::where('id', $specialize_id)->first();
        if (!$specialize) {
            return redirect()->route('admin.specialize.index')->with('fail', 'لا توجد بيانات!!!');
        }

        return view('dashboard.admin.setting.specialize.project.index', compact('specialize'));
    }

    public function getData($specialize_id)
    {
        // if (request()->ajax()) {

        $projects = SpecializeProject::select('id', 'min', 'max')
            ->where('specialize_id', $specialize_id)
            ->select('specialize_projects.*');

        return DataTables::of($projects)
            ->addIndexColumn()

            ->editColumn('min', function ($row) {
                return "{$row->min} عضو";
            })

            ->editColumn('max', function ($row) {
                return "{$row->max} عضو";
            })

            ->addColumn('actions', function ($row) {

                $editBtn = "<a class='btn mb-2 btn-success btn-sm btn-edit' data-bs-toggle='modal' data-bs-target='#editModal'
                              data-id='{$row->id}' data-name='{$row->name}' data-min='{$row->min}'
                              data-max='{$row->max}' title='تعديل'>
                              <i class='ti ti-pencil'></i>
                          </a>";
                $deleteBtn = "<button type='button' class='btn mb-2 btn-danger btn-sm btn-delete' data-bs-toggle='modal' data-bs-target='#deleteModal'
                              data-id='{$row->id}' data-name='{$row->name}' title='حذف'>
                              <i class='ti ti-trash'></i>
                          </button>";

                $actionBtn = '<div class="btn-group">' . $deleteBtn . $editBtn . '</div>';

                return $actionBtn;
            })
            ->rawColumns(['actions'])
            ->make(true);

        //}

    }

    public function store(SpecializeProjectRequest $request)
    {
        try {
            // dd($request->all());

            SpecializeProject::create($request->all());
            return redirect()->route("admin.specialize.projects.index", $request->specialize_id)->with('success', "تم اضافة السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى' . $ex->getMessage());
        }

    }

    public function update(SpecializeProjectRequest $request)
    {
        try {
            $specialize = SpecializeProject::where('id', $request->id)->first();
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
            $specialize = SpecializeProject::where('id', request()->id)->first();
            if (!$specialize) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $specialize->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }
}
