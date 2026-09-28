<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            // نقطة عامة بلا دخول: حدود صريحة للطول. كانت بلا حدود، و\u200Esubject\u200E
            // بالذات يُعرض في صفحة الأدمن
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
        ];
    }

    /** خطأ التحقّق يعيد إلى النموذج نفسه — كان يعيد إلى رأس الصفحة فيُظنّ أن شيئاً لم يحدث */
    protected function getRedirectUrl()
    {
        return strtok($this->redirector->getUrlGenerator()->previous(), '#') . '#contact';
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
            '*.array' => __('validation-inline.array'),

        ];
    }

}
