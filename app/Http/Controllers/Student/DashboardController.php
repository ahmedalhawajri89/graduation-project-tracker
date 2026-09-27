<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ProjectRequest;
use App\Models\Project;
use App\Models\Semester;
use App\Models\Specialize;
use App\Models\Student;
use App\Support\Discussion;
use App\Support\ProjectActivity;
use App\Support\ProjectSimilarity;
use App\Notifications\SuperVisorRequestProjectNotify;
use App\Notifications\ProjectActivityNotify;
use App\Support\Audit;
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
                    // مع أصل كل مرحلة في خطة المشرف: قالبها وتعليماتها تُعرض في صفّها،
                    // وتسليماتها، والفريق والمشرف للبطاقة الرئيسية — بلا استعلام لكل صفّ
                    $q->with([
                        'project.milestones.stage',
                        'project.milestones.submissions.student',
                        'project.group.student',
                        'project.group.roles',
                        'project.files.uploader',
                        'project.files.notes.author',
                        'project.files.notes.mentioned',
                        'project.files.notes.resolver',
                        'project.supervisor.specialize',
                        'project.project_type',
                    ]);
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

        // التعريف موحَّد في \u200EStudent::availableForTeam\u200E: كان مكتوباً هنا
        // وفي لوحة الأدمن، وفاته إصلاح الحذف الناعم في أحدهما
        $data['availableCount'] = Student::availableForTeam($student->specialize_id, $student->id)->count();
        $data['searchUrl'] = route('student.mates.search');

        // المشروع النشط: أول مجموعة لم يُرفض مشروعها — ونشاطه ورسائله للبطاقة
        $active = $student->groups->first()?->project;
        $active = $active && $active->status !== 'reject' ? $active : null;
        $data['activity'] = $active ? ProjectActivity::recent(collect([$active->id]), $student) : collect();
        $data['unreadMsgs'] = $active ? (Discussion::unreadFor($student, [$active->id])[$active->id] ?? 0) : 0;

        return view('dashboard.student.index', $data);
    }

    /** بحث زملاء التخصص المتاحين — يغذّي منتقي الفريق */
    public function searchMates()
    {
        $student = $this->getInfo();

        $base = Student::availableForTeam($student->specialize_id, $student->id);

        $total = (clone $base)->count();
        $offset = max(0, (int) request('offset'));

        $results = (clone $base)->matching(request('q'))
            ->orderBy('name')
            ->offset($offset)
            ->limit(20)
            ->get(['id', 'name', 'university_id', 'avatar']);

        $matched = request()->filled('q')
            ? (clone $base)->matching(request('q'))->count()
            : $total;

        return response()->json([
            'total' => $total,
            'matched' => $matched,
            'offset' => $offset,
            'hasMore' => ($offset + $results->count()) < $matched,
            'results' => $results->map(fn ($mate) => [
                'university_id' => $mate->university_id,
                'name' => $mate->name,
                'initials' => $mate->initials,
                'avatar_url' => $mate->avatar_url,
            ]),
        ]);
    }

    /** مستكشف المشاريع السابقة — للإلهام وتجنّب تكرار الأفكار */
    /**
     * المشاريع المنجزة — يُفتح قبل المقترح ليجيب: هل نُفّذت فكرتي؟ ما
     * الذي يُقبل في تخصصي؟ ومن أشرف على ما يشبهها؟
     *
     * المكتمل وحده: كان يعرض \u200Eaccept\u200E أيضاً، فكان ٢٦ من ٣٥ مشروعاً
     * أفكار زملاء جارية بعناوينها وأوصافها تحت اسم «السابقة».
     */
    public function exploreProjects()
    {
        $student = auth('student')->user();

        // تخصص الطالب افتراضياً؛ \u200Eall\u200E يوسّع
        $specializeId = request('specialize') === 'all'
            ? null
            : (int) (request('specialize') ?: $student->specialize_id);

        $base = Project::where('status', 'complete')
            ->when($specializeId, fn ($q) => $q->whereHas(
                'project_type', fn ($t) => $t->where('specialize_id', $specializeId)
            ));

        // مشرفو النطاق المختار بعدد ما أنجزوه — قبل تصفية المشرف نفسها
        $supervisors = (clone $base)
            ->whereNotNull('supervisor_id')
            ->selectRaw('supervisor_id, count(*) as n')
            ->groupBy('supervisor_id')
            ->with('supervisor:id,name')
            ->get()
            ->filter(fn ($row) => $row->supervisor)
            ->sortByDesc('n')
            ->values();

        $projects = $base
            ->with(['supervisor:id,name', 'project_type', 'semester:id,name'])
            ->withCount('group')
            ->when(request('supervisor'), fn ($q) => $q->where('supervisor_id', request('supervisor')))
            ->when(request('q'), function ($q) {
                $q->where(function ($q) {
                    $q->where('title', 'like', '%' . request('q') . '%')
                        ->orWhere('description', 'like', '%' . request('q') . '%');
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('dashboard.student.explore', [
            'projects' => $projects,
            'supervisors' => $supervisors,
            'specializes' => Specialize::orderBy('name')->get(['id', 'name']),
            'specializeId' => $specializeId,
            'ownSpecialize' => $student->specialize_id,
        ]);
    }

    /** «مشاريع منجزة مشابهة» تحت حقل العنوان في نموذج المقترح */
    public function similarProjects()
    {
        $title = (string) request('title');

        if (mb_strlen(trim($title)) < 4) {
            return response()->json(['results' => []]);
        }

        $results = ProjectSimilarity::find($title, auth('student')->user()->specialize_id)
            ->map(fn ($p) => [
                'title' => $p->title,
                'supervisor' => $p->supervisor?->name,
                'semester' => $p->semester?->name,
                'url' => route('student.projects.explore', ['q' => $p->title, 'specialize' => 'all']),
            ]);

        return response()->json(['results' => $results]);
    }

    /**
     * تقديم مقترح المشروع.
     *
     * كان يحفظ \u200EProject::create($request->all())\u200E، و\u200EProjectRequest\u200E يقبل
     * \u200Estatus\u200E، و\u200E$fillable\u200E يشمل الدرجة والاعتماد: طلب مصنوع واحد يُنشئ
     * مشروعاً مقبولاً مقيَّماً معتمداً. وكان الفصل يُؤخذ من العميل، والمرسِل
     * لا يُشترط أن يكون في فريقه، والقيم الفارغة تُعدّ أعضاءً.
     */
    public function createProject(ProjectRequest $request)
    {
        $semester = Semester::current();
        if (! $semester) {
            return redirect()->back()->with('fail', 'لا يوجد فصل دراسي نشط — راجع إدارة القسم.');
        }

        $student = $this->getInfo($semester->id);
        $specialize = $student->specialize;

        if (! $specialize || ! $specialize->id) {
            return redirect()->back()->with('fail', 'لم يُحدَّد تخصصك بعد — راجع إدارة القسم.');
        }

        $project_type = $specialize->projects->firstWhere('id', (int) $request->specialize_project_id);
        if (! $project_type) {
            return redirect()->back()->withInput()->with('fail', 'نوع المشروع غير متاح في تخصصك.');
        }

        $supervisor = $specialize->supervisorsAvailable->firstWhere('id', (int) $request->supervisor_id);
        if (! $supervisor) {
            return redirect()->back()->withInput()->with('fail', 'المشرف غير متاح في تخصصك.');
        }
        if ($supervisor->seatsLeft() <= 0) {
            return redirect()->back()->withInput()->with('fail', 'اكتملت مجموعات هذا المشرف — اختر مشرفاً آخر.');
        }

        // الأعضاء بعد حذف الفارغ والمكرّر — كانت القيم الفارغة تُعدّ في الحدّ الأدنى
        $ids = collect($request->student_ids)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        if (! $ids->contains((string) $student->university_id)) {
            return redirect()->back()->withInput()->with('fail', 'يجب أن تكون ضمن فريقك — أنت قائده.');
        }
        if ($ids->count() < $project_type->min || $ids->count() > $project_type->max) {
            return redirect()->back()->withInput()->with('fail',
                "حجم الفريق لهذا النوع بين {$project_type->min} و{$project_type->max} طلاب.");
        }

        try {
            $project = DB::transaction(function () use ($ids, $specialize, $semester, $supervisor, $project_type, $request, $student) {
                // قفل صفوف الأعضاء: النقر المزدوج أو طلبان متزامنان كانا يضعان
                // الطالب في مشروعين — الفحص والإدراج كانا خارج أي قفل
                $members = Student::whereIn('university_id', $ids)->lockForUpdate()->get();

                if ($members->count() !== $ids->count()) {
                    throw new \DomainException('بعض الأرقام الجامعية غير موجودة.');
                }
                if ($members->contains(fn ($m) => (int) $m->specialize_id !== (int) $specialize->id)) {
                    throw new \DomainException('يجب أن يكون جميع أعضاء الفريق من تخصصك.');
                }

                // مشروع قائم غير مرفوض — والمحذوف حذفاً مرناً يُعدّ، كما في availableForTeam
                $busy = Student::whereIn('id', $members->pluck('id'))
                    ->whereHas('groups.project', fn ($q) => $q->withTrashed()->where('status', '!=', 'reject'))
                    ->pluck('name');
                if ($busy->isNotEmpty()) {
                    throw new \DomainException('مسجَّل في فريق آخر، أو في مشروع موقوف لدى الإدارة: ' . $busy->implode('، '));
                }

                // الحقول صراحةً: الحالة والفصل من الخادم، لا من الطلب
                $project = Project::create([
                    'title' => $request->title,
                    'description' => $request->description,
                    'semester_id' => $semester->id,
                    'supervisor_id' => $supervisor->id,
                    'specialize_project_id' => $project_type->id,
                    'status' => 'request',
                ]);

                foreach ($members as $member) {
                    $member->groups()->create([
                        'project_id' => $project->id,
                        'type' => (int) $member->id === (int) $student->id ? 'leader' : 'member',
                    ]);
                }

                return $project;
            });
        } catch (\DomainException $ex) {
            return redirect()->back()->withInput()->with('fail', $ex->getMessage());
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::error('فشل تسجيل مشروع', ['exception' => $ex]);

            return redirect()->back()->withInput()->with('fail', 'تعذّر تسجيل المشروع. حاول مرة أخرى.');
        }

        // بعد الالتزام وفي محاولة مستقلّة: فشل البريد كان يُبلِّغ الطالب بفشل
        // تسجيلٍ تمّ فعلاً، فيعيد المحاولة فيُرفض بـ«مسجَّل في فريق آخر»
        try {
            Notification::send($supervisor, new SuperVisorRequestProjectNotify($project, $ids->all(), $project_type->name));
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::warning('سُجِّل المشروع وتعذّر إشعار المشرف', ['project' => $project->id, 'exception' => $ex]);
        }

        return redirect()->back()->with('success', 'أُرسل مقترحك إلى المشرف — ستصلك موافقته أو ملاحظاته هنا.');
    }

    /**
     * سحب طلب معلّق — للقائد وحده، وقبل ردّ المشرف.
     *
     * كان الفريق الذي لا يردّ مشرفه عالقاً: أعضاؤه لا يدخلون فريقاً آخر ولا
     * يقدّمون لمشرف غيره. المشروع لم يُقبل فلا تاريخ فيه يُحفظ — يُحذف نهائياً
     * مع صفوف فريقه فيتحرّر الأعضاء، ويبقى أثره في سجلّ التدقيق.
     */
    public function withdrawProject(Project $project)
    {
        $student = auth('student')->user();

        $isLeader = $project->group()
            ->where('student_id', $student->id)
            ->where('type', 'leader')
            ->exists();

        abort_unless($isLeader, 403);

        try {
            $members = DB::transaction(function () use ($project) {
                $locked = Project::whereKey($project->id)->lockForUpdate()->first();

                // قبل الردّ وحده: بعد القبول يدير المشرف المشروع، وبعد الرفض لا شيء يُسحب
                if (! $locked || $locked->status !== 'request') {
                    throw new \DomainException('لا يُسحب إلا طلب ما زال بانتظار ردّ المشرف.');
                }

                $members = $locked->group()->with('student')->get()->pluck('student')->filter();

                Audit::record('project.withdrawn', $locked, [
                    'supervisor' => ['from' => $locked->supervisor?->name],
                    'members' => ['from' => $members->pluck('name')->implode('، ')],
                ]);

                // إشعار الطلب عند المشرف: الطلب لم يعد قائماً
                DB::table('notifications')
                    ->where('type', SuperVisorRequestProjectNotify::class)
                    ->where('notifiable_id', $locked->supervisor_id)
                    ->whereNull('read_at')
                    ->get(['id', 'data'])
                    ->filter(fn ($n) => (int) (json_decode($n->data, true)['project_id'] ?? 0) === (int) $locked->id)
                    ->each(fn ($n) => DB::table('notifications')->where('id', $n->id)->delete());

                $locked->group()->delete();
                $locked->forceDelete();

                return $members;
            });
        } catch (\DomainException $ex) {
            return redirect()->back()->with('fail', $ex->getMessage());
        }

        // بعد الالتزام: الأعضاء الآخرون يعرفون أن فريقهم تحرّر
        try {
            Notification::send($members->where('id', '!=', $student->id), new ProjectActivityNotify([
                'project' => $project->title,
                'supervisor_name' => $student->name,
                'msg' => 'سحب قائد الفريق طلب المشروع — يمكنكم التقديم من جديد.',
            ]));
        } catch (\Exception $ex) {
            \Illuminate\Support\Facades\Log::warning('سُحب الطلب وتعذّر إشعار الفريق', ['exception' => $ex]);
        }

        return redirect()->route('student.dashboard')
            ->with('success', 'سُحب الطلب وتحرّر الفريق — يمكنك تقديم مقترح جديد أو اختيار مشرف آخر.');
    }

    public function showNotification()
    {
        $student = auth('student')->user();

        // يُلتقط قبل التعليم: ما كان جديداً لحظة الفتح يُميَّز في العرض
        $newIds = $student->unreadNotifications()->pluck('id')->all();
        $student->unreadNotifications()->update(['read_at' => now()]);

        return view('dashboard.student.replayProjects', [
            'notifications' => $student->notifications()->latest()->take(50)->get(),
            'newIds' => $newIds,
        ]);
    }

}
