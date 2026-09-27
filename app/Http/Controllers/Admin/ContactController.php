<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        // كان يعلّم كل الرسائل مقروءة بمجرد فتح الصفحة، فيفقد الأدمن
        // تتبّع ما لم يعالجه بعد. الآن تُعلَّم الرسالة مقروءة حين تُفتح
        // وحدها — والفتح عبر ?open=ID فيعمل بلا جافاسكربت.
        // \u200E(int) request()\u200E لا \u200Erequest()->integer()\u200E: الأولى صريحة ولا
        // تعتمد على سلوك مُساعد قد يختلف بين إصدارات
        $openId = (int) request('open') ?: null;

        if ($openId) {
            Contact::where('id', $openId)->where('is_read', 0)->update(['is_read' => 1]);
        }

        $q = trim((string) request('q'));
        $onlyUnread = request()->boolean('unread');

        $base = Contact::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('subject', 'like', "%{$q}%")
                        ->orWhere('message', 'like', "%{$q}%");
                });
            });

        $data['unreadCount'] = Contact::where('is_read', 0)->count();
        $data['totalCount'] = Contact::count();
        $data['openId'] = $openId;
        $data['q'] = $q;
        $data['onlyUnread'] = $onlyUnread;

        $data['messages'] = (clone $base)
            ->when($onlyUnread, fn ($query) => $query->where('is_read', 0))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // الرسالة المفتوحة تُحمَّل بمفتاحها لا من صفحة القائمة: قد
        // تكون في صفحة أخرى، أو خارج التصفية الحالية — فتُفتح ويبقى
        // لوح القراءة فارغاً بلا تفسير
        $data['openMessage'] = $openId ? Contact::find($openId) : null;

        return view('dashboard.admin.messages.index', $data);
    }

    /** إعادة رسالة إلى «غير مقروءة» — لم يكن ممكناً إطلاقاً */
    public function markUnread($id)
    {
        Contact::where('id', $id)->update(['is_read' => 0]);

        return redirect()->back()->with('success', 'أُعيدت الرسالة إلى غير المقروءة.');
    }

    /** تعليم الكل كمقروء — فعل صريح بدل أن يقع تلقائياً */
    public function markAllRead()
    {
        $n = Contact::where('is_read', 0)->update(['is_read' => 1]);

        return redirect()->back()->with('success', "عُلِّمت {$n} رسالة كمقروءة.");
    }

    public function destroy($id)
    {
        // كان يقرأ request()->id ويتجاهل معامل المسار
        $message = Contact::find($id ?: request()->id);

        if (! $message) {
            return redirect()->back()->with('fail', 'الرسالة غير موجودة.');
        }

        try {
            $message->delete();

            return redirect()->back()->with('success', 'حُذفت الرسالة.');
        } catch (\Exception $ex) {
            report($ex);

            return redirect()->back()->with('fail', 'تعذّر حذف الرسالة. حاول مرة أخرى.');
        }
    }
}
