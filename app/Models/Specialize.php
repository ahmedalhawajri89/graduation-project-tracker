<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

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
