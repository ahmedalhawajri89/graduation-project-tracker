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
        // يُحفظ في الإشعارات وسجلّ التدقيق: عربي دائماً مهما كانت لغة من جدول
        return Arabic::run(fn () => $d->starts_at->translatedFormat('l j F Y')
            . ' · ' . self::range($d)
            . ' · ' . $d->place_label);
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

    /** اللجنة تعرف أن العرض صار جاهزاً للتحضير منه */
    public static function presentationUploaded(Defense $d, string $by, bool $replaced): void
    {
        $d->loadMissing(['project', 'members.supervisor']);

        try {
            Notification::send($d->members->pluck('supervisor')->filter(fn ($s) => $s && $s->exists), new ProjectActivityNotify([
                'project' => $d->project->title,
                'supervisor_name' => $by,
                'msg' => ($replaced ? 'استبدل الفريق العرض التقديمي لمناقشة «' : 'رفع الفريق العرض التقديمي لمناقشة «') . $d->project->title . '».',
                'kind' => 'defense',
                'title' => 'العرض التقديمي',
                'defense_id' => $d->id,
            ]));
        } catch (\Throwable $e) {
            Log::warning('presentation notify failed', ['defense' => $d->id, 'error' => $e->getMessage()]);
        }
    }

    /** قبل المناقشة بيوم والعرض لم يُرفع: تنبيه للفريق وحده */
    public static function presentationMissing(Defense $d): void
    {
        $d->loadMissing('project.group');

        try {
            Notification::send($d->project->students(), new ProjectActivityNotify([
                'project' => $d->project->title,
                'supervisor_name' => 'الإدارة',
                'msg' => 'لم يُرفع العرض التقديمي بعد — ارفعوه من بطاقة «مناقشتك» لتطّلع عليه اللجنة قبل الموعد.',
                'kind' => 'defense',
                'title' => 'العرض التقديمي',
                'defense_id' => $d->id,
            ]));
        } catch (\Throwable $e) {
            Log::warning('presentation reminder failed', ['defense' => $d->id, 'error' => $e->getMessage()]);
        }
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
        // ملف التقويم مع البريد: يُضاف إلى أي تقويم بنقرة، والإلغاء يحذفه منه
        // يُرفق بالبريد: عربي كالإشعار نفسه (التنزيل من اللوحة يتبع لغة المستخدم)
        $ics = Arabic::run(fn () => DefenseIcs::make($d, $d->status === Defense::CANCELLED));
        $withIcs = fn ($n) => $n->attach($ics, DefenseIcs::filename($d), 'text/calendar');

        $data = [
            'project' => $project->title,
            'supervisor_name' => 'الإدارة',
            'msg' => $msg,
            'kind' => 'defense',
            'title' => $title,
            'defense_id' => $d->id,
        ];

        try {
            Notification::send($project->students(), $withIcs(ProjectActivityNotify::withMail($data, $subject, route('student.dashboard') . '#defense')));
        } catch (\Throwable $e) {
            Log::warning('defense notify (team) failed', ['defense' => $d->id, 'error' => $e->getMessage()]);
        }

        $committee = $d->members->pluck('supervisor')->filter(fn ($s) => $s && $s->exists);
        try {
            Notification::send($committee, $withIcs(ProjectActivityNotify::withMail($data, $subject, route('supervisor.defenses.show', $d->id))));
        } catch (\Throwable $e) {
            Log::warning('defense notify (committee) failed', ['defense' => $d->id, 'error' => $e->getMessage()]);
        }
    }
}
