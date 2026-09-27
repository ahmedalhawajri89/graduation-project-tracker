@props([
    'user',
    'class' => 'cell-avatar',
])

{{--
    الأفاتار: صورة إن وُجدت، وإلا الأحرف الأولى.

    الصورة اختيارية دائماً، فالأحرف الأولى ليست حالة خطأ بل الأساس.
    ونظيره للمواضع التي تُبنى في PHP هو \u200EApp\Support\Avatar::html()\u200E،
    والترميزان متطابقان حتى لا ينحرف الشكلان.
--}}

@php
    $url = $user?->avatar_url;
@endphp

<span {{ $attributes->merge(['class' => $class . ($url ? ' has-photo' : '')]) }}>
    @if ($url)
        <img src="{{ $url }}" alt="" loading="lazy" decoding="async">
    @else
        {{ $user?->initials ?? '؟' }}
    @endif
</span>
