<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * سطر في سجلّ التدقيق — إلحاق فقط.
 *
 * لا \u200Eupdated_at\u200E: السطر لا يُعدَّل. ولا مسار تعديل أو حذف في الواجهة.
 */
class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_type', 'actor_id', 'actor_name', 'actor_role',
        'action',
        'subject_type', 'subject_id', 'subject_label',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * نصّ الفعل بالعربية.
     *
     * يُحفظ في القاعدة مفتاحاً ثابتاً لا جملة: يُبحث به ويُرشَّح، ولا
     * يتغيّر معناه إن أُعيدت صياغة النصّ المعروض.
     */
    public const LABELS = [
        'grade.set' => 'وضع درجة',
        'grade.changed' => 'تعديل درجة',
        'grade.locked' => 'اعتماد درجة',
        'grade.unlocked' => 'فكّ اعتماد درجة',
        'project.deleted' => 'حذف مشروع',
        'project.restored' => 'استرجاع مشروع',
        'project.forceDeleted' => 'حذف مشروع نهائياً',
        'project.withdrawn' => 'سحب طلب مشروع',
        'stage.deleted' => 'حذف مرحلة من خطة',
        'milestone.submitted' => 'تسليم مرحلة',
        'milestone.approved' => 'اعتماد مرحلة',
        'milestone.revision' => 'طلب تعديل على مرحلة',
        'team.roles' => 'توزيع أدوار الفريق',
        'file.note' => 'ملاحظة على ملف',
        'project.supervisorChanged' => 'تغيير مشرف مشروع',
        'project.statusChanged' => 'تغيير حالة مشروع',
        'project.memberAdded' => 'إضافة عضو لفريق',
        'project.memberRemoved' => 'إزالة عضو من فريق',
        'project.leaderChanged' => 'تغيير قائد فريق',
        'specialize.archived' => 'إيقاف تخصص',
        'specialize.restored' => 'استئناف تخصص',
        'semester.activated' => 'تفعيل فصل دراسي',
        'admin.created' => 'إضافة مسؤول',
        'admin.deleted' => 'حذف مسؤول',
        'student.deleted' => 'حذف طالب',
        'supervisor.deleted' => 'حذف مشرف',
        'defense.scheduled' => 'جدولة مناقشة',
        'defense.rescheduled' => 'تعديل موعد مناقشة',
        'defense.cancelled' => 'إلغاء مناقشة',
    ];

    /**
     * تبويبات السجلّ: ما يُتنازَع عليه أولاً، ثم سير العمل.
     * تعريف واحد للصفحة والتصدير — كان كلٌّ منهما يكرّر الشروط.
     */
    public const SCOPES = [
        'grade' => ['grade.%'],
        'work' => ['milestone.%', 'team.roles', 'file.note', 'stage.deleted'],
        'lifecycle' => ['project.deleted', 'project.restored', 'project.forceDeleted'],
        'defense' => ['defense.%'],
    ];

    /** يقصر الاستعلام على أحداث التبويب — والتبويب المجهول لا يرشّح */
    public static function applyScope($query, ?string $scope)
    {
        if (! isset(self::SCOPES[$scope])) {
            return $query;
        }

        return $query->where(function ($q) use ($scope) {
            foreach (self::SCOPES[$scope] as $pattern) {
                str_contains($pattern, '%') ? $q->orWhere('action', 'like', $pattern) : $q->orWhere('action', $pattern);
            }
        });
    }

    /** المُدد السريعة: المفتاح ← [الاسم، عدد الأيام أو null لليوم وحده] */
    public const PERIODS = [
        'today' => 'اليوم',
        '7' => 'آخر 7 أيام',
        '30' => 'آخر 30 يوماً',
    ];

    /** يقصر الاستعلام على مدّة سريعة — والمجهولة لا ترشّح (للصفحة والتصدير معاً) */
    public static function applyPeriod($query, ?string $period)
    {
        return match ($period) {
            'today' => $query->whereDate('created_at', today()),
            '7', '30' => $query->where('created_at', '>=', today()->subDays((int) $period - 1)),
            default => $query,
        };
    }

    public const ROLES = [
        'admin' => 'مسؤول النظام',
        'supervisor' => 'مشرف',
        'student' => 'طالب',
    ];

    /*
    | الثوابت أعلاه عربية (مفاتيح الترجمة نفسها)؛ هذه نسخها المعروضة بلغة
    | الواجهة — المفاتيح كما هي، والقيم مترجمة.
    */

    /** @return array<string, string> */
    public static function labels(): array
    {
        return array_map(fn ($label) => __($label), self::LABELS);
    }

    /** @return array<string, string> */
    public static function periods(): array
    {
        return array_map(fn ($label) => __($label), self::PERIODS);
    }

    /** @return array<string, string> */
    public static function roles(): array
    {
        return array_map(fn ($label) => __($label), self::ROLES);
    }

    public function getActionLabelAttribute(): string
    {
        return isset(self::LABELS[$this->action]) ? __(self::LABELS[$this->action]) : $this->action;
    }

    public function getRoleLabelAttribute(): string
    {
        return isset(self::ROLES[$this->actor_role]) ? __(self::ROLES[$this->actor_role]) : '—';
    }

    /** الأفعال التي تمسّ الدرجة — تُبرَز في العرض */
    public function getIsGradeAttribute(): bool
    {
        return str_starts_with($this->action, 'grade.');
    }

    public function actor()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
