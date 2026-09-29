<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function loginView()
    {
        return view('login.login');
    }

    public function login(Request $request)
    {
        // التحقق من المدخلات
        $request->validate([
            'identify' => 'required',
            // بلا حدّ أدنى: حسابات قديمة بكلمات أقصر يجب أن تدخل. والأقصى ٦٠ كالتعيين —
            // كان ٣٠ فيُقفَل خارج حسابه من اختار ٣١ حرفاً فأكثر من صفحة ملفّه
            'password' => 'required|string|max:60',
        ], [
            'identify.required' => 'أدخل بريدك الإلكتروني أو رقمك الجامعي.',
            'password.required' => 'أدخل كلمة السر.',
        ], [
            'identify' => 'البريد الإلكتروني أو الرقم الجامعي',
            'password' => 'كلمة السر',
        ]);

        $identify = $request->identify;
        $isEmail  = filter_var($identify, FILTER_VALIDATE_EMAIL) !== false;

        // يُكتشف نوع المستخدم تلقائياً من بيانات الاعتماد، دون أن يختاره المستخدم.
        // المسؤول يسجّل الدخول بالبريد فقط، والطالب/المشرف بالبريد أو الرقم الجامعي.
        $guards = ['admin', 'supervisor', 'student'];

        foreach ($guards as $guard) {
            $field = ($guard === 'admin') ? 'email' : ($isEmail ? 'email' : 'university_id');

            // إن كان الحقل بريداً بينما المُدخل ليس بريداً صالحاً، فلا داعي للمحاولة.
            if ($field === 'email' && ! $isEmail) {
                continue;
            }

            if (auth()->guard($guard)->attempt([$field => $identify, 'password' => $request->password])) {
                // تجديد الجلسة لمنع تثبيت الجلسة (session fixation)
                $request->session()->regenerate();

                return redirect()->route("{$guard}.dashboard");
            }
        }

        return redirect()->route('login')
            ->withInput($request->only('identify'))
            ->with('fail', 'بيانات الدخول غير صحيحة. تأكد من البريد/الرقم الجامعي وكلمة السر.');
    }

    public function logout(\Illuminate\Http\Request $request)
    {
        foreach (config('auth.guards') as $key => $guard) {
            if (auth()->guard($key)->check()) {
                auth()->guard($key)->logout();
                break;
            }
        }

        // الخروج يُنهي الجلسة ورمز CSRF معها — كانا يبقيان صالحين بعده
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('site.home');
    }

}
