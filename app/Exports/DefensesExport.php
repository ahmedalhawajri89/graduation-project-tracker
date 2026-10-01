<?php

namespace App\Exports;

use App\Models\Defense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * جدول المناقشات — يُعلَّق في القسم أو يُرسل للجان: موعد كل مناقشة
 * ومكانها ولجنتها وحالتها، مرتّباً بالموعد. الملغاة لا تدخل.
 */
class DefensesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private ?int $semesterId = null)
    {
    }

    public function collection()
    {
        return Defense::query()
            ->where('status', '!=', Defense::CANCELLED)
            ->when($this->semesterId, fn ($q) => $q->whereHas('project', fn ($p) => $p->where('semester_id', $this->semesterId)))
            ->with(['project.group.student', 'project.project_type', 'members.supervisor', 'room'])
            ->orderBy('starts_at')
            ->get();
    }

    public function headings(): array
    {
        return [__('التاريخ'), app()->getLocale() === 'ar' ? 'اليوم' : 'Day', __('من'), __('إلى'), __('المشروع'), __('النوع'), __('الفريق'), __('المشرف'), __('الممتحنون'), __('رئيس اللجنة'), __('المكان'), __('رابط الاجتماع'), __('الحالة'), __('الدرجة')];
    }

    public function map($d): array
    {
        // اللجنة قد تضمّ أكثر من ممتحن
        $member = fn ($role) => $d->members->where('role', $role)->map(fn ($m) => $m->supervisor?->name)->filter()->implode(__('، '));
        $graded = $d->members->whereNotNull('grade')->count();

        return [
            $d->starts_at->format('Y-m-d'),
            $d->starts_at->translatedFormat('l'),
            $d->starts_at->format('H:i'),
            $d->endsAt()->format('H:i'),
            $d->project->title,
            $d->project->project_type->name ?? '',
            $d->project->group->map(fn ($g) => $g->student?->name)->filter()->implode(__('، ')),
            $member('supervisor'),
            $member('examiner'),
            $d->chair()?->supervisor?->name,
            $d->place_label . ($d->room?->location ? ' — ' . $d->room->location : ''),
            $d->needsLink() ? $d->meeting_url : '',
            match (true) {
                $d->status === Defense::DONE => __('منتهية'),
                $d->endsAt()->isPast() => __('بانتظار الدرجة (:done من :total)', ['done' => $graded, 'total' => $d->members->count()]),
                default => __('مجدولة'),
            },
            $d->status === Defense::DONE ? (float) $d->project->grade : '',
        ];
    }
}
