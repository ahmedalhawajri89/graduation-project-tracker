<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * كشف نتائج مشاريع فصل دراسي — للتصدير من لوحة الأدمن.
 */
class ProjectsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private $semesterId;

    public function __construct(int $semesterId)
    {
        $this->semesterId = $semesterId;
    }

    public function collection()
    {
        return Project::where('semester_id', $this->semesterId)
            ->whereIn('status', ['accept', 'complete'])
            ->with(['supervisor', 'project_type', 'group.student', 'milestones'])
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'عنوان المشروع',
            'نوع المشروع',
            'المشرف',
            'الحالة',
            'نسبة الإنجاز',
            'الدرجة',
            'التقدير',
            'أعضاء الفريق',
            'الأرقام الجامعية',
            'الموعد النهائي',
            'تاريخ التقديم',
        ];
    }

    public function map($project): array
    {
        return [
            $project->title,
            $project->project_type->name,
            $project->supervisor->name,
            __('site.' . $project->status),
            is_null($project->progress) ? '—' : $project->progress . '%',
            is_null($project->grade) ? '—' : (float) $project->grade,
            $project->grade_label ?? '—',
            $project->group->pluck('student.name')->implode('، '),
            $project->group->pluck('student.university_id')->implode('، '),
            $project->date_line ? $project->date_line->format('Y-m-d') : '—',
            $project->created_at->format('Y-m-d'),
        ];
    }
}
