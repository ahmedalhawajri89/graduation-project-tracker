<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * إشعار عام لتحديثات المشروع (مراحل، ملفات، تعليقات، مواعيد، تقييم).
 * المفاتيح متوافقة مع صفحات الإشعارات الحالية:
 * project (عنوان المشروع)، supervisor_name (اسم المُرسِل)، msg (النص).
 *
 * الجرس وحده افتراضياً. وما يستدعي تصرّفاً من الطالب — طلب تعديل، الدرجة،
 * موعد يقترب — يُرسل بريداً أيضاً عبر {@see self::withMail()}: الطالب لا يفتح
 * المنصّة كل يوم، وإرسال البريد مع كل ملف ورسالة يجعله ضجيجاً يُتجاهَل.
 */
class ProjectActivityNotify extends Notification implements ShouldQueue
{
    use Queueable;

    private $data;

    private ?string $mailSubject = null;

    private ?string $mailUrl = null;

    public function __construct(array $data)
    {
        $this->data = array_merge(['kind' => 'activity'], $data);
    }

    /** إشعار يصل بالبريد أيضاً — لمن له بريد */
    public static function withMail(array $data, string $subject, ?string $url = null): self
    {
        $notification = new self($data);
        $notification->mailSubject = $subject;
        $notification->mailUrl = $url;

        return $notification;
    }

    /** نسخة المنصّة فوريّة — الجرس لا ينتظر العامل؛ البريد وحده في الطابور */
    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    public function via($notifiable)
    {
        return $this->mailSubject && $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->mailSubject . ' — تخرُّج')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('**' . ($this->data['project'] ?? 'مشروعك') . '**')
            ->line($this->data['msg'] ?? '')
            ->action('فتح لوحتي', $this->mailUrl ?? route('student.dashboard'))
            ->salutation('فريق تخرُّج');
    }

    public function toArray($notifiable)
    {
        return $this->data;
    }
}
