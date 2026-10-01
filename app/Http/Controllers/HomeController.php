<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendMessageRequest;
use App\Models\Admin;
use App\Models\Contact;
use App\Notifications\AdminNewMessageNotify;
use Illuminate\Support\Facades\Notification;

class HomeController extends Controller
{

    public function index()
    {
        return view('index');

    }

    public function send(SendMessageRequest $request)
    {
        try {
            // الحقول صراحةً: $request->all() كان يكتب is_read فتصل الرسالة مقروءةً فلا تُرى
            Contact::create($request->only(['name', 'email', 'subject', 'message']));

            $admins = Admin::select('id', 'name')->get();
            foreach ($admins as $admin) {
                Notification::send($admin, new AdminNewMessageNotify());
            }

            // الإرسال من الصفحة بلا إعادة تحميل: الجواب JSON فيبقى القارئ في قسمه
            if ($request->expectsJson()) {
                return response()->json(['message' => 'sent']);
            }

            return redirect()->to($this->backToContact())->with('success', __('تم ارسال رسالتك. شكرا لك!'));

        } catch (\Exception$ex) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'error'], 500);
            }

            return redirect()->to($this->backToContact())->with('fail', __('حدث خطأ .. الرجاء المحاولة مرة أخرى'));

        }
    }

    /** بلا JavaScript: الرجوع إلى قسم التواصل لا إلى رأس الصفحة */
    private function backToContact(): string
    {
        return strtok(url()->previous(), '#') . '#contact';
    }

    /**
     * JSON contact endpoint for the Next.js public frontend.
     */
    public function sendApi(SendMessageRequest $request)
    {
        try {
            // الحقول صراحةً: $request->all() كان يكتب is_read فتصل الرسالة مقروءةً فلا تُرى
            Contact::create($request->only(['name', 'email', 'subject', 'message']));

            $admins = Admin::select('id', 'name')->get();
            foreach ($admins as $admin) {
                Notification::send($admin, new AdminNewMessageNotify());
            }

            return response()->json(['message' => 'sent'], 200);

        } catch (\Exception$ex) {
            return response()->json(['message' => 'error'], 500);
        }
    }
}
