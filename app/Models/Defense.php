<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * مناقشة مشروع: موعدها، ونوعها (حضوري/عن بُعد/مدمج)، وقاعتها أو رابطها،
 * ولجنتها. القواعد (التعارض، المقترحون) في App\Support\DefenseScheduler.
 */
class Defense extends Model
{
    public const SCHEDULED = 'scheduled';
    public const DONE = 'done';
    public const CANCELLED = 'cancelled';

    public const MODES = [
        'in_person' => ['label' => 'حضوري', 'icon' => 'ti-building'],
        'online' => ['label' => 'عن بُعد', 'icon' => 'ti-video'],
        'hybrid' => ['label' => 'مدمج', 'icon' => 'ti-device-laptop'],
    ];

    public const DURATIONS = [30, 45, 60, 90];

    protected $fillable = [
        'project_id', 'starts_at', 'duration_minutes', 'mode', 'room_id',
        'meeting_url', 'status', 'notes', 'reminded_on', 'scheduled_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'reminded_on' => 'date',
        'duration_minutes' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function room()
    {
        return $this->belongsTo(DefenseRoom::class, 'room_id');
    }

    public function members()
    {
        // المشرف أولاً ثم الممتحن
        return $this->hasMany(DefenseMember::class)->orderByRaw("role = 'examiner'");
    }

    /** رئيس اللجنة: العضو المعلَّم، وإلا مشرف المشروع */
    public function chair(): ?DefenseMember
    {
        return $this->members->firstWhere('is_chair', true) ?? $this->members->firstWhere('role', 'supervisor');
    }

    /** «الممتحن · رئيس اللجنة» — صفة العضو كما تُعرض */
    public function roleOf(DefenseMember $m): string
    {
        return $m->role_label . ($this->chair()?->id === $m->id ? ' · رئيس اللجنة' : '');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::SCHEDULED);
    }

    /** تتداخل مع الفترة [من، إلى) — للتحقق من تعارض القاعة والأعضاء */
    public function scopeOverlapping(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->active()
            ->where('starts_at', '<', $to)
            ->whereRaw('DATE_ADD(starts_at, INTERVAL duration_minutes MINUTE) > ?', [$from]);
    }

    public function endsAt(): Carbon
    {
        return $this->starts_at->copy()->addMinutes($this->duration_minutes);
    }

    public function needsRoom(): bool
    {
        return in_array($this->mode, ['in_person', 'hybrid'], true);
    }

    public function needsLink(): bool
    {
        return in_array($this->mode, ['online', 'hybrid'], true);
    }

    public function getModeLabelAttribute(): string
    {
        return self::MODES[$this->mode]['label'] ?? $this->mode;
    }

    public function getModeIconAttribute(): string
    {
        return self::MODES[$this->mode]['icon'] ?? 'ti-presentation';
    }

    /** «قاعة 204» أو «عن بُعد» أو «قاعة 204 + عن بُعد» — سطر المكان في كل مكان */
    public function getPlaceLabelAttribute(): string
    {
        $room = $this->room?->name;

        return match ($this->mode) {
            'online' => 'عن بُعد',
            'hybrid' => ($room ?: 'قاعة غير محدّدة') . ' + عن بُعد',
            default => $room ?: 'قاعة غير محدّدة',
        };
    }

    /**
     * «أضف إلى تقويم Google» — رابط قالب لا يحتاج API ولا حساباً مرتبطاً.
     * الأوقات بـ UTC كما يتوقّعها التقويم.
     */
    public function googleCalendarUrl(): string
    {
        $fmt = fn (Carbon $t) => $t->copy()->utc()->format('Ymd\THis\Z');
        $title = 'مناقشة مشروع: ' . ($this->project?->title ?? '');
        $details = trim(implode("\n", array_filter([
            $this->needsLink() && $this->meeting_url ? 'رابط الاجتماع: ' . $this->meeting_url : null,
            $this->notes,
            'منصّة تخرُّج',
        ])));

        return 'https://calendar.google.com/calendar/render?' . http_build_query([
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $fmt($this->starts_at) . '/' . $fmt($this->endsAt()),
            'details' => $details,
            'location' => $this->needsLink() && ! $this->needsRoom() ? ($this->meeting_url ?? '') : $this->place_label,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /** زر «انضم» يظهر من قبل الموعد بربع ساعة حتى نهايته */
    public function isJoinable(): bool
    {
        return $this->status === self::SCHEDULED && $this->needsLink() && $this->meeting_url
            && now()->between($this->starts_at->copy()->subMinutes(15), $this->endsAt());
    }
}
