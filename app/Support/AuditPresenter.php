<?php

namespace App\Support;

use App\Models\AuditLog;
use App\Models\Project;
use Carbon\CarbonInterface;

/**
 * عرض حدث التدقيق: فئته ولونه، وأيقونته، وشارات ما تغيّر فيه، ورابط كيانه.
 *
 * كان السطر يعرض أيقونة القلم لكل حدث تقريباً، والدرجة والمشرف وحدهما من
 * التغييرات — و\u200Echanges\u200E تحفظ الحالة من وإلى، والمرحلة وجولتها، والقائد،
 * والأعضاء. التعريف هنا وحده، لصفحة السجلّ ولتبويب «سجلّ المشروع».
 */
class AuditPresenter
{
    /** الفئات بترتيب العرض: المفتاح ← الاسم */
    public const CATEGORIES = [
        'grade' => 'الدرجات',
        'work' => 'سير العمل',
        'project' => 'المشاريع والفرق',
        'danger' => 'حذف وفكّ اعتماد',
        'account' => 'الإعداد والحسابات',
    ];

    private const ICONS = [
        'grade.set' => 'ti-award',
        'grade.changed' => 'ti-award',
        'grade.locked' => 'ti-lock-check',
        'grade.unlocked' => 'ti-lock-open',
        'milestone.submitted' => 'ti-upload',
        'milestone.approved' => 'ti-circle-check',
        'milestone.revision' => 'ti-pencil-exclamation',
        'team.roles' => 'ti-users-group',
        'file.note' => 'ti-message-2',
        'stage.deleted' => 'ti-list-details',
        'project.statusChanged' => 'ti-arrows-exchange',
        'project.supervisorChanged' => 'ti-user-share',
        'project.memberAdded' => 'ti-user-plus',
        'project.memberRemoved' => 'ti-user-minus',
        'project.leaderChanged' => 'ti-crown',
        'project.withdrawn' => 'ti-arrow-back',
        'project.deleted' => 'ti-trash',
        'project.restored' => 'ti-restore',
        'project.forceDeleted' => 'ti-trash-x',
        'specialize.archived' => 'ti-archive',
        'specialize.restored' => 'ti-archive-off',
        'semester.activated' => 'ti-calendar-check',
        'admin.created' => 'ti-user-plus',
        'admin.deleted' => 'ti-user-x',
        'student.deleted' => 'ti-user-x',
        'supervisor.deleted' => 'ti-user-x',
    ];

    public static function category(string $action): string
    {
        return match (true) {
            in_array($action, ['grade.unlocked', 'project.deleted', 'project.forceDeleted'], true) => 'danger',
            str_starts_with($action, 'grade.') => 'grade',
            str_starts_with($action, 'milestone.'), in_array($action, ['team.roles', 'file.note', 'stage.deleted'], true) => 'work',
            str_starts_with($action, 'project.') => 'project',
            default => 'account',
        };
    }

    public static function icon(string $action): string
    {
        return self::ICONS[$action] ?? 'ti-point';
    }

    public static function hasIcon(string $action): bool
    {
        return isset(self::ICONS[$action]);
    }

    /**
     * ما تغيّر، شاراتٍ: \u200E['label' => ?string, 'from' => ?string, 'to' => ?string]\u200E.
     * السبب لا يدخل هنا — يُعرض اقتباساً كاملاً لأنه ما يُقرأ عند التنازع.
     *
     * @return list<array{label: ?string, from: ?string, to: ?string}>
     */
    public static function chips(AuditLog $log): array
    {
        $c = $log->changes ?? [];
        $chips = [];

        $pair = fn ($v) => is_array($v) ? [$v['from'] ?? null, $v['to'] ?? null] : [null, $v];
        $add = function (?string $label, $from, $to) use (&$chips) {
            if (($from === null || $from === '') && ($to === null || $to === '')) {
                return;
            }
            $chips[] = ['label' => $label, 'from' => $from === null || $from === '' ? null : (string) $from, 'to' => $to === null || $to === '' ? null : (string) $to];
        };

        if (isset($c['status'])) {
            [$from, $to] = $pair($c['status']);
            $add('الحالة', $from ? __('site.' . $from) : null, $to ? __('site.' . $to) : null);
            if (($c['status']['reason'] ?? null) === 'seats_full') {
                $add(null, null, 'مقاعد المشرف مكتملة');
            }
        }

        if (isset($c['grade']) && is_array($c['grade'])) {
            $fmt = fn ($g) => $g === null ? null : rtrim(rtrim(number_format((float) $g, 2), '0'), '.');
            $add('الدرجة', $fmt($c['grade']['from'] ?? null), ($fmt($c['grade']['to'] ?? null) ?? '') . ' / 100');
        }

        if (isset($c['milestone'])) {
            [, $to] = $pair($c['milestone']);
            $add('المرحلة', null, $to);
        }

        if (isset($c['round'])) {
            [, $to] = $pair($c['round']);
            $add(null, null, 'الجولة ' . $to);
        }

        if (isset($c['supervisor'])) {
            [$from, $to] = $pair($c['supervisor']);
            $add('المشرف', $from, $to);
        }

        if (isset($c['semester'])) {
            [$from, $to] = $pair($c['semester']);
            $add('الفصل', $from, $to);
        }

        if (isset($c['member'])) {
            [$from, $to] = $pair($c['member']);
            $add($log->action === 'project.leaderChanged' ? 'القائد' : 'العضو', $from, $to);
        }

        if (isset($c['members'])) {
            [$from, $to] = $pair($c['members']);
            $label = $log->action === 'team.roles' ? 'الأعضاء' : 'الفريق';
            $add($label, $from, $log->action === 'team.roles' && is_numeric($to) ? $to . ' أعضاء' : $to);
        }

        if (isset($c['changed'])) {
            [, $to] = $pair($c['changed']);
            $add('تغيّرت أدوارهم', null, (string) $to);
        }

        if (isset($c['file'])) {
            [, $to] = $pair($c['file']);
            $add('الملف', null, $to);
        }

        if (isset($c['stage'])) {
            [$from, $to] = $pair($c['stage']);
            $add('المرحلة', null, $from ?? $to);
        }

        if (isset($c['removed'])) {
            [, $to] = $pair($c['removed']);
            if ($to) {
                $add(null, null, 'من ' . $to . ($to == 1 ? ' مجموعة' : ' مجموعات'));
            }
        }

        return $chips;
    }

    /** رابط الكيان — المشروع الموجود وحده؛ المحذوف يبقى اسماً بلا رابط */
    public static function subjectUrl(AuditLog $log): ?string
    {
        if ($log->subject_type !== Project::class || ! $log->subject_id) {
            return null;
        }

        return $log->relationLoaded('subject') && $log->subject
            ? route('admin.groups.show', $log->subject_id)
            : null;
    }

    public static function dayLabel(CarbonInterface $at): string
    {
        return match (true) {
            $at->isToday() => 'اليوم',
            $at->isYesterday() => 'أمس',
            default => $at->locale('ar')->translatedFormat('l j F Y'),
        };
    }
}
