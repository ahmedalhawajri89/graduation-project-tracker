@extends('layouts.admin.admin')
@section('title', __('المناقشات'))

@section('crumbs')
    <x-crumb>{{ __('المناقشات') }}</x-crumb>
@endsection

@section('content')

    @use('App\Models\Defense')

    @php
        $activeRooms = $rooms->where('is_active', true)->values();
        // اختيار رئيس اللجنة يظهر بعد ترحيل عموده
        $chairs = \App\Models\DefenseMember::chairSupported();

        // بيانات كل مشروع للنافذة: تُملأ منها عند الجدولة، وعند إعادة فتحها بعد خطأ
        $meta = fn ($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'type' => $p->project_type->name ?? '',
            'supervisor' => $p->supervisor->name ?? '',
            'supervisor_id' => $p->supervisor_id,
            'specialize_id' => $p->project_type->specialize_id ?? null,
            'team' => $p->group->map(fn ($g) => $g->student?->name)->filter()->implode(__('، ')),
        ];
        $projectsMeta = $awaiting->mapWithKeys(fn ($p) => [$p->id => $meta($p)])
            ->union($upcoming->mapWithKeys(fn ($d) => [$d->project_id => $meta($d->project)]))
            ->union($week['defenses']->mapWithKeys(fn ($d) => [$d->project_id => $meta($d->project)]));

        $defenseMeta = fn ($d) => [
            'id' => $d->id,
            'date' => $d->starts_at->format('Y-m-d'),
            'time' => $d->starts_at->format('H:i'),
            'duration_minutes' => $d->duration_minutes,
            'mode' => $d->mode,
            'room_id' => $d->room_id,
            'meeting_url' => $d->meeting_url,
            'examiner_ids' => $d->members->where('role', 'examiner')->pluck('supervisor_id')->values(),
            'chair_id' => $d->chair()?->supervisor_id,
            'notes' => $d->notes,
        ];

        $dayLabel = fn ($date) => $date->isToday() ? __('اليوم') : ($date->isTomorrow() ? __('غداً') : $date->translatedFormat('l j F'));
    @endphp

    @php
        $planned = collect($plan)->filter();
        $slotMinutes = (int) config('defenses.slot');
        // ما يُعرض في خانة: مناقشات تبدأ داخلها، واقتراحات هذا الأسبوع أشباحاً
        $inSlot = fn ($day, $time) => $week['defenses']->filter(function ($d) use ($day, $time, $slotMinutes) {
            $from = $day->copy()->setTimeFromTimeString($time);

            return $d->starts_at->gte($from) && $d->starts_at->lt($from->copy()->addMinutes($slotMinutes));
        });
        $ghosts = fn ($day, $time) => $planned->filter(fn ($s) => $s['starts_at']->format('Y-m-d H:i') === $day->format('Y-m-d') . ' ' . $time);
        $suggestionMeta = fn ($s) => [
            'date' => $s['starts_at']->format('Y-m-d'), 'time' => $s['starts_at']->format('H:i'),
            'duration_minutes' => $s['duration_minutes'], 'mode' => 'in_person',
            'room_id' => $s['room_id'], 'examiner_id' => $s['examiner_id'],
        ];
    @endphp

    <x-page-header title="{{ __('المناقشات') }}" subtitle="{{ __('لجنة وموعد لكل مشروع مكتمل — حضورياً أو عن بُعد') }}">
        <x-slot:actions>
            <a href="{{ route('admin.defenses.export') }}" class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                {{ __('تصدير Excel') }}
            </a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#roomsModal">
                <i class="ti ti-door me-1" aria-hidden="true"></i>
                {{ __('القاعات') }}
                <span class="df-count">{{ $activeRooms->count() }}</span>
            </button>
            @if ($planned->count() >= 2)
                {{-- جدول مقترح للكل يُعرض معاينةً ولا يُحفظ قبل الاعتماد --}}
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal">
                    <i class="ti ti-wand me-1" aria-hidden="true"></i>
                    {{ __('جدولة الكل تلقائياً') }}
                </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ مسار المناقشات: أين يقف كل مشروع ═══ --}}
    <nav class="df-pipe" aria-label="{{ __('مسار المناقشات') }}">
        @foreach ([
            ['awaiting', __('بانتظار الجدولة'), 'ti-hourglass', $pipeline['awaiting'], 'awaiting', true],
            ['scheduled', __('مجدولة'), 'ti-calendar-event', $pipeline['scheduled'], 'upcoming', false],
            ['grading', __('بانتظار الدرجة'), 'ti-award', $pipeline['grading'], 'past', false],
            ['done', __('منتهية'), 'ti-circle-check', $pipeline['done'], 'past', false],
        ] as [$key, $label, $icon, $n, $to, $action])
            <a href="{{ route('admin.defenses.index', ['tab' => $to]) }}"
                class="df-pipe-step is-{{ $key }} {{ $n ? 'has-items' : '' }} {{ $action && $n ? 'is-action' : '' }}">
                <span class="df-pipe-icon"><i class="ti {{ $icon }}" aria-hidden="true"></i></span>
                <span class="df-pipe-body"><b>{{ $n }}</b><small>{{ $label }}</small></span>
            </a>
        @endforeach
    </nav>

    @if (! $activeRooms->count())
        <div class="df-note">
            <i class="ti ti-info-circle" aria-hidden="true"></i>
            {{ __('لا قاعات بعد — الاقتراح التلقائي يحتاج قاعة، والمناقشات عن بُعد متاحة يدوياً.') }}
            <button type="button" class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#roomsModal">{{ __('أضف قاعة') }}</button>.
        </div>
    @endif

    {{-- ═══ التبويبات ═══ --}}
    <div class="filter-tabs df-tabs mb-3" role="tablist">
        @foreach (['awaiting' => [__('المخطِّط'), $awaiting->count()], 'upcoming' => [__('القادمة'), $upcoming->count()], 'past' => [__('السابقة'), $past->count()]] as $key => [$label, $n])
            <a href="{{ route('admin.defenses.index', ['tab' => $key]) }}" class="filter-tab {{ $tab === $key ? 'is-active' : '' }}"
                @if ($tab === $key) aria-current="page" @endif>
                {{ $label }} <span class="filter-count">{{ $n }}</span>
            </a>
        @endforeach
    </div>

    {{-- ═══ المخطِّط: قائمة الانتظار باقتراحاتها + أسبوع الدوام ═══ --}}
    @if ($tab === 'awaiting')
        @php
            $initials = fn ($d) => $d->members->map(fn ($m) => $m->supervisor->initials)->all();
            // عدد ما في كل يوم: المجدول والمقترح
            $dayCount = fn ($day) => $week['defenses']->filter(fn ($d) => $d->starts_at->isSameDay($day))->count()
                + $planned->filter(fn ($x) => $x['starts_at']->isSameDay($day))->count();
        @endphp

        <div class="df-plan">
            <aside class="df-queue" aria-labelledby="df-queue-title">
                <h2 id="df-queue-title">
                    {{ __('بانتظار الجدولة') }} <span data-queue-count>{{ $awaiting->count() }}</span>
                    @if ($awaiting->count())
                        <small><i class="ti ti-hand-move" aria-hidden="true"></i> {{ __('اسحب مشروعاً إلى خانة') }}</small>
                    @endif
                </h2>

                {{-- حين تكثر المشاريع: بحث فوري، وبطاقات مدمجة تُفتح تفاصيلها بالنقر --}}
                @if ($awaiting->count() > 4)
                    <label class="df-queue-search">
                        <i class="ti ti-search" aria-hidden="true"></i>
                        <input type="search" data-queue-search placeholder="{{ __('ابحث بالمشروع أو المشرف أو النوع…') }}" aria-label="{{ __('بحث في المشاريع المنتظرة') }}">
                    </label>
                    <p class="df-queue-none" data-queue-none hidden>{{ __('لا مشروع يطابق البحث.') }}</p>
                @endif

                @forelse ($awaiting as $p)
                    @php $s = $plan[$p->id] ?? null; @endphp
                    {{-- البطاقة تُسحب إلى خانة في المخطِّط، والمرور عليها يُضيء اقتراحها فيه --}}
                    <article class="df-card {{ $awaiting->count() > 4 ? 'is-dense' : '' }}" data-project="{{ $p->id }}" draggable="true"
                        data-search="{{ mb_strtolower($p->title . ' ' . $p->supervisor->name . ' ' . ($p->project_type->name ?? '')) }}">
                        <header>
                            <i class="ti ti-grip-vertical df-grip" aria-hidden="true"></i>
                            <div>
                                <a href="{{ route('admin.groups.show', $p->id) }}" class="df-title" draggable="false">{{ $p->title }}</a>
                                <span class="df-sub">{{ $p->project_type->name ?? '—' }}<span class="df-sub-sup"> · {{ $p->supervisor->name }}</span></span>
                            </div>
                            @if ($awaiting->count() > 4)
                                <button type="button" class="df-expand" data-expand aria-expanded="false" aria-label="{{ __('تفاصيل :title', ['title' => $p->title]) }}">
                                    <i class="ti ti-chevron-down" aria-hidden="true"></i>
                                </button>
                            @endif
                        </header>
                        <div class="df-facts">
                            <span class="is-person"><x-avatar :user="$p->supervisor" class="cell-avatar df-av" />{{ $p->supervisor->name }}</span>
                            <span title="{{ $p->group->map(fn ($g) => $g->student?->name)->filter()->implode(__('، ')) }}"><i class="ti ti-users" aria-hidden="true"></i>{{ $p->group->count() }}</span>
                            <span title="{{ __('المراحل المنجزة') }}"><i class="ti ti-list-check" aria-hidden="true"></i>{{ $p->milestones_done }}/{{ $p->milestones_count }}</span>
                            <span title="{{ __('اكتمل :when', ['when' => $p->updated_at?->diffForHumans()]) }}"><i class="ti ti-clock" aria-hidden="true"></i>{{ $p->updated_at?->diffForHumans(null, true) }}</span>
                            @if ($p->defense && $p->defense->status === Defense::CANCELLED)
                                <span class="is-warn"><i class="ti ti-calendar-x" aria-hidden="true"></i>{{ __('أُلغيت سابقاً') }}</span>
                            @endif
                        </div>

                        @if ($s)
                            {{-- الاقتراح: أول خانة يكون فيها المشرف والقاعة وممتحن أحراراً --}}
                            <div class="df-suggest">
                                <span class="df-suggest-when">
                                    <i class="ti ti-sparkles" aria-hidden="true"></i>
                                    <b>{{ $s['starts_at']->translatedFormat('l j F') }}</b>
                                    <bdi dir="ltr">{{ $s['starts_at']->format('H:i') }}</bdi>
                                </span>
                                <span class="df-suggest-who">{{ $s['room'] }} · {{ $s['examiner'] }}@if ($s['same_specialize']) <em>{{ __('من التخصص') }}</em>@endif</span>
                                <button type="submit" form="qf-{{ $p->id }}" class="df-quick" title="{{ __('جدولة بهذا الاقتراح: :details', ['details' => $s['room'] . ' · ' . $s['examiner']]) }}" aria-label="{{ __('جدولة :title بالاقتراح', ['title' => $p->title]) }}">
                                    <i class="ti ti-calendar-check" aria-hidden="true"></i> {{ __('جدولة') }}
                                </button>
                            </div>
                            <footer>
                                <form action="{{ route('admin.defenses.store') }}" method="post" id="qf-{{ $p->id }}">
                                    @csrf
                                    <input type="hidden" name="project_id" value="{{ $p->id }}">
                                    @foreach ($suggestionMeta($s) as $k => $v)
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endforeach
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ti ti-calendar-check me-1" aria-hidden="true"></i>{{ __('جدولة بهذا الاقتراح') }}
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-schedule='@json($meta($p))' data-defense='@json($suggestionMeta($s))'>
                                    {{ __('تعديل') }}
                                </button>
                            </footer>
                        @else
                            <footer>
                                <button type="button" class="btn btn-primary btn-sm" data-schedule='@json($meta($p))'>
                                    <i class="ti ti-calendar-plus me-1" aria-hidden="true"></i>{{ __('جدولة المناقشة') }}
                                </button>
                            </footer>
                        @endif
                    </article>
                @empty
                    <div class="df-queue-empty">
                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                        <b>{{ __('لا مشاريع بانتظار الجدولة') }}</b>
                        <span>{{ __('حين يضغط المشرف «اكتمال المشروع» يظهر هنا لتُشكَّل لجنته ويُحدَّد موعده.') }}</span>
                    </div>
                @endforelse
            </aside>

            {{-- أسبوع الدوام: ما جُدول كتلاً، وما اقتُرح أشباحاً، والخانة الفارغة تُجدول بنقرة أو بإفلات --}}
            <section class="df-week" aria-labelledby="df-week-title">
                <header class="df-week-head">
                    <div class="df-week-nav">
                        <a href="{{ route('admin.defenses.index', ['tab' => 'awaiting', 'week' => $week['prev']]) }}" class="df-nav-btn" aria-label="{{ __('الأسبوع السابق') }}" title="{{ __('الأسبوع السابق') }}"><i class="ti ti-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('admin.defenses.index', ['tab' => 'awaiting', 'week' => $week['next']]) }}" class="df-nav-btn" aria-label="{{ __('الأسبوع التالي') }}" title="{{ __('الأسبوع التالي') }}"><i class="ti ti-chevron-left" aria-hidden="true"></i></a>
                        <h2 id="df-week-title">
                            {{-- «إلى» لا شرطة: المدى بشرطة يُقرأ معكوساً داخل نصّ عربي --}}
                            {{ __(':from إلى :to', ['from' => $week['days']->first()->translatedFormat($week['days']->first()->month === $week['days']->last()->month ? 'j' : 'j F'), 'to' => $week['days']->last()->translatedFormat('j F Y')]) }}
                        </h2>
                        <a href="{{ route('admin.defenses.index', ['tab' => 'awaiting']) }}" class="df-nav-today">{{ __('الأقرب') }}</a>
                    </div>
                    <div class="df-legend">
                        <span><i class="is-in_person"></i>{{ __('حضوري') }}</span>
                        <span><i class="is-online"></i>{{ __('عن بُعد') }}</span>
                        <span><i class="is-hybrid"></i>{{ __('مدمج') }}</span>
                        <span><i class="is-ghost"></i>{{ __('مقترح') }}</span>
                    </div>
                </header>

                @php
                    $counts = $week['days']->mapWithKeys(fn ($d) => [$d->format('Y-m-d') => $dayCount($d)]);
                    // اليوم المفتوح: أول يوم فيه شيء، وإلا اليوم، وإلا أول الأسبوع
                    $activeDay = $counts->filter()->keys()->first()
                        ?? $week['days']->first(fn ($d) => $d->isToday())?->format('Y-m-d')
                        ?? $week['days']->first()->format('Y-m-d');
                @endphp

                {{-- شريط الأيام: يوم واحد يُعرض كاملاً، والبقية بعدّادها — والإفلات على يوم يفتحه --}}
                <div class="df-days" role="tablist" aria-label="{{ __('أيام الأسبوع') }}">
                    @foreach ($week['days'] as $day)
                        @php $key = $day->format('Y-m-d'); $n = $counts[$key]; @endphp
                        <button type="button" role="tab" id="df-tab-{{ $key }}" data-day="{{ $key }}" aria-controls="df-day-{{ $key }}"
                            class="df-daytab {{ $key === $activeDay ? 'is-active' : '' }} {{ $day->isToday() ? 'is-today' : '' }} {{ $n ? '' : 'is-empty' }}"
                            aria-selected="{{ $key === $activeDay ? 'true' : 'false' }}"
                            title="{{ $n ? __(':n في هذا اليوم — مجدولة ومقترحة', ['n' => $n]) : __('لا مناقشات في هذا اليوم') }}">
                            <span class="df-daytab-date">
                                <small>{{ $day->isToday() ? __('اليوم') : $day->translatedFormat('l') }}</small>
                                <b>{{ $day->format('j') }}</b>
                            </span>
                            <span class="df-daytab-n">{{ $n ?: '—' }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="df-cal">
                    @foreach ($week['days'] as $day)
                        @php $key = $day->format('Y-m-d'); @endphp
                        <div class="df-tday" id="df-day-{{ $key }}" role="tabpanel" aria-labelledby="df-tab-{{ $key }}" data-day-panel="{{ $key }}" @if ($key !== $activeDay) hidden @endif>
                            @foreach ($week['times'] as $time)
                                @php
                                    $items = $inSlot($day, $time);
                                    $ghost = $ghosts($day, $time);
                                    $gone = $day->copy()->setTimeFromTimeString($time)->isPast();
                                    $droppable = ! $gone && $awaiting->count();
                                @endphp
                                <div class="df-tslot {{ $gone ? 'is-past' : '' }} {{ $items->count() + $ghost->count() ? 'has-items' : '' }}"
                                    @if ($droppable) data-slot-date="{{ $key }}" data-slot-time="{{ $time }}" @endif>
                                    <span class="df-tslot-time" dir="ltr">{{ $time }}</span>
                                    <div class="df-cell">
                                        @foreach ($items as $d)
                                            @php $editable = $d->status === Defense::SCHEDULED && ! $d->members->whereNotNull('grade')->count(); @endphp
                                            <button type="button" class="df-block is-{{ $d->mode }} {{ $d->status === Defense::DONE ? 'is-done' : '' }}"
                                                @if ($editable) data-schedule='@json($meta($d->project))' data-defense='@json($defenseMeta($d))' @else disabled @endif
                                                title="{{ $d->project->title }} — {{ $d->members->map(fn ($m) => $m->supervisor->name)->implode(__('، ')) }}">
                                                <span class="df-block-body">
                                                    <b>{{ $d->project->title }}</b>
                                                    <small>
                                                        <span><i class="ti ti-clock" aria-hidden="true"></i><bdi dir="ltr">{{ $d->starts_at->format('H:i') }}–{{ $d->endsAt()->format('H:i') }}</bdi></span>
                                                        <span><i class="ti ti-map-pin" aria-hidden="true"></i>{{ $d->place_label }}</span>
                                                        @if ($d->project->presentation)
                                                            <span class="df-slides-mark" title="{{ __('رفع الفريق العرض التقديمي') }}"><i class="ti ti-presentation-analytics" aria-hidden="true"></i>{{ __('العرض جاهز') }}</span>
                                                        @endif
                                                    </small>
                                                </span>
                                                <span class="df-who">@foreach ($initials($d) as $i)<i>{{ $i }}</i>@endforeach</span>
                                            </button>
                                        @endforeach
                                        @foreach ($ghost as $pid => $g)
                                            <button type="button" class="df-block is-ghost" data-project="{{ $pid }}"
                                                data-schedule='@json($projectsMeta[$pid])' data-defense='@json($suggestionMeta($g))'
                                                title="{{ __('اقتراح — انقر للمراجعة والجدولة') }}">
                                                <span class="df-block-body">
                                                    <b>{{ $projectsMeta[$pid]['title'] }}</b>
                                                    <small>
                                                        <span><i class="ti ti-door" aria-hidden="true"></i>{{ $g['room'] }}</span>
                                                        <span><i class="ti ti-user-check" aria-hidden="true"></i>{{ $g['examiner'] }}</span>
                                                    </small>
                                                </span>
                                                <em class="df-tag"><i class="ti ti-sparkles" aria-hidden="true"></i>{{ __('مقترح') }}</em>
                                            </button>
                                        @endforeach
                                        @if ($droppable)
                                            <button type="button" class="df-add" data-add aria-label="{{ __('جدولة في :day :time', ['day' => $day->translatedFormat('l'), 'time' => $time]) }}">
                                                <i class="ti ti-plus" aria-hidden="true"></i><span>{{ __('جدولة') }} <bdi dir="ltr">{{ $time }}</bdi></span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>
        </div>
    @endif

    {{-- ═══ القادمة: أجندة باليوم ═══ --}}
    @if ($tab === 'upcoming')
        @if ($upcoming->count())
            @foreach ($upcoming->groupBy(fn ($d) => $d->starts_at->format('Y-m-d')) as $day => $items)
                <section class="df-day">
                    <h2 class="df-day-title">
                        {{ $dayLabel($items->first()->starts_at) }}
                        <small>{{ $items->first()->starts_at->format('Y-m-d') }} · {{ $items->count() }} {{ $items->count() === 1 ? __('مناقشة') : __('مناقشات') }}</small>
                    </h2>
                    <ol class="df-agenda">
                        @foreach ($items as $d)
                            <li class="df-slot {{ $d->isJoinable() ? 'is-live' : '' }}">
                                <div class="df-time">
                                    <b dir="ltr">{{ $d->starts_at->format('H:i') }}</b>
                                    <small dir="ltr">{{ $d->endsAt()->format('H:i') }}</small>
                                    <em>{{ __(':n د', ['n' => $d->duration_minutes]) }}</em>
                                </div>
                                <div class="df-slot-body">
                                    <a href="{{ route('admin.groups.show', $d->project_id) }}" class="df-title">{{ $d->project->title }}</a>
                                    <span class="df-sub">{{ $d->project->group->map(fn ($g) => $g->student?->name)->filter()->implode(__('، ')) }}</span>
                                    <div class="df-chips">
                                        <span class="df-mode is-{{ $d->mode }}"><i class="ti {{ $d->mode_icon }}" aria-hidden="true"></i>{{ $d->place_label }}</span>
                                        @foreach ($d->members as $m)
                                            <span class="df-member"><x-avatar :user="$m->supervisor" class="cell-avatar df-av" />{{ $m->supervisor->name }} <em>{{ $d->roleOf($m) }}</em></span>
                                        @endforeach
                                        @if ($d->project->presentation)
                                            <a href="{{ route('files.download', $d->project->presentation->id) }}" class="df-member df-slides-mark" title="{{ __('تنزيل العرض التقديمي') }}">
                                                <i class="ti ti-presentation-analytics" aria-hidden="true"></i>{{ __('العرض جاهز') }}
                                            </a>
                                        @else
                                            <span class="df-member" title="{{ __('لم يرفع الفريق العرض التقديمي بعد') }}"><i class="ti ti-presentation-off" aria-hidden="true"></i>{{ __('لا عرض بعد') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="df-actions">
                                    @if ($d->needsLink() && $d->meeting_url)
                                        <a href="{{ $d->meeting_url }}" target="_blank" rel="noopener" class="btn btn-sm {{ $d->isJoinable() ? 'btn-primary' : 'btn-outline-primary' }}">
                                            <i class="ti ti-video me-1" aria-hidden="true"></i>{{ $d->isJoinable() ? __('انضم الآن') : __('رابط الاجتماع') }}
                                        </a>
                                    @endif
                                    <a href="{{ $d->googleCalendarUrl() }}" target="_blank" rel="noopener" class="btn-action" title="{{ __('أضف إلى تقويم Google') }}" aria-label="{{ __('أضف إلى تقويم Google') }}">
                                        <i class="ti ti-brand-google" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('defenses.minutes', $d->id) }}" class="btn-action" title="{{ __('مسودة المحضر — تُطبع للتوقيع يوم المناقشة') }}" aria-label="{{ __('محضر مناقشة :title', ['title' => $d->project->title]) }}">
                                        <i class="ti ti-file-certificate" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('defenses.ics', $d->id) }}" class="btn-action" title="{{ __('ملف التقويم (.ics) — Outlook وتقويم الجوال') }}" aria-label="{{ __('تنزيل ملف التقويم') }}">
                                        <i class="ti ti-calendar-down" aria-hidden="true"></i>
                                    </a>
                                    <button type="button" class="btn-action" title="{{ __('تعديل الموعد') }}" aria-label="{{ __('تعديل موعد :title', ['title' => $d->project->title]) }}"
                                        data-schedule='@json($meta($d->project))' data-defense='@json($defenseMeta($d))'>
                                        <i class="ti ti-pencil" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn-action btn-action--danger" title="{{ __('إلغاء المناقشة') }}" aria-label="{{ __('إلغاء مناقشة :title', ['title' => $d->project->title]) }}"
                                        data-cancel="{{ route('admin.defenses.cancel', $d->id) }}" data-title="{{ $d->project->title }}">
                                        <i class="ti ti-calendar-x" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
        @else
            <div class="dist-panel">
                <x-empty-state icon="ti-calendar" title="{{ __('لا مناقشات قادمة') }}"
                    text="{{ __('جدول مناقشات المشاريع المكتملة من تبويب «بانتظار الجدولة».') }}" class="py-6" />
            </div>
        @endif
    @endif

    {{-- ═══ السابقة ═══ --}}
    @if ($tab === 'past')
        @if ($past->count())
            <div class="df-past">
                @foreach ($past as $d)
                    @php
                        [$label, $tone] = match (true) {
                            $d->status === Defense::CANCELLED => [__('ملغاة'), 'is-muted'],
                            $d->status === Defense::DONE => [__('منتهية · :grade', ['grade' => rtrim(rtrim(number_format((float) $d->project->grade, 2, '.', ''), '0'), '.')]), 'is-done'],
                            default => [__('بانتظار الدرجة · :done من :total', ['done' => $d->members->whereNotNull('grade')->count(), 'total' => $d->members->count()]), 'is-wait'],
                        };
                    @endphp
                    {{-- الصفّ كلّه رابط إلى المشروع، وزر المحضر فوقه --}}
                    <div class="df-past-row">
                        <span class="df-past-date" dir="ltr">{{ $d->starts_at->format('Y-m-d H:i') }}</span>
                        <a href="{{ route('admin.groups.show', $d->project_id) }}" class="df-past-link"><b>{{ $d->project->title }}</b></a>
                        <span class="df-sub">{{ $d->place_label }} · {{ $d->members->map(fn ($m) => $m->supervisor->name)->implode(__('، ')) }}</span>
                        <span class="df-status {{ $tone }}">{{ $label }}</span>
                        @if ($d->status !== Defense::CANCELLED)
                            <a href="{{ route('defenses.minutes', $d->id) }}" class="btn-action df-past-minutes" title="{{ __('محضر المناقشة — طباعة أو PDF') }}" aria-label="{{ __('محضر مناقشة :title', ['title' => $d->project->title]) }}">
                                <i class="ti ti-file-certificate" aria-hidden="true"></i>
                            </a>
                        @else
                            <span></span>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="dist-panel">
                <x-empty-state icon="ti-history" title="{{ __('لا مناقشات سابقة') }}" text="{{ __('ستظهر هنا المناقشات بعد موعدها والملغاة.') }}" class="py-6" />
            </div>
        @endif
    @endif

    {{-- ═══════════ نافذة الجدولة ═══════════ --}}
    <div class="modal fade" id="defenseModal" tabindex="-1" aria-labelledby="defenseModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <form class="modal-content df-form" method="post" action="{{ route('admin.defenses.store') }}" data-store="{{ route('admin.defenses.store') }}"
                data-update="{{ route('admin.defenses.update', '__ID__') }}" novalidate>
                @csrf
                <input type="hidden" name="_method" value="POST">
                <input type="hidden" name="project_id" value="{{ old('project_id') }}">
                <input type="hidden" name="defense_id" value="{{ old('defense_id') }}">

                <div class="modal-header dm-head">
                    <span class="dm-head-icon" aria-hidden="true"><i class="ti ti-presentation"></i></span>
                    <div class="dm-head-body">
                        <h2 class="modal-title" id="defenseModalTitle">{{ __('جدولة المناقشة') }}</h2>
                        <div class="df-form-project" data-f="project"></div>
                        <select class="form-select form-select-sm df-pick" data-f="pick" aria-label="{{ __('المشروع') }}" hidden>
                            @foreach ($awaiting as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
                </div>

                <div class="modal-body dm-body">
                    @error('date')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    {{-- ① الموعد --}}
                    <section class="dm-sec">
                        <h3 class="dm-sec-title"><i class="ti ti-calendar-event" aria-hidden="true"></i> {{ __('الموعد') }}</h3>
                        <div class="dm-when">
                            <div>
                                <label class="form-label required" for="df-date">{{ __('التاريخ') }}</label>
                                <input type="date" id="df-date" name="date" class="form-control @error('date') is-invalid @enderror"
                                    min="{{ today()->format('Y-m-d') }}" value="{{ old('date') }}" required>
                            </div>
                            <div>
                                <label class="form-label required" for="df-time">{{ __('الوقت') }}</label>
                                <input type="time" id="df-time" name="time" step="900" class="form-control @error('time') is-invalid @enderror"
                                    value="{{ old('time', '10:00') }}" required>
                            </div>
                            <div>
                                <span class="form-label" id="df-duration-label">{{ __('المدة') }}</span>
                                <div class="dm-chips" role="radiogroup" aria-labelledby="df-duration-label">
                                    @foreach (Defense::DURATIONS as $m)
                                        <label class="dm-chip">
                                            <input type="radio" name="duration_minutes" value="{{ $m }}" @checked((int) old('duration_minutes', 45) === $m)>
                                            <span>{{ $m }} <small>{{ __('د') }}</small></span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @error('time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </section>

                    {{-- ② المكان --}}
                    <section class="dm-sec">
                        <h3 class="dm-sec-title"><i class="ti ti-map-pin" aria-hidden="true"></i> {{ __('المكان') }}</h3>
                        <div class="dm-modes" role="radiogroup" aria-label="{{ __('نوع المناقشة') }}">
                            @foreach (['in_person' => __('في قاعة بالجامعة'), 'online' => __('برابط اجتماع'), 'hybrid' => __('قاعة ورابط معاً')] as $key => $hint)
                                <label class="dm-mode">
                                    <input type="radio" name="mode" value="{{ $key }}" @checked(old('mode', $activeRooms->count() ? 'in_person' : 'online') === $key)>
                                    <span>
                                        <i class="ti {{ Defense::MODES[$key]['icon'] }}" aria-hidden="true"></i>
                                        <b>{{ __(Defense::MODES[$key]['label']) }}</b>
                                        <small>{{ $hint }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="dm-field" data-when="room">
                            <label class="form-label required" for="df-room">{{ __('القاعة') }}</label>
                            <select id="df-room" name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                                <option value="">{{ __('اختر قاعة…') }}</option>
                                @foreach ($activeRooms as $r)
                                    <option value="{{ $r->id }}" @selected((int) old('room_id') === $r->id)>
                                        {{ $r->name }}{{ $r->location ? ' — ' . $r->location : '' }}{{ $r->capacity ? ' · ' . __(':n مقعداً', ['n' => $r->capacity]) : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if (! $activeRooms->count())
                                <div class="form-hint">{{ __('لا قاعات بعد — أضفها من زر «القاعات» أعلى الصفحة.') }}</div>
                            @endif
                        </div>

                        {{-- Google Meet بلا ربط حساب: يُنشأ الاجتماع في تبويب جديد ويُلصق رابطه --}}
                        <div class="dm-field" data-when="link">
                            <label class="form-label required" for="df-link">{{ __('رابط الاجتماع') }}</label>
                            <div class="dm-link @error('meeting_url') is-invalid @enderror">
                                <span class="dm-link-icon" aria-hidden="true"><i class="ti ti-link"></i></span>
                                <input type="url" id="df-link" name="meeting_url" dir="ltr" inputmode="url" autocomplete="off"
                                    placeholder="https://meet.google.com/abc-defg-hij" value="{{ old('meeting_url') }}">
                                <button type="button" class="dm-paste" data-f="paste"><i class="ti ti-clipboard" aria-hidden="true"></i> {{ __('لصق') }}</button>
                            </div>
                            @error('meeting_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="dm-link-state" data-f="link-state" aria-live="polite"></div>

                            <a href="https://meet.google.com/new" target="_blank" rel="noopener" class="dm-meet">
                                <svg class="dm-meet-logo" viewBox="0 0 87.5 72" aria-hidden="true"><path fill="#00832d" d="M49.5 36l8.53 9.75 11.47 7.33 2-17.02-2-16.64-11.69 6.44z"/><path fill="#0066da" d="M0 51.5V66c0 3.315 2.685 6 6 6h14.5l3-10.96-3-9.54-9.95-3z"/><path fill="#e94235" d="M20.5 0L0 20.5l10.55 3 9.95-3 2.95-9.41z"/><path fill="#2684fc" d="M20.5 20.5H0v31h20.5z"/><path fill="#00ac47" d="M82.6 8.68L69.5 19.42v33.66l13.16 10.79c1.97 1.54 4.85.135 4.85-2.37V11c0-2.535-2.945-3.925-4.91-2.32zM49.5 36v15.5h-29V72h43c3.315 0 6-2.685 6-6V53.08z"/><path fill="#ffba00" d="M63.5 0h-43v20.5h29V36l20-16.57V6c0-3.315-2.685-6-6-6z"/></svg>
                                <span>
                                    <b>{{ __('إنشاء Google Meet') }}</b>
                                    <small>{{ __('يُفتح في تبويب جديد — انسخ رابط الاجتماع والصقه هنا. روابط Zoom وTeams مقبولة أيضاً.') }}</small>
                                </span>
                                <i class="ti ti-external-link" aria-hidden="true"></i>
                            </a>
                        </div>
                    </section>

                    {{-- ③ اللجنة --}}
                    <section class="dm-sec">
                        <h3 class="dm-sec-title"><i class="ti ti-users-group" aria-hidden="true"></i> {{ __('اللجنة') }}</h3>
                        <div class="dm-committee">
                            <div class="dm-member">
                                <span class="dm-member-av" data-f="sup-av" aria-hidden="true"></span>
                                <span><small>{{ __('المشرف') }}</small><b data-f="supervisor">—</b></span>
                                @if ($chairs)
                                    <label class="dm-chair" title="{{ __('رئيس اللجنة: يدير الجلسة ويوقّع المحضر أولاً') }}">
                                        <input type="radio" name="chair_id" value="" data-f="chair-sup" checked><span><i class="ti ti-crown" aria-hidden="true"></i>{{ __('رئيس') }}</span>
                                    </label>
                                @else
                                    <i class="ti ti-lock" aria-hidden="true" title="{{ __('مشرف المشروع عضو ثابت في اللجنة') }}"></i>
                                @endif
                            </div>
                            {{-- صفوف الممتحنين يبنيها السكربت: واحد على الأقل، وحتى الحدّ الأقصى --}}
                            <button type="button" class="dm-member-add" data-f="add-examiner">
                                <i class="ti ti-user-plus" aria-hidden="true"></i> {{ __('إضافة ممتحن') }}
                            </button>
                        </div>
                        @foreach (['examiner_id', 'examiner_ids', 'examiner_ids.*', 'chair_id'] as $key)
                            @error($key)<div class="invalid-feedback d-block">{{ $message }}</div>@break @enderror
                        @endforeach
                        <div class="form-hint" data-f="examiner-hint">{{ __('مرتّبون: من تخصص المشروع أولاً، ثم الأقلّ مناقشاتٍ قادمة.') }}</div>
                    </section>

                    {{-- ④ ملاحظات --}}
                    <section class="dm-sec">
                        <h3 class="dm-sec-title"><i class="ti ti-notes" aria-hidden="true"></i> <label for="df-notes">{{ __('ملاحظات للفريق واللجنة') }}</label> <small>{{ __('اختيارية') }}</small></h3>
                        <textarea id="df-notes" name="notes" rows="2" class="form-control" maxlength="1000"
                            placeholder="{{ __('مثال: عرض تقديمي 15 دقيقة ثم أسئلة اللجنة. أحضروا نسخة مطبوعة من التقرير.') }}">{{ old('notes') }}</textarea>
                    </section>
                </div>

                <div class="modal-footer dm-foot">
                    {{-- الملخّص الحيّ: ما سيُرسل للفريق واللجنة بعد الضغط --}}
                    <div class="dm-summary" aria-live="polite">
                        <i class="ti ti-bell" aria-hidden="true"></i>
                        <span><small>{{ __('سيُشعَر الفريق واللجنة بـ') }}</small><b data-f="summary">—</b></span>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                    <button type="submit" class="btn btn-primary" data-f="submit">
                        <i class="ti ti-calendar-check me-1" aria-hidden="true"></i> {{ __('جدولة') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ جدولة الكل: معاينة ثم اعتماد ═══════════ --}}
    @if ($planned->count() >= 2)
        <div class="modal fade" id="planModal" tabindex="-1" aria-labelledby="planModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <form class="modal-content" method="post" action="{{ route('admin.defenses.plan.store') }}">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title" id="planModalTitle">{{ __('جدول مقترح لـ:n مناقشات', ['n' => $planned->count()]) }}</h2>
                            <div class="df-form-project">{{ __('لا شيء يُحفظ قبل الاعتماد. كل مناقشة حضورية :n دقيقة، بلا تعارض في القاعات ولا اللجان.', ['n' => $slotMinutes]) }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
                    </div>
                    <div class="modal-body p-0">
                        <table class="table df-plan-table mb-0">
                            <thead><tr><th>{{ __('المشروع') }}</th><th>{{ __('الموعد') }}</th><th>{{ __('القاعة') }}</th><th>{{ __('الممتحن') }}</th></tr></thead>
                            <tbody>
                                @foreach ($planned->sortBy(fn ($x) => $x['starts_at']) as $pid => $x)
                                    <tr>
                                        <td>
                                            <b>{{ $projectsMeta[$pid]['title'] }}</b>
                                            <small>{{ __('المشرف :name', ['name' => $projectsMeta[$pid]['supervisor']]) }}</small>
                                            @foreach (['date' => $x['starts_at']->format('Y-m-d'), 'time' => $x['starts_at']->format('H:i'), 'room_id' => $x['room_id'], 'examiner_id' => $x['examiner_id']] as $k => $v)
                                                <input type="hidden" name="items[{{ $pid }}][{{ $k }}]" value="{{ $v }}">
                                            @endforeach
                                        </td>
                                        <td>{{ $x['starts_at']->translatedFormat('l j F') }}<small dir="ltr">{{ $x['starts_at']->format('H:i') }}–{{ $x['starts_at']->copy()->addMinutes($x['duration_minutes'])->format('H:i') }}</small></td>
                                        <td>{{ $x['room'] }}</td>
                                        <td>{{ $x['examiner'] }}@if ($x['same_specialize'])<small>{{ __('من تخصص المشروع') }}</small>@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($planned->count() < $awaiting->count())
                            <p class="df-plan-left"><i class="ti ti-info-circle" aria-hidden="true"></i> {{ __(':n مشاريع بلا خانة متاحة ضمن أوقات الدوام — جدولها يدوياً.', ['n' => $awaiting->count() - $planned->count()]) }}</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <span class="df-foot-note"><i class="ti ti-bell" aria-hidden="true"></i> {{ __('يُشعَر كل فريق ولجنته عند الاعتماد') }}</span>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('إلغاء') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-checks me-1" aria-hidden="true"></i>{{ __('اعتماد الجدول') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ═══════════ نافذة الإلغاء ═══════════ --}}
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" method="post" action="">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title" id="cancelModalTitle">{{ __('إلغاء المناقشة') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">{!! __('تُلغى مناقشة «:title» ويعود المشروع إلى «بانتظار الجدولة». يُشعَر الفريق واللجنة.', ['title' => '<b data-f="cancel-title"></b>']) !!}</p>
                    <label class="form-label" for="cancel-reason">{{ __('السبب') }} <span class="text-secondary">{{ __('(يصل إليهم)') }}</span></label>
                    <textarea id="cancel-reason" name="reason" rows="2" class="form-control" maxlength="300" placeholder="{{ __('مثال: تعارض مع امتحانات القسم') }}"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('إبقاء المناقشة') }}</button>
                    <button type="submit" class="btn btn-danger">{{ __('إلغاء المناقشة') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ نافذة القاعات ═══════════ --}}
    <div class="modal fade" id="roomsModal" tabindex="-1" aria-labelledby="roomsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content df-form">
                <div class="modal-header dm-head">
                    <span class="dm-head-icon is-teal" aria-hidden="true"><i class="ti ti-door"></i></span>
                    <div class="dm-head-body">
                        <h2 class="modal-title" id="roomsModalTitle">{{ __('قاعات المناقشة') }}</h2>
                        <div class="df-form-project">{{ __(':active متاحة من :total — منها يختار الاقتراح التلقائي', ['active' => $activeRooms->count(), 'total' => $rooms->count()]) }}</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('إغلاق') }}"></button>
                </div>

                <div class="modal-body dm-body">
                    {{-- قاعة جديدة: الاسم وحده مطلوب --}}
                    <form method="post" action="{{ route('admin.defenses.rooms.store', ['tab' => $tab]) }}" class="dm-sec dr-add">
                        @csrf
                        <h3 class="dm-sec-title"><i class="ti ti-plus" aria-hidden="true"></i> {{ __('قاعة جديدة') }}</h3>
                        <div class="dr-add-grid">
                            <div class="dr-add-name">
                                <label class="form-label required" for="dr-name">{{ __('اسم القاعة') }}</label>
                                <input type="text" id="dr-name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="{{ __('قاعة 204') }}" value="{{ old('name') }}" required maxlength="60">
                                @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label" for="dr-location">{{ __('المبنى والطابق') }}</label>
                                <input type="text" id="dr-location" name="location" class="form-control" placeholder="{{ __('مبنى الهندسة — الطابق الثاني') }}" value="{{ old('location') }}" maxlength="120">
                            </div>
                            <div>
                                <label class="form-label" for="dr-capacity">{{ __('السعة') }}</label>
                                <input type="number" id="dr-capacity" name="capacity" class="form-control" placeholder="40" min="1" max="1000" value="{{ old('capacity') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary dr-add-btn">
                            <i class="ti ti-plus me-1" aria-hidden="true"></i>{{ __('إضافة القاعة') }}
                        </button>
                    </form>

                    <div class="dr-list">
                        @forelse ($rooms as $r)
                            <div class="dr-room {{ $r->is_active ? '' : 'is-off' }}">
                                <span class="dr-room-icon"><i class="ti ti-door" aria-hidden="true"></i></span>
                                <span class="dr-room-body">
                                    <b>{{ $r->name }}</b>
                                    <span class="dr-room-meta">
                                        @if ($r->location)<span><i class="ti ti-building" aria-hidden="true"></i>{{ $r->location }}</span>@endif
                                        @if ($r->capacity)<span><i class="ti ti-armchair" aria-hidden="true"></i>{{ __(':n مقعداً', ['n' => $r->capacity]) }}</span>@endif
                                        @if ($r->upcoming_count)<span class="is-busy"><i class="ti ti-calendar-event" aria-hidden="true"></i>{{ match (true) { $r->upcoming_count == 1 => __('مناقشة قادمة'), $r->upcoming_count == 2 => __('مناقشتان قادمتان'), $r->upcoming_count <= 10 => __(':n مناقشات قادمة', ['n' => $r->upcoming_count]), default => __(':n مناقشة قادمة', ['n' => $r->upcoming_count]) } }}</span>@endif
                                    </span>
                                </span>

                                {{-- مفتاح الإتاحة: القاعة المعطّلة تبقى لسجلّها ولا تُعرض للجدولة --}}
                                <form method="post" action="{{ route('admin.defenses.rooms.toggle', $r->id) }}" class="dr-switch-form">
                                    @csrf
                                    <button type="submit" class="dr-switch {{ $r->is_active ? 'is-on' : '' }}" role="switch" aria-checked="{{ $r->is_active ? 'true' : 'false' }}"
                                        aria-label="{{ $r->is_active ? __('تعطيل :name', ['name' => $r->name]) : __('تفعيل :name', ['name' => $r->name]) }}" title="{{ $r->is_active ? __('متاحة — انقر للتعطيل') : __('معطّلة — انقر للتفعيل') }}">
                                        <span></span>
                                    </button>
                                    <small>{{ $r->is_active ? __('متاحة') : __('معطّلة') }}</small>
                                </form>

                                <form method="post" action="{{ route('admin.defenses.rooms.destroy', $r->id) }}"
                                    data-confirm="{{ __('حذف «:name»؟ القاعة التي لها مناقشات مسجّلة تُعطَّل ولا تُحذف.', ['name' => $r->name]) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dr-delete" title="{{ __('حذف القاعة') }}" aria-label="{{ __('حذف :name', ['name' => $r->name]) }}"><i class="ti ti-trash" aria-hidden="true"></i></button>
                                </form>
                            </div>
                        @empty
                            <div class="df-queue-empty">
                                <i class="ti ti-door-off" aria-hidden="true" style="color: var(--ds-ink-mute)"></i>
                                <b>{{ __('لا قاعات بعد') }}</b>
                                <span>{{ __('أضف أول قاعة لتعمل المناقشات الحضورية والاقتراح التلقائي.') }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    @php
        $t = [
            'locale' => app()->getLocale() === 'ar' ? 'ar-u-nu-latn' : 'en',
            'sep' => __('، '),
            'chairTitle' => __('رئيس اللجنة: يدير الجلسة ويوقّع المحضر أولاً'),
            'chair' => __('رئيس'),
            'removeFromCommittee' => __('إزالة من اللجنة'),
            'examinerN' => __('الممتحن :n'),
            'examiner' => __('الممتحن'),
            'removeExaminerN' => __('إزالة الممتحن :n'),
            'sameSpec' => __('من تخصص المشروع'),
            'otherSpecs' => __('تخصصات أخرى'),
            'load' => __(':n مناقشات قادمة'),
            'loadOne' => __('مناقشة قادمة'),
            'noLoad' => __('لا مناقشات قادمة'),
            'editTitle' => __('تعديل موعد المناقشة'),
            'scheduleTitle' => __('جدولة المناقشة'),
            'saveEdit' => __('حفظ التعديل'),
            'schedule' => __('جدولة'),
            'linkIncomplete' => __('الرابط غير مكتمل — الصقه كاملاً بدايةً من https://'),
            'providerLink' => __('رابط :provider — سيظهر للفريق واللجنة زر «انضم» قبل الموعد بربع ساعة'),
            'meetingLinkHint' => __('رابط اجتماع — سيظهر للفريق واللجنة زر «انضم» قبل الموعد'),
            'meetingLink' => __('رابط اجتماع'),
            'online' => __('عن بُعد'),
            'examinersList' => __('الممتحنون :names'),
            'examinerOne' => __('الممتحن :name'),
            'chairName' => __('الرئيس :name'),
            'otherSpec' => __('من تخصص آخر'),
            'committeeOf' => __('لجنة من :n أعضاء'),
            'nSame' => __(':n من الممتحنين من تخصص المشروع'),
            'noneSame' => __('لا ممتحن من تخصص المشروع'),
            'weighted' => __('الدرجة بأوزان الأعضاء'),
            'average' => __('الدرجة متوسط درجاتهم'),
            'shownOf' => __(':shown من :total'),
        ];
    @endphp
    <script>
        (function () {
            var T = @json($t);
            var L = function (key, vars) {
                var s = T[key];
                Object.keys(vars || {}).forEach(function (k) { s = s.split(':' + k).join(vars[k]); });
                return s;
            };
            var EXAMINERS = @json($examiners);
            var PROJECTS = @json($projectsMeta);
            var modalEl = document.getElementById('defenseModal');
            if (!modalEl) return;
            var form = modalEl.querySelector('form');
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            var f = function (name) { return form.querySelector('[data-f="' + name + '"]'); };
            var field = function (name) { return form.elements[name]; };

            // النوع يحدّد ما يُطلب: القاعة للحضوري والمدمج، والرابط لعن بُعد والمدمج
            function syncMode() {
                var mode = (form.querySelector('input[name="mode"]:checked') || {}).value;
                form.querySelector('[data-when="room"]').hidden = mode === 'online';
                form.querySelector('[data-when="link"]').hidden = mode === 'in_person';
            }
            form.querySelectorAll('input[name="mode"]').forEach(function (r) { r.addEventListener('change', syncMode); });

            // الممتحنون: بلا مشرف المشروع، التخصص نفسه أولاً ثم الأقلّ حملاً
            var MAX_EXAMINERS = {{ (int) config('defenses.max_examiners', 3) }};
            var CHAIRS = @json($chairs);

            // الرئيس: المشرف افتراضياً، أو الممتحن الذي تحمله المناقشة
            function setChair(p, chairId) {
                if (!CHAIRS) return;
                var sup = f('chair-sup');
                sup.value = p.supervisor_id;
                sup.checked = true;
                if (chairId && String(chairId) !== String(p.supervisor_id)) {
                    examinerSelects().forEach(function (s) {
                        if (s.value === String(chairId)) s.closest('.dm-member').querySelector('input[name="chair_id"]').checked = true;
                    });
                }
            }
            var addBtn = f('add-examiner');
            var examinerSelects = function () { return Array.prototype.slice.call(form.querySelectorAll('select[name="examiner_ids[]"]')); };

            function examinerRow(p, selected) {
                var row = document.createElement('div');
                row.className = 'dm-member is-pick';
                row.innerHTML = '<span class="dm-member-av is-examiner" aria-hidden="true"><i class="ti ti-user-search"></i></span>'
                    + '<span class="dm-member-field"><label><small></small></label><select name="examiner_ids[]" required></select></span>'
                    + (CHAIRS ? '<label class="dm-chair"><input type="radio" name="chair_id"><span><i class="ti ti-crown" aria-hidden="true"></i></span></label>' : '')
                    + '<button type="button" class="dm-member-remove"><i class="ti ti-x" aria-hidden="true"></i></button>';
                row.querySelector('.dm-member-remove').title = T.removeFromCommittee;
                if (CHAIRS) {
                    row.querySelector('.dm-chair').title = T.chairTitle;
                    row.querySelector('.dm-chair span').appendChild(document.createTextNode(T.chair));
                }
                var sel = row.querySelector('select');
                fillOptions(sel, p);
                var taken = examinerSelects().map(function (s) { return s.value; });
                if (selected != null && sel.querySelector('option[value="' + selected + '"]')) {
                    sel.value = String(selected);
                } else {
                    // الافتراضي: أول مرشّح لم يُختر في صفّ آخر
                    var free = Array.prototype.filter.call(sel.options, function (o) { return taken.indexOf(o.value) === -1; })[0];
                    if (free) sel.value = free.value;
                }
                row.querySelector('.dm-member-remove').addEventListener('click', function () {
                    // إزالة الرئيس تعيد الرئاسة إلى المشرف
                    var radio = row.querySelector('input[name="chair_id"]');
                    if (radio && radio.checked) f('chair-sup').checked = true;
                    row.remove(); syncExaminers(); refresh();
                });
                addBtn.parentNode.insertBefore(row, addBtn);
                return row;
            }

            // الترقيم، ومنع اختيار الممتحن نفسه مرتين، وإظهار «إضافة» حتى الحدّ الأقصى
            function syncExaminers() {
                var sels = examinerSelects();
                sels.forEach(function (sel, i) {
                    var row = sel.closest('.dm-member');
                    var radio = row.querySelector('input[name="chair_id"]');
                    if (radio) radio.value = sel.value;
                    sel.id = 'df-examiner-' + i;
                    row.querySelector('label').htmlFor = sel.id;
                    row.querySelector('small').textContent = (sels.length > 1 ? L('examinerN', { n: i + 1 }) : T.examiner) + ' ';
                    row.querySelector('small').appendChild(document.createElement('em')).textContent = '*';
                    row.querySelector('.dm-member-remove').hidden = sels.length < 2;
                    row.querySelector('.dm-member-remove').setAttribute('aria-label', L('removeExaminerN', { n: i + 1 }));
                    Array.prototype.forEach.call(sel.options, function (o) {
                        o.disabled = sels.some(function (other) { return other !== sel && other.value === o.value; });
                    });
                });
                var candidates = sels.length ? sels[0].options.length : 0;
                addBtn.hidden = sels.length >= MAX_EXAMINERS || sels.length >= candidates;
            }

            function fillExaminers(p, selected) {
                examinerSelects().forEach(function (s) { s.closest('.dm-member').remove(); });
                var ids = (selected || []).slice(0, MAX_EXAMINERS);
                if (!ids.length) ids = [null];
                ids.forEach(function (id) { examinerRow(p, id); });
                addBtn.onclick = function () {
                    var row = examinerRow(p, null);
                    syncExaminers();
                    refresh();
                    row.querySelector('select').focus();
                };
                syncExaminers();
            }

            function fillOptions(sel, p) {
                var list = EXAMINERS.filter(function (e) { return e.id !== p.supervisor_id; })
                    .sort(function (a, b) {
                        var sa = a.specialize_id === p.specialize_id ? 0 : 1, sb = b.specialize_id === p.specialize_id ? 0 : 1;
                        return sa - sb || a.load - b.load || a.name.localeCompare(b.name, T.locale);
                    });
                var groups = [[T.sameSpec, list.filter(function (e) { return e.specialize_id === p.specialize_id; })],
                              [T.otherSpecs, list.filter(function (e) { return e.specialize_id !== p.specialize_id; })]];
                groups.forEach(function (g) {
                    if (!g[1].length) return;
                    var og = document.createElement('optgroup');
                    og.label = g[0];
                    g[1].forEach(function (e) {
                        var o = document.createElement('option');
                        o.value = e.id;
                        o.textContent = e.name + ' — ' + (!e.load ? T.noLoad
                            : (e.load === 1 && document.documentElement.lang === 'en' ? T.loadOne : L('load', { n: e.load })));
                        og.appendChild(o);
                    });
                    sel.appendChild(og);
                });
            }

            function open(p, d, keepOld) {
                var edit = !!(d && d.id);
                form.action = edit ? form.dataset.update.replace('__ID__', d.id) : form.dataset.store;
                field('_method').value = edit ? 'PUT' : 'POST';
                field('project_id').value = p.id;
                field('defense_id').value = edit ? d.id : '';
                modalEl.querySelector('.modal-title').textContent = edit ? T.editTitle : T.scheduleTitle;
                f('submit').lastChild.textContent = ' ' + (edit ? T.saveEdit : T.schedule);
                f('project').textContent = p.title + (p.team ? ' · ' + p.team : '');
                f('supervisor').textContent = p.supervisor || '—';
                f('sup-av').textContent = (p.supervisor || '').replace(/^(أ\.د\.|د\.|أ\.)\s*/, '').slice(0, 2);

                if (!keepOld) {
                    d = d || {};
                    field('date').value = d.date || '';
                    field('time').value = d.time || '10:00';
                    field('duration_minutes').value = d.duration_minutes || 45;
                    var mode = d.mode || (field('room_id').options.length > 1 ? 'in_person' : 'online');
                    form.querySelector('input[name="mode"][value="' + mode + '"]').checked = true;
                    field('room_id').value = d.room_id || '';
                    field('meeting_url').value = d.meeting_url || '';
                    field('notes').value = d.notes || '';
                    form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
                    form.querySelectorAll('.invalid-feedback, .alert-danger').forEach(function (el) { el.remove(); });
                }
                // المجدولة تحمل لجنتها كلها، والاقتراح ممتحناً واحداً
                fillExaminers(p, keepOld
                    ? @json(old('examiner_ids', old('examiner_id') ? [old('examiner_id')] : []))
                    : (d && (d.examiner_ids || (d.examiner_id ? [d.examiner_id] : []))));
                syncExaminers();
                setChair(p, keepOld ? @json(old('chair_id')) : (d && d.chair_id));
                syncMode();
                refresh();
                modal.show();
            }

            // ---- رابط الاجتماع: يُعرَّف مزوّده ويُتحقّق من شكله أثناء الكتابة ----
            var PROVIDERS = [
                [/(^|\.)meet\.google\.com$/, 'Google Meet', 'is-meet'],
                [/(^|\.)zoom\.us$/, 'Zoom', 'is-zoom'],
                [/(^|\.)teams\.(microsoft|live)\.com$/, 'Microsoft Teams', 'is-teams'],
            ];
            function linkState() {
                var box = f('link-state'), wrap = form.querySelector('.dm-link'), v = field('meeting_url').value.trim();
                wrap.classList.remove('is-ok', 'is-meet', 'is-zoom', 'is-teams');
                if (!v) { box.textContent = ''; box.className = 'dm-link-state'; return null; }
                var host = null;
                try { var u = new URL(v); if (/^https?:$/.test(u.protocol)) host = u.hostname; } catch (e) {}
                if (!host) { box.textContent = T.linkIncomplete; box.className = 'dm-link-state is-bad'; return null; }
                var found = PROVIDERS.filter(function (p) { return p[0].test(host); })[0];
                wrap.classList.add('is-ok');
                if (found) wrap.classList.add(found[2]);
                box.textContent = found ? L('providerLink', { provider: found[1] }) : T.meetingLinkHint;
                box.className = 'dm-link-state is-ok';
                return found ? found[1] : T.meetingLink;
            }
            var paste = f('paste');
            if (paste) {
                if (!(navigator.clipboard && navigator.clipboard.readText)) paste.hidden = true;
                paste.addEventListener('click', function () {
                    navigator.clipboard.readText().then(function (t) {
                        field('meeting_url').value = (t || '').trim();
                        refresh();
                        field('meeting_url').focus();
                    }, function () { field('meeting_url').focus(); });
                });
            }

            // ---- الملخّص الحيّ: «الأحد 4 أكتوبر · 09:45–10:30 · قاعة 204 · الممتحن …» ----
            var dayFmt = new Intl.DateTimeFormat(T.locale, { weekday: 'long', day: 'numeric', month: 'long' });
            function refresh() {
                var provider = linkState();
                var mode = (form.querySelector('input[name="mode"]:checked') || {}).value;
                var date = field('date').value, time = field('time').value, dur = parseInt(field('duration_minutes').value || 45, 10);
                var parts = [];
                if (date) {
                    var day = new Date(date + 'T00:00:00');
                    if (!isNaN(day)) parts.push(dayFmt.format(day));
                }
                if (time) {
                    var t = time.split(':'), end = new Date(2000, 0, 1, +t[0], +t[1] + dur);
                    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
                    // LRI…PDI: المدى لا ينقلب داخل النص العربي
                    parts.push('\u2066' + time + '–' + pad(end.getHours()) + ':' + pad(end.getMinutes()) + '\u2069');
                }
                var room = field('room_id'), place = [];
                if (mode !== 'online' && room.value) place.push(room.options[room.selectedIndex].text.split(' — ')[0].trim());
                if (mode !== 'in_person') place.push(provider || T.online);
                if (place.length) parts.push(place.join(' + '));
                syncExaminers();
                var opts = examinerSelects().map(function (s) { return s.options[s.selectedIndex]; }).filter(Boolean);
                var opt = opts[0];
                var sameOf = function (o) { return o.parentNode && o.parentNode.label === T.sameSpec; };
                if (opts.length) {
                    var names = opts.map(function (o) { return o.textContent.split(' — ')[0]; }).join(T.sep);
                    parts.push(opts.length > 1 ? L('examinersList', { names: names }) : L('examinerOne', { name: names }));
                }
                var chair = form.querySelector('input[name="chair_id"]:checked');
                if (chair && opts.length) {
                    var chairRow = chair.closest('.dm-member'), chairSel = chairRow.querySelector('select');
                    var chairName = chairSel ? chairSel.options[chairSel.selectedIndex].textContent.split(' — ')[0] : f('supervisor').textContent;
                    parts.push(L('chairName', { name: chairName }));
                }
                f('summary').textContent = parts.length ? parts.join(' · ') : '—';

                var hint = f('examiner-hint');
                if (hint && opts.length === 1) {
                    var same = sameOf(opt);
                    hint.textContent = (same ? T.sameSpec : T.otherSpec) + ' · ' + (opt.textContent.split(' — ')[1] || '');
                    hint.classList.toggle('is-same', same);
                } else if (hint && opts.length > 1) {
                    var n = opts.filter(sameOf).length;
                    hint.textContent = L('committeeOf', { n: opts.length + 1 }) + ' · ' + (n ? L('nSame', { n: n }) : T.noneSame)
                        + ' · ' + (@json(\App\Support\DefenseGrading::supervisorWeight() !== null) ? T.weighted : T.average);
                    hint.classList.toggle('is-same', n > 0);
                }
            }
            form.addEventListener('input', refresh);
            form.addEventListener('change', refresh);

            var pick = f('pick');
            document.querySelectorAll('[data-schedule]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (pick) pick.hidden = true;
                    open(JSON.parse(btn.dataset.schedule), btn.dataset.defense ? JSON.parse(btn.dataset.defense) : null, false);
                });
            });

            // خانة في المخطِّط: الوقت معلوم، والمشروع يُختار من المنتظرين (أو هو المُفلَت عليها)
            function openSlot(cell, projectId) {
                if (!pick || !pick.options.length) return;
                if (projectId) pick.value = projectId;
                pick.hidden = false;
                pick.onchange = function () { open(PROJECTS[pick.value], { date: field('date').value, time: field('time').value, mode: (form.querySelector('input[name="mode"]:checked') || {}).value, room_id: field('room_id').value }, false); };
                open(PROJECTS[pick.value], { date: cell.dataset.slotDate, time: cell.dataset.slotTime }, false);
            }
            document.querySelectorAll('[data-add]').forEach(function (btn) {
                btn.addEventListener('click', function () { openSlot(btn.closest('.df-tslot')); });
            });

            // الخانة المزدحمة: «+N» يفتحها لتُعرض مناقشاتها كلها
            document.querySelectorAll('[data-more]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var open = btn.closest('.df-cell').classList.toggle('is-open');
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    btn.textContent = open ? '−' : btn.dataset.n;
                });
                btn.dataset.n = btn.textContent;
            });

            // القائمة الكثيرة: بحث فوري، وتفاصيل البطاقة المدمجة بالنقر
            var search = document.querySelector('[data-queue-search]');
            if (search) {
                var cards = document.querySelectorAll('.df-queue .df-card'), none = document.querySelector('[data-queue-none]'), count = document.querySelector('[data-queue-count]'), total = cards.length;
                search.addEventListener('input', function () {
                    var q = search.value.trim().toLowerCase(), shown = 0;
                    cards.forEach(function (c) { var on = !q || c.dataset.search.indexOf(q) !== -1; c.hidden = !on; if (on) shown++; });
                    if (none) none.hidden = shown > 0;
                    if (count) count.textContent = q ? L('shownOf', { shown: shown, total: total }) : total;
                });
            }
            document.querySelectorAll('[data-expand]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var open = btn.closest('.df-card').classList.toggle('is-open');
                    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                });
            });

            // المرور على بطاقة مشروع يُضيء اقتراحها في المخطِّط، والعكس
            function link(id, on) {
                document.querySelectorAll('[data-project="' + id + '"]').forEach(function (el) { el.classList.toggle('is-linked', on); });
            }
            document.querySelectorAll('[data-project]').forEach(function (el) {
                el.addEventListener('mouseenter', function () { link(el.dataset.project, true); });
                el.addEventListener('mouseleave', function () { link(el.dataset.project, false); });
                el.addEventListener('focusin', function () { link(el.dataset.project, true); });
                el.addEventListener('focusout', function () { link(el.dataset.project, false); });
            });

            // السحب والإفلات: بطاقة من القائمة على خانة ← نافذة الجدولة بوقتها ومشروعها.
            // النقر يبقى الطريق للمس ولوحة المفاتيح.
            // شريط الأيام: يوم واحد معروض؛ والسحب فوق يوم يفتحه ليُفلت في خاناته
            var dayTabs = document.querySelectorAll('.df-daytab');
            function showDay(key) {
                dayTabs.forEach(function (t) {
                    var on = t.dataset.day === key;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                document.querySelectorAll('[data-day-panel]').forEach(function (d) { d.hidden = d.dataset.dayPanel !== key; });
            }
            dayTabs.forEach(function (t) {
                t.addEventListener('click', function () { showDay(t.dataset.day); });
                t.addEventListener('dragenter', function (e) { e.preventDefault(); showDay(t.dataset.day); });
                t.addEventListener('dragover', function (e) { e.preventDefault(); });
            });
            // المرور على بطاقة اقتراحها في يوم آخر: يُعلَّم ذلك اليوم
            document.querySelectorAll('.df-queue .df-card[data-project]').forEach(function (card) {
                var ghost = document.querySelector('.df-block.is-ghost[data-project="' + card.dataset.project + '"]');
                var panel = ghost && ghost.closest('[data-day-panel]');
                var tab = panel && document.querySelector('.df-daytab[data-day="' + panel.dataset.dayPanel + '"]');
                if (!tab) return;
                card.addEventListener('mouseenter', function () { tab.classList.add('is-hint'); });
                card.addEventListener('mouseleave', function () { tab.classList.remove('is-hint'); });
            });

            var planEl = document.querySelector('.df-plan');
            document.querySelectorAll('.df-card[draggable="true"]').forEach(function (card) {
                card.addEventListener('dragstart', function (e) {
                    e.dataTransfer.setData('text/plain', card.dataset.project);
                    e.dataTransfer.effectAllowed = 'move';
                    card.classList.add('is-dragging');
                    if (planEl) planEl.classList.add('is-dragging');
                });
                card.addEventListener('dragend', function () {
                    card.classList.remove('is-dragging');
                    if (planEl) planEl.classList.remove('is-dragging');
                    document.querySelectorAll('.df-tslot.is-drop').forEach(function (td) { td.classList.remove('is-drop'); });
                });
            });
            document.querySelectorAll('.df-tslot[data-slot-date]').forEach(function (td) {
                td.addEventListener('dragenter', function (e) { e.preventDefault(); td.classList.add('is-drop'); });
                td.addEventListener('dragover', function (e) { e.preventDefault(); e.dataTransfer.dropEffect = 'move'; td.classList.add('is-drop'); });
                // الانتقال إلى عنصر داخل الخانة ليس خروجاً منها
                td.addEventListener('dragleave', function (e) { if (e.relatedTarget && !td.contains(e.relatedTarget)) td.classList.remove('is-drop'); });
                td.addEventListener('drop', function (e) {
                    e.preventDefault();
                    td.classList.remove('is-drop');
                    var id = e.dataTransfer.getData('text/plain');
                    if (PROJECTS[id]) openSlot(td, id);
                });
            });

            // خطأ من الخادم: تُفتح النافذة من جديد بما أُدخل
            @if ($errors->hasAny(['date', 'time', 'duration_minutes', 'mode', 'room_id', 'meeting_url', 'examiner_id', 'examiner_ids', 'examiner_ids.*', 'chair_id', 'notes']) && old('project_id'))
                (function () {
                    var p = PROJECTS[@json((int) old('project_id'))];
                    if (p) open(p, @json(old('defense_id') ? ['id' => (int) old('defense_id')] : null), true);
                })();
            @endif

            // الإلغاء
            var cancelEl = document.getElementById('cancelModal');
            document.querySelectorAll('[data-cancel]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    cancelEl.querySelector('form').action = btn.dataset.cancel;
                    cancelEl.querySelector('[data-f="cancel-title"]').textContent = btn.dataset.title;
                    bootstrap.Modal.getOrCreateInstance(cancelEl).show();
                });
            });

            @if ($errors->has('name'))
                bootstrap.Modal.getOrCreateInstance(document.getElementById('roomsModal')).show();
            @endif
        })();
    </script>
@endpush
