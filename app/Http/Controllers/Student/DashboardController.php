<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ProjectRequest;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Student;
use App\Notifications\SuperVisorRequestProjectNotify;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    private function getInfo($semester_id = 0)
    {
        return Student::where('id', auth()->id())
            ->with([
                'specialize' => function ($q) use ($semester_id) {
                    $q->with([
                        'projects',
                        'supervisorsAvailable' => function ($q) use ($semester_id) {
                            $q->withCount(['projectsAccept' => function ($q) use ($semester_id) {
                                $q->where('semester_id', $semester_id);
                            }]);
                        },
                    ]);
                },
                'groups' => function ($q) {
                    $q->with('project');
                },
            ])
            ->first();

    }

    public function index()
    {

        $semester = Semester::current();
        $student = $this->getInfo($semester->id);
        $data['student'] = $student;
        $data['semester'] = $semester;

        // الطلاب المتاحون لتكوين الفريق: نفس التخصص، بدون مشروع نشط
        $data['availableStudents'] = Student::where('specialize_id', $student->specialize_id)
            ->where('id', '!=', $student->id)
            ->whereDoesntHave('groups', function ($q) {
                $q->whereHas('project', function ($q) {
                    $q->where('status', '!=', 'reject');
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'university_id']);

        return view('dashboard.student.index', $data);
    }

    /** مستكشف المشاريع السابقة — للإلهام وتجنّب تكرار الأفكار */
    public function exploreProjects()
    {
        $projects = Project::whereIn('status', ['accept', 'complete'])
            ->with(['supervisor', 'project_type', 'semester'])
            ->withCount('group')
            ->when(request('q'), function ($q) {
                $q->where(function ($q) {
                    $q->where('title', 'like', '%' . request('q') . '%')
                        ->orWhere('description', 'like', '%' . request('q') . '%');
                });
            })
            ->when(request('semester_id'), function ($q) {
                $q->where('semester_id', request('semester_id'));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard.student.explore', [
            'projects' => $projects,
            'semesters' => Semester::latest()->get(['id', 'name']),
        ]);
    }

    public function createProject(ProjectRequest $request)
    {
        // return $request;
        try {
            $semester = Semester::current();
            if (!$semester && $semester->id != $request->semester_id) {
                return redirect()->back()->with('fail', 'بيانات الفصل الدراسي الحالي غير متوفرة');
            }

            $student = $this->getInfo();
            $specialize = $student->specialize;
            $project_type = $specialize->projects->where('id', $request->specialize_project_id)->first();
            $supervisor = $specialize->supervisorsAvailable->where('id', $request->supervisor_id)->first();

            if (!$specialize && $specialize->id != $request->specialize_id) {
                return redirect()->back()->with('fail', 'بيانات التخصص غير صحيحة');
            }
            if (count($request->student_ids) < $project_type->min || count($request->student_ids) > $project_type->max) {
                return redirect()->back()->with('fail', "يجب أن يتكون الفريق من {$project_type->min} أو {$project_type->max} من الطلاب");
            }

            if (!$supervisor && $supervisor->id != $request->supervisor_id) {
                return redirect()->back()->with('fail', 'بيانات المشرف غير صحيحة');
            } else if ($supervisor->max_group <= $supervisor->projects_accept_count) {
                return redirect()->back()->with('fail', 'لقد اكتملت مجموعات المشرف .. الرجاء اختار مشرف أخر');
            }

            $groups = Student::whereIn('university_id', $request->student_ids)
                ->with(['groups' => function ($q) {
                    $q->with('project');
                }])
                ->get();

            if ($groups->where('specialize_id', '!=', $specialize->id)->count() > 0) {
                return redirect()->back()->with('fail', 'يجب ان يكون جميع الطلاب في المجموعة في نفس التخصص');
            }
            $is_error = false;
            foreach ($groups as $std) {
                if ($std->groups->count() > 0 && $std->groups->first()->project->status != 'reject') {
                    $is_error = true;
                    break;
                }
            }
            if ($is_error) {
                return redirect()->back()->with('fail', 'يوجد طالب مشترك في مجموعة مسبقا');

            }

            // return $request->all();
            DB::beginTransaction();
            $new_project_request = Project::create($request->all());
            foreach ($groups as $std) {
                $std->groups()->create([
                    'project_id' => $new_project_request->id,
                    'type' => auth()->id() == $std->id ? 'leader' : 'member',

                ]);
            }

            DB::commit();

            $project_type = $specialize->projects
                ->where('id', $request->specialize_project_id)
                ->first()->name;
            Notification::send($supervisor, new SuperVisorRequestProjectNotify($new_project_request, $request->student_ids, $project_type));

            // return $request->all();
            return redirect()->back()->with('success', 'تم اضافة بيات المشروع بنجاح انتظر موافقة المشرف');

        } catch (\Exception$ex) {
            DB::rollBack();
            return redirect()->back()->with('fail', 'حدث خطا ما الرجاء المحاولة مرة أخرى ' . $ex->getMessage());
        }
    }

    public function showNotification()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        return view('dashboard.student.replayProjects');
    }

}
