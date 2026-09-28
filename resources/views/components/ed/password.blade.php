@props(['confirm' => false])

{{--
    كلمة السر في درج التعديل: مطويّة خلف زرّ. كانت حقلاً فارغاً عنوانه
    «كلمة السر» لا يقول إن تركه يُبقي الحالية أم يمحوها. الطيّ يفرّغه،
    فلا تُرسل كلمة سر إلا لمن فتحه وكتب — أو ولّد — واحدة.
--}}
@php
    $invalid = old('id') && ($errors->has('password'));
@endphp

<div class="ed-password {{ $invalid ? 'is-open' : '' }}" data-ed-password>
    <button type="button" class="ed-password-toggle" data-ed-password-open @if ($invalid) hidden @endif>
        <i class="ti ti-key" aria-hidden="true"></i>
        <span>
            <b>تعيين كلمة سر جديدة</b>
            <small>كلمة السر الحالية تبقى كما هي ما لم تعيّن غيرها</small>
        </span>
        <i class="ti ti-chevron-left" aria-hidden="true"></i>
    </button>

    <div class="ed-password-body" data-ed-password-body @unless ($invalid) hidden @endunless>
        <div class="ed-password-head">
            <label for="ed-password" class="form-label">كلمة السر الجديدة</label>
            <button type="button" class="ed-link" data-ed-password-close>إلغاء — إبقاء الحالية</button>
        </div>
        <div class="ed-password-input">
            <input id="ed-password" name="password" type="password" minlength="8" maxlength="60"
                class="form-control {{ $invalid ? 'is-invalid' : '' }}" autocomplete="new-password" dir="ltr" disabled>
            <button type="button" class="ed-icon-btn" data-ed-password-show title="إظهار" aria-label="إظهار كلمة السر">
                <i class="ti ti-eye" aria-hidden="true"></i>
            </button>
            <button type="button" class="ed-icon-btn" data-ed-password-generate title="توليد كلمة سر قوية" aria-label="توليد كلمة سر قوية">
                <i class="ti ti-wand" aria-hidden="true"></i>
            </button>
        </div>
        @if ($confirm)
            <input name="password_confirmation" type="hidden" data-ed-password-confirm disabled>
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
