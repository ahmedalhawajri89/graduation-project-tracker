<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // \u200Eheader()\u200E موجودة على \u200EIlluminate\Http\Response\u200E وحدها. أما
        // التنزيلات (\u200EBinaryFileResponse\u200E) والبثّ (\u200EStreamedResponse\u200E)
        // فترثان من Symfony مباشرةً بلا تلك الدالة — فكان كل تصدير
        // أو تنزيل ملف يسقط بـ \u200ECall to undefined method\u200E.
        // \u200Eheaders->set()\u200E موجودة على كل أنواع الاستجابات.
        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
