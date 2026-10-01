<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** جولة تسليم لمرحلة: ما سلّمه الطالب، وردّ المشرف عليه */
class MilestoneSubmission extends Model
{
    protected $fillable = [
        'milestone_id',
        'student_id',
        'round',
        'note',
        'file_path',
        'file_name',
        'file_size',
        'decision',
        'feedback',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function milestone()
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id')->withDefault(['name' => __('عضو سابق')]);
    }

    public function reviewer()
    {
        return $this->belongsTo(Supervisor::class, 'reviewed_by');
    }

    public function hasFile(): bool
    {
        return (bool) $this->file_path;
    }

    /** شارة نوع الملف — كقالب المرحلة */
    public function fileBadge(): array
    {
        return SupervisorStage::badgeFor($this->file_name);
    }
}
