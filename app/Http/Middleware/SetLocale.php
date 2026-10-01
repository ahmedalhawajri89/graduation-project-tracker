<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * لغة الواجهة من كوكي «locale» — اختيار واحد يتبع المستخدم من الصفحة
 * الرئيسية (premium.js يكتبه) إلى الدخول إلى لوحته (مسار locale.switch).
 * العربية افتراضاً، وأي قيمة أخرى تُهمل. Carbon يتبع لغة التطبيق تلقائياً.
 */
class SetLocale
{
    public const SUPPORTED = ['ar', 'en'];

    public const COOKIE = 'locale';

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->cookie(self::COOKIE);

        if (in_array($locale, self::SUPPORTED, true)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
