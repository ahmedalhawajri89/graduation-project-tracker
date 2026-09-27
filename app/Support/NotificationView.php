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
            return self::make('admin', 'admin', 'ti-shield-check', 'is-admin', 'تحديث من الإدارة', 'الإدارة', $msg, $dash . '#team');
        }

        $sender = $data['supervisor_name'] ?? 'المشرف';

        if ($n->type === StudentReplayProjectNotify::class) {
            return str_contains($msg, 'رفض')
                ? self::make('reject', 'supervisor', 'ti-circle-x', 'is-danger', 'رُفض طلب الإشراف', $sender, $msg, $dash)
                : self::make('accept', 'supervisor', 'ti-circle-check', 'is-success', 'قُبل مشروعك', $sender, $msg, $dash);
        }

        // نشاط المشروع: الفئة من نصّ الرسالة — الأخصّ أولاً
        return match (true) {
            // من القائد لا المشرف: المرسل زميل
            str_contains($msg, 'عيّنك القائد') => self::make('role', 'team', 'ti-id-badge-2', 'is-brand', 'دورك في المشروع', $sender, $msg, route('student.team')),
            str_contains($msg, 'مطلوب تعديل') => self::make('revision', 'supervisor', 'ti-pencil', 'is-warn', 'مطلوب تعديل', $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'اعتُمدت') => self::make('approved', 'supervisor', 'ti-rosette-discount-check', 'is-success', 'اعتُمدت مرحلة', $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'مرحلة جديدة') => self::make('stage', 'supervisor', 'ti-flag', 'is-brand', 'مرحلة جديدة', $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'موعد') => self::make('deadline', 'supervisor', 'ti-calendar-due', 'is-warn', 'تغيّر موعد', $sender, $msg, $dash . '#milestones'),
            str_contains($msg, 'تقييم') || str_contains($msg, 'درجة') => self::make('grade', 'supervisor', 'ti-award', 'is-brand', 'التقييم', $sender, $msg, $dash),
            str_contains($msg, 'ملف') => self::make('file', 'supervisor', 'ti-file-text', '', 'ملف جديد', $sender, $msg, $dash . '#files'),
            str_contains($msg, 'مرحلة') => self::make('stage', 'supervisor', 'ti-list-check', 'is-brand', 'تحديث مرحلة', $sender, $msg, $dash . '#milestones'),
            default => self::make('other', 'supervisor', 'ti-bell', '', 'تحديث من المشرف', $sender, $msg, $dash),
        };
    }

    private static function make(string $cat, string $source, string $icon, string $tone, string $title, string $sender, string $body, string $href): array
    {
        return compact('cat', 'source', 'icon', 'tone', 'title', 'sender', 'body', 'href');
    }

    /** عنوان مجموعة اليوم: اليوم، أمس، هذا الأسبوع، أقدم */
    public static function dayGroup(\Carbon\CarbonInterface $at): string
    {
        return match (true) {
            $at->isToday() => 'اليوم',
            $at->isYesterday() => 'أمس',
            $at->greaterThanOrEqualTo(today()->subDays(6)) => 'هذا الأسبوع',
            default => 'أقدم',
        };
    }
}
