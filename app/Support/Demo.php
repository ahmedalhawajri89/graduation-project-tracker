<?php

namespace App\Support;

use App\Models\Admin;
use App\Models\Defense;
use App\Models\Group;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Database\Eloquent\Model;

/**
 * وضع العرض التجريبي: هل هو مفعّل، ومن يدخل كل زرّ.
 *
 * الحسابات تُختار من البيانات نفسها لا من أسماء ثابتة: اللقطة قد تُبنى من
 * قاعدة أخرى. الطالب قائد فريق مشروعه بلغ المناقشة (فيرى الرحلة كاملة)،
 * والمشرف مشرف ذلك المشروع (فيرى الطرف الآخر من القصة نفسها).
 */
class Demo
{
    public const ROLES = ['student', 'supervisor', 'admin'];

    public static function enabled(): bool
    {
        return (bool) config('demo.enabled');
    }

    /** أقصى حجم رفع بالميغابايت: حدّ الوضع إن كان أصغر من حدّ الحقل */
    public static function uploadLimit(int $fieldMb): int
    {
        return self::enabled() ? min($fieldMb, (int) config('demo.upload_mb', 2)) : $fieldMb;
    }

    public static function account(string $role): ?Model
    {
        $email = config("demo.accounts.{$role}");

        return match ($role) {
            'admin' => $email ? Admin::where('email', $email)->first() : Admin::orderBy('id')->first(),
            'supervisor' => $email ? Supervisor::where('email', $email)->first() : self::showcase()?->supervisor,
            'student' => $email ? Student::where('email', $email)->first() : self::leaderOf(self::showcase()),
            default => null,
        };
    }

    /** مشروع العرض: مكتمل وله مناقشة مجدولة، وإلا أكثر المشاريع المقبولة نشاطاً */
    public static function showcase(): ?Project
    {
        $semester = \App\Models\Semester::current();
        $base = fn () => Project::whereNotNull('supervisor_id')->whereHas('group')
            ->when($semester, fn ($q) => $q->where('semester_id', $semester->id));

        $withDefense = DefenseScheduler::enabled()
            ? $base()->whereHas('defense', fn ($q) => $q->where('status', Defense::SCHEDULED))->first()
            : null;

        return $withDefense
            ?? $base()->whereIn('status', ['accept', 'complete'])->withCount('comments')->orderByDesc('comments_count')->first();
    }

    private static function leaderOf(?Project $project): ?Student
    {
        if (! $project) {
            return null;
        }

        $row = Group::where('project_id', $project->id)->orderByRaw("type = 'leader' desc")->first();

        return $row?->student;
    }
}
