<?php

namespace App\Support;

use App\Models\Defense;

/**
 * ملف تقويم (iCalendar / .ics) للمناقشة.
 *
 * يُرفق بالبريد ويُنزَّل من اللوحات، فيضيفها المستلم إلى Google Calendar
 * أو Outlook أو تقويم الجوال بنقرة — بالموعد والمكان ورابط الاجتماع —
 * بلا أي API ولا ربط حساب. المعرّف ثابت لكل مناقشة، والتسلسل يزيد مع كل
 * تعديل، فإعادة الجدولة تحدّث الحدث نفسه في التقويم ولا تكرّره.
 */
class DefenseIcs
{
    public static function make(Defense $d, bool $cancelled = false): string
    {
        $d->loadMissing(['project.group.student', 'members.supervisor', 'room']);
        $utc = fn ($t) => $t->copy()->utc()->format('Ymd\THis\Z');

        $description = collect([
            __('مناقشة مشروع التخرج: :title', ['title' => $d->project->title]),
            __('الفريق: :names', ['names' => $d->project->group->map(fn ($g) => $g->student?->name)->filter()->implode(__('، '))]),
            __('اللجنة: :names', ['names' => $d->members->map(fn ($m) => $m->supervisor->name . ' (' . $m->role_label . ')')->implode(__('، '))]),
            $d->needsLink() && $d->meeting_url ? __('رابط الاجتماع: :url', ['url' => $d->meeting_url]) : null,
            $d->notes,
        ])->filter()->implode("\n");

        $location = match (true) {
            $d->mode === 'online' => (string) $d->meeting_url,
            default => trim($d->place_label . ($d->room?->location ? ' — ' . $d->room->location : '')),
        };

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Takharruj//Defenses//AR',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:defense-' . $d->id . '@' . (parse_url(config('app.url'), PHP_URL_HOST) ?: 'takharruj'),
            'SEQUENCE:' . ($d->updated_at?->timestamp ?? 0) % 100000,
            'DTSTAMP:' . $utc(now()),
            'DTSTART:' . $utc($d->starts_at),
            'DTEND:' . $utc($d->endsAt()),
            'SUMMARY:' . self::escape(__('مناقشة: :title', ['title' => $d->project->title])),
            'DESCRIPTION:' . self::escape($description),
            'LOCATION:' . self::escape($location),
            $d->needsLink() && $d->meeting_url ? 'URL:' . $d->meeting_url : null,
            'STATUS:' . ($cancelled ? 'CANCELLED' : 'CONFIRMED'),
            // تنبيه التقويم قبل ساعة
            $cancelled ? null : 'BEGIN:VALARM',
            $cancelled ? null : 'TRIGGER:-PT1H',
            $cancelled ? null : 'ACTION:DISPLAY',
            $cancelled ? null : 'DESCRIPTION:' . self::escape(__('المناقشة بعد ساعة')),
            $cancelled ? null : 'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", array_map([self::class, 'fold'], array_filter($lines, fn ($l) => $l !== null))) . "\r\n";
    }

    public static function filename(Defense $d): string
    {
        return 'defense-' . $d->id . '.ics';
    }

    /** الفاصلة والفاصلة المنقوطة والشرطة المائلة والسطر الجديد تُهرَّب كما يطلب المعيار */
    private static function escape(string $text): string
    {
        return str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], $text);
    }

    /** السطر لا يتجاوز 75 بايتاً؛ يُطوى على حدود الحروف لا البايتات (العربية متعددة البايت) */
    private static function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $out = [];
        $current = '';
        foreach (mb_str_split($line) as $char) {
            $limit = $out ? 74 : 75; // السطر المطويّ يبدأ بمسافة
            if (strlen($current . $char) > $limit) {
                $out[] = $current;
                $current = '';
            }
            $current .= $char;
        }
        $out[] = $current;

        return implode("\r\n ", $out);
    }
}
