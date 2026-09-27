<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\UpdatesOwnProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\Admin;

class ProfileController extends Controller
{
    use UpdatesOwnProfile;

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * المسؤول يملك اسمه وبريده وجنسه وجواله.
     *
     * كان لا يُعدّل من ملفه إلا رقم الجوال — بينما \u200EAdminController\u200E
     * يتيح له تعديل اسم وبريد أي مسؤول آخر. فلو أخطأ في اسمه احتاج
     * مسؤولاً آخر ليصلحه. معكوس.
     */
    protected function editableFields(): array
    {
        return ['name', 'email', 'phone', 'gender'];
    }

    protected function profileUser()
    {
        return auth('admin')->user();
    }

    public function edit()
    {
        return view('dashboard.admin.profile', [
            'admin' => $this->profileUser(),
            // حساب وحيد = نقطة فشل مفردة: لا استرجاع لكلمة مرور فُقدت
            'isOnlyAdmin' => Admin::count() <= 1,
        ]);
    }

    public function update(ProfileRequest $request)
    {
        return $this->saveProfile($request);
    }
}
