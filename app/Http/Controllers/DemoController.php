<?php

namespace App\Http\Controllers;

use App\Support\Demo;
use Illuminate\Http\Request;

/**
 * «جرّب كطالب / مشرف / مسؤول»: دخول بنقرة في النسخة التجريبية وحدها، وتبديل
 * الدور من داخل اللوحة دون خروج — ليرى الزائر القصة من الجهتين.
 */
class DemoController extends Controller
{
    public function enter(Request $request, string $role)
    {
        abort_unless(Demo::enabled() && in_array($role, Demo::ROLES, true), 404);

        $user = Demo::account($role);
        abort_unless($user, 404);

        // دور واحد في كل مرة: الحارس السابق يخرج
        foreach (config('auth.guards') as $guard => $cfg) {
            if (($cfg['driver'] ?? null) === 'session') {
                auth()->guard($guard)->logout();
            }
        }

        auth()->guard($role)->login($user);
        $request->session()->regenerate();

        return redirect()->route("{$role}.dashboard");
    }
}
