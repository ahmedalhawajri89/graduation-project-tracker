<?php

namespace App\Http\Controllers\Concerns;

use App\Exceptions\AvatarException;
use App\Http\Requests\ProfileRequest;
use App\Support\AvatarProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * تحديث المستخدم لملفه الشخصي.
 *
 * كان هذا المنطق مكرّراً حرفياً في ثلاثة متحكّمات (أدمن ومشرف وطالب).
 */
trait UpdatesOwnProfile
{
    /** الحقول التي يملك صاحب الحساب تعديلها في هذا الدور */
    abstract protected function editableFields(): array;

    abstract protected function profileUser();

    protected function saveProfile(ProfileRequest $request)
    {
        $user = $this->profileUser();
        $editable = $this->editableFields();

        $changingEmail = in_array('email', $editable, true)
            && $request->filled('email')
            && $request->email !== $user->email;

        $changingPassword = $request->filled('password');

        // البريد هوية الدخول، فتغييره يطلب كلمة المرور الحالية كما
        // يطلبها تغييرها. ولا يكفي التحقّق في القواعد لأن الشرط
        // «تغيّر البريد» لا يُعبَّر عنه بقاعدة.
        if (($changingEmail || $changingPassword) && ! Hash::check($request->current_password, $user->password)) {
            return redirect()->back()
                ->withErrors(['current_password' => 'كلمة السر الحالية غير صحيحة'])
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']));
        }

        if ($changingEmail && ! $request->filled('current_password')) {
            return redirect()->back()
                ->withErrors(['current_password' => 'أدخل كلمة السر الحالية لتغيير البريد الإلكتروني'])
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']));
        }

        // لا يُنسخ إلا ما يملكه هذا الدور: طلبٌ مُلفَّق يحمل \u200Eemail\u200E أو
        // \u200Euniversity_id\u200E من حساب طالب لا يغيّر شيئاً
        foreach ($editable as $field) {
            if ($request->has($field)) {
                $user->{$field} = $request->input($field);
            }
        }

        if ($changingPassword) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->back()->with(
            'success',
            $changingPassword ? 'تم تحديث بياناتك وكلمة السر بنجاح' : 'تم تحديث بياناتك بنجاح'
        );
    }

    /**
     * رفع الصورة الشخصية.
     *
     * التحقّق من النوع والحجم أولاً، ثم **إعادة الترميز** — وهي
     * الحماية الحقيقية لا فحص الامتداد: ملفّ يتنكّر في صورة لا ينجو
     * من \u200Eimagecreatefrom*\u200E و\u200Eimagejpeg\u200E.
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => [
                'required', 'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100',
            ],
        ], [
            'avatar.required' => 'اختر صورة أولاً.',
            'avatar.image' => 'الملف ليس صورة.',
            'avatar.mimes' => 'الصيغ المقبولة: JPG أو PNG أو WebP.',
            'avatar.max' => 'حجم الصورة لا يتجاوز 2 ميغابايت.',
            'avatar.dimensions' => 'الصورة صغيرة جداً — 100×100 بكسل على الأقل.',
        ]);

        if (! AvatarProcessor::available()) {
            return redirect()->back()->withErrors([
                'avatar' => 'إضافة GD غير مفعّلة على الخادم، فلا يمكن معالجة الصور.',
            ]);
        }

        try {
            $this->profileUser()->storeAvatar($request->file('avatar'));
        } catch (AvatarException $e) {
            // رسالة مكتوبة للمستخدم
            return redirect()->back()->withErrors(['avatar' => $e->getMessage()]);
        } catch (Throwable $e) {
            // عطل في النظام: يُسجَّل ولا يُعرض. كان \u200Ecatch (RuntimeException)\u200E
            // يلتقط \u200EQueryException\u200E (يرث منه عبر PDOException) فيعرض نصّ
            // الاستعلام كاملاً — اسم الجدول والأعمدة والمضيف والمنفذ.
            Log::error('فشل رفع الصورة الشخصية', ['exception' => $e]);

            return redirect()->back()->withErrors([
                'avatar' => 'تعذّر حفظ الصورة. حاول مرة أخرى، وإن تكرّر فراجع مسؤول النظام.',
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث صورتك الشخصية');
    }

    public function destroyAvatar()
    {
        $this->profileUser()->deleteAvatar();

        return redirect()->back()->with('success', 'تمت إزالة صورتك الشخصية');
    }
}
