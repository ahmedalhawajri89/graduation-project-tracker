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
        private ?string $period = null,
    ) {
    }

    public function collection()
    {
        $query = AuditLog::applyPeriod(AuditLog::applyScope(AuditLog::query(), $this->scope), $this->period);

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
        return [__('التاريخ'), __('الفاعل'), __('الدور'), __('الحدث'), __('الكيان'), __('التفاصيل')];
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
                ? __('الدرجة: :from ← :to', ['from' => $grade['from'], 'to' => $grade['to']])
                : __('الدرجة: :to', ['to' => $grade['to']]);
        }

        if (isset($changes['supervisor'])) {
            $parts[] = __('المشرف: :from ← :to', ['from' => $changes['supervisor']['from'], 'to' => $changes['supervisor']['to']]);
        }

        if (! empty($changes['reason'])) {
            $parts[] = __('السبب: :reason', ['reason' => $changes['reason']]);
        }

        return $parts ? implode(' · ', $parts) : '—';
    }
}
