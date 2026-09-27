<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    protected $table = 'project_milestones';

    /** open ← submitted ← approved | revision — و\u200Eapproved\u200E ⇔ \u200Eis_done\u200E */
    public const OPEN = 'open';
    public const SUBMITTED = 'submitted';
    public const REVISION = 'revision';
    public const APPROVED = 'approved';

    protected $fillable = [
        'project_id',
        'stage_id',
        'title',
        'note',
        'due_date',
        'is_done',
        'status',
        'done_at',
    ];

    protected $attributes = [
        'status' => self::OPEN,
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

    /** جولات التسليم، الأحدث أولاً */
    public function submissions()
    {
        return $this->hasMany(MilestoneSubmission::class, 'milestone_id')->orderByDesc('round');
    }

    /** يستطيع الفريق أن يسلّمها: مفتوحة، أو أُعيدت بطلب تعديل */
    public function canSubmit(): bool
    {
        return in_array($this->status, [self::OPEN, self::REVISION], true);
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::SUBMITTED;
    }

    public function needsRevision(): bool
    {
        return $this->status === self::REVISION;
    }

    /**
     * متأخّرة: فات موعدها ولم تُنجز — والمسلَّمة بانتظار المراجعة ليست متأخّرة،
     * فالانتظار على المشرف لا على الفريق.
     */
    public function isLate(): bool
    {
        return ! $this->is_done
            && $this->status !== self::SUBMITTED
            && $this->due_date
            && $this->due_date->isPast();
    }
}
