<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;

class ContactController extends Controller
{
    public function index()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);

        // نجلب الرسائل أولاً (بحالة القراءة الحالية لعرض شارة "جديد")،
        // ثم نعلّم الجميع كمقروء للزيارة القادمة.
        $messages = Contact::latest()->paginate(15);
        Contact::where('is_read', 0)->update(['is_read' => 1]);

        return view('dashboard.admin.messages.index', compact('messages'));
    }

    public function destroy($id)
    {
        $message = Contact::where('id', request()->id)->first();

        if (!$message) {
            return redirect()->back()->with('fail', 'لا توجد بيانات!!!');
        }
        try {
            $message->delete();
            return redirect()->back()->with('success', 'تم حذف البيانات بنجاح');
        } catch (\Exception$ex) {
            return redirect()->back()->with('fail', 'حدث خطا ما الرجاء المحاولة مرة أخرى ' . $ex->getMessage());
        }

    }
}
