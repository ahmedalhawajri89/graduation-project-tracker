@props(['confirm' => false, 'mode' => 'edit'])

{{--
    كلمة السر في الدرج.

    في التعديل: مطويّة خلف زرّ. كانت حقلاً فارغاً عنوانه «كلمة السر» لا يقول
    إن تركه يُبقي الحالية أم يمحوها. الطيّ يعطّله فلا يُرسل إلا لمن فتحه.
    في الإضافة: مفتوحة ومطلوبة — الحساب الجديد يحتاج كلمة سر، و«توليد» يكفي.
--}}
@php
    $create = $mode === 'create';
    $prefix = $create ? 'cr' : 'ed';
    $mine = $create ? ! old('id') : (bool) old('id');
    $invalid = $mine && $errors->has('password');
    $open = $create || $invalid;
@endphp

<div class="ed-password {{ $open ? 'is-open' : '' }}" data-ed-password @if ($create) data-always-open @endif>
    @unless ($create)
        <button type="button" class="ed-password-toggle" data-ed-password-open @if ($invalid) hidden @endif>
            <i class="ti ti-key" aria-hidden="true"></i>
            <span>
                <b>تعيين كلمة سر جديدة</b>
                <small>كلمة السر الحالية تبقى كما هي ما لم تعيّن غيرها</small>
            </span>
            <i class="ti ti-chevron-left" aria-hidden="true"></i>
        </button>
    @endunless

    <div class="ed-password-body" data-ed-password-body @unless ($open) hidden @endunless>
        <div class="ed-password-head">
            <label for="{{ $prefix }}-password" class="form-label {{ $create ? 'required' : '' }}">
                {{ $create ? 'كلمة السر' : 'كلمة السر الجديدة' }}
            </label>
            @if ($create)
                <span class="ed-hint m-0">يُبلَّغ بها صاحب الحساب ليدخل بها أول مرة</span>
            @else
                <button type="button" class="ed-link" data-ed-password-close>إلغاء — إبقاء الحالية</button>
            @endif
        </div>
        <div class="ed-password-input">
            <input id="{{ $prefix }}-password" name="password" type="password" minlength="8" maxlength="60"
                class="form-control {{ $invalid ? 'is-invalid' : '' }}" autocomplete="new-password" dir="ltr"
                data-ed-password-input @if ($create) required @else disabled @endif>
            <button type="button" class="ed-icon-btn" data-ed-password-show title="إظهار" aria-label="إظهار كلمة السر">
                <i class="ti ti-eye" aria-hidden="true"></i>
            </button>
            <button type="button" class="ed-icon-btn" data-ed-password-generate title="توليد كلمة سر قوية" aria-label="توليد كلمة سر قوية">
                <i class="ti ti-wand" aria-hidden="true"></i>
            </button>
        </div>
        @if ($confirm)
            <input name="password_confirmation" type="hidden" data-ed-password-confirm @unless ($create) disabled @endunless>
        @endif
        <div class="ed-strength" data-ed-strength data-level="0">
            <span></span><span></span><span></span><span></span>
            <small data-ed-strength-label>8 أحرف على الأقل</small>
        </div>
        @if ($invalid)
            <div class="invalid-feedback d-block">{{ $errors->first('password') }}</div>
        @endif
    </div>
</div>
