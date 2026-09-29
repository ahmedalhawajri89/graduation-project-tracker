<?php

namespace App\Support;

use App\Models\Defense;
use App\Notifications\ProjectActivityNotify;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * إشعارات المناقشة للفريق ولجنتها (المشرف والممتحن)، على المنصّة وبالبريد.
 * نصّ واحد يحمل الموعد والمكان — والرابط حين تكون عن بُعد أو مدمجة.
 */
class DefenseNotifier
{
    /** «الأحد 12 أكتوبر 2026 · 10:00–10:45 · قاعة 204» */
    public static function when(Defense $d): string
    {
        // المدى معزول LTR (LRI…PDI): داخل نصّ عربي كان «10:00–10:45» يُقرأ «10:45–10:00»
        return $d->starts_at->translatedFormat('l j F Y')
            . ' · ' . self::range($d)
            . ' · ' . $d->place_label;
    }

    public static function range(Defense $d): string
    {
        return "\u{2066}" . $d->starts_at->format('H:i') . '–' . $d->endsAt()->format('H:i') . "\u{2069}";
    }

    public static function scheduled(Defense $d): void
    {
        self::send($d, 'موعد المناقشة', 'حُدّد موعد مناقشة مشروعكم: ' . self::when($d) . self::link($d), 'موعد مناقشة المشروع');
    }

    public static function rescheduled(Defense $d, string $before): void
    {
        self::send($d, 'تغيّر موعد المناقشة', 'تغيّر موعد المناقشة إلى: ' . self::when($d) . ' (كان: ' . $before . ')' . self::link($d), 'تغيّر موعد المناقشة');
    }

    public static function cancelled(Defense $d, ?string $reason): void
    {
        self::send($d, 'أُلغيت المناقشة', 'أُلغي موعد المناقشة (' . self::when($d) . ')' . ($reason ? ' — ' . $reason : '') . '. سيُحدَّد موعد جديد.', 'إلغاء موعد المناقشة');
    }

    public static function reminder(Defense $d, bool $today): void
    {
        $lead = $today ? 'مناقشتكم اليوم: ' : 'تذكير — مناقشتكم غداً: ';
        self::send($d, $today ? 'المناقشة اليوم' : 'المناقشة غداً', $lead . self::when($d) . self::link($d), $today ? 'المناقشة اليوم' : 'تذكير بموعد المناقشة');
    }

    private static function link(Defense $d): string
    {
        return $d->needsLink() && $d->meeting_url ? ' — رابط الاجتماع: ' . $d->meeting_url : '';
    }

    /** الفريق إلى لوحته، واللجنة إلى لوحتها — لكلٍّ إشعار ببريد، وفشل واحد لا يوقف البقية */
    private static function send(Defense $d, string $title, string $msg, string $subject): void
    {
        $d->loadMissing(['project.group', 'members.supervisor']);
        $project = $d->project;

        $data = [
            'project' => $project->title,
            'supervisor_name' => 'الإدارة',
            'msg' => $msg,
            'kind' => 'defense',
            'title' => $title,
            'defense_id' => $d->id,
        ];

        try {
            Notification::send($project->students(), ProjectActivityNotify::withMail($data, $subject, route('student.dashboard') . '#defense'));
        } catch (\Throwable $e) {
            Log::warning('defense notify (team) failed', ['defense' => $d->id, 'error' => $e->getMessage()]);
        }

        $committee = $d->members->pluck('supervisor')->filter(fn ($s) => $s && $s->exists);
        try {
            Notification::send($committee, ProjectActivityNotify::withMail($data, $subject, route('supervisor.dashboard')));
        } catch (\Throwable $e) {
            Log::warning('defense notify (committee) failed', ['defense' => $d->id, 'error' => $e->getMessage()]);
        }
    }
}
