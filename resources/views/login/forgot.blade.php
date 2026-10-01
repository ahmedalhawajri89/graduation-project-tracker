@extends('login.layout')
@section('title', __('استرجاع كلمة السر'))

@section('card')
    <h2>{{ __('نسيت كلمة السر؟') }}</h2>
    <p class="login-lead">
        {{ __('أدخل بريدك أو رقمك الجامعي، ونرسل إليك رابطاً لتعيين كلمة سر جديدة.') }}
    </p>

    {{-- الرسالة واحدة سواء وُجد الحساب أو لم يوجد — ردّ مختلف يحوّل
         النموذج إلى أداة تُعدّد البُرد المسجّلة --}}
    @if (Session::get('success'))
        <div class="alert alert-success" role="status">
            <i class="ti ti-mail-check me-1" aria-hidden="true"></i>
            {{ Session::get('success') }}
        </div>
    @endif

    <form action="{{ route('password.email') }}" method="POST" novalidate>
        @csrf

        <div class="mb-4 auth-field">
            <label class="form-label" for="identify">{{ __('البريد الإلكتروني أو الرقم الجامعي') }}</label>
            <input id="identify" type="text" class="form-control @error('identify') is-invalid @enderror"
                name="identify" value="{{ old('identify') }}" placeholder="{{ __('مثال: 2300000238') }}" required
                autocomplete="username" autofocus>
            @error('identify')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="auth-actions">
            <button type="submit" class="btn btn-login" data-loading="{{ __('جارٍ الإرسال…') }}">{{ __('إرسال الرابط') }}</button>
        </div>
    </form>

    <p class="auth-note">
        {{ __('الرابط صالح 30 دقيقة ويُستعمل مرة واحدة. إن لم تصلك الرسالة فتحقّق من صندوق الرسائل غير المرغوبة، أو راجع إدارة القسم.') }}
    </p>
@endsection

@section('back')
    <a href="{{ route('login') }}" class="auth-back">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        {{ __('العودة إلى تسجيل الدخول') }}
    </a>
@endsection
