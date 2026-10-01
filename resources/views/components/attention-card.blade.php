@props([
    'icon' => 'ti-alert-triangle',
    'count' => 0,
    'title' => '',
    'text' => null,
    'href' => null,
    'cta' => __('عرض القائمة'),
    'tone' => 'warning', // warning | azure | red | green
])

{{--
    بطاقة "يحتاج انتباه" — تُبرز رقماً قابلاً للإجراء مع زر.
    <x-attention-card icon="ti-user-x" :count="$not_has_group"
        title="طالب بدون مجموعة" text="لم ينضمّوا لأي فريق بعد."
        :href="route('admin.students.index')" tone="warning" />
--}}

<div {{ $attributes->merge(['class' => 'card card-link-pop']) }}>
    <div class="card-status-start bg-{{ $tone }}"></div>
    <div class="card-body d-flex align-items-center">
        <span class="avatar avatar-lg bg-{{ $tone }}-lt text-{{ $tone }} rounded-3 me-3">
            <i class="ti {{ $icon }} fs-2"></i>
        </span>
        <div class="me-auto">
            <div class="d-flex align-items-baseline gap-2">
                <span class="h2 mb-0">{{ $count }}</span>
                <span class="fw-semibold">{{ $title }}</span>
            </div>
            @if ($text)
                <div class="text-secondary small mt-1">{{ $text }}</div>
            @endif
        </div>
        @if ($href)
            <a href="{{ $href }}" class="btn btn-sm btn-{{ $tone }}-lt ms-3">
                {{ $cta }}
                <i class="ti ti-arrow-left ms-1"></i>
            </a>
        @endif
    </div>
</div>
