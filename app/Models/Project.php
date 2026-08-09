<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $fillable = [
        'title',
        'description',
        'semester_id',
        'supervisor_id',
        'specialize_project_id',
        'status', //['request', 'accept', 'reject', 'complete']
        'date_line',
        'grade',
        'evaluation_note',
        'evaluated_at',
    ];

    protected $casts = [
        'date_line' => 'date',
        'evaluated_at' => 'datetime',
    ];

    ################# relations

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'id')
            ->withDefault([
                'name' => '',
            ]);
    }
    public function supervisor()
    {
        return $this->belongsTo(Supervisor::class, 'supervisor_id', 'id')
            ->withDefault([
                'name' => '',
            ]);
    }
    public function project_type()
    {
        return $this->belongsTo(SpecializeProject::class, 'specialize_project_id', 'id')
            ->withDefault([
                'name' => '',
            ]);
    }

    public function group()
    {
        return $this->hasMany(Group::class, 'project_id', 'id');
    }

    public function milestones()
    {
        return $this->hasMany(ProjectMilestone::class, 'project_id', 'id')->orderBy('id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id', 'id')->latest();
    }

    public function comments()
    {
        return $this->hasMany(ProjectComment::class, 'project_id', 'id')->orderBy('id');
    }

    ################# end relations

    /** طلاب المشروع (عبر المجموعات) */
    public function students()
    {
        return Student::whereIn('id', $this->group->pluck('student_id'))->get();
    }

    /** الأيام المتبقية للموعد النهائي (سالبة إذا انقضى) */
    public function getDaysLeftAttribute()
    {
        if (! $this->date_line) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->date_line->startOfDay(), false);
    }

    /**
     * قفل ناعم بعد التقييم:
     * بعد رصد الدرجة يصبح المشروع أرشيفياً — تُقفل المراحل والملفات والموعد النهائي،
     * ويبقى النقاش وتعديل الدرجة متاحين.
     */
    public function getIsLockedAttribute()
    {
        return ! is_null($this->grade);
    }

    /** التقدير حسب الدرجة */
    public function getGradeLabelAttribute()
    {
        if (is_null($this->grade)) {
            return null;
        }
        $g = (float) $this->grade;
        if ($g >= 90) return 'ممتاز';
        if ($g >= 80) return 'جيد جداً';
        if ($g >= 70) return 'جيد';
        if ($g >= 60) return 'مقبول';

        return 'راسب';
    }

    /** نسبة الإنجاز من المراحل المنجزة (null إذا لا توجد مراحل) */
    public function getProgressAttribute()
    {
        $total = $this->milestones->count();
        if ($total === 0) {
            return null;
        }

        return (int) round($this->milestones->where('is_done', true)->count() * 100 / $total);
    }

}
