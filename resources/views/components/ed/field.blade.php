@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'dir' => null,
    'inputmode' => null,
    'autocomplete' => 'off',
    'hint' => null,
    'wide' => false,
])

{{-- حقل في درج التعديل. القيمة يملؤها السكربت من السجلّ، والخطأ لا يظهر
     إلا لخطأ التعديل (\u200Eold('id')\u200E) — لا لخطأ نافذة الإضافة في الصفحة نفسها --}}
@php
    $invalid = old('id') && $errors->has($name);
@endphp

<div class="ed-field {{ $wide ? 'is-wide' : '' }}" data-ed-wrap="{{ $name }}">
    <label for="ed-{{ $name }}" class="form-label {{ $required ? 'required' : '' }}">
        {{ $label }}
        <span class="ed-changed-dot" title="تغيّر" aria-hidden="true"></span>
    </label>
    @if (trim($slot) !== '')
        {{ $slot }}
    @else
        <input id="ed-{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            class="form-control {{ $invalid ? 'is-invalid' : '' }}"
            @if ($required) required @endif
            @if ($dir) dir="{{ $dir }}" @endif
            @if ($inputmode) inputmode="{{ $inputmode }}" @endif
            autocomplete="{{ $autocomplete }}">
    @endif
    @if ($invalid)
        <div class="invalid-feedback d-block">{{ $errors->first($name) }}</div>
    @elseif ($hint)
        <div class="ed-hint">{{ $hint }}</div>
    @endif
</div>
