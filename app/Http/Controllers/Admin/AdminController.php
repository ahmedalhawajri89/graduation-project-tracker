<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdministratorsRequest;
use App\Models\Admin;
use DataTables;

class AdminController extends Controller
{

    public function index()
    {
        return view('dashboard.admin.adminData.index');
    }

    public function getData()
    {
        // if (request()->ajax()) {

        $admins = Admin::select('id', 'name', 'email', 'phone', 'gender')->where('id', '!=', auth()->id());

        return DataTables::of($admins)
            ->addIndexColumn()

            ->editColumn('gender', function ($row) {
                return __('site.' . $row->gender);
            })

            ->addColumn('actions', function ($row) {

                $editBtn = "<a class='btn mb-2 btn-success btn-sm btn-edit' data-bs-toggle='modal' data-bs-target='#editModal'
                              data-id='{$row->id}' data-name='{$row->name}' data-email='{$row->email}'
                              data-phone='{$row->phone}' data-gender='{$row->gender}' title='تعديل'>
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

        // }

    }

    public function store(AdministratorsRequest $request)
    {
        try {
            // dd($request->all());
            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            Admin::create($data);
            return redirect()->route("admin.administrators.index")->with('success', "تم اضافة السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');
        }

    }

    public function update(AdministratorsRequest $request)
    {
        try {
            $admin = Admin::where('id', $request->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            $admin->update($data);
            return redirect()->back()->with('success', "تم تعديل السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    public function destroy($id)
    {
        try {
            $admin = Admin::where('id', request()->id)->first();
            if (!$admin) {
                return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
            }

            $admin->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception$ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }
}
