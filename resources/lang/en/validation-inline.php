<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (inline)
    |--------------------------------------------------------------------------
    |
    | The same rules as validation.php, phrased for display right under the
    | field — so they don't repeat the field's name.
    |
    */

    'accepted'        => 'This field must be accepted.',
    'active_url'      => 'This is not a valid URL.',
    'after'           => 'This must be a date after :date.',
    'after_or_equal'  => 'This must be a date after or equal to :date.',
    'alpha'           => 'This field may only contain letters.',
    'alpha_dash'      => 'This field may only contain letters, numbers, dashes and underscores.',
    'alpha_num'       => 'This field may only contain letters and numbers.',
    'array'           => 'This field must be an array.',
    'before'          => 'This must be a date before :date.',
    'before_or_equal' => 'This must be a date before or equal to :date.',
    'between'         => [
        'numeric' => 'This value must be between :min and :max.',
        'file'    => 'This file must be between :min and :max kilobytes.',
        'string'  => 'This text must be between :min and :max characters.',
        'array'   => 'This must have between :min and :max items.',
    ],
    'boolean'        => 'This field must be true or false.',
    'confirmed'      => 'The confirmation does not match.',
    'date'           => 'This is not a valid date.',
    'date_equals'    => 'This must be a date equal to :date.',
    'date_format'    => 'This does not match the format :format.',
    'different'      => 'This value must be different from :other.',
    'digits'         => 'This must be :digits digits.',
    'digits_between' => 'This must be between :min and :max digits.',
    'dimensions'     => 'This image has invalid dimensions.',
    'distinct'       => 'This field has a duplicate value.',
    'email'          => 'This must be a valid email address.',
    'ends_with'      => 'This must end with one of the following: :values.',
    'exists'         => 'The selected value is invalid.',
    'file'           => 'This must be a file.',
    'filled'         => 'This field must have a value.',
    'gt'             => [
        'numeric' => 'The value must be greater than :value.',
        'file'    => 'The file size must be greater than :value kilobytes.',
        'string'  => 'The text must be longer than :value characters.',
        'array'   => 'There must be more than :value items.',
    ],
    'gte' => [
        'numeric' => 'The value must be greater than or equal to :value.',
        'file'    => 'The file size must be at least :value kilobytes.',
        'string'  => 'The text must be at least :value characters.',
        'array'   => 'There must be :value items or more.',
    ],
    'image'    => 'This must be an image.',
    'in'       => 'The selected value is invalid.',
    'in_array' => 'This value does not exist in :other.',
    'integer'  => 'This must be an integer.',
    'ip'       => 'This must be a valid IP address.',
    'ipv4'     => 'This must be a valid IPv4 address.',
    'ipv6'     => 'This must be a valid IPv6 address.',
    'json'     => 'This must be a valid JSON string.',
    'lt'       => [
        'numeric' => 'The value must be less than :value.',
        'file'    => 'The file size must be less than :value kilobytes.',
        'string'  => 'The text must be shorter than :value characters.',
        'array'   => 'There must be fewer than :value items.',
    ],
    'lte' => [
        'numeric' => 'The value must be less than or equal to :value.',
        'file'    => 'The file size must not exceed :value kilobytes.',
        'string'  => 'The text must not exceed :value characters.',
        'array'   => 'There must not be more than :value items.',
    ],
    'max' => [
        'numeric' => 'The value may not be greater than :max.',
        'file'    => 'The file may not be larger than :max kilobytes.',
        'string'  => 'The text may not be longer than :max characters.',
        'array'   => 'There may not be more than :max items.',
    ],
    'mimes'     => 'This must be a file of type: :values.',
    'mimetypes' => 'This must be a file of type: :values.',
    'min'       => [
        'numeric' => 'The value must be at least :min.',
        'file'    => 'The file must be at least :min kilobytes.',
        'string'  => 'The text must be at least :min characters.',
        'array'   => 'There must be at least :min items.',
    ],
    'not_in'               => 'The selected value is invalid.',
    'not_regex'            => 'This format is invalid.',
    'numeric'              => 'This must be a number.',
    'password'             => 'The password is incorrect.',
    'present'              => 'This field must be present.',
    'regex'                => 'This format is invalid.',
    'required'             => 'This field is required.',
    'required_if'          => 'This field is required when :other is :value.',
    'required_unless'      => 'This field is required unless :other is in :values.',
    'required_with'        => 'This field is required when :values is present.',
    'required_with_all'    => 'This field is required when :values are present.',
    'required_without'     => 'This field is required when :values is not present.',
    'required_without_all' => 'This field is required when none of :values are present.',
    'same'                 => 'This value must match :other.',
    'size'                 => [
        'numeric' => 'The value must be :size.',
        'file'    => 'The file must be :size kilobytes.',
        'string'  => 'The text must be :size characters.',
        'array'   => 'This must contain :size items.',
    ],
    'starts_with' => 'This must start with one of the following: :values.',
    'string'      => 'This must be a string.',
    'timezone'    => 'This must be a valid time zone.',
    'unique'      => 'This value has already been taken.',
    'uploaded'    => 'The upload failed.',
    'url'         => 'This format is invalid.',
    'uuid'        => 'This must be a valid UUID.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

];
