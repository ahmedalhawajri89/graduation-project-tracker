<?php

namespace App\Support;

use App\Notifications\AdminChangeGroupNotify;
use App\Notifications\StudentReplayProjectNotify;
use Illuminate\Notifications\DatabaseNotification;

/**
 * عرض إشعار الطالب: فئته وأيقونته ومرسِله ورابطه.
 *
 * الإشعارات تُخزَّن نصّاً حرّاً (\u200Emsg\u200E)، فكانت تُعرض كلّها بشكل واحد:
 * قبول المشروع وطلب التعديل وتغيير الإدارة سطورٌ رمادية متشابهة. وإشعار
 * الإدارة كان يُنسب إلى المشرف لأن اسمه في \u200Esupervisor_name\u200E.
 */
class NotificationView
{
    /** @return array{cat: string, source: string, icon: string, tone: string, title: string, sender: string, body: string, href: string} */
    public static function present(DatabaseNotification $n): array
    {
        $data = $n->data;
        $msg = (string) ($data['msg'] ?? '');
        $dash = route('student.dashboard');

        // الإدارة: المرسل الإدارة لا المشرف المذكور في البيانات
        if ($n->type === AdminChangeGroupNotify::class) {
            return self::make('admin', 'admin', 'ti-shield-check', 'is-admin', __('تحديث من الإدارة'), __('الإدارة'), $msg, $dash . '#team');
        }

        $sender = $data['supervisor_name'] ?? __('المشرف');

        if ($n->type === StudentReplayProjectNotify::class) {
            return str_contains($msg, 'رفض')
                ? self::make('reject', 'supervisor', 'ti-circle-x', 'is-danger', __('رُفض طلب الإشراف'), $sender, $msg, $dash)
                : self::make('accept', 'supervisor', 'ti-circle-check', 'is-success', __('قُبل مشروعك'), $sender, $msg, $dash);
        }

        // المناقشة: موعدها وتعديله وإلغاؤه وتذكيرها — من الإدارة، قبل مطابقة النصّ
        // (وإلا التقطت كلمة «موعد» الرسالة كتغيير موعد مرحلة)
        if (($data['kind'] ?? null) === 'defense') {
            return self::make('defense', 'admin', 'ti-presentation', 'is-brand', $data['title'] ?? __('موعد المناقشة'), __('الإدارة'), $msg, $dash . '#defense');
        }

        // تذكير المنصّة بموعد مرحلة: المرسل المنصّة، والرابط إلى المرحلة نفسها
        if (($data['kind'] ?? null) === 'reminder') {
            $href = $dash . (isset($data['milestone_id']) ? '#milestone-' . $data['milestone_id'] : '#milestones');

            return self::make('reminder', 'supervisor', 'ti-alarm', 'is-warn', __('تذكير بموعد'), __('تخرُّج'), $msg, $href);
        }

        // نشاط المشروع: الفئة من نصّ الرسالة — الأخصّ أولاً
        return match (true) {
            // ذكر بـ@ في نقاش الفريق — من زميل
            str_contains($msg, 'في نقاش الفريق') => self::make('mention', 'team', 'ti-at', 'is-brand', __('ذكرك زميل'), $sender, $msg, route('student.discussion', ['tab' => 'team'])),
            // ملاحظة على ملف: من زميل أو من المشرف — المصدر من اسم المرسل لا يُعرف، فهي «الفريق»
            str_contains($msg, 'عولجت ملاحظتك') => self::make('note-done', 'team', 'ti-circle-check', 'is-success', __('عولجت ملاحظتك'), $sender, $msg, $dash . '#files'),
            str_contains($msg, 'ملاحظة على «') => self::make('note', 'team', 'ti-message-2-exclamation', 'is-warn', __('ملاحظة على ملف'), $sender, $msg, $dash . '#files'),
            // من القائد لا المشرف: المرسل زميل
            str_contains($msg, 'عيّنك القائد') => self::make('role', 'team', 'ti-id-badge-2', 'is-brand', __('دورك في المشروع'), $sender, $msg, route('student.team')),
            str_contains($msg, 'مطلوب تعديل') => self::make('revision', 'supervisor', 'ti-pencil', 'is-warn', __('مطلوب تعديل'), $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'اعتُمدت') => self::make('approved', 'supervisor', 'ti-rosette-discount-check', 'is-success', __('اعتُمدت مرحلة'), $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'مرحلة جديدة') => self::make('stage', 'supervisor', 'ti-flag', 'is-brand', __('مرحلة جديدة'), $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'موعد') => self::make('deadline', 'supervisor', 'ti-calendar-due', 'is-warn', __('تغيّر موعد'), $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'تقييم') || str_contains($msg, 'درجة') => self::make('grade', 'supervisor', 'ti-award', 'is-brand', __('التقييم'), $sender, $msg, $dash),
            str_contains($msg, 'ملف') => self::make('file', 'supervisor', 'ti-file-text', '', __('ملف جديد'), $sender, $msg, $dash . '#files'),
            str_contains($msg, 'مرحلة') => self::make('stage', 'supervisor', 'ti-list-check', 'is-brand', __('تحديث مرحلة'), $sender, $msg, $dash . '#milestones'),
            default => self::make('other', 'supervisor', 'ti-bell', '', __('تحديث من المشرف'), $sender, $msg, $dash),
        };
    }

    private static function make(string $cat, string $source, string $icon, string $tone, string $title, string $sender, string $body, string $href): array
    {
        // نصّ الرسالة محفوظ عربياً؛ الثابت منه (بلا أسماء ولا أرقام) له ترجمة بمطابقة كاملة
        $body = __($body);

        return compact('cat', 'source', 'icon', 'tone', 'title', 'sender', 'body', 'href');
    }

    /** عنوان مجموعة اليوم: اليوم، أمس، هذا الأسبوع، أقدم */
    public static function dayGroup(\Carbon\CarbonInterface $at): string
    {
        return match (true) {
            $at->isToday() => __('اليوم'),
            $at->isYesterday() => __('أمس'),
            $at->greaterThanOrEqualTo(today()->subDays(6)) => __('هذا الأسبوع'),
            default => __('أقدم'),
        };
    }
}
