<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    /**
     * التخصص الموقوف لا يُسجَّل عليه أحد جديد، لكن طلابه ومشرفيه
     * ومشاريعه تبقى تعمل كما كانت. \u200Eactive()\u200E لكل قائمة تُضاف منها
     * بيانات جديدة، والقوائم التي تبحث في القائم تستعمل الكل.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return ! is_null($this->archived_at);
    }

    ################# relations

    public function students()
    {
        return $this->hasMany(Student::class, 'specialize_id', 'id');
        #relation is one to many
    }
    public function supervisors()
    {
        return $this->hasMany(Supervisor::class, 'specialize_id', 'id');
        #relation is one to many

    }
    public function supervisorsAvailable()
    {
        return $this->hasMany(Supervisor::class, 'specialize_id', 'id')
            ->where('max_group', '>', 0);
        #relation is one to many

    }

    public function projects()
    {
        return $this->hasMany(SpecializeProject::class, 'specialize_id', 'id');
    }
    #relation is one to many

    ################# end relations
}
