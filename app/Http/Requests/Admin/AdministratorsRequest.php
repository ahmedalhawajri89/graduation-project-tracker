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

        ];
    }
}
