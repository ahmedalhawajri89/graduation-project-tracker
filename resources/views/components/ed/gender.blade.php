@props(['mode' => 'edit'])

{{-- الجنس: زرّان مجزّآن بدل قائمة منسدلة لخيارين --}}
@php
    $prefix = $mode === 'create' ? 'cr' : 'ed';
    $mine = $mode === 'create' ? ! old('id') : (bool) old('id');
@endphp

<div class="ed-field" data-ed-wrap="gender">
    <span class="form-label required" id="{{ $prefix }}-gender-label">
        {{ __('الجنس') }}
        <span class="ed-changed-dot" title="{{ __('تغيّر') }}" aria-hidden="true"></span>
    </span>
    <div class="ed-segmented" role="radiogroup" aria-labelledby="{{ $prefix }}-gender-label">
        <label>
            <input type="radio" name="gender" value="male" required>
            <span><i class="ti ti-gender-male" aria-hidden="true"></i> {{ __('ذكر') }}</span>
        </label>
        <label>
            <input type="radio" name="gender" value="female">
            <span><i class="ti ti-gender-female" aria-hidden="true"></i> {{ __('أنثى') }}</span>
        </label>
    </div>
    @if ($mine && $errors->has('gender'))
        <div class="invalid-feedback d-block">{{ $errors->first('gender') }}</div>
    @endif
</div>
