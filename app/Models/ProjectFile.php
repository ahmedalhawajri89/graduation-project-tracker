<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $table = 'project_files';

    protected $fillable = [
        'project_id',
        'uploader_type',
        'uploader_id',
        'title',
        'path',
        'size',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function uploader()
    {
        return $this->morphTo();
    }

    /** حجم الملف بصيغة مقروءة */
    public function getHumanSizeAttribute()
    {
        $size = (int) $this->size;
        if ($size >= 1048576) {
            return round($size / 1048576, 1) . ' MB';
        }
        if ($size >= 1024) {
            return round($size / 1024) . ' KB';
        }

        return $size . ' B';
    }
}
