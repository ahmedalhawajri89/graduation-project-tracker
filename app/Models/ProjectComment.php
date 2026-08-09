<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    protected $table = 'project_comments';

    protected $fillable = [
        'project_id',
        'author_type',
        'author_id',
        'body',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function author()
    {
        return $this->morphTo();
    }

    /** هل الكاتب مشرف؟ */
    public function getIsSupervisorAttribute()
    {
        return $this->author_type === Supervisor::class;
    }
}
