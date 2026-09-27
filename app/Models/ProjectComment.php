<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    /** قناة المشرف (الافتراضية) ونقاش الفريق الخاص بأعضائه */
    public const SUPERVISOR = 'supervisor';
    public const TEAM = 'team';

    protected $table = 'project_comments';

    protected $fillable = [
        'project_id',
        'channel',
        'author_type',
        'author_id',
        'body',
        'mentions',
    ];

    protected $casts = [
        'mentions' => 'array',
    ];

    protected $attributes = [
        'channel' => self::SUPERVISOR,
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

    public function scopeChannel($query, string $channel)
    {
        return $query->where('project_comments.channel', $channel);
    }

    public function isTeam(): bool
    {
        return $this->channel === self::TEAM;
    }

    /** هل ذُكر هذا الطالب بـ@ في الرسالة؟ */
    public function mentions(int $studentId): bool
    {
        return in_array($studentId, array_map('intval', $this->mentions ?? []), true);
    }
}
