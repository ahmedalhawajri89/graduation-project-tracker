<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * كشف الطلاب — للتصدير من لوحة الأدمن.
 *
 * الاستيراد من Excel كان موجوداً بلا تصدير مقابل، فنصف الدورة مفقود:
 * تستطيع إدخال كشف الدفعة ولا تستطيع إخراجه.
 *
 * يحترم الفلاتر المطبَّقة على الجدول (التخصص وحالة الانضمام) فيصدّر
 * ما يراه الأدمن لا كل شيء.
 */
class StudentsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private ?string $group;
    private ?int $specializeId;

    public function __construct(?string $group = null, ?int $specializeId = null)
    {
        $this->group = in_array($group, ['in', 'none'], true) ? $group : null;
        $this->specializeId = $specializeId;
    }

    public function collection()
    {
        $active = fn ($q) => $q->whereIn('status', ['accept', 'complete']);

        return Student::with([
            'specialize:id,name',
            'groups' => fn ($q) => $q->whereHas('project', $active)
                ->with('project:id,title')
                ->limit(1),
        ])
            ->when($this->group === 'in', fn ($q) => $q->whereHas('groups.project', $active))
            ->when($this->group === 'none', fn ($q) => $q->whereDoesntHave('groups.project', $active))
            ->when($this->specializeId, fn ($q) => $q->where('specialize_id', $this->specializeId))
            ->orderBy('university_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            __('الرقم الجامعي'),
            __('اسم الطالب'),
            __('التخصص'),
            __('البريد الإلكتروني'),
            __('رقم الجوال'),
            __('الجنس'),
            __('المشروع'),
        ];
    }

    public function map($student): array
    {
        return [
            $student->university_id,
            $student->name,
            $student->specialize->name ?? '—',
            $student->email,
            $student->phone,
            __('site.' . $student->gender),
            $student->groups->first()?->project?->title ?? __('بلا فريق'),
        ];
    }
}
