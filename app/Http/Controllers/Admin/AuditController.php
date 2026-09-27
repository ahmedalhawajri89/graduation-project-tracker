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
    /** التبويبات: ما يُتنازَع عليه أولاً */
    private const SCOPES = [
        'grade' => ['grade.'],
        'lifecycle' => ['project.deleted', 'project.restored', 'project.forceDeleted'],
    ];

    public function index()
    {
        $scope = in_array(request('scope'), array_keys(self::SCOPES), true) ? request('scope') : null;

        $logs = $this->filtered($scope)
            ->latest('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('dashboard.admin.audit.index', [
            'logs' => $logs,
            'scope' => $scope,
            'countAll' => $this->filtered(null)->count(),
            'countGrade' => $this->filtered('grade')->count(),
            'countLifecycle' => $this->filtered('lifecycle')->count(),
            'currentAction' => request('action'),
            'currentRole' => request('role'),
            'actions' => AuditLog::LABELS,
            'roles' => AuditLog::ROLES,
        ]);
    }

    public function export()
    {
        return Excel::download(
            new AuditLogExport(
                request('scope'),
                request('action'),
                request('role'),
                request('from'),
                request('to')
            ),
            'audit_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * المُرشِّحات مطبَّقة على الخادم: السجلّ ينمو بلا حدّ، وتحميله
     * كاملاً إلى المتصفّح يصير أبطأ كل يوم.
     */
    private function filtered(?string $scope)
    {
        $query = AuditLog::query();

        if ($scope === 'grade') {
            $query->where('action', 'like', 'grade.%');
        } elseif ($scope === 'lifecycle') {
            $query->whereIn('action', self::SCOPES['lifecycle']);
        }

        if (request()->filled('action') && isset(AuditLog::LABELS[request('action')])) {
            $query->where('action', request('action'));
        }

        if (request()->filled('role') && isset(AuditLog::ROLES[request('role')])) {
            $query->where('actor_role', request('role'));
        }

        // \u200EwhereDate\u200E لا \u200Ewhere\u200E: المدى شامل لليوم المحدَّد من طرفيه
        if (request()->filled('from')) {
            $query->whereDate('created_at', '>=', request('from'));
        }

        if (request()->filled('to')) {
            $query->whereDate('created_at', '<=', request('to'));
        }

        return $query;
    }
}
