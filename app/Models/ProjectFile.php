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

    /**
     * العرض التقديمي للمناقشة ملف مشروع كغيره (التنزيل والصلاحيات نفسها)،
     * يميّزه مجلّده — فلا عمود جديد ولا ترحيل.
     */
    public const PRESENTATION_DIR = 'defense_presentations';

    public function isPresentation(): bool
    {
        return str_starts_with((string) $this->path, self::PRESENTATION_DIR . '/');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function uploader()
    {
        return $this->morphTo();
    }

    /** ملاحظات الفريق والمشرف على الملف، الأقدم أولاً كمحادثة */
    public function notes()
    {
        return $this->hasMany(FileNote::class, 'project_file_id')->orderBy('id');
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
