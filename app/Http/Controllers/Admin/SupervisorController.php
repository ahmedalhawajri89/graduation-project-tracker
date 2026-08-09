<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupervisorRequest;
use App\Http\Requests\UploadExcelFileRequest;
use App\Imports\SupervisorsImport;
use App\Models\Semester;
use App\Models\Specialize;
use App\Models\Supervisor;
use DataTables;
use Excel;

class SupervisorController extends Controller
{

    public function checkRequiredTables()
    {
        if (Specialize::count() == 0) {
            return redirect()->route('admin.specialize.index')->with('fail', 'الرجاء ادخال تخصص أو أكثر');
        }
    }

    public function index()
    {
        // $supervisors = Supervisor::get();
        // foreach ($supervisors as $supvisor) {
        //     $supvisor->update(['max_group' => 2]);
        // }
        $this->checkRequiredTables();

        $specializes = Specialize::select('id', 'name')->orderBy('name')->get();
        return view('dashboard.admin.supervisor.index', compact('specializes'));
    }

    public function getData()
    {
        // if (request()->ajax()) {

        $last_semester = Semester::current();
        $supervisors = Supervisor::select('id', 'name', 'university_id', 'specialize_id', 'email', 'phone', 'gender', 'max_group')
            ->with(['specialize' => function ($q) {
                return $q->select('id', 'name');
            }])
            ->select('supervisors.*')
            ->withCount(['projects' => function ($q) use ($last_semester) {
                $q->whereIn('status', ['accept', 'complete'])->where('semester_id', $last_semester->id);
            }]);

        return DataTables::of($supervisors)
            ->addIndexColumn()

            ->editColumn('gender', function ($row) {
                return __('site.' . $row->gender);
            })

            ->editColumn('projects_count', function ($row) {
                return "<a href='" . route('admin.supervisors.groups', $row->id) . "' class='btn btn-info'>{$row->projects_count} مجموعة</a>";
            })

            ->addColumn('actions', function ($row) {

                $editBtn = "<a class='btn mb-2 btn-success btn-sm btn-edit' data-bs-toggle='modal' data-bs-target='#editModal'
                              data-id='{$row->id}' data-name='{$row->name}' data-email='{$row->email}'
                              data-phone='{$row->phone}' data-gender='{$row->gender}' data-max_group='{$row->max_group}'
                              data-university_id='{$row->university_id}' data-specialize_id='{$row->specialize_id}' title='تعديل'>
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

    public function store(SupervisorRequest $request)
    {
        try {
            // dd($request->all());
            $data = $request->except('password');
            if ($request->has('password') && $request->password) {
                $data['password'] = bcrypt($request->password);
            }

            Supervisor::create($data);
            return redirect()->route("admin.supervisors.index")->with('success', "تم اضافة السجل بنجاح");

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

            $admin->delete();
            return redirect()->back()->with('success', "تم حذف السجل بنجاح");

        } catch (\Exception $ex) {

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى');

        }

    }

    public function import(UploadExcelFileRequest $request)
    {

        $file = $request->file('attachment');
        $admin_id = auth()->id();

        Excel::queueImport(new SupervisorsImport($admin_id), $file);

        // Excel::import(new StudentsImport($admin_id), $file);

        return redirect()->back()->with([
            'success' => 'بدأت عملية الرفع بنجاح',
        ]);

    }

    public function groups($id)
    {
        $last_semester = Semester::current();
        $data['semester'] = $last_semester;
        $data['supervisor'] = Supervisor::where('id', $id)
            ->select('id', 'name')
            ->with(['projects' => function ($q) use ($last_semester) {
                $q->whereIn('status', ['accept', 'complete'])
                    ->where('semester_id', $last_semester->id)
                    ->with(['group' => function ($q) {
                        $q->with(['student' => function ($q) {
                            $q->with('specialize');
                        }]);
                    }]);
            }])
            ->first();
        return view('dashboard.admin.supervisor.groups', $data);
    }

}
