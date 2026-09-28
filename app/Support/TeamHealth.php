<?php

namespace App\Support;

use App\Models\ProjectMilestone;
use Illuminate\Database\Eloquent\Builder;

/**
 * متابعة الفرق — أين يتعثّر الفصل.
 *
 * كانت لوحة الإدارة تعدّ الطلاب والمجموعات فقط، فلا يُعرف أيّ فريق متأخّر
 * ولا أيّ مشرف لم يراجع. تعريف كل مؤشّر هنا وحده: العدّ في الرئيسية وتصفية
 * جدول المجموعات يقرآن الشرط نفسه، فلا يعِد الرقمُ بقائمة لا تطابقه.
 *
 * المشاريع الجارية وحدها: مقبولة ولم تُقيَّم — المقيَّم مؤرشف.
 */
class TeamHealth
{
    /** تسليم ينتظر المشرف أكثر من هذا يُعدّ متأخّر المراجعة */
    public const REVIEW_DAYS = 3;

    /** فريق بلا تسليم هذه المدّة يُعدّ متوقّفاً */
    public const IDLE_DAYS = 14;

    /** المؤشّرات بترتيب العرض: المفتاح ← [العنوان، الشرح، الأيقونة] */
    public static function issues(): array
    {
        return [
            'late' => ['مرحلة فات موعدها', 'فرق عليها مرحلة لم تُسلَّم وقد مضى موعدها.', 'ti-alarm'],
            'review' => ['تسليم ينتظر المشرف', 'سُلّمت قبل أكثر من ' . self::REVIEW_DAYS . ' أيام ولم يراجعها المشرف.', 'ti-inbox'],
            'idle' => ['فريق متوقّف', 'لم يسلّم شيئاً منذ ' . self::IDLE_DAYS . ' يوماً وأمامه مراحل.', 'ti-player-pause'],
            'roles' => ['فريق بلا أدوار', 'لم يوزّع القائد المسؤوليات على الأعضاء.', 'ti-users-group'],
        ];
    }

    public static function isIssue(?string $key): bool
    {
        return $key !== null && array_key_exists($key, self::issues());
    }

    /** يقصر الاستعلام على المشاريع الجارية التي فيها هذه المشكلة */
    public static function apply(Builder $query, string $issue): Builder
    {
        $query->where('status', 'accept')->whereNull('grade');

        $owed = [ProjectMilestone::OPEN, ProjectMilestone::REVISION];

        return match ($issue) {
            'late' => $query->whereHas('milestones', fn ($q) => $q
                ->whereIn('status', $owed)
                ->whereDate('due_date', '<', today())),

            'review' => $query->whereHas('milestones', fn ($q) => $q
                ->where('status', ProjectMilestone::SUBMITTED)
                ->whereHas('submissions', fn ($s) => $s
                    ->whereNull('decision')
                    ->where('created_at', '<', now()->subDays(self::REVIEW_DAYS)))),

            // المشروع الجديد لا يُحاسَب قبل أن تمضي المدّة على بدايته
            'idle' => $query
                ->where('created_at', '<', now()->subDays(self::IDLE_DAYS))
                ->whereHas('milestones', fn ($q) => $q->whereIn('status', $owed))
                ->whereDoesntHave('milestones.submissions', fn ($s) => $s
                    ->where('created_at', '>=', now()->subDays(self::IDLE_DAYS))),

            'roles' => $query->whereDoesntHave('group.roles'),

            default => $query,
        };
    }

    /**
     * مؤشّرات مشروع واحد — لشريط «يحتاج انتباهاً» في صفحته، بالشرط نفسه
     * الذي عدّه في الرئيسية. المقيَّم وغير المقبول لا يطابق شيئاً أصلاً.
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function issuesFor(\App\Models\Project $project): array
    {
        if ($project->status !== 'accept' || ! is_null($project->grade)) {
            return [];
        }

        return collect(self::issues())
            ->filter(fn ($meta, $key) => self::apply(\App\Models\Project::whereKey($project->id), $key)->exists())
            ->all();
    }

    /** عدد كل مؤشّر في فصل — استعلام عدّ لكل واحد، لا تحميل مشاريع */
    public static function counts(int $semesterId): array
    {
        return collect(self::issues())
            ->keys()
            ->mapWithKeys(fn ($key) => [$key => self::apply(
                \App\Models\Project::where('semester_id', $semesterId),
                $key
            )->count()])
            ->all();
    }
}
