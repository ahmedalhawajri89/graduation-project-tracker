<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** عضو لجنة المناقشة: مشرف المشروع أو الممتحن، ودرجته وملاحظاته */
class DefenseMember extends Model
{
    public const ROLES = [
        'supervisor' => 'المشرف',
        'examiner' => 'الممتحن',
    ];

    protected $fillable = ['defense_id', 'supervisor_id', 'role', 'is_chair', 'grade', 'comments', 'graded_at'];

    protected $casts = [
        'grade' => 'float',
        'is_chair' => 'boolean',
        'graded_at' => 'datetime',
    ];

    private static ?bool $chairSupported = null;

    /** عمود الرئاسة موجود — قبل ترحيله يُخفى اختيار الرئيس ويُعدّ المشرف رئيساً */
    public static function chairSupported(): bool
    {
        return self::$chairSupported ??= \Illuminate\Support\Facades\Schema::hasColumn('defense_members', 'is_chair');
    }

    public function defense()
    {
        return $this->belongsTo(Defense::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class)->withDefault(['name' => '']);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? $this->role;
    }
}
