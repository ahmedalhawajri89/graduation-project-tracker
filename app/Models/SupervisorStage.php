<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** مرحلة في خطة المشرف — أصلٌ تُنسخ عنه مرحلة في كل مجموعة. انظر \App\Support\StagePlan */
class SupervisorStage extends Model
{
    protected $fillable = [
        'supervisor_id',
        'semester_id',
        'title',
        'instructions',
        'due_date',
        'template_path',
        'template_name',
        'template_size',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class);
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class, 'stage_id');
    }

    public function hasTemplate(): bool
    {
        return (bool) $this->template_path;
    }

    /** امتداد القالب — لشارة نوعه */
    public function templateExtension(): string
    {
        return strtolower(pathinfo((string) $this->template_name, PATHINFO_EXTENSION));
    }

    /** شارة نوع القالب [صنف، تسمية] — كشارات بطاقة الملفات */
    public function templateBadge(): array
    {
        return self::badgeFor($this->template_name);
    }

    /** شارة نوع أيّ ملف باسمه — للقالب ولملف التسليم */
    public static function badgeFor(?string $fileName): array
    {
        $ext = strtolower(pathinfo((string) $fileName, PATHINFO_EXTENSION));

        return match (true) {
            $ext === 'pdf' => ['is-pdf', 'PDF'],
            in_array($ext, ['doc', 'docx'], true) => ['is-doc', 'DOC'],
            in_array($ext, ['ppt', 'pptx'], true) => ['is-ppt', 'PPT'],
            in_array($ext, ['xls', 'xlsx'], true) => ['is-xls', 'XLS'],
            in_array($ext, ['png', 'jpg', 'jpeg'], true) => ['is-img', 'IMG'],
            default => ['is-zip', strtoupper($ext) ?: 'FILE'],
        };
    }

    public function templateSizeLabel(): string
    {
        $size = (int) $this->template_size;

        return match (true) {
            $size >= 1048576 => round($size / 1048576, 1) . ' MB',
            $size >= 1024 => round($size / 1024) . ' KB',
            default => $size . ' B',
        };
    }
}
