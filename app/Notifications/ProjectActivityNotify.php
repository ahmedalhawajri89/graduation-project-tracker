<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * إشعار عام لتحديثات المشروع (مراحل، ملفات، تعليقات، مواعيد، تقييم).
 * المفاتيح متوافقة مع صفحات الإشعارات الحالية:
 * project (عنوان المشروع)، supervisor_name (اسم المُرسِل)، msg (النص).
 */
class ProjectActivityNotify extends Notification
{
    use Queueable;

    private $data;

    public function __construct(array $data)
    {
        $this->data = array_merge(['kind' => 'activity'], $data);
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return $this->data;
    }
}
