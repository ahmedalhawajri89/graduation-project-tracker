<?php

namespace App\Http\Middleware;

use App\Models\Semester;
use Closure;
use Illuminate\Http\Request;

/**
 * لا صفحة تُفتح قبل أول فصل دراسي.
 *
 * ‎Semester::current()‎ يعيد null حين يخلو جدول الفصول تماماً — تثبيت جديد قبل
 * إنشاء الأول — و‎->id‎ عليه في نحو عشرين موضعاً كان يُسقط الصفحة بخطأ 500،
 * أوّلها لوحة الأدمن بعد الدخول مباشرة. حارس واحد بدل عشرين فحصاً.
 */
class EnsureSemesterExists
{
    /** ما يحتاجه الأدمن ليُنشئ الفصل الأول — أو لا علاقة له بالفصل */
    private const ADMIN_ALLOWED = [
        'admin.semesters.*',
        'admin.specialize.*',
        'admin.administrators.*',
        'admin.profile.*',
        'admin.audit.*',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (Semester::current()) {
            return $next($request);
        }

        if (auth('admin')->check()) {
            if ($request->routeIs(...self::ADMIN_ALLOWED)) {
                return $next($request);
            }

            return redirect()->route('admin.semesters.index')
                ->with('fail', 'لا فصل دراسي بعد — أنشئ الفصل الأول وفعّله قبل أي شيء آخر.');
        }

        return response()->view('errors._page', [
            'code' => '—',
            'title' => 'لم يُفتح فصل دراسي بعد',
            'text' => 'تُنشئ إدارة القسم الفصل الدراسي أولاً، ثم تُفتح لوحتك. حاول لاحقاً.',
            'actionUrl' => null,
            'actionLabel' => null,
        ], 503);
    }
}
