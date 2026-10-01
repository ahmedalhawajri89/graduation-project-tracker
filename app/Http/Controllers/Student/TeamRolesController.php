<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Notifications\ProjectActivityNotify;
use App\Support\Audit;
use App\Support\TeamRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * توزيع الأدوار — القائد وحده يوزّع، والجميع يرى في بطاقة الفريق.
 *
 * المدخل: members[<group_id>][roles][] مفتاح جاهز أو «custom:<تسمية>»،
 * و members[<group_id>][responsibility] سطر المسؤولية.
 */
class TeamRolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:student');
    }

    /**
     * الفريق والأدوار — صفحة لا نافذة منبثقة: المصفوفة وبطاقات الأعضاء تحتاج
     * العرض، والعضو يفتحها ليعرف دوره، والإشعار يربط إليها مباشرة.
     */
    public function index()
    {
        $student = auth('student')->user();
        $project = $student->groups()->with('project')->get()->pluck('project')
            ->first(fn ($p) => $p && $p->status !== 'reject');

        if (! $project) {
            return redirect()->route('student.dashboard')->with('fail', __('تظهر صفحة الفريق حين يكون لك مشروع.'));
        }

        $project->load(['group.student', 'group.roles', 'supervisor', 'project_type']);
        $members = $project->group->sortBy(fn ($m) => $m->type === 'leader' ? 0 : 1)->values();

        $isLeader = $members->contains(fn ($m) => $m->type === 'leader' && (int) $m->student_id === (int) $student->id);

        return view('dashboard.student.team', [
            'project' => $project,
            'members' => $members,
            'canAssign' => $isLeader && ! $project->is_locked,
            'isLeader' => $isLeader,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $student = auth('student')->user();

        abort_unless(
            $project->group()->where('student_id', $student->id)->where('type', 'leader')->exists(),
            403
        );

        if ($project->status === 'reject') {
            return back()->with('fail', __('المشروع مرفوض — لا توزيع أدوار له.'));
        }

        if ($project->is_locked) {
            return back()->with('fail', __('المشروع مؤرشف بعد التقييم — الأدوار سجلّ لا يُعدَّل.'));
        }

        $max = TeamRoles::max();
        $request->validate([
            'members' => ['required', 'array'],
            'members.*.roles' => ['nullable', 'array', 'max:' . $max],
            'members.*.roles.*' => ['string', 'max:40'],
            'members.*.responsibility' => ['nullable', 'string', 'max:160'],
        ], [
            'members.*.roles.max' => __('لكل عضو :max أدوار على الأكثر.', ['max' => $max]),
        ], [
            'members.*.responsibility' => __('المسؤولية'),
        ]);

        $groups = $project->group()->with('roles', 'student:id,name')->get()->keyBy('id');
        $input = $request->input('members', []);

        // عضو من فريق آخر لا يُمسّ — ولا يُسكت عنه
        if (array_diff(array_map('intval', array_keys($input)), $groups->keys()->all())) {
            throw ValidationException::withMessages(['members' => __('في الطلب عضو ليس من فريق هذا المشروع.')]);
        }

        $parsed = [];
        foreach ($input as $groupId => $member) {
            $roles = [];
            foreach ($member['roles'] ?? [] as $value) {
                $role = $this->parseRole((string) $value);
                if (! $role) {
                    throw ValidationException::withMessages(['members' => __('دور غير معروف: :role', ['role' => $value])]);
                }
                $roles[$role['label']] = $role; // التكرار بالتسمية نفسها يُطوى
            }
            $parsed[(int) $groupId] = [
                'roles' => array_values($roles),
                'responsibility' => trim((string) ($member['responsibility'] ?? '')) ?: null,
            ];
        }

        $changed = DB::transaction(function () use ($parsed, $groups) {
            $changed = [];

            foreach ($parsed as $groupId => $data) {
                $group = $groups[$groupId];
                $before = $group->roles->pluck('label')->sort()->values()->all();
                $after = collect($data['roles'])->pluck('label')->sort()->values()->all();

                $group->roles()->delete();
                foreach ($data['roles'] as $role) {
                    $group->roles()->create(['role_key' => $role['key'], 'label' => $role['label']]);
                }
                $group->update(['responsibility' => $data['responsibility']]);

                if ($before !== $after && $after) {
                    $changed[] = [$group, collect($data['roles'])->pluck('label')->all()];
                }
            }

            return $changed;
        });

        Audit::record('team.roles', $project, ['members' => ['to' => count($parsed)], 'changed' => ['to' => count($changed)]]);

        // الإشعار لمن تغيّرت أدواره وحده — لا لكل الفريق عند كل حفظ
        foreach ($changed as [$group, $labels]) {
            if ((int) $group->student_id === (int) auth('student')->id() || ! $group->student?->id) {
                continue;
            }
            try {
                $group->student->notify(new ProjectActivityNotify([
                    'project' => $project->title,
                    'supervisor_name' => auth('student')->user()->name,
                    'msg' => 'عيّنك القائد: ' . implode('، ', $labels),
                ]));
            } catch (\Exception $ex) {
                Log::warning('تعذّر إشعار عضو بدوره', ['group' => $group->id, 'exception' => $ex]);
            }
        }

        return redirect()->route('student.team')->with('success', __('حُفظ توزيع الأدوار.'));
    }

    /** مفتاح جاهز، أو «custom:<تسمية>» لدور حرّ */
    private function parseRole(string $value): ?array
    {
        if (str_starts_with($value, 'custom:')) {
            $label = trim(mb_substr($value, 7));

            return $label !== '' && mb_strlen($label) <= 30 ? TeamRoles::custom($label) : null;
        }

        return TeamRoles::find($value);
    }
}
