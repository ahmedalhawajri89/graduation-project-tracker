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

    protected $fillable = ['defense_id', 'supervisor_id', 'role', 'grade', 'comments', 'graded_at'];

    protected $casts = [
        'grade' => 'float',
        'graded_at' => 'datetime',
    ];

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
