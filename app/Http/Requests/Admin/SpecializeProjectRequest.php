<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SpecializeProjectRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100',
                Rule::unique('specialize_projects')
                    ->ignore($this->id)
                    ->where('specialize_id', $this->specialize_id)
                    ->where('name', $this->name),
            ], //'required|string|max:70|', //unique:specializes,name,' . $this->id,
            'specialize_id' => 'required|numeric|exists:specializes,id',
            // كانا \u200Enullable\u200E، و\u200EStudent\DashboardController::createProject\u200E
            // يقارن حجم الفريق بهما — فنوع بلا حدود كان يُلغي التحقّق
            // من حجم الفرق كلّه بصمت.
            'min' => 'required|integer|min:1|lte:max',
            'max' => 'required|integer|min:1|gte:min',
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
