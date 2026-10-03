<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * الرفع بشريط تقدّم (public/js/upload.js) يرسل النموذج بطلب خفي ليعرف نسبة
 * ما وصل. لو تبع الطلب الخفي إعادة التوجيه لاستهلك رسالة النجاح أو أخطاء
 * التحقّق (flash) قبل أن يراها المستخدم. فحين يحمل الطلب X-Upload يُعاد
 * عنوان التوجيه نفسه JSON، والسكربت ينتقل إليه — فتبقى الرسالة للصفحة.
 * بلا الترويسة لا شيء يتغيّر، والمتحكّمات لا تعرف بالأمر.
 */
class UploadResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->headers->get('X-Upload') === '1' && $response instanceof RedirectResponse) {
            return response()->json(['redirect' => $response->getTargetUrl()]);
        }

        return $response;
    }
}
