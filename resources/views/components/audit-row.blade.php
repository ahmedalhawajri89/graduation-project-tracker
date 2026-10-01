@props([
    'log',
    // خارج صفحة السجلّ لا عناوين أيام — فيُكتب التاريخ مع الساعة
    'dated' => false,
])

{{--
    حدث في سجلّ التدقيق — عقدة على خطّ زمني.

    مكوّن واحد لصفحة السجلّ ولتبويب «سجلّ المشروع» في صفحة المجموعة.
    الأسماء من اللقطات النصّية (\u200Eactor_name\u200E و\u200Esubject_label\u200E) لا من العلاقات:
    الفاعل أو الكيان قد يكون حُذف، وهو أوّل ما يُحتاج إليه عند التنازع.
    الفئة والأيقونة والشارات من \u200EApp\Support\AuditPresenter\u200E.
--}}

@php
    $cat = \App\Support\AuditPresenter::category($log->action);
    $chips = \App\Support\AuditPresenter::chips($log);
    $url = \App\Support\AuditPresenter::subjectUrl($log);
    $reason = $log->changes['reason'] ?? null;
    $actor = $log->relationLoaded('actor') ? $log->actor : null;
    $initials = mb_substr(trim(preg_replace('/^\s*(أ\.د\.|د\.|أ\.)\s*/u', '', (string) $log->actor_name)) ?: '؟', 0, 2);
@endphp

<article class="at-row" data-cat="{{ $cat }}">
    <span class="at-node" aria-hidden="true"><i class="ti {{ \App\Support\AuditPresenter::icon($log->action) }}"></i></span>

    <div class="at-body">
        <p class="at-head">
            <b>{{ $log->action_label }}</b>
            @if ($log->subject_label)
                @if ($url)
                    <a href="{{ $url }}" class="at-subject">{{ $log->subject_label }}</a>
                @else
                    <span class="at-subject">{{ $log->subject_label }}</span>
                @endif
            @endif
        </p>

        @if ($chips)
            <ul class="at-chips">
                @foreach ($chips as $chip)
                    <li class="at-chip">
                        @if ($chip['label'])
                            <span class="at-chip-label">{{ $chip['label'] }}</span>
                        @endif
                        @if ($chip['from'])
                            <span class="at-from">{{ $chip['from'] }}</span>
                            <i class="ti ti-arrow-left" aria-label="{{ __('إلى') }}"></i>
                        @endif
                        @if ($chip['to'])
                            <span class="at-to">{{ $chip['to'] }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- السبب هو ما يُقرأ عند التنازع، فيُعرض كاملاً لا مقتطعاً --}}
        @if (is_string($reason) && $reason !== '' && $reason !== 'seats_full')
            <blockquote class="at-reason">{{ $reason }}</blockquote>
        @endif

        <p class="at-meta">
            @if ($actor)
                <x-avatar :user="$actor" class="ctx-avatar at-avatar" />
            @else
                <span class="ctx-avatar at-avatar" aria-hidden="true">{{ $initials }}</span>
            @endif
            <span class="at-actor">{{ $log->actor_name ?: __('النظام') }}</span>
            <span class="at-role">{{ $log->role_label }}</span>
            <span class="at-sep" aria-hidden="true">·</span>
            <time datetime="{{ $log->created_at?->toIso8601String() }}"
                title="{{ $log->created_at?->translatedFormat('l j F Y — H:i') }}">
                {{ $dated ? $log->created_at?->format('Y-m-d H:i') : $log->created_at?->format('H:i') }}
            </time>
        </p>
    </div>
</article>
