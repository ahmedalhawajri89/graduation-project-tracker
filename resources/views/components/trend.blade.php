@props(['value' => null])

@php
    $v = is_numeric($value) ? (int) $value : null;
@endphp

{{-- مؤشّر اتجاه صغير بجانب الرقم. كان شارة ملوّنة بحجم كامل؛ صار سهماً
     ونسبة بحجم ١١ بكسل — الرقم هو البطل لا الشارة. --}}
@if (! is_null($v) && $v !== 0)
    <span class="trend {{ $v > 0 ? 'trend--up' : 'trend--down' }}" title="{{ __('مقارنة بآخر لقطة') }}">
        <i class="ti {{ $v > 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }}"></i>{{ abs($v) }}%
    </span>
@endif
