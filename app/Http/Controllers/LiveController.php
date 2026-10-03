<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Supervisor;
use App\Support\LiveCounts;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * التحديث الحيّ للّوحة: يسأله live.js كل بضع ثوانٍ فيعرف المستخدم بالجديد
 * دون إعادة تحميل الصفحة — الجرس وقائمته، وشارات الشريط الجانبي، وتنبيه
 * منبثق لكل إشعار وصل منذ آخر سؤال.
 *
 * قراءة فقط: لا يعلّم شيئاً مقروءاً. والقائمة تُرسم من الجزء نفسه الذي
 * ترسمه الصفحة، فلا نسختان من القالب.
 */
class LiveController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = LiveCounts::user();
        abort_unless($user, 401);

        $counts = LiveCounts::for($user);
        $latest = $user->notifications()->latest()->take(5)->get();
        $notifyRoute = match (true) {
            $user instanceof Admin => route('admin.contact.index'),
            $user instanceof Supervisor => route('supervisor.showNotification'),
            default => route('student.showNotification'),
        };

        // ما وصل بعد آخر سؤال — للتنبيه المنبثق (غير المقروء وحده، وثلاثة على الأكثر)
        $since = rescue(fn () => Carbon::parse((string) $request->query('since')), null, false);
        $fresh = $since
            ? $user->unreadNotifications()->where('created_at', '>', $since)->latest()->take(3)->get()
                ->map(fn ($n) => [
                    'title' => $n->data['project'] ?? ($n->data['title'] ?? __('إشعار')),
                    'text' => Str::limit(__($n->data['msg'] ?? ($n->data['type'] ?? '')), 110),
                    'href' => $notifyRoute,
                ])->values()
            : [];

        return response()->json([
            'counts' => $counts,
            // مؤشّر الزمن للسؤال التالي: وقت الخادم لا وقت المتصفح
            'now' => now()->toIso8601String(),
            'fresh' => $fresh,
            'menu' => view('layouts.admin.inc._notif-menu', [
                'unreadCount' => $counts['notifications'],
                'latestNotifications' => $latest,
                'notifyRoute' => $notifyRoute,
            ])->render(),
            'labels' => [
                'bell' => __('الإشعارات (:n غير مقروء)', ['n' => $counts['notifications']]),
            ],
        ])->header('Cache-Control', 'no-store');
    }
}
