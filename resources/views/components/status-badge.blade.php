@props(['status'])

@php
    $conf = config('statuses.map')[$status] ?? config('statuses.fallback');
@endphp

<span {{ $attributes->merge(['class' => 'badge status-badge ' . $conf['badge']]) }}>
    <i class="ti {{ $conf['icon'] }}"></i>
    {{ __('site.' . $status) }}
</span>
