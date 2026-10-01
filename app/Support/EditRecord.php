<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Supervisor;

/**
 * بيانات درج التعديل لسجلّ — في خاصية \u200Edata-record\u200E واحدة على زرّ التعديل.
 *
 * كان الزرّ يحمل عشر خصائص \u200Edata-*\u200E ويقرؤها jQuery منسوخ في ثلاث صفحات.
 * التعريف هنا وحده: الجدول يبنيه لكل صفّ، والصفحة تبنيه لسجلّ أُعيد فتح
 * درجه بعد فشل التحقّق — فيطابق الرأسُ الصفَّ في الحالتين.
 */
class EditRecord
{
    /** للخاصية: JSON مهرَّب يصلح داخل علامتَي اقتباس مفردتين أو مزدوجتين */
    public static function attr(array $record): string
    {
        return e(json_encode($record, JSON_UNESCAPED_UNICODE));
    }

    public static function student(Student $s): array
    {
        $project = $s->relationLoaded('groups')
            ? $s->groups->first()?->project
            : $s->groups()->whereHas('project', fn ($q) => $q->whereIn('status', ['accept', 'complete']))
                ->with('project:id,title')->first()?->project;

        return self::identity($s) + [
            'university_id' => (string) $s->university_id,
            'specialize_id' => $s->specialize_id,
            'sub' => (string) $s->university_id,
            'status' => $project
                ? ['label' => $project->title, 'tone' => 'in']
                : ['label' => __('بلا فريق'), 'tone' => 'none'],
        ];
    }

    public static function supervisor(Supervisor $s): array
    {
        $used = $s->projects_count ?? $s->projects()
            ->whereIn('status', ['accept', 'complete'])
            ->where('semester_id', Semester::current()->id)
            ->count();
        $max = (int) $s->max_group;

        return self::identity($s) + [
            'university_id' => (string) $s->university_id,
            'specialize_id' => $s->specialize_id,
            'max_group' => $max,
            'sub' => (string) $s->university_id,
            'status' => [
                'label' => __(':used/:max مجموعات', ['used' => $used, 'max' => $max]),
                'tone' => $max > 0 && $used >= $max ? 'full' : 'in',
            ],
        ];
    }

    public static function admin(Admin $a): array
    {
        $isMe = (int) $a->id === (int) auth('admin')->id();

        return self::identity($a) + [
            'sub' => (string) $a->email,
            'status' => $isMe ? ['label' => __('حسابك'), 'tone' => 'in'] : null,
        ];
    }

    /** ما يشترك فيه الثلاثة */
    private static function identity($user): array
    {
        return [
            'id' => $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'phone' => (string) $user->phone,
            'gender' => (string) $user->gender,
            'avatar_url' => $user->avatar_url,
            'initials' => $user->initials,
        ];
    }
}
