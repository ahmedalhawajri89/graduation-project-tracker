<?php

namespace App\Exports;

use App\Models\AuditLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * تصدير سجلّ التدقيق.
 *
 * السجلّ يُقرأ على الشاشة، لكنه يُطبع ويُرفق حين يُتنازَع — فالتصدير
 * ليس ترفاً هنا بل الغرض الأخير منه.
 */
class AuditLogExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        private ?string $scope = null,
        private ?string $action = null,
        private ?string $role = null,
        private ?string $from = null,
        private ?string $to = null,
    ) {
    }

    public function collection()
    {
        $query = AuditLog::applyScope(AuditLog::query(), $this->scope);

        if ($this->action && isset(AuditLog::LABELS[$this->action])) {
            $query->where('action', $this->action);
        }

        if ($this->role && isset(AuditLog::ROLES[$this->role])) {
            $query->where('actor_role', $this->role);
        }

        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }

        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }

        return $query->latest('created_at')->get();
    }

    public function headings(): array
    {
        return ['التاريخ', 'الفاعل', 'الدور', 'الحدث', 'الكيان', 'التفاصيل'];
    }

    public function map($log): array
    {
        return [
            $log->created_at?->format('Y-m-d H:i'),
            $log->actor_name ?: '—',
            $log->role_label,
            $log->action_label,
            $log->subject_label ?: '—',
            $this->details($log),
        ];
    }

    /** القيم في عمود واحد مقروء بدل json خام */
    private function details($log): string
    {
        $changes = $log->changes ?? [];
        $parts = [];

        if (isset($changes['grade'])) {
            $grade = $changes['grade'];
            $parts[] = isset($grade['from'])
                ? "الدرجة: {$grade['from']} ← {$grade['to']}"
                : "الدرجة: {$grade['to']}";
        }

        if (isset($changes['supervisor'])) {
            $parts[] = "المشرف: {$changes['supervisor']['from']} ← {$changes['supervisor']['to']}";
        }

        if (! empty($changes['reason'])) {
            $parts[] = "السبب: {$changes['reason']}";
        }

        return $parts ? implode(' · ', $parts) : '—';
    }
}
