{{--
    شارة عدّاد في الشريط الجانبي يحدّثها live.js دون إعادة تحميل.
    تُرسم دائماً (مخفية حين تكون صفراً) ليجد السكربت مكانها حين يصل جديد.
--}}
@props(['key', 'n' => 0])
<span {{ $attributes->merge(['class' => 'sidebar-count']) }} data-live-count="{{ $key }}" @if (! $n) hidden @endif>{{ $n > 99 ? '99+' : $n }}</span>
