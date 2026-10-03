@props(['icon' => 'ti-inbox', 'title', 'text' => null, 'person' => null])

{{--
    حالة فارغة موحّدة.
    <x-empty-state icon="ti-users-group" title="لا توجد مجموعات بعد" text="ابدأ بإضافة أول مجموعة.">
        <x-slot:action><a href="#" class="btn btn-primary">إضافة</a></x-slot:action>
    </x-empty-state>

    بدل أيقونة رمادية وحدها: شخصية من «رحلة المشروع» (partials/cast.blade.php)
    تقف في هالة، وأيقونة الحالة شارة بجانبها. الشخصية تُختار من معنى الأيقونة
    (الرسائل للطالبة، الفريق لثلاثة طلاب، المواعيد للجنة...)، أو تُمرَّر بـ
    person="hj-supervisor" أو person="team". النسخة السطرية (is-inline) تبقى أيقونة.
--}}

@php
    $inline = str_contains((string) $attributes->get('class'), 'is-inline');
    $person ??= match (true) {
        in_array($icon, ['ti-messages', 'ti-messages-off', 'ti-mail-off'], true) => 'hj-girl',
        $icon === 'ti-users-group' => 'team',
        in_array($icon, ['ti-files', 'ti-file-off', 'ti-archive', 'ti-list-details'], true) => 'hj-boy',
        $icon === 'ti-calendar' => 'hj-committee',
        in_array($icon, ['ti-search-off', 'ti-telescope'], true) => 'hj-boy2',
        $icon === 'ti-history' => 'hj-supervisor',
        default => 'hj-admin',
    };
    // [viewBox, عرض الرسم عند ارتفاع 100]
    [$box, $w] = match ($person) {
        'team' => ['-66 -100 132 106', 125],
        'hj-committee' => ['-46 -74 92 80', 115],
        default => ['-34 -100 68 106', 64],
    };
@endphp

<div {{ $attributes->merge(['class' => 'empty']) }}>
    @if ($inline)
        <div class="empty-icon">
            <i class="ti {{ $icon }} fs-1 text-secondary"></i>
        </div>
    @else
        <div class="empty-scene" aria-hidden="true">
            <svg class="empty-cast" viewBox="{{ $box }}" width="{{ $w }}" height="100" focusable="false">
                @if ($person === 'team')
                    <use href="#hj-girl" x="-40" /><use href="#hj-boy" x="0" /><use href="#hj-boy2" x="40" />
                @else
                    <use href="#{{ $person }}" />
                @endif
            </svg>
            <span class="empty-badge"><i class="ti {{ $icon }}"></i></span>
        </div>
    @endif
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
