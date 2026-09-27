<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** آخر تعليق قرأه شخص في نقاش مشروع — انظر \App\Support\Discussion */
class DiscussionRead extends Model
{
    protected $fillable = [
        'project_id',
        'reader_type',
        'reader_id',
        'last_read_comment_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function reader()
    {
        return $this->morphTo();
    }
}
