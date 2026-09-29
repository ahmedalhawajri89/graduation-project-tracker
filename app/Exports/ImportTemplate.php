<?php

namespace App\Exports;

use App\Models\Specialize;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * قالب الاستيراد — يُولَّد لكل نوع بأعمدته الصحيحة وتخصص حقيقي في صفّ المثال.
 *
 * كان ملفاً ثابتاً واحداً (\u200Epublic/example.xlsx\u200E) للطلاب والمشرفين معاً: بلا
 * عمود \u200Emax_group\u200E للمشرف، وصفّ مثاله نصوص وصفية («اسم الطالب هنا»،
 * «mail/femail») تُرفض لو رُفع كما هو. الأعمدة هنا مفاتيح \u200EStudentsImport\u200E
 * و\u200ESupervisorsImport\u200E نفسها.
 */
class ImportTemplate implements FromArray, WithHeadings, ShouldAutoSize
{
    public const COLUMNS = [
        'student' => ['name', 'university_id', 'email', 'phone', 'specialization', 'gender', 'password'],
        'supervisor' => ['name', 'university_id', 'email', 'phone', 'specialization', 'gender', 'max_group', 'password'],
    ];

    public function __construct(private string $kind)
    {
    }

    public function headings(): array
    {
        return self::COLUMNS[$this->kind];
    }

    public function array(): array
    {
        $specialization = Specialize::whereNull('archived_at')->value('name') ?? '';

        $example = $this->kind === 'student'
            ? ['محمد أحمد سالم', '2300000999', 'student.example@student.com', '0591234567', $specialization, 'male', '']
            : ['د. سارة خالد', '700200999', 'supervisor.example@supervisor.com', '0591234567', $specialization, 'female', 5, ''];

        return [$example];
    }
}
