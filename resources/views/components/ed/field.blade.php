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
    'placeholder' => null,
    // edit | create — يحدّد بادئة المعرّف، وأيّ الخطأين يُعرض هنا
    'mode' => 'edit',
])

{{-- حقل في درج التعديل أو الإضافة. القيمة يملؤها السكربت، والخطأ يُعرض في
     درجه وحده: خطأ التعديل يحمل \u200Eold('id')\u200E وخطأ الإضافة لا يحمله --}}
@php
    $prefix = $mode === 'create' ? 'cr' : 'ed';
    $mine = $mode === 'create' ? ! old('id') : (bool) old('id');
    $invalid = $mine && $errors->has($name);
@endphp

<div class="ed-field {{ $wide ? 'is-wide' : '' }}" data-ed-wrap="{{ $name }}">
    <label for="{{ $prefix }}-{{ $name }}" class="form-label {{ $required ? 'required' : '' }}">
        {{ $label }}
        <span class="ed-changed-dot" title="تغيّر" aria-hidden="true"></span>
    </label>
    @if (trim($slot) !== '')
        {{ $slot }}
    @else
        <input id="{{ $prefix }}-{{ $name }}" name="{{ $name }}" type="{{ $type }}"
            class="form-control {{ $invalid ? 'is-invalid' : '' }}"
            @if ($required) required @endif
            @if ($dir) dir="{{ $dir }}" @endif
            @if ($inputmode) inputmode="{{ $inputmode }}" @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            autocomplete="{{ $autocomplete }}">
    @endif
    @if ($invalid)
        <div class="invalid-feedback d-block">{{ $errors->first($name) }}</div>
    @elseif ($hint)
        <div class="ed-hint">{{ $hint }}</div>
    @endif
</div>
