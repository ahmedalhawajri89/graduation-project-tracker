<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    // الحذف ناعم: المشروع يُخفى ويبقى قابلاً للاسترجاع بمراحله وملفاته
    // وتعليقاته ودرجته. كل الاستعلامات القائمة تستثني المحذوف تلقائياً.
    use HasFactory, SoftDeletes;

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
        'grade_locked_at',
        'graded_by',
    ];

    protected $casts = [
        'date_line' => 'date',
        'evaluated_at' => 'datetime',
        'grade_locked_at' => 'datetime',
    ];

    /**
     * الدرجة معتمدة: لا يعدّلها المشرف بعدها، ولا يفكّها إلا مسؤول
     * النظام بسبب مكتوب.
     */
    public function isGradeLocked(): bool
    {
        return ! is_null($this->grade_locked_at);
    }

    /** لا معنى لاعتماد درجة لم تُوضع بعد */
    public function canLockGrade(): bool
    {
        return ! is_null($this->grade) && ! $this->isGradeLocked();
    }

    public function grader()
    {
        return $this->belongsTo(Supervisor::class, 'graded_by', 'id')
            ->withDefault([
                'name' => '',
            ]);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'subject')->latest('created_at');
    }

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

    /** المناقشة: واحدة لكل مشروع (المجدولة أو المنتهية أو الملغاة) */
    public function defense()
    {
        return $this->hasOne(Defense::class, 'project_id', 'id');
    }

    public function milestones()
    {
        // بالموعد ثم بالإنشاء: مراحل الخطة تُنشأ في أوقات مختلفة، والترتيب
        // بالمعرّف كان يضع «الفصل الأول» بعد «العرض النهائي» إن أُضيف لاحقاً
        return $this->hasMany(ProjectMilestone::class, 'project_id', 'id')
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->orderBy('id');
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class, 'project_id', 'id')->latest();
    }

    /**
     * نقاش المشرف وحده. كل قارئ قديم (خيط المشرف وصندوقه، «مجموعة تنتظر
     * ردّك»، صفحة الأدمن) يمرّ بهذه العلاقة — فنقاش الفريق لا يصله أبداً.
     */
    public function comments()
    {
        return $this->hasMany(ProjectComment::class, 'project_id', 'id')
            ->where('project_comments.channel', ProjectComment::SUPERVISOR)
            ->orderBy('id');
    }

    /** نقاش الفريق — لأعضائه وحدهم، لا المشرف ولا الإدارة */
    public function teamComments()
    {
        return $this->hasMany(ProjectComment::class, 'project_id', 'id')
            ->where('project_comments.channel', ProjectComment::TEAM)
            ->orderBy('id');
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
