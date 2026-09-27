<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuperVisorRequestProjectNotify extends Notification implements ShouldQueue
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

    private $project;
    private $groups;
    private $project_type;

    public function __construct($project, $groups, $project_type)
    {
        $this->afterCommit();
        $this->project = $project;
        $this->groups = $groups;
        $this->project_type = $project_type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    /**
     * البريد هنا ليس ترفاً: المشرف الذي لا يفتح المنصّة لا يعلم أن
     * طلباً ينتظره، فيبقى الطلب معلّقاً أسابيع بلا سبب ظاهر — وطلبة
     * ينتظرون. وإشعار قاعدة البيانات وحده لا يصل إلى من ليس داخلاً.
     */
    public function via($notifiable)
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toMail($notifiable)
    {
        $count = count($this->groups);

        return (new MailMessage)
            ->subject('طلب مشروع جديد بانتظار ردّك — تخرُّج')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('وصلك طلب إشراف على مشروع تخرّج جديد:')
            ->line('**' . $this->project->title . '**')
            ->line('النوع: ' . $this->project_type . ' · عدد أعضاء الفريق: ' . $count)
            ->action('مراجعة الطلب', route('supervisor.showNotification'))
            ->line('الطلب يبقى معلّقاً حتى تقبله أو ترفضه، والطلبة ينتظرون ردّك.')
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
        $students = Student::whereIn('university_id', $this->groups)
            ->with([
                'groups',
                'specialize',
            ])
            ->get();

        return [
            'project_id' => $this->project->id,
            'title' => $this->project->title,
            'description' => $this->project->description,
            'type' => $this->project_type,
            'students' => $students,
        ];
    }
}
