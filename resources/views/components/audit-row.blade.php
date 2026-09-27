@props(['log'])

{{--
    سطر في سجلّ التدقيق.

    مكوّن واحد لصفحة السجلّ ولشريط «تاريخ هذا المشروع» معاً — وإلا
    انحرف الشكلان مع أول تعديل.

    الأسماء تُقرأ من اللقطات النصّية (\u200Eactor_name\u200E و\u200Esubject_label\u200E) لا
    من العلاقات: الفاعل أو الكيان قد يكون حُذف، وسطر يشير إلى صفّ
    محذوف يصير فارغاً — وهو أوّل ما يُحتاج إليه عند التنازع.
--}}

@php
    $icon = match (true) {
        str_starts_with($log->action, 'grade.') => 'ti-award',
        str_contains($log->action, 'Deleted') || str_ends_with($log->action, '.deleted') => 'ti-trash',
        str_ends_with($log->action, '.restored') => 'ti-arrow-back-up',
        str_ends_with($log->action, '.archived') => 'ti-archive',
        str_ends_with($log->action, '.created') => 'ti-plus',
        default => 'ti-pencil',
    };

    $tone = match (true) {
        in_array($log->action, ['grade.locked'], true) => 'is-good',
        in_array($log->action, ['grade.unlocked', 'project.forceDeleted'], true) => 'is-danger',
        str_starts_with($log->action, 'grade.') => 'is-warn',
        str_ends_with($log->action, '.deleted') => 'is-danger',
        default => '',
    };

    $changes = $log->changes ?? [];
@endphp

<article class="audit-row {{ $tone }}">
    <span class="audit-icon" aria-hidden="true"><i class="ti {{ $icon }}"></i></span>

    <div class="audit-body">
        <p class="audit-head">
            <b>{{ $log->action_label }}</b>
            @if ($log->subject_label)
                <span class="audit-subject">{{ $log->subject_label }}</span>
            @endif
        </p>

        @if (isset($changes['grade']))
            <p class="audit-change">
                @if (array_key_exists('from', $changes['grade']) && ! is_null($changes['grade']['from']))
                    <span class="audit-from">{{ $changes['grade']['from'] }}</span>
                    <i class="ti ti-arrow-left" aria-hidden="true"></i>
                @endif
                <span class="audit-to">{{ $changes['grade']['to'] }}</span>
                <small>من ١٠٠</small>
            </p>
        @endif

        @if (isset($changes['supervisor']))
            <p class="audit-change">
                <span class="audit-from">{{ $changes['supervisor']['from'] }}</span>
                <i class="ti ti-arrow-left" aria-hidden="true"></i>
                <span class="audit-to">{{ $changes['supervisor']['to'] }}</span>
            </p>
        @endif

        {{-- السبب هو ما يُقرأ عند التنازع، فيُعرض كاملاً لا مقتطعاً --}}
        @if (! empty($changes['reason']))
            <p class="audit-reason">
                <i class="ti ti-quote" aria-hidden="true"></i>
                {{ $changes['reason'] }}
            </p>
        @endif

        <p class="audit-meta">
            <span>{{ $log->actor_name ?: 'النظام' }}</span>
            <span class="audit-role">{{ $log->role_label }}</span>
            <span class="audit-sep" aria-hidden="true">·</span>
            <time datetime="{{ $log->created_at?->toIso8601String() }}"
                title="{{ $log->created_at?->format('Y-m-d H:i') }}">
                {{ $log->created_at?->diffForHumans() }}
            </time>
        </p>
    </div>
</article>
