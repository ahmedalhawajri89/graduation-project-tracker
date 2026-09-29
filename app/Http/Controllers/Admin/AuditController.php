<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AuditLogExport;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Maatwebsite\Excel\Facades\Excel;

/**
 * سجلّ التدقيق — قراءة وتصدير فقط.
 *
 * لا \u200Estore\u200E ولا \u200Eupdate\u200E ولا \u200Edestroy\u200E، ولا مسار لأيٍّ منها.
 * سجلّ يُعدَّل ليس سجلّاً.
 */
class AuditController extends Controller
{
    public function index()
    {
        $scope = in_array(request('scope'), array_keys(AuditLog::SCOPES), true) ? request('scope') : null;

        $logs = $this->filtered($scope)
            ->with(['actor', 'subject'])
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        // الملخّص: ما يجري الآن، لا مجموع السجلّ منذ بدئه
        $week = today()->subDays(6);
        $topActor = AuditLog::where('created_at', '>=', $week)
            ->whereNotNull('actor_name')
            ->selectRaw('actor_name, actor_role, COUNT(*) as total')
            ->groupBy('actor_name', 'actor_role')
            ->orderByDesc('total')
            ->first();

        return view('dashboard.admin.audit.index', [
            'logs' => $logs,
            'scope' => $scope,
            'period' => $this->period(),
            'countAll' => $this->filtered(null)->count(),
            'countGrade' => $this->filtered('grade')->count(),
            'countWork' => $this->filtered('work')->count(),
            'countLifecycle' => $this->filtered('lifecycle')->count(),
            'countDefense' => $this->filtered('defense')->count(),
            'summary' => [
                'today' => AuditLog::whereDate('created_at', today())->count(),
                'week' => AuditLog::where('created_at', '>=', $week)->count(),
                'grades' => AuditLog::where('action', 'like', 'grade.%')->where('created_at', '>=', today()->subDays(29))->count(),
                'topActor' => $topActor,
            ],
            'currentAction' => request('action'),
            'currentRole' => request('role'),
            'actions' => AuditLog::LABELS,
            'roles' => AuditLog::ROLES,
        ]);
    }

    public function export()
    {
        $period = $this->period();

        return Excel::download(
            new AuditLogExport(
                request('scope'),
                request('action'),
                request('role'),
                $period === 'custom' ? request('from') : null,
                $period === 'custom' ? request('to') : null,
                $period,
            ),
            'audit_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /** المدّة: سريعة، أو «مخصّص» حين يُعطى تاريخ، أو الكل (null) */
    private function period(): ?string
    {
        if (array_key_exists((string) request('period'), AuditLog::PERIODS)) {
            return (string) request('period');
        }

        return request()->filled('from') || request()->filled('to') || request('period') === 'custom'
            ? 'custom'
            : null;
    }

    /**
     * المُرشِّحات مطبَّقة على الخادم: السجلّ ينمو بلا حدّ، وتحميله
     * كاملاً إلى المتصفّح يصير أبطأ كل يوم.
     */
    private function filtered(?string $scope)
    {
        $period = $this->period();
        $query = AuditLog::applyPeriod(AuditLog::applyScope(AuditLog::query(), $scope), $period);

        if (request()->filled('action') && isset(AuditLog::LABELS[request('action')])) {
            $query->where('action', request('action'));
        }

        if (request()->filled('role') && isset(AuditLog::ROLES[request('role')])) {
            $query->where('actor_role', request('role'));
        }

        // التاريخان للمدى المخصّص وحده — والمدد السريعة تتجاهلهما.
        // \u200EwhereDate\u200E لا \u200Ewhere\u200E: المدى شامل لليوم المحدَّد من طرفيه
        if ($period === 'custom' && request()->filled('from')) {
            $query->whereDate('created_at', '>=', request('from'));
        }

        if ($period === 'custom' && request()->filled('to')) {
            $query->whereDate('created_at', '<=', request('to'));
        }

        return $query;
    }
}
