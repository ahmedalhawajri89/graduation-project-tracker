<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';
    protected $fillable = [
        'student_id',
        'project_id',
        'type',
    ];

    ################# relations

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
        #relation is one to many
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id')
            ->withDefault([
                'name' => '',
            ]);
        #relation is one to many


    }

    ################# end relations

}
