@props(['icon' => 'ti-inbox', 'title', 'text' => null])

{{--
    حالة فارغة موحّدة.
    <x-empty-state icon="ti-users-group" title="لا توجد مجموعات بعد" text="ابدأ بإضافة أول مجموعة.">
        <x-slot:action><a href="#" class="btn btn-primary">إضافة</a></x-slot:action>
    </x-empty-state>
--}}

<div {{ $attributes->merge(['class' => 'empty']) }}>
    <div class="empty-icon">
        <i class="ti {{ $icon }} fs-1 text-secondary"></i>
    </div>
    <p class="empty-title h3 mt-3">{{ $title }}</p>
    @if ($text)
        <p class="empty-subtitle text-secondary">{{ $text }}</p>
    @endif
    @isset($action)
        <div class="empty-action mt-3">
            {{ $action }}
        </div>
    @endisset
</div>
