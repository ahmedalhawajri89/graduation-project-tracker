@props(['href' => null])

{{--
    حلقة واحدة من مسار التنقّل.
    بـ href = سلف قابل للنقر، وبدونه = الصفحة الحالية.
--}}

<i class="ti ti-chevron-left crumb-sep" aria-hidden="true"></i>

@if ($href)
    <a href="{{ $href }}" class="crumb-link">{{ $slot }}</a>
@else
    <span class="crumb-current" aria-current="page">{{ $slot }}</span>
@endif
