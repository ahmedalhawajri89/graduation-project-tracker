<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StudentRequest;
use App\Http\Requests\UploadExcelFileRequest;
use App\Imports\StudentsImport;
use App\Models\Specialize;
use App\Models\Student;
use DataTables;
use Excel;

class StudentController extends Controller
{

    public function checkRequiredTables()
    {
        if (Specialize::count() == 0) {
            return redirect()->route('admin.specialize.index')->with('fail', 'الرجاء ادخال تخصص أو أكثر');
        }
    }

    public function index()
    {
        $this->checkRequiredTables();
        $specializes = Specialize::select('id', 'name')->orderBy('name')->get();
        return view('dashboard.admin.student.index', compact('specializes'));
    }

    public function getData()
    {

        // if (request()->ajax()) {

        $students = Student::select('id', 'name', 'university_id', 'specialize_id', 'email', 'phone', 'gender')
            ->with([
                'specialize' => function ($q) {
                    return $q->select('id', 'name');
                },
                'groups' => function ($q) {
                    $q->whereHas('project', function ($q) {
                        return $q->whereIn('status', ['accept', 'complete']);
                    });
                },
            ])
            ->select('students.*');

        return DataTables::of($students)
            ->addIndexColumn()

            ->editColumn('gender', function ($row) {
                return __('site.' . $row->gender);
            })

            ->addColumn('isGroup', function ($row) {
                return $row->groups->count() > 0 ? "<span class='ti ti-check text-green fs-2'></span>" : "<span class='ti ti-x text-red fs-2'></span>";
            })

            ->addColumn('actions', function ($row) {

                $editBtn = "<a class='btn mb-2 btn-success btn-sm btn-edit' data-bs-toggle='modal' data-bs-target='#editModal'
                              data-id='{$row->id}' data-name='{$row->name}' data-email='{$row->email}'
                              data-phone='{$row->phone}' data-gender='{$row->gender}'
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
            ->rawColumns(['isGroup', 'actions'])
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

            return back()->with('fail', 'حدث خطأ .. الرجاء المحاولة مرة أخرى ' . $ex->getMessage());
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

        Excel::queueImport(new StudentsImport($admin_id), $file);
        // Excel::import(new StudentsImport($admin_id), $file);

        return redirect()->back()->with([
            'success' => 'بدأت عملية الرفع بنجاح',
        ]);

    }

}
