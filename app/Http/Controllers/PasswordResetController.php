<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * استرجاع كلمة المرور.
 *
 * لم يكن في النظام مسار استرجاع إطلاقاً — ٨٠٠ مستخدم وطريق واحد لمن
 * نسي كلمته: أن يعدّلها المسؤول يدوياً. والمسؤول نفسه بلا استرجاع.
 *
 * ثلاثة حُرّاس، فالمستخدم لا يُسأل عن دوره: يُستنتج من الحساب نفسه
 * كما يفعل تسجيل الدخول.
 */
class PasswordResetController extends Controller
{
    /** الترتيب نفسه المستعمل في \u200EAuthController::login\u200E */
    private const GUARDS = [
        'admin' => [Admin::class, 'admins'],
        'supervisor' => [Supervisor::class, 'supervisors'],
        'student' => [Student::class, 'students'],
    ];

    public function requestForm()
    {
        return view('login.forgot');
    }

    public function sendLink(Request $request)
    {
        $request->validate([
            'identify' => 'required|string|max:100',
        ], [], ['identify' => 'البريد الإلكتروني أو الرقم الجامعي']);

        $identify = trim($request->identify);
        $match = $this->locate($identify);

        if ($match) {
            [$guard, $user] = $match;

            try {
                Password::broker(self::GUARDS[$guard][1])
                    ->sendResetLink(['email' => $user->email]);
            } catch (Throwable $e) {
                // إعداد بريد معطوب لا يُعرَض للمستخدم، لكنه لا يُبتلع
                // أيضاً: يُسجَّل ليراه المطوّر
                Log::error('فشل إرسال رابط استرجاع كلمة المرور', ['exception' => $e]);
            }
        }

        // الردّ واحد سواء وُجد الحساب أو لم يوجد: ردّ مختلف يحوّل
        // النموذج إلى أداة تُعدّد البُرد والأرقام الجامعية المسجّلة
        return back()->with('success',
            'إن كان الحساب مسجَّلاً فستصلك رسالة بالبريد خلال دقائق. تحقّق من صندوق الرسائل غير المرغوبة أيضاً.');
    }

    public function resetForm(Request $request, string $guard, string $token)
    {
        abort_unless(isset(self::GUARDS[$guard]), 404);

        return view('login.reset', [
            'guard' => $guard,
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request, string $guard)
    {
        abort_unless(isset(self::GUARDS[$guard]), 404);

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            // ٨ أحرف كما في إنشاء الحسابات وفي الملف الشخصي — لا
            // تتساهل أضعف نقطة في السلسلة
            'password' => 'required|min:8|max:60|confirmed',
        ], [
            'password.min' => 'كلمة السر لا تقلّ عن ٨ أحرف.',
            'password.confirmed' => 'تأكيد كلمة السر غير مطابق.',
        ], [
            'email' => 'البريد الإلكتروني',
            'password' => 'كلمة السر الجديدة',
        ]);

        $status = Password::broker(self::GUARDS[$guard][1])->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'تم تغيير كلمة السر. يمكنك الدخول بها الآن.');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $this->failure($status)]);
    }

    /**
     * يبحث في الأدوار الثلاثة بالبريد أو بالرقم الجامعي.
     *
     * @return array{0: string, 1: object}|null
     */
    private function locate(string $identify): ?array
    {
        $isEmail = filter_var($identify, FILTER_VALIDATE_EMAIL) !== false;

        foreach (self::GUARDS as $guard => [$model, $broker]) {
            // المسؤول لا يملك رقماً جامعياً
            $field = ($guard === 'admin') ? 'email' : ($isEmail ? 'email' : 'university_id');

            if ($field === 'email' && ! $isEmail) {
                continue;
            }

            $user = $model::where($field, $identify)->first();

            // حساب بلا بريد لا يمكن إرسال رابط إليه
            if ($user && $user->email) {
                return [$guard, $user];
            }
        }

        return null;
    }

    private function failure(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'الرابط منتهي الصلاحية أو استُعمل من قبل. اطلب رابطاً جديداً.',
            Password::INVALID_USER => 'الرابط منتهي الصلاحية أو استُعمل من قبل. اطلب رابطاً جديداً.',
            Password::RESET_THROTTLED => 'طلبتَ رابطاً قبل قليل. انتظر دقيقة ثم حاول.',
            default => 'تعذّر تغيير كلمة السر. اطلب رابطاً جديداً.',
        };
    }
}
