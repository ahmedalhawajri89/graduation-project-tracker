<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
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
            // لا \u200Estatus\u200E ولا \u200Edate_line\u200E ولا \u200Esemester_id\u200E: الحالة «request» والفصل
            // الحالي يفرضهما الخادم، والموعد يضعه المشرف. كان \u200Estatus\u200E مقبولاً
            // بقيم \u200Eaccept\u200E و\u200Ecomplete\u200E، والطلب يُحفظ كاملاً
            'title' => 'required|string|max:254',
            'description' => 'nullable|string|max:500',
            'supervisor_id' => 'required|integer|exists:supervisors,id',
            'specialize_project_id' => 'required|integer|exists:specialize_projects,id',
            'student_ids' => 'required|array|min:1',
            // \u200Erequired\u200E لا \u200Enullable\u200E: القيم الفارغة كانت تُعدّ في الحدّ الأدنى ولا تصير أعضاءً
            'student_ids.*' => 'required|integer|starts_with:130,230|distinct|exists:students,university_id|min:10',

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
            '*.array' => __('validation-inline.array'),

        ];
    }

}
