<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProjectsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupRequest;
use App\Models\Project;
use App\Models\Semester;
use App\Models\SpecializeProject;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\AdminChangeGroupNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class GroupsController extends Controller
{

    public function index()
    {
        $data['supervisors'] = Supervisor::select('id', 'name')->get();
        $data['project_type'] = SpecializeProject::select('id', 'name')->get();
        $data['semesters'] = Semester::latest()->get(['id', 'name']);

        $last_semester = Semester::current();

        // فلتر الفصل الدراسي: الافتراضي الفصل الحالي، ويمكن تصفح أرشيف الفصول السابقة
        $semesterId = request()->filled('semester') ? (int) request()->semester : $last_semester->id;
        $data['currentSemesterId'] = $semesterId;

        $supervisor = isset(request()->supervisor) && !empty(request()->supervisor) ? request()->supervisor : ((request()->supervisor === '0') ? (int) request()->supervisor : null);
        $type = isset(request()->type) && !empty(request()->type) ? request()->type : ((request()->type === '0') ? (int) request()->type : null);

        $projects = Project::where('semester_id', $semesterId)
            ->whereIn('status', ['accept', 'complete'])
            ->with([
                'group' => function ($q) {
                    $q->with('student');
                },
                'supervisor',
                'project_type',
            ]);

        if ($supervisor !== null) {
            $projects = $projects->where('supervisor_id', $supervisor);
        }

        if ($type !== null) {
            $projects = $projects->where('specialize_project_id', $type);
        }

        $data['projects'] = $projects->paginate(15);
        return view('dashboard.admin.group.index', $data);
    }

    /** تصدير كشف نتائج فصل دراسي (Excel) */
    public function export()
    {
        $last_semester = Semester::current();
        $semesterId = request()->filled('semester') ? (int) request()->semester : $last_semester->id;

        return Excel::download(new ProjectsExport($semesterId), "projects_semester_{$semesterId}.xlsx");
    }

    /** عرض تفاصيل مشروع (قراءة فقط) — رقابة إدارية كاملة دون تدخل */
    public function show($id)
    {
        $project = Project::where('id', $id)
            ->with([
                'group.student.specialize',
                'supervisor.specialize',
                'project_type',
                'semester',
                'milestones',
                'files',
                'comments.author',
            ])
            ->first();

        if (!$project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        return view('dashboard.admin.group.show', ['project' => $project]);
    }

    public function edit($id)
    {
        $project = Project::where('id', $id)
            ->with([
                'group' => function ($q) {
                    $q->with(['student' => function ($q) {
                        $q->with('specialize');
                    }]);

                },

                'supervisor' => function ($q) {
                    $q->with('specialize');

                },
                'project_type',
            ])
            ->first();

        if (!$project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        $data['project'] = $project;
        $data['supervisors'] = Supervisor::where('id', '!=', $project->supervisor_id)
            ->where('specialize_id', $project->supervisor->specialize_id)->get();

        // الطلاب المتاحون لإضافتهم للمجموعة: نفس تخصص المشرف، بدون مشروع نشط
        $data['availableStudents'] = Student::where('specialize_id', $project->supervisor->specialize_id)
            ->whereDoesntHave('groups', function ($q) {
                $q->whereHas('project', function ($q) {
                    $q->where('status', '!=', 'reject');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'university_id']);

        return view('dashboard.admin.group.edit', $data);
    }

    public function update(GroupRequest $request)
    {
        // return $request->all();
        $project = Project::where('id', $request->id)
            ->with([
                'group' => function ($q) {
                    $q->with(['student' => function ($q) {
                        $q->with('specialize');
                    }]);

                },

                'supervisor' => function ($q) {
                    $q->with('specialize');

                },
                'project_type',
            ])
            ->first();

        if (!$project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        try {
            $old_group = $project->group;
            $old_supervisor = $project->supervisor;
            $newStudentCount = 0;
            $isSupervisor_change = false;
            $is_error = false;

            DB::beginTransaction();

            if ($request->student_ids) {
                $groups = Student::whereIn('university_id', $request->student_ids)
                    ->with(['groups' => function ($q) {
                        $q->with('project');
                    }])
                    ->get();

                foreach ($groups as $std) {
                    if ($std->groups->count() > 0 && $std->groups->first()->project->status != 'reject') {
                        $is_error = true;
                        break;
                    }
                }
                if ($is_error) {
                    return redirect()->back()->with('fail', 'يوجد طالب مشترك في مجموعة مسبقا');

                }

                foreach ($groups as $std) {
                    $std->groups()->create([
                        'project_id' => $project->id,

                    ]);
                    $newStudentCount++;
                }

            }
            if ($request->supervisor_id && $request->supervisor_id != $old_supervisor->id) {
                $project->update([
                    'supervisor_id' => $request->supervisor_id,
                ]);
                $isSupervisor_change = true;
            }

            DB::commit();

            if ($isSupervisor_change) {

                Notification::send($old_supervisor, new AdminChangeGroupNotify([
                    'project' => $project->title,
                    'msg' => "تم نقل المجموعة لمشرف آخر وشكرأ",
                ]));

                $old_supervisor = Supervisor::where('id', $request->supervisor_id)->first();
                Notification::send($old_supervisor, new AdminChangeGroupNotify([
                    'project' => $project->title,
                    'msg' => "تم اضافة المجموعة تحت اشرافك وشكرا",
                ]));

                foreach ($old_group as $grp) {
                    Notification::send($grp->student, new AdminChangeGroupNotify([
                        'project' => $project->title,
                        'supervisor_name' => $old_supervisor->name,
                        'msg' => "تم تغيير مشرف المجموعة ... وشكرا",
                    ]));

                }
            }

            if (!$is_error && $request->student_ids && count($request->student_ids) > 0) {

                $unique = array_unique($request->student_ids);
                $newGroups = join(',', $unique);
                $count = count($unique);
                $word = $count == 1 ? 'طالب' : 'طلاب';
                $msg = "تم اضافة {$count} {$word} للمجموعة ({$newGroups})";

                $studentdata = [
                    'project' => $project->title,
                    'msg' => $msg,
                ];

                Notification::send($old_supervisor, new AdminChangeGroupNotify($studentdata));

                $studentdata['supervisor_name'] = $old_supervisor->name;
                foreach ($old_group as $grp) {
                    Notification::send($grp->student, new AdminChangeGroupNotify($studentdata));
                }

            }

            return redirect()->back()->with('success', 'تم تعديل البيانات بنجاح');

        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('fail', 'حدث خطا ما الرجاء المحاولة مرة أخرى ' . $ex->getMessage());

        }

    }

    public function destroy()
    {
        // return $request->all();
        $project = Project::where('id', request()->id)
            ->with([
                'group' => function ($q) {
                    $q->with(['student' => function ($q) {
                        $q->with('specialize');
                    }]);

                },

                'supervisor' => function ($q) {
                    $q->with('specialize');

                },
                'project_type',
            ])
            ->first();

        if (!$project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        try {
            DB::beginTransaction();

            $project->group()->delete();
            $project->delete();

            DB::commit();
            return redirect()->back()->with('success', 'تم حذف البيانات بنجاح');

        } catch (\Exception $ex) {
            DB::rollBack();
            return redirect()->back()->with('fail', 'حدث خطا ما الرجاء المحاولة مرة أخرى ' . $ex->getMessage());

        }

    }

}
