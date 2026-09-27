<?php

namespace App\Models;

use App\Support\TeamRoles;
use Illuminate\Database\Eloquent\Model;

/** دور عضو في مشروعه — جاهز من \App\Support\TeamRoles أو حرّ */
class GroupRole extends Model
{
    protected $fillable = [
        'group_id',
        'role_key',
        'label',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /** أيقونة الدور ولونه — الحرّ بوسم عامّ */
    public function meta(): array
    {
        return TeamRoles::find($this->role_key) ?? TeamRoles::custom($this->label);
    }
}
