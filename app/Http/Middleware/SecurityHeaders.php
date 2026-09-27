<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * ترويسات أمان أساسية.
 *
 * لا CSP هنا عمداً: الصفحات تعتمد على سكربتات مضمَّنة، وسياسة صارمة تكسرها
 * وسياسة متساهلة لا تحمي شيئاً — تستحقّ عملاً مستقلاً.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');           // لا تضمين في إطار نطاق آخر (clickjacking)
        $response->headers->set('X-Content-Type-Options', 'nosniff');       // الملف يُعامَل بنوعه المعلَن لا بتخمين المتصفّح
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
