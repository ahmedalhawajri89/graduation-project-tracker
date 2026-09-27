<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentReplayProjectNotify extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * البريد في الطابور وبعد الالتزام: كان يُرسل داخل معاملة القرار، فتعطّل
     * SMTP يُرجع القبول أو الرفض كلّه بعد أن يكون بعض الطلاب قد بلغهم.
     * ونسخة المنصّة (database) فوريّة — الجرس لا ينتظر العامل.
     */
    // afterCommit() في المُنشئ لا خاصية: Queueable يعرّفها فيتعارض التعريفان

    public function viaConnections(): array
    {
        return ['database' => 'sync'];
    }

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
        $this->afterCommit();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    /**
     * البريد هنا لأن الردّ يقطع انتظاراً: الطالب لا يفتح المنصّة كل
     * يوم ليرى إن قُبلت فكرته أم رُفضت، والرفض يحتاج بدءاً من جديد.
     */
    public function via($notifiable)
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('ردّ المشرف على مشروعك — تخرُّج')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('**' . ($this->data['project'] ?? 'مشروعك') . '**')
            ->line($this->data['msg'] ?? 'ردّ المشرف على طلبكم.')
            ->line('المشرف: ' . ($this->data['supervisor_name'] ?? '—'))
            ->action('فتح لوحتي', route('student.dashboard'))
            ->salutation('فريق تخرُّج');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return $this->data;
    }
}
