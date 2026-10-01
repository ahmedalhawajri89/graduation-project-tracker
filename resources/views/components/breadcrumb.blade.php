@php
    $guard = auth()->guard('admin')->check() ? 'admin'
        : (auth()->guard('supervisor')->check() ? 'supervisor' : 'student');

    // المسار تُعلنه كل صفحة بـ @section('crumbs'). Blade يقيّم القالب
    // الابن ويسجّل أقسامه قبل رسم التخطيط، فالقسم متاح هنا رغم أن
    // الهيدر فوق المحتوى.
    $trail = trim($__env->yieldContent('crumbs'));

    // لا مسار معلَن؟ نكتفي بعنوان الصفحة كحلقة أخيرة
    $fallback = $trail === '' ? trim($__env->yieldContent('title')) : null;
@endphp

{{--
    مسار التنقّل — يقول أين أنت ويتيح العودة لأي سلف.
    كان يعرض الرئيسية والصفحة الحالية فقط، ويُسقط ما بينهما — ولهذا
    اضطرّت ستّ صفحات إلى كتابة أزرار عودة خاصة بها.
--}}
<nav class="crumb" aria-label="{{ __('مسار التنقّل') }}">
    <a href="{{ route($guard . '.dashboard') }}" class="crumb-home" aria-label="{{ __('الصفحة الرئيسية') }}">
        <i class="ti ti-home" aria-hidden="true"></i>
    </a>

    @if ($trail !== '')
        {!! $trail !!}
    @elseif ($fallback)
        <i class="ti ti-chevron-left crumb-sep" aria-hidden="true"></i>
        <span class="crumb-current" aria-current="page">{{ $fallback }}</span>
    @endif
</nav>
