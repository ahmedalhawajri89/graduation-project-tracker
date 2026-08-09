<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecializeProject extends Model
{
    use HasFactory;

    protected $table = 'specialize_projects';
    protected $fillable = [
        'name',
        'specialize_id',
        'min',
        'max',
    ];

    ################# relations

    public function specialize()
    {
        return $this->belongsTo(Specialize::class, 'specialize_id', 'id');
        #relation is one to many

    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'specialize_project_id', 'id');
        #relation is one to many

    }

    ################# end relations

}
