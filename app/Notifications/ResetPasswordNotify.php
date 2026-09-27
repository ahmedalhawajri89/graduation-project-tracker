<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

/**
 * رسالة استرجاع كلمة المرور.
 *
 * بديل \u200EIlluminate\Auth\Notifications\ResetPassword\u200E لسببين: النصّ
 * بالعربية، والرابط يحمل الدور — فالنظام ثلاثة حُرّاس، ونموذج إعادة
 * التعيين لا يعرف أي وسيط كلمات مرور يستعمل بلا هذه المعلومة.
 */
class ResetPasswordNotify extends Notification implements ShouldQueue
{
    use Queueable;

    /** في الطابور: إرسال الرابط لا يُبطئ الطلب ولا يكشف بزمنه وجود الحساب */
    // afterCommit() في المُنشئ لا خاصية: Queueable يعرّفها فيتعارض التعريفان

    public function __construct(
        public string $token,
        public string $guard,
    ) {
        $this->afterCommit();
    }

    /**
     * البريد وحده: إشعار داخل المنصّة لا معنى له لمن لا يستطيع الدخول
     * إليها أصلاً.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $minutes = config("auth.passwords.{$this->guard}s.expire", 30);

        // من APP_URL لا من ترويسة Host: ‎url()‎ يبني الرابط من الطلب، فطلب استرجاع
        // بترويسة Host مزوّرة كان يُرسل للضحية رابطاً إلى نطاق المهاجم يحمل رمزها.
        // وفي الطابور لا طلب أصلاً يُبنى منه
        $url = rtrim(config('app.url'), '/') . route('password.reset', [
            'guard' => $this->guard,
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false);

        return (new MailMessage)
            ->subject('استرجاع كلمة المرور — منصّة تخرُّج')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('وصلنا طلب لإعادة تعيين كلمة المرور لحسابك في منصّة تخرُّج.')
            ->action('إعادة تعيين كلمة المرور', $url)
            ->line("الرابط صالح لمدة {$minutes} دقيقة.")
            // من لم يطلب شيئاً يحتاج أن يعرف أن لا إجراء عليه — وإلا
            // ظنّ حسابه مُخترقاً
            ->line('إن لم تطلب هذا فتجاهل الرسالة: لن يتغيّر شيء في حسابك.')
            ->salutation('فريق تخرُّج');
    }
}
