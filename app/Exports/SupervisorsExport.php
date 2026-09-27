<?php

namespace App\Exports;

use App\Models\Semester;
use App\Models\Supervisor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * كشف المشرفين — للتصدير من لوحة الأدمن.
 *
 * الطلاب صار لهم تصدير والمشرفون لا، مع أن كشف الأعباء هو ما يُطبع
 * ويُناقَش في اجتماع توزيع المشاريع.
 *
 * يحترم الفلاتر المطبَّقة على الجدول فيصدّر ما يراه الأدمن لا كل شيء.
 */
class SupervisorsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private ?string $load;
    private ?int $specializeId;
    private int $semesterId;

    public function __construct(?string $load = null, ?int $specializeId = null, ?int $semesterId = null)
    {
        $this->load = in_array($load, ['free', 'full', 'over'], true) ? $load : null;
        $this->specializeId = $specializeId;
        $this->semesterId = $semesterId ?: Semester::current()->id;
    }

    public function collection()
    {
        return Supervisor::query()
            ->with('specialize:id,name')
            ->withCount(['projects' => fn ($q) => $q
                ->whereIn('status', ['accept', 'complete'])
                ->where('semester_id', $this->semesterId)])
            ->when($this->specializeId, fn ($q) => $q->where('specialize_id', $this->specializeId))
            ->when($this->load, fn ($q) => $q->whereRaw(
                Supervisor::loadExpression() . ' ' . Supervisor::loadOperator($this->load) . ' supervisors.max_group',
                [$this->semesterId]
            ))
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'الرقم الجامعي',
            'اسم المشرف',
            'التخصص',
            'البريد الإلكتروني',
            'رقم الجوال',
            'الجنس',
            'المجموعات هذا الفصل',
            'الحد الأقصى',
            'الوضع',
        ];
    }

    public function map($supervisor): array
    {
        return [
            $supervisor->university_id,
            $supervisor->name,
            $supervisor->specialize->name ?: '—',
            $supervisor->email,
            $supervisor->phone,
            __('site.' . $supervisor->gender),
            $supervisor->projects_count,
            $supervisor->max_group,
            $this->label($supervisor),
        ];
    }

    /** العمود الذي يقرأه الإنسان: «متجاوز» أوضح من \u200E3\u200E و\u200E2\u200E في عمودين */
    private function label($supervisor): string
    {
        return match (true) {
            $supervisor->projects_count > $supervisor->max_group => 'تجاوز الحد',
            $supervisor->projects_count == $supervisor->max_group => 'مكتمل',
            default => 'متاح',
        };
    }
}
