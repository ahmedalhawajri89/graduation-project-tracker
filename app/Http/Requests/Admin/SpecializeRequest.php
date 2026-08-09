<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SpecializeRequest extends FormRequest
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
            'name' => 'required|string|max:70|unique:specializes,name,' . $this->id,
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
