<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdministratorsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:70',
            'email' => 'required|email|unique:admins,email,' . $this->id,
            // كان \u200Enullable\u200E هنا و\u200Erequired\u200E في النموذج — الاثنان يتناقضان
            'phone' => 'required|digits:10',
            // ستة أحرف بلا تأكيد لحساب يملك كل شيء في النظام. والتأكيد
            // يمنع خطأً مطبعياً يقفل الحساب الجديد صامتاً — لا أحد
            // يعرف كلمة المرور التي كُتبت فعلاً.
            'password' => 'nullable|required_without:id|string|min:8|max:60|confirmed',
            'gender' => 'required|in:male,female',
        ];
    }

    public function messages()
    {
        return [
            '*.required_without' => __('validation-inline.required_without'),
            '*.required' => __('validation-inline.required'),
            '*.unique' => __('validation-inline.unique'),
            '*.max' => __('validation-inline.max'),
            '*.min' => __('validation-inline.min'),
            '*.confirmed' => __('validation-inline.confirmed'),
            '*.string' => __('validation-inline.string'),
            '*.email' => __('validation-inline.email'),
            '*.digits' => __('validation-inline.digits'),
            '*.in' => __('validation-inline.in'),

        ];
    }
}
