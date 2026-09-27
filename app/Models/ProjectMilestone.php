<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    protected $table = 'project_milestones';

    protected $fillable = [
        'project_id',
        'stage_id',
        'title',
        'note',
        'due_date',
        'is_done',
        'done_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'is_done' => 'boolean',
        'done_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /** أصلها في خطة المشرف — null لمرحلة خاصة بهذه المجموعة */
    public function stage()
    {
        return $this->belongsTo(SupervisorStage::class, 'stage_id');
    }
}
