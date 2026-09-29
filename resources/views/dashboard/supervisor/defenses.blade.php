@extends('layouts.admin.admin')
@section('title', 'مناقشاتي')

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">لوحتي</x-crumb>
    <x-crumb>مناقشاتي</x-crumb>
@endsection

@section('content')

    @php
        $dayLabel = fn ($date) => $date->isToday() ? 'اليوم' : ($date->isTomorrow() ? 'غداً' : $date->translatedFormat('l j F'));
        $fmt = fn ($g) => is_null($g) ? '—' : rtrim(rtrim(number_format((float) $g, 2, '.', ''), '0'), '.');
    @endphp

    <x-page-header title="مناقشاتي"
        subtitle="اللجان التي أنت عضو فيها — مشرفاً لمشروعك أو ممتحناً لغيره" />

    {{-- ═══ ينتظر درجتك ═══ --}}
    @if ($toGrade->count())
        <section class="dsv-block">
            <h2 class="dsv-title is-warn"><i class="ti ti-award" aria-hidden="true"></i> تنتظر درجتك <span>{{ $toGrade->count() }}</span></h2>
            <div class="df-agenda">
                @foreach ($toGrade as $d)
                    <a href="{{ route('supervisor.defenses.show', $d->id) }}" class="df-slot dsv-slot is-warn">
                        <div class="df-time">
                            <b dir="ltr">{{ $d->starts_at->format('H:i') }}</b>
                            <small>{{ $d->starts_at->format('m-d') }}</small>
                        </div>
                        <div class="df-slot-body">
                            <span class="df-title">{{ $d->project->title }}</span>
                            <span class="df-sub">نوقش {{ $d->starts_at->diffForHumans() }} · دورك: {{ $d->mine->role_label }}
                                · رصد {{ $d->members->whereNotNull('grade')->count() }} من {{ $d->members->count() }}</span>
                        </div>
                        <span class="btn btn-primary btn-sm">رصد درجتي</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══ القادمة ═══ --}}
    <section class="dsv-block">
        <h2 class="dsv-title"><i class="ti ti-calendar-event" aria-hidden="true"></i> القادمة <span>{{ $upcoming->count() }}</span></h2>
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
                            <span class="df-sub">{{ $d->project->group->map(fn ($g) => $g->student?->name)->filter()->implode('، ') }}</span>
                            <div class="df-chips">
                                <span class="df-mode is-{{ $d->mode }}"><i class="ti {{ $d->mode_icon }}" aria-hidden="true"></i>{{ $d->place_label }}</span>
                                <span class="dsv-role is-{{ $d->mine->role }}">{{ $d->mine->role_label }}</span>
                            </div>
                        </div>
                        <div class="df-actions">
                            @if ($d->needsLink() && $d->meeting_url)
                                <a href="{{ $d->meeting_url }}" target="_blank" rel="noopener" class="btn btn-sm {{ $d->isJoinable() ? 'btn-primary' : 'btn-outline-primary' }}">
                                    <i class="ti ti-video me-1" aria-hidden="true"></i>{{ $d->isJoinable() ? 'انضم الآن' : 'الرابط' }}
                                </a>
                            @endif
                            <a href="{{ $d->googleCalendarUrl() }}" target="_blank" rel="noopener" class="btn-action" title="أضف إلى تقويم Google" aria-label="أضف إلى تقويم Google">
                                <i class="ti ti-calendar-plus" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('supervisor.defenses.show', $d->id) }}" class="btn btn-sm btn-outline-secondary">ملف المشروع</a>
                        </div>
                    </li>
                @endforeach
            </ol>
        @empty
            <div class="dist-panel">
                <x-empty-state icon="ti-calendar" title="لا مناقشات قادمة"
                    text="حين تُجدولك الإدارة في لجنة مناقشة — لمشروعك أو ممتحناً لغيره — تظهر هنا، ويصلك إشعار وبريد." class="py-5" />
            </div>
        @endforelse
    </section>

    {{-- ═══ رصدتَ درجتها ═══ --}}
    @if ($graded->count())
        <section class="dsv-block">
            <h2 class="dsv-title"><i class="ti ti-circle-check" aria-hidden="true"></i> رصدتَ درجتها <span>{{ $graded->count() }}</span></h2>
            <div class="df-past">
                @foreach ($graded as $d)
                    <a href="{{ route('supervisor.defenses.show', $d->id) }}" class="df-past-row">
                        <span class="df-past-date" dir="ltr">{{ $d->starts_at->format('Y-m-d') }}</span>
                        <b>{{ $d->project->title }}</b>
                        <span class="df-sub">درجتك {{ $fmt($d->mine->grade) }} · {{ $d->mine->role_label }}</span>
                        @if ($d->status === 'done')
                            <span class="df-status is-done">النهائية {{ $fmt($d->project->grade) }}</span>
                        @else
                            <span class="df-status is-wait">بانتظار زميلك</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

@endsection
