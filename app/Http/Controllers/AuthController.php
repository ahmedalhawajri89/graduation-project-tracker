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
            'password' => 'required|min:5|max:30',
        ], [], [
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

    public function logout()
    {
        // dd(config('auth.guards'));
        foreach (config('auth.guards') as $key => $guard) {
            if (auth()->guard($key)->check()) {
                auth()->guard($key)->logout();
                break;
            }
        }

        return redirect()->route('site.home');

    }

}
