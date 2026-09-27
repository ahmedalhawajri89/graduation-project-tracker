@extends('login.layout')
@section('title', 'تعيين كلمة سر جديدة')

@section('card')
    <h2>كلمة سر جديدة</h2>
    <p class="login-lead">
        اختر كلمة سر لا تقلّ عن ٨ أحرف. ستُستعمل للدخول بعد الحفظ مباشرةً.
    </p>

    @if ($errors->any())
        <div class="alert" role="alert">{{ $errors->first() }}</div>
    @endif

    <form action="{{ route('password.update', $guard) }}" method="POST" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3 auth-field">
            <label class="form-label" for="email">البريد الإلكتروني</label>
            {{-- يأتي من الرابط: يُعرَض ليتأكّد المستخدم أنه يعيّن كلمة
                 سرّ الحساب الصحيح، ويبقى قابلاً للتصحيح --}}
            <input id="email" type="email" dir="ltr"
                class="form-control @error('email') is-invalid @enderror" name="email"
                value="{{ old('email', $email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3 auth-field">
            <label class="form-label" for="password">كلمة السر الجديدة</label>
            <div class="password-wrapper">
                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                    name="password" placeholder="٨ أحرف على الأقل" required minlength="8"
                    autocomplete="new-password" autofocus>
                <button type="button" class="toggle-password" aria-label="إظهار كلمة السر" data-target="password">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 auth-field">
            <label class="form-label" for="password_confirmation">تأكيد كلمة السر</label>
            <div class="password-wrapper">
                <input id="password_confirmation" type="password" class="form-control"
                    name="password_confirmation" placeholder="••••••••" required autocomplete="new-password">
                <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                    data-target="password_confirmation">
                    <i class="ti ti-eye"></i>
                </button>
            </div>
        </div>

        <div class="auth-actions">
            <button type="submit" class="btn btn-login" data-loading="جارٍ الحفظ…">حفظ كلمة السر</button>
        </div>
    </form>

    <p class="auth-note">
        بعد الحفظ تُلغى جلساتك السابقة على الأجهزة الأخرى.
    </p>
@endsection

@section('back')
    <a href="{{ route('login') }}" class="auth-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        العودة إلى تسجيل الدخول
    </a>
@endsection
