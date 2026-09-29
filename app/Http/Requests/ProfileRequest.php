<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * تحديث المستخدم لملفه الشخصي — للأدوار الثلاثة.
 *
 * كانت القواعد مكتوبة ثلاث مرات داخل \u200EProfileController\u200E لكل دور،
 * متطابقة حرفياً — فأي إصلاح يُنسى في واحدة. وكانت فيها علّتان:
 *
 * \u200Ephone\u200E كان \u200Estring|max:20\u200E بينما هو \u200Edigits:10\u200E في كل مكان آخر في
 * النظام، فيمكن ضبط الجوال على أي نصّ.
 *
 * و\u200Epassword\u200E كان \u200Emin:6\u200E بينما صار \u200Emin:8|confirmed\u200E في إنشاء حسابات
 * المسؤولين — والمسار الأكثر استعمالاً كان الأضعف.
 */
class ProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'phone' => 'required|digits:10',
            'password' => 'nullable|string|min:8|max:60|confirmed',
            'current_password' => 'required_with:password|nullable|string',
        ];

        // الأدمن وحده يملك اسمه وبريده وجنسه. أما الطالب والمشرف فهذه
        // بيانات تصدرها الجامعة، ولا يُعدّلها صاحبها من ملفه.
        if ($this->routeIs('admin.profile.*')) {
            $rules['name'] = 'required|string|max:70';
            $rules['gender'] = 'required|in:male,female';
            $rules['email'] = [
                'required', 'email', 'max:100',
                Rule::unique('admins', 'email')->ignore(auth('admin')->id()),
            ];
        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الجوال',
            'gender' => 'الجنس',
            'current_password' => 'كلمة السر الحالية',
            'password' => 'كلمة السر الجديدة',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attribute مطلوب.',
            'phone.digits' => 'رقم الجوال يجب أن يكون 10 أرقام.',
            'password.min' => 'كلمة السر الجديدة لا تقلّ عن 8 أحرف.',
            'password.confirmed' => 'تأكيد كلمة السر غير مطابق.',
            'email.unique' => 'هذا البريد مستعمل في حساب آخر.',
            'current_password.required_with' => 'أدخل كلمة السر الحالية لتأكيد التغيير.',
        ];
    }
}
