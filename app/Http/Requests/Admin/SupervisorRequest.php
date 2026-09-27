<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupervisorRequest extends FormRequest
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
        // عند الإضافة: التخصص النشط وحده. وعند التعديل: أي تخصص، وإلا
        // تعذّر حفظ مشرف قائم في تخصص أُوقف — وهو ليس خطأه.
        $specializeRule = $this->id
            ? Rule::exists('specializes', 'id')
            : Rule::exists('specializes', 'id')->whereNull('archived_at');

        return [
            'university_id' => 'required|numeric|digits:9|unique:supervisors,university_id,' . $this->id,
            'specialize_id' => ['required', $specializeRule],
            'name' => 'required|string|max:70',
            'email' => 'required|email|unique:supervisors,email,' . $this->id,
            'phone' => 'nullable|digits:10',
            // كانت nullable حتى عند الإنشاء، والعمود NOT NULL: مشرف بلا كلمة سر يفشل بخطأ عامّ
            'password' => 'nullable|required_without:id|string|min:8|max:60',
            'gender' => 'required|in:male,female',
            // صفر = لا يستقبل طلبات (supervisorsAvailable يستبعده). كان يقبل السالب
            'max_group' => 'nullable|integer|min:0|max:50',
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
            '*.string' => __('validation-inline.string'),
            '*.email' => __('validation-inline.email'),
            '*.digits' => __('validation-inline.digits'),
            '*.in' => __('validation-inline.in'),
            '*.integer' => __('validation-inline.integer'),
            '*.starts_with' => __('validation-inline.starts_with'),
            '*.distinct' => __('validation-inline.distinct'),
            '*.exists' => __('validation-inline.exists'),
            '*.numeric' => __('validation-inline.numeric'),
            '*.lte' => __('validation-inline.lte'),
            '*.gte' => __('validation-inline.gte'),

        ];
    }

}
