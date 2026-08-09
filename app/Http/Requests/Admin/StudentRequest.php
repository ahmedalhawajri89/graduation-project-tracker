<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
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
            'university_id' => 'required|numeric|starts_with:130,230|digits:10|unique:students,university_id,' . $this->id,
            'specialize_id' => 'required|exists:specializes,id',
            'name' => 'required|string|max:70',
            'email' => 'required|email|unique:students,email,' . $this->id,
            'phone' => 'nullable|digits:10',
            'password' => 'nullable|required_without:id|min:6',
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
