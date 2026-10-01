@extends('layouts.admin.admin')
@section('title', __('مناقشاتي'))

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">{{ __('لوحتي') }}</x-crumb>
    <x-crumb>{{ __('مناقشاتي') }}</x-crumb>
@endsection

@section('content')

    @php
        $dayLabel = fn ($date) => $date->isToday() ? __('اليوم') : ($date->isTomorrow() ? __('غداً') : $date->translatedFormat('l j F'));
        $fmt = fn ($g) => is_null($g) ? '—' : rtrim(rtrim(number_format((float) $g, 2, '.', ''), '0'), '.');
    @endphp

    <x-page-header title="{{ __('مناقشاتي') }}"
        subtitle="{{ __('اللجان التي أنت عضو فيها — مشرفاً لمشروعك أو ممتحناً لغيره') }}" />

    {{-- ═══ ينتظر درجتك ═══ --}}
    @if ($toGrade->count())
        <section class="dsv-block">
            <h2 class="dsv-title is-warn"><i class="ti ti-award" aria-hidden="true"></i> {{ __('تنتظر درجتك') }} <span>{{ $toGrade->count() }}</span></h2>
            <div class="df-agenda">
                @foreach ($toGrade as $d)
                    <a href="{{ route('supervisor.defenses.show', $d->id) }}" class="df-slot dsv-slot is-warn">
                        <div class="df-time">
                            <b dir="ltr">{{ $d->starts_at->format('H:i') }}</b>
                            <small>{{ $d->starts_at->format('m-d') }}</small>
                        </div>
                        <div class="df-slot-body">
                            <span class="df-title">{{ $d->project->title }}</span>
                            <span class="df-sub">{{ __('نوقش :when', ['when' => $d->starts_at->diffForHumans()]) }} · {{ __('دورك: :role', ['role' => $d->roleOf($d->mine)]) }}
                                · {{ __('رصد :n من :total', ['n' => $d->members->whereNotNull('grade')->count(), 'total' => $d->members->count()]) }}</span>
                        </div>
                        <span class="btn btn-primary btn-sm">{{ __('رصد درجتي') }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══ القادمة ═══ --}}
    <section class="dsv-block">
        <h2 class="dsv-title"><i class="ti ti-calendar-event" aria-hidden="true"></i> {{ __('القادمة') }} <span>{{ $upcoming->count() }}</span></h2>
        @forelse ($upcoming->groupBy(fn ($d) => $d->starts_at->format('Y-m-d')) as $items)
            <h3 class="df-day-title">{{ $dayLabel($items->first()->starts_at) }} <small>{{ $items->first()->starts_at->format('Y-m-d') }}</small></h3>
            <ol class="df-agenda mb-3">
                @foreach ($items as $d)
                    <li class="df-slot {{ $d->isJoinable() ? 'is-live' : '' }}">
                        <div class="df-time">
                            <b dir="ltr">{{ $d->starts_at->format('H:i') }}</b>
                            <small dir="ltr">{{ $d->endsAt()->format('H:i') }}</small>
                        </div>
                        <div class="df-slot-body">
                            <a href="{{ route('supervisor.defenses.show', $d->id) }}" class="df-title">{{ $d->project->title }}</a>
                            <span class="df-sub">{{ $d->project->group->map(fn ($g) => $g->student?->name)->filter()->implode(__('، ')) }}</span>
                            <div class="df-chips">
                                <span class="df-mode is-{{ $d->mode }}"><i class="ti {{ $d->mode_icon }}" aria-hidden="true"></i>{{ $d->place_label }}</span>
                                <span class="dsv-role is-{{ $d->mine->role }}">{{ $d->roleOf($d->mine) }}</span>
                            </div>
                        </div>
                        <div class="df-actions">
                            @if ($d->needsLink() && $d->meeting_url)
                                <a href="{{ $d->meeting_url }}" target="_blank" rel="noopener" class="btn btn-sm {{ $d->isJoinable() ? 'btn-primary' : 'btn-outline-primary' }}">
                                    <i class="ti ti-video me-1" aria-hidden="true"></i>{{ $d->isJoinable() ? __('انضم الآن') : __('الرابط') }}
                                </a>
                            @endif
                            <a href="{{ $d->googleCalendarUrl() }}" target="_blank" rel="noopener" class="btn-action" title="{{ __('أضف إلى تقويم Google') }}" aria-label="{{ __('أضف إلى تقويم Google') }}">
                                <i class="ti ti-calendar-plus" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('supervisor.defenses.show', $d->id) }}" class="btn btn-sm btn-outline-secondary">{{ __('ملف المشروع') }}</a>
                        </div>
                    </li>
                @endforeach
            </ol>
        @empty
            <div class="dist-panel">
                <x-empty-state icon="ti-calendar" title="{{ __('لا مناقشات قادمة') }}"
                    text="{{ __('حين تُجدولك الإدارة في لجنة مناقشة — لمشروعك أو ممتحناً لغيره — تظهر هنا، ويصلك إشعار وبريد.') }}" class="py-5" />
            </div>
        @endforelse
    </section>

    {{-- ═══ رصدتَ درجتها ═══ --}}
    @if ($graded->count())
        <section class="dsv-block">
            <h2 class="dsv-title"><i class="ti ti-circle-check" aria-hidden="true"></i> {{ __('رصدتَ درجتها') }} <span>{{ $graded->count() }}</span></h2>
            <div class="df-past">
                @foreach ($graded as $d)
                    <a href="{{ route('supervisor.defenses.show', $d->id) }}" class="df-past-row">
                        <span class="df-past-date" dir="ltr">{{ $d->starts_at->format('Y-m-d') }}</span>
                        <b>{{ $d->project->title }}</b>
                        <span class="df-sub">{{ __('درجتك :grade', ['grade' => $fmt($d->mine->grade)]) }} · {{ $d->roleOf($d->mine) }}</span>
                        @if ($d->status === 'done')
                            <span class="df-status is-done">{{ __('النهائية :grade', ['grade' => $fmt($d->project->grade)]) }}</span>
                        @else
                            <span class="df-status is-wait">{{ __('بانتظار بقية اللجنة') }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

@endsection
