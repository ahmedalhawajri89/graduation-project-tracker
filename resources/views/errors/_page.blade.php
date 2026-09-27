{{--
    صفحة خطأ عربية — كانت صفحات Laravel الإنجليزية الافتراضية تظهر في
    منصّة عربية RTL، و500 مع APP_DEBUG تكشف تتبّع الكود.

    فوق تخطيط صفحة الدخول: مستقلّ، لا يحتاج جلسة ولا مستخدماً ولا قاعدة.

    @param string      $code
    @param string      $title
    @param string      $text
    @param string|null $actionUrl
    @param string|null $actionLabel
--}}

@extends('login.layout')
@section('title', $title)

@section('card')
    <p class="error-code" aria-hidden="true">{{ $code }}</p>
    <h2>{{ $title }}</h2>
    <p class="login-lead">{{ $text }}</p>

    @if (! empty($actionUrl))
        <a href="{{ $actionUrl }}" class="btn btn-login w-100">{{ $actionLabel }}</a>
    @endif
@endsection

{{-- الزرّ يعود إلى الرئيسية أصلاً — رابط «العودة إلى الموقع» تحته يكرّره --}}
@if (($actionUrl ?? null) === url('/'))
    {{-- عنصر فارغ لا قسم فارغ: @hasSection يعدّ القسم الفارغ غائباً فيعيد الرابط الافتراضي --}}
    @section('back')
        <span hidden></span>
    @endsection
@endif
