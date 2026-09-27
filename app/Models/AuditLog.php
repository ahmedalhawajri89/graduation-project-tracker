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
        'project.supervisorChanged' => 'تغيير مشرف مشروع',
        'project.statusChanged' => 'تغيير حالة مشروع',
        'project.memberAdded' => 'إضافة عضو لفريق',
        'project.memberRemoved' => 'إزالة عضو من فريق',
        'project.leaderChanged' => 'تغيير قائد فريق',
        'specialize.archived' => 'إيقاف تخصص',
        'specialize.restored' => 'استئناف تخصص',
        'admin.created' => 'إضافة مسؤول',
        'admin.deleted' => 'حذف مسؤول',
        'student.deleted' => 'حذف طالب',
        'supervisor.deleted' => 'حذف مشرف',
    ];

    public const ROLES = [
        'admin' => 'مسؤول النظام',
        'supervisor' => 'مشرف',
        'student' => 'طالب',
    ];

    public function getActionLabelAttribute(): string
    {
        return self::LABELS[$this->action] ?? $this->action;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->actor_role] ?? '—';
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
