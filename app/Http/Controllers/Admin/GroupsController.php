<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProjectsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GroupRequest;
use App\Models\Group;
use App\Models\Project;
use App\Models\Semester;
use App\Models\SpecializeProject;
use App\Models\Student;
use App\Models\Supervisor;
use App\Notifications\AdminChangeGroupNotify;
use App\Support\Audit;
use App\Support\TeamHealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

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

        $supervisor = request()->filled('supervisor') ? (int) request()->supervisor : null;
        $type = request()->filled('type') ? (int) request()->type : null;

        // كانت مقصورة على accept و complete، فالطلبات المعلّقة والمرفوضة
        // غير مرئية إطلاقاً — رغم أن لوحة التحكم تربط «طلب بانتظار
        // المراجعة» إلى هذه الصفحة بالذات. الحالة صارت فلتراً.
        $allowed = array_keys(config('statuses.map'));
        $status = request()->filled('status') && in_array(request()->status, $allowed, true)
            ? request()->status
            : null;

        $base = Project::where('semester_id', $semesterId)
            ->when($supervisor !== null, fn ($q) => $q->where('supervisor_id', $supervisor))
            ->when($type !== null, fn ($q) => $q->where('specialize_project_id', $type));

        // عدّاد لكل حالة ضمن نفس الفلاتر — يُعرض على أزرار التصفية
        $data['statusCounts'] = (clone $base)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $data['currentStatus'] = $status;
        $data['trashedCount'] = Project::onlyTrashed()->count();

        // قادم من «متابعة الفرق» في الرئيسية: الشرط نفسه الذي عُدّ هناك
        $issue = TeamHealth::isIssue(request('issue')) ? request('issue') : null;
        $data['currentIssue'] = $issue;

        // الجدول صار يُحمَّل عبر DataTables من \u200EgetData()\u200E كصفحة الطلاب،
        // فلم يبقَ هنا إلا العدّ الذي يظهر تحت العنوان
        $data['totalFiltered'] = (clone $base)
            ->when($status !== null, fn ($q) => $q->where('status', $status))
            ->when($issue !== null, fn ($q) => TeamHealth::apply($q, $issue))
            ->count();

        return view('dashboard.admin.group.index', $data);
    }

    /**
     * مصدر بيانات الجدول.
     *
     * الفلاتر نفسها تُقرأ هنا من الرابط كما في \u200Eindex()\u200E، لأن DataTables
     * تطلبها في رابط الـ ajax — فالتصفية تبقى على الخادم ولا يُحمَّل
     * كشف الفصل كاملاً إلى المتصفّح.
     */
    public function getData()
    {
        $semesterId = request()->filled('semester')
            ? (int) request()->semester
            : Semester::current()->id;

        $allowed = array_keys(config('statuses.map'));

        $projects = Project::query()
            ->where('semester_id', $semesterId)
            ->when(request()->filled('supervisor'), fn ($q) => $q->where('supervisor_id', (int) request()->supervisor))
            ->when(request()->filled('type'), fn ($q) => $q->where('specialize_project_id', (int) request()->type))
            ->when(
                request()->filled('status') && in_array(request()->status, $allowed, true),
                fn ($q) => $q->where('status', request()->status)
            )
            ->when(TeamHealth::isIssue(request('issue')), fn ($q) => TeamHealth::apply($q, request('issue')))
            ->with(['group.student:id,name', 'supervisor:id,name', 'project_type:id,name,max'])
            ->select('projects.*');

        return DataTables::of($projects)

            // المشروع ونوعه في خليّة واحدة — النوع صفة للمشروع لا عمود
            // مستقلّ، وإفراده كان يستهلك عرضاً بلا مقابل
            ->addColumn('identity', function ($project) {
                // النوع شارة لا سطراً ثانياً: نصّان صغيران متتاليان كانا
                // يلتصقان فيُقرآن جملة واحدة — «… نسخة 2برمجة ذكاء صناعي».
                // الشارة تفصل بحدّها وخلفيتها لا بحجم الخطّ وحده، فيبقى
                // الفرق واضحاً مهما ضاق العمود.
                return '<a href="' . route('admin.groups.show', $project->id) . '" class="cell-identity-link">'
                    . '<span class="cell-name">' . e($project->title) . '</span>'
                    . '<span class="cell-tag">' . e($project->project_type->name ?? 'بلا نوع') . '</span>'
                    . '</a>';
            })

            // \u200E?->\u200E ضروري: لو حُذف الطالب (المفتاح nullOnDelete) أو لم
            // يوجد قائد، لانفجر الصفّ قبل أن تُقيَّم \u200E??\u200E
            ->addColumn('leader', function ($project) {
                $leader = $project->group->firstWhere('type', 'leader')?->student;

                return e($leader?->name ?? '—');
            })

            ->addColumn('team', function ($project) {
                $size = $project->group->count();
                $max = $project->project_type->max ?? null;

                $over = $max && $size > $max ? ' is-over' : '';
                $title = $max ? ' title="الحد الأقصى لهذا النوع: ' . e($max) . '"' : '';
                $limit = $max ? '<small>/' . e($max) . '</small>' : '';

                return '<span class="team-size' . $over . '"' . $title . '>' . e($size) . $limit . '</span>';
            })

            ->addColumn('supervisor_name', fn ($project) => e($project->supervisor->name ?: '—'))

            // نفس ترميز \u200E<x-status-badge>\u200E ومن نفس \u200Econfig/statuses.php\u200E،
            // فلا تنشأ شارة ثانية تختلف عن شارة بقية الصفحات
            ->editColumn('status', function ($project) {
                $conf = config('statuses.map')[$project->status] ?? config('statuses.fallback');

                return '<span class="badge status-badge ' . e($conf['badge']) . '">'
                    . '<i class="ti ' . e($conf['icon']) . '"></i> '
                    . e(__('site.' . $project->status))
                    . '</span>';
            })

            ->editColumn('grade', function ($project) {
                if (is_null($project->grade)) {
                    return '<span class="text-secondary small">—</span>';
                }

                $value = rtrim(rtrim(number_format($project->grade, 2), '0'), '.');

                return '<span class="grade-pill">' . e($value)
                    . '<small>' . e($project->grade_label) . '</small></span>';
            })

            ->addColumn('actions', function ($project) {
                return '<div class="btn-group">'
                    . '<a href="' . route('admin.groups.show', $project->id) . '" class="btn-action" title="عرض التفاصيل" aria-label="عرض التفاصيل"><i class="ti ti-eye"></i></a>'
                    . '<a href="' . route('admin.groups.edit', $project->id) . '" class="btn-action" title="تعديل" aria-label="تعديل"><i class="ti ti-pencil"></i></a>'
                    . '<button type="button" class="btn-action btn-action--danger btn-delete" data-bs-toggle="modal" data-bs-target="#deleteModal"'
                    . ' data-id="' . e($project->id) . '" data-name="' . e($project->title) . '" title="حذف" aria-label="حذف"><i class="ti ti-trash"></i></button>'
                    . '</div>';
            })

            ->rawColumns(['identity', 'team', 'status', 'grade', 'actions'])
            ->make(true);
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
                'group.roles',
                'supervisor.specialize',
                'project_type',
                'semester',
                'milestones.stage',
                'milestones.submissions.student',
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

        $semesterId = Semester::current()->id;

        // عبء الإشراف: الصفحة قرارها الأول «هل أنقل المجموعة إلى مشرف
        // آخر؟»، وكانت تعرض أسماء مجرّدة — تختار بلا أن تعرف من يحمل
        // مجموعة ومن تجاوز حدّه.
        $load = fn ($q) => $q->whereIn('status', ['accept', 'complete'])
            ->where('semester_id', $semesterId);

        $project->supervisor->loadCount(['projects' => $load]);

        $data['project'] = $project;
        $data['supervisors'] = Supervisor::where('id', '!=', $project->supervisor_id)
            ->where('specialize_id', $project->supervisor->specialize_id)
            ->withCount(['projects' => $load])
            ->orderBy('name')
            ->get();

        // العدد وحده يُحمَّل: القائمة تأتي عبر البحث، فلا تُرسَل مئة
        // صفّ في الصفحة ليُمسح بالعين ما لا يُمسح
        $data['availableCount'] = Student::availableForTeam($project->supervisor->specialize_id)->count();
        $data['searchUrl'] = route('admin.groups.students.search', $project->id);

        return view('dashboard.admin.group.edit', $data);
    }

    /**
     * تعديل المجموعة: إضافة أعضاء، وتغيير المشرف.
     *
     * كان الحدّ الأقصى للفريق يُفحص في JavaScript وحده، وفحص الانشغال خارج أي
     * قفل ويتجاهل المشاريع المحذوفة حذفاً مرناً، والخروج المبكر يترك المعاملة
     * مفتوحة، ورسالة الفشل «تعذّر الحذف النهائي» منسوخة من دالّة أخرى، وإضافة
     * العضو لا تُسجَّل.
     */
    public function update(GroupRequest $request)
    {
        $project = Project::where('id', $request->id)
            ->with(['group.student', 'supervisor', 'project_type'])
            ->first();

        if (! $project) {
            return redirect()->back()->with('fail', 'المشروع غير موجود.');
        }

        $oldSupervisor = $project->supervisor;
        $oldGroup = $project->group;
        $ids = collect($request->student_ids)->filter(fn ($id) => filled($id))->map(fn ($id) => (string) $id)->unique()->values();
        $changeSupervisor = $request->supervisor_id && (int) $request->supervisor_id !== (int) $oldSupervisor?->id;

        try {
            $added = DB::transaction(function () use ($project, $ids, $changeSupervisor, $request, $oldSupervisor) {
                $added = collect();

                if ($ids->isNotEmpty()) {
                    $members = Student::whereIn('university_id', $ids)->lockForUpdate()->get();

                    $type = $project->project_type;
                    if ($type && $project->group->count() + $members->count() > $type->max) {
                        throw new \DomainException("الفريق يتجاوز الحدّ الأقصى لنوعه ({$type->max} طلاب).");
                    }
                    if ($type && $members->contains(fn ($m) => (int) $m->specialize_id !== (int) $type->specialize_id)) {
                        throw new \DomainException('يجب أن يكون الأعضاء من تخصص المشروع.');
                    }

                    // المحذوف حذفاً مرناً يُعدّ — كما في availableForTeam وتقديم المقترح
                    $busy = Student::whereIn('id', $members->pluck('id'))
                        ->whereHas('groups.project', fn ($q) => $q->withTrashed()->where('status', '!=', 'reject'))
                        ->pluck('name');
                    if ($busy->isNotEmpty()) {
                        throw new \DomainException('مسجَّل في فريق آخر، أو في مشروع موقوف لدى الإدارة: ' . $busy->implode('، '));
                    }

                    foreach ($members as $member) {
                        $member->groups()->create(['project_id' => $project->id, 'type' => 'member']);
                    }

                    Audit::record('project.memberAdded', $project, ['members' => ['to' => $members->pluck('name')->implode('، ')]]);
                    $added = $members;
                }

                if ($changeSupervisor) {
                    $project->update(['supervisor_id' => $request->supervisor_id]);

                    // المجموعة المنقولة تأخذ خطة مشرفها الجديد (ما لديها من مراحل يبقى)
                    \App\Support\StagePlan::applyTo($project->fresh());

                    // نقل مجموعة بين مشرفين يمسّ من يضع درجتها لاحقاً
                    Audit::record('project.supervisorChanged', $project, [
                        'supervisor' => [
                            'from' => $oldSupervisor?->name,
                            'to' => Supervisor::find($request->supervisor_id)?->name ?? '—',
                        ],
                    ]);
                }

                return $added;
            });
        } catch (\DomainException $ex) {
            return redirect()->back()->with('fail', $ex->getMessage());
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error('فشل تعديل مجموعة', ['project' => $project->id, 'exception' => $ex]);

            return redirect()->back()->with('fail', 'تعذّر حفظ التعديل. حاول مرة أخرى، وإن تكرّر فراجع السجلّ.');
        }

        // الإشعارات بعد الالتزام
        $currentSupervisor = $changeSupervisor ? Supervisor::find($request->supervisor_id) : $oldSupervisor;

        if ($changeSupervisor) {
            if ($oldSupervisor) {
                Notification::send($oldSupervisor, new AdminChangeGroupNotify([
                    'project' => $project->title,
                    'msg' => 'نُقلت المجموعة إلى مشرف آخر.',
                ]));
            }
            Notification::send($currentSupervisor, new AdminChangeGroupNotify([
                'project' => $project->title,
                'msg' => 'أُضيفت المجموعة إلى إشرافك.',
            ]));
            foreach ($oldGroup as $grp) {
                if ($grp->student) {
                    Notification::send($grp->student, new AdminChangeGroupNotify([
                        'project' => $project->title,
                        'supervisor_name' => $currentSupervisor->name,
                        'msg' => 'تغيّر مشرف مجموعتك إلى ' . $currentSupervisor->name . '.',
                    ]));
                }
            }
        }

        if ($added->isNotEmpty()) {
            // بالأسماء لا بالأرقام الجامعية: كان النصّ «(2300000159,2300000040,…)»
            $data = ['project' => $project->title, 'msg' => 'أُضيف إلى المجموعة: ' . $added->pluck('name')->implode('، ')];

            Notification::send($currentSupervisor, new AdminChangeGroupNotify($data));
            foreach ($oldGroup as $grp) {
                if ($grp->student) {
                    Notification::send($grp->student, new AdminChangeGroupNotify($data + ['supervisor_name' => $currentSupervisor->name]));
                }
            }
        }

        return redirect()->back()->with('success', 'حُفظت التعديلات.');
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
            // صفوف الفريق لم تعد تُحذف: كان حذفها صريحاً يُفقد الاسترجاعَ
            // معناه — فيعود المشروع بلا أعضاء. تبقى وتُمحى بالـ cascade
            // عند الحذف النهائي وحده.
            $project->delete();

            Audit::record('project.deleted', $project);

            return redirect()->back()->with('success', 'نُقلت المجموعة إلى المحذوفات — يمكن استرجاعها.');

        } catch (\Exception $ex) {
            report($ex);
            return redirect()->back()->with('fail', 'تعذّر حذف المجموعة. حاول مرة أخرى.');
        }
    }

    /**
     * بحث الطلاب المتاحين — يغذّي منتقي الأعضاء.
     *
     * كانت الصفحة تُرسل كل المتاحين (أكثر من مئة في التخصص الواحد)
     * ليُمسحوا بالعين، وهو ما لا يُمسح. البحث على الخادم، والنتائج
     * محدودة — فلا يكبر الحمل مع كبر الدفعة.
     */
    public function searchStudents($id)
    {
        $project = Project::with('supervisor')->find($id);

        if (! $project) {
            return response()->json(['total' => 0, 'results' => []], 404);
        }

        $specializeId = $project->supervisor->specialize_id;

        return $this->respondWithStudents(
            Student::availableForTeam($specializeId)
        );
    }

    /**
     * ردّ موحَّد لمنتقي الأعضاء — بحثاً أو تصفّحاً.
     *
     * التصفّح على دفعات لا دفعةً واحدة: زرٌّ يُفرغ ١٤٧ اسماً يُعيدنا
     * إلى القائمة الطويلة التي أزلناها.
     */
    private function respondWithStudents($base)
    {
        $total = (clone $base)->count();
        $offset = max(0, (int) request('offset'));
        $perPage = 20;

        $results = $base->matching(request('q'))
            ->orderBy('name')
            ->offset($offset)
            ->limit($perPage)
            ->get(['id', 'name', 'university_id', 'avatar']);

        // العدد بعد التصفية لا الكلّي: «٢٠ من ١٢ مطابق» لا معنى له
        $matched = request()->filled('q')
            ? (clone $base)->matching(request('q'))->count()
            : $total;

        return response()->json([
            'total' => $total,
            'matched' => $matched,
            'offset' => $offset,
            'hasMore' => ($offset + $results->count()) < $matched,
            'results' => $results->map(fn ($student) => [
                'university_id' => $student->university_id,
                'name' => $student->name,
                'initials' => $student->initials,
                'avatar_url' => $student->avatar_url,
            ]),
        ]);
    }

    /**
     * إزالة عضو من الفريق.
     *
     * لم يكن ممكناً إطلاقاً: الصفحة تعرض «الفريق ٥ والحدّ ٣» ثم لا
     * تملك ما تُصلح به — تعرض المشكلة ولا تحلّها.
     */
    public function removeMember($id, $memberId)
    {
        $project = Project::with('group.student')->find($id);

        if (! $project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        $member = $project->group->firstWhere('id', (int) $memberId);

        if (! $member) {
            return redirect()->back()->with('fail', 'العضو غير موجود في هذا الفريق.');
        }

        // مشروع بلا أعضاء يتيم: لا يظهر لأحد ولا يملك من يعمل عليه
        if ($project->group->count() <= 1) {
            return redirect()->back()->with('fail',
                'لا يمكن إزالة آخر عضو — المشروع يبقى بلا فريق. احذف المشروع نفسه إن كان هذا المقصود.');
        }

        // القائد يُنقل قبل أن يُزال: فريق بلا قائد لا مُخاطَب له
        if ($member->type === 'leader') {
            return redirect()->back()->with('fail',
                'هذا قائد الفريق. عيّن قائداً آخر أولاً ثم أزِله.');
        }

        $name = $member->student?->name ?? 'طالب محذوف';
        $member->delete();

        Audit::record('project.memberRemoved', $project, ['member' => $name]);

        return redirect()->back()->with('success', "أُزيل {$name} من الفريق.");
    }

    /** تعيين قائد للفريق — كان القائد يُحدَّد عند الإنشاء ولا يتغيّر */
    public function setLeader($id, $memberId)
    {
        $project = Project::with('group.student')->find($id);

        if (! $project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        $member = $project->group->firstWhere('id', (int) $memberId);

        if (! $member) {
            return redirect()->back()->with('fail', 'العضو غير موجود في هذا الفريق.');
        }

        if ($member->type === 'leader') {
            return redirect()->back()->with('fail', 'هو قائد الفريق أصلاً.');
        }

        $previous = $project->group->firstWhere('type', 'leader')?->student?->name;

        DB::transaction(function () use ($project, $member) {
            // قائد واحد لا أكثر: القدامى يُخفَّضون في نفس المعاملة
            Group::where('project_id', $project->id)->update(['type' => 'member']);
            $member->update(['type' => 'leader']);
        });

        Audit::record('project.leaderChanged', $project, [
            'member' => ['from' => $previous ?? '—', 'to' => $member->student?->name ?? '—'],
        ]);

        return redirect()->back()->with('success', 'تم تعيين قائد الفريق.');
    }

    /**
     * فكّ اعتماد الدرجة.
     *
     * مخرج الأدمن حين يُعتمد خطأ. والسبب **مطلوب**: قفلٌ يُفكّ بنقرة
     * بلا تبرير ليس قفلاً، والسبب هو ما يُقرأ في السجلّ عند التنازع.
     */
    public function unlockGrade(Request $request, $id)
    {
        $project = Project::find($id);

        if (! $project) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }

        if (! $project->isGradeLocked()) {
            return redirect()->back()->with('fail', 'الدرجة غير معتمدة أصلاً.');
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'اكتب سبب فكّ الاعتماد — يُحفظ في سجلّ التدقيق.',
            'reason.min' => 'السبب قصير جداً — اشرحه في جملة مفهومة.',
        ], ['reason' => 'السبب']);

        $project->update(['grade_locked_at' => null]);

        Audit::record('grade.unlocked', $project, [
            'grade' => ['to' => (float) $project->grade],
            'reason' => $request->reason,
        ]);

        return redirect()->back()->with('success', 'فُكّ الاعتماد — يستطيع المشرف تعديل الدرجة الآن.');
    }

    /** المشاريع المحذوفة — قابلة للاسترجاع أو للحذف النهائي */
    public function trash()
    {
        $data['projects'] = Project::onlyTrashed()
            ->with(['supervisor', 'project_type', 'semester'])
            ->withCount(['group', 'milestones', 'files', 'comments'])
            ->latest('deleted_at')
            ->paginate(15);

        return view('dashboard.admin.group.trash', $data);
    }

    /** استرجاع مشروع محذوف بكل ما يتبعه */
    public function restore($id)
    {
        $project = Project::onlyTrashed()->find($id);

        if (! $project) {
            return redirect()->back()->with('fail', 'المشروع غير موجود في المحذوفات.');
        }

        $project->restore();

        Audit::record('project.restored', $project);

        return redirect()->back()->with('success', 'تم استرجاع المشروع بمراحله وملفاته.');
    }

    /**
     * حذف نهائي لا رجعة فيه.
     * الـ cascade يمحو المراحل والملفات والتعليقات من القاعدة، لكنه لا
     * يمسّ القرص — فتُمسح ملفات المشروع هنا صراحةً وإلا تراكمت يتيمة.
     */
    public function forceDestroy($id)
    {
        $project = Project::onlyTrashed()->with('files')->find($id);

        if (! $project) {
            return redirect()->back()->with('fail', 'المشروع غير موجود في المحذوفات.');
        }

        try {
            DB::beginTransaction();

            $paths = $project->files->pluck('path')->filter()->all();

            // يُسجَّل **قبل** الحذف: بعده يفقد الكيان مفتاحه وعنوانه،
            // وهذا أكثر فعل يُحتاج أثره لأنه لا رجعة فيه
            Audit::record('project.forceDeleted', $project, [
                'grade' => is_null($project->grade) ? null : ['to' => (float) $project->grade],
            ]);

            $project->group()->delete();
            $project->forceDelete();

            DB::commit();

            // القرصان كحذف الملف المفرد: الملفات القديمة رُفعت إلى public قبل
            // نقلها إلى القرص الخاص، وكانت تبقى يتيمة بعد الحذف النهائي
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
                Storage::disk('public')->delete($path);
            }
            Storage::disk('local')->deleteDirectory('project_files/' . $project->id);
            Storage::disk('public')->deleteDirectory('project_files/' . $project->id);

            return redirect()->back()->with('success', 'حُذف المشروع نهائياً مع ملفاته.');

        } catch (\Exception $ex) {
            DB::rollBack();
            report($ex);
            return redirect()->back()->with('fail', 'تعذّر الحذف النهائي. حاول مرة أخرى.');
        }
    }
}
