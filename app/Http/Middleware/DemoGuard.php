<?php

namespace App\Http\Middleware;

use App\Support\Demo;
use Closure;
use Illuminate\Http\Request;

/**
 * حماية النسخة التجريبية من التخريب — يعمل حين يكون DEMO_MODE مفعّلاً وحده.
 *
 * الزائر يجرّب كل شيء (رسائل، رفع، جدولة، درجات) — عدا ما يُقفل الحسابات
 * التجريبية على غيره (كلمة السر، البريد، الصورة) أو يمسح بيانات العرض كلّها
 * (حذف المستخدمين والفصول والتخصصات، الاستيراد). والملفات بحدّ صغير.
 * وكل ما يفعله يزول في الإعادة الليلية (demo:reset).
 */
class DemoGuard
{
    /** مسارات معطّلة في النسخة التجريبية (أنماط Str::is) */
    public const BLOCKED = [
        '*.profile.*',
        'password.email',
        'password.update',
        'admin.administrators.*',
        'admin.students.destroy',
        'admin.students.import',
        'admin.supervisors.destroy',
        'admin.supervisors.import',
        'admin.semesters.destroy',
        'admin.semesters.activate',
        'admin.specialize.destroy',
        'admin.groups.forceDestroy',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! Demo::enabled() || $request->isMethodSafe()) {
            return $next($request);
        }

        $route = $request->route()?->getName() ?? '';

        if ($route && \Illuminate\Support\Str::is(self::BLOCKED, $route)) {
            return $this->refuse($request, __('هذا الإجراء معطّل في النسخة التجريبية — جرّب غيره بحرّية.'));
        }

        // تعديل طالب أو مشرف من الإدارة مسموح — إلا حسابَي الأزرار: بريدهما وكلمة سرّهما مفاتيح الدخول
        foreach (['student' => 'admin.students.update', 'supervisor' => 'admin.supervisors.update'] as $role => $name) {
            $target = $request->route($role);
            $id = is_object($target) ? $target->getKey() : $target;
            if ($route === $name && $id && (int) $id === (int) Demo::account($role)?->getKey()) {
                return $this->refuse($request, __('حساب التجربة لا يُعدَّل — جرّب على حساب آخر.'));
            }
        }

        // الملفات بحدّ صغير: الخادم التجريبي لا يمتلئ
        $limit = (int) config('demo.upload_mb', 2) * 1024 * 1024;
        foreach ($request->allFiles() as $file) {
            foreach (is_array($file) ? $file : [$file] as $f) {
                if ($f && $f->getSize() > $limit) {
                    return $this->refuse($request, __('في النسخة التجريبية الملف حتى :n ميغابايت.', ['n' => config('demo.upload_mb', 2)]));
                }
            }
        }

        return $next($request);
    }

    private function refuse(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->back(fallback: '/')->with('fail', $message);
    }
}
