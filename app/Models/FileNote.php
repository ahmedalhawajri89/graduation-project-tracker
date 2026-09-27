<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** ملاحظة على ملف مشروع — تنبّه عضواً، وتُغلق حين تُعالَج */
class FileNote extends Model
{
    protected $fillable = [
        'project_file_id',
        'author_type',
        'author_id',
        'mentioned_id',
        'body',
        'resolved_at',
        'resolved_by_type',
        'resolved_by_id',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function file()
    {
        return $this->belongsTo(ProjectFile::class, 'project_file_id');
    }

    public function author()
    {
        return $this->morphTo();
    }

    public function mentioned()
    {
        return $this->belongsTo(Student::class, 'mentioned_id');
    }

    public function resolver()
    {
        return $this->morphTo('resolved_by');
    }

    public function isOpen(): bool
    {
        return is_null($this->resolved_at);
    }

    public function isBy(Model $person): bool
    {
        return $this->author_type === $person::class && (int) $this->author_id === (int) $person->getKey();
    }
}
