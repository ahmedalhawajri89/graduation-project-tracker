@extends('layouts.admin.admin')
@section('title', 'المناقشات')

@section('crumbs')
    <x-crumb>المناقشات</x-crumb>
@endsection

@section('content')

    @use('App\Models\Defense')

    @php
        $activeRooms = $rooms->where('is_active', true)->values();

        // بيانات كل مشروع للنافذة: تُملأ منها عند الجدولة، وعند إعادة فتحها بعد خطأ
        $meta = fn ($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'type' => $p->project_type->name ?? '',
            'supervisor' => $p->supervisor->name ?? '',
            'supervisor_id' => $p->supervisor_id,
            'specialize_id' => $p->project_type->specialize_id ?? null,
            'team' => $p->group->map(fn ($g) => $g->student?->name)->filter()->implode('، '),
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
            'examiner_id' => optional($d->members->firstWhere('role', 'examiner'))->supervisor_id,
            'notes' => $d->notes,
        ];

        $dayLabel = fn ($date) => $date->isToday() ? 'اليوم' : ($date->isTomorrow() ? 'غداً' : $date->translatedFormat('l j F'));
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

    <x-page-header title="المناقشات" subtitle="لجنة وموعد لكل مشروع مكتمل — حضورياً أو عن بُعد">
        <x-slot:actions>
            <a href="{{ route('admin.defenses.export') }}" class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                تصدير Excel
            </a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#roomsModal">
                <i class="ti ti-door me-1" aria-hidden="true"></i>
                القاعات
                <span class="df-count">{{ $activeRooms->count() }}</span>
            </button>
            @if ($planned->count() >= 2)
                {{-- جدول مقترح للكل يُعرض معاينةً ولا يُحفظ قبل الاعتماد --}}
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#planModal">
                    <i class="ti ti-wand me-1" aria-hidden="true"></i>
                    جدولة الكل تلقائياً
                </button>
            @endif
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ مسار المناقشات: أين يقف كل مشروع ═══ --}}
    <nav class="df-pipe" aria-label="مسار المناقشات">
        @foreach ([
            ['awaiting', 'بانتظار الجدولة', 'ti-hourglass', $pipeline['awaiting'], 'awaiting', true],
            ['scheduled', 'مجدولة', 'ti-calendar-event', $pipeline['scheduled'], 'upcoming', false],
            ['grading', 'بانتظار الدرجة', 'ti-award', $pipeline['grading'], 'past', false],
            ['done', 'منتهية', 'ti-circle-check', $pipeline['done'], 'past', false],
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
            لا قاعات بعد — الاقتراح التلقائي يحتاج قاعة، والمناقشات عن بُعد متاحة يدوياً.
            <button type="button" class="btn btn-link p-0 align-baseline" data-bs-toggle="modal" data-bs-target="#roomsModal">أضف قاعة</button>.
        </div>
    @endif

    {{-- ═══ التبويبات ═══ --}}
    <div class="filter-tabs df-tabs mb-3" role="tablist">
        @foreach (['awaiting' => ['المخطِّط', $awaiting->count()], 'upcoming' => ['القادمة', $upcoming->count()], 'past' => ['السابقة', $past->count()]] as $key => [$label, $n])
            <a href="{{ route('admin.defenses.index', ['tab' => $key]) }}" class="filter-tab {{ $tab === $key ? 'is-active' : '' }}"
                @if ($tab === $key) aria-current="page" @endif>
                {{ $label }} <span class="filter-count">{{ $n }}</span>
            </a>
        @endforeach
    </div>

    {{-- ═══ المخطِّط: قائمة الانتظار باقتراحاتها + أسبوع الدوام ═══ --}}
    @if ($tab === 'awaiting')
        <div class="df-plan">
            <aside class="df-queue" aria-labelledby="df-queue-title">
                <h2 id="df-queue-title">بانتظار الجدولة <span>{{ $awaiting->count() }}</span></h2>

                @forelse ($awaiting as $p)
                    @php $s = $plan[$p->id] ?? null; @endphp
                    <article class="df-card">
                        <header>
                            <a href="{{ route('admin.groups.show', $p->id) }}" class="df-title">{{ $p->title }}</a>
                            <span class="df-sub">{{ $p->project_type->name ?? '—' }}</span>
                        </header>
                        <div class="df-people">
                            <span class="df-person"><x-avatar :user="$p->supervisor" class="cell-avatar df-av" />{{ $p->supervisor->name }} <em>المشرف</em></span>
                            <span class="df-person is-team" title="{{ $p->group->map(fn ($g) => $g->student?->name)->filter()->implode('، ') }}">
                                <i class="ti ti-users" aria-hidden="true"></i>{{ $p->group->count() }} طلاب
                            </span>
                        </div>
                        <div class="df-facts">
                            <span><i class="ti ti-list-check" aria-hidden="true"></i>{{ $p->milestones_done }} من {{ $p->milestones_count }} مراحل</span>
                            <span><i class="ti ti-circle-check" aria-hidden="true"></i>اكتمل {{ $p->updated_at?->diffForHumans() }}</span>
                            @if ($p->defense && $p->defense->status === Defense::CANCELLED)
                                <span class="is-warn"><i class="ti ti-calendar-x" aria-hidden="true"></i>أُلغيت مناقشته السابقة</span>
                            @endif
                        </div>

                        @if ($s)
                            {{-- الاقتراح: أول خانة يكون فيها المشرف والقاعة وممتحن أحراراً --}}
                            <div class="df-suggest">
                                <small><i class="ti ti-sparkles" aria-hidden="true"></i> اقتراح</small>
                                <b>{{ $s['starts_at']->translatedFormat('l j F') }} · <bdi dir="ltr">{{ $s['starts_at']->format('H:i') }}</bdi></b>
                                <span>{{ $s['room'] }} · الممتحن {{ $s['examiner'] }}@if ($s['same_specialize']) <em>من التخصص</em>@endif</span>
                            </div>
                            <footer>
                                <form action="{{ route('admin.defenses.store') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="project_id" value="{{ $p->id }}">
                                    @foreach ($suggestionMeta($s) as $k => $v)
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endforeach
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-calendar-check me-1" aria-hidden="true"></i>جدولة بهذا الاقتراح
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-secondary" data-schedule='@json($meta($p))' data-defense='@json($suggestionMeta($s))'>
                                    تعديل
                                </button>
                            </footer>
                        @else
                            <footer>
                                <button type="button" class="btn btn-primary" data-schedule='@json($meta($p))'>
                                    <i class="ti ti-calendar-plus me-1" aria-hidden="true"></i>جدولة المناقشة
                                </button>
                            </footer>
                        @endif
                    </article>
                @empty
                    <div class="df-queue-empty">
                        <i class="ti ti-circle-check" aria-hidden="true"></i>
                        <b>لا مشاريع بانتظار الجدولة</b>
                        <span>حين يضغط المشرف «اكتمال المشروع» يظهر هنا لتُشكَّل لجنته ويُحدَّد موعده.</span>
                    </div>
                @endforelse
            </aside>

            {{-- أسبوع الدوام: ما جُدول كتلاً، وما اقتُرح أشباحاً، والخانة الفارغة تُجدول بنقرة --}}
            <section class="df-week" aria-labelledby="df-week-title">
                <header class="df-week-head">
                    <h2 id="df-week-title">
                        {{-- «إلى» لا شرطة: المدى بشرطة يُقرأ معكوساً داخل نصّ عربي --}}
                        أسبوع {{ $week['days']->first()->translatedFormat($week['days']->first()->month === $week['days']->last()->month ? 'j' : 'j F') }}
                        إلى {{ $week['days']->last()->translatedFormat('j F Y') }}
                    </h2>
                    <div class="df-week-nav">
                        <a href="{{ route('admin.defenses.index', ['tab' => 'awaiting', 'week' => $week['prev']]) }}" class="btn-action" aria-label="الأسبوع السابق" title="الأسبوع السابق"><i class="ti ti-chevron-right" aria-hidden="true"></i></a>
                        <a href="{{ route('admin.defenses.index', ['tab' => 'awaiting']) }}" class="btn btn-sm btn-outline-secondary">الأقرب</a>
                        <a href="{{ route('admin.defenses.index', ['tab' => 'awaiting', 'week' => $week['next']]) }}" class="btn-action" aria-label="الأسبوع التالي" title="الأسبوع التالي"><i class="ti ti-chevron-left" aria-hidden="true"></i></a>
                    </div>
                </header>

                <div class="df-grid-scroll">
                    <table class="df-cal">
                        <thead>
                            <tr>
                                <th class="df-cal-time" scope="col"><span class="visually-hidden">الوقت</span></th>
                                @foreach ($week['days'] as $day)
                                    <th scope="col" class="{{ $day->isToday() ? 'is-today' : '' }}">
                                        <b>{{ $day->translatedFormat('l') }}</b>
                                        <small dir="ltr">{{ $day->format('j/n') }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($week['times'] as $time)
                                <tr>
                                    <th scope="row" class="df-cal-time" dir="ltr">{{ $time }}</th>
                                    @foreach ($week['days'] as $day)
                                        @php
                                            $items = $inSlot($day, $time);
                                            $ghost = $ghosts($day, $time);
                                            $gone = $day->copy()->setTimeFromTimeString($time)->isPast();
                                        @endphp
                                        <td class="{{ $gone ? 'is-past' : '' }}">
                                            @foreach ($items as $d)
                                                @php $editable = $d->status === Defense::SCHEDULED && ! $d->members->whereNotNull('grade')->count(); @endphp
                                                <button type="button" class="df-block is-{{ $d->mode }} {{ $d->status === Defense::DONE ? 'is-done' : '' }}"
                                                    @if ($editable) data-schedule='@json($meta($d->project))' data-defense='@json($defenseMeta($d))' @else disabled @endif
                                                    title="{{ $d->project->title }} — {{ $d->members->map(fn ($m) => $m->supervisor->name)->implode('، ') }}">
                                                    <b>{{ $d->project->title }}</b>
                                                    <small><bdi dir="ltr">{{ $d->starts_at->format('H:i') }}</bdi> · {{ $d->place_label }}</small>
                                                </button>
                                            @endforeach
                                            @foreach ($ghost as $pid => $g)
                                                <button type="button" class="df-block is-ghost" data-schedule='@json($projectsMeta[$pid])' data-defense='@json($suggestionMeta($g))'
                                                    title="اقتراح — انقر للمراجعة والجدولة">
                                                    <b>{{ $projectsMeta[$pid]['title'] }}</b>
                                                    <small>مقترح · {{ $g['room'] }}</small>
                                                </button>
                                            @endforeach
                                            @if (! $gone && $awaiting->count())
                                                <button type="button" class="df-add" data-slot-date="{{ $day->format('Y-m-d') }}" data-slot-time="{{ $time }}"
                                                    aria-label="جدولة في {{ $day->translatedFormat('l') }} {{ $time }}">
                                                    <i class="ti ti-plus" aria-hidden="true"></i>
                                                </button>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <footer class="df-legend">
                    <span><i class="is-in_person"></i>حضوري</span>
                    <span><i class="is-online"></i>عن بُعد</span>
                    <span><i class="is-hybrid"></i>مدمج</span>
                    <span><i class="is-ghost"></i>مقترح</span>
                    <small>انقر خانة فارغة للجدولة فيها، أو مناقشة لتعديلها.</small>
                </footer>
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
                        <small>{{ $items->first()->starts_at->format('Y-m-d') }} · {{ $items->count() }} {{ $items->count() === 1 ? 'مناقشة' : 'مناقشات' }}</small>
                    </h2>
                    <ol class="df-agenda">
                        @foreach ($items as $d)
                            <li class="df-slot {{ $d->isJoinable() ? 'is-live' : '' }}">
                                <div class="df-time">
                                    <b dir="ltr">{{ $d->starts_at->format('H:i') }}</b>
                                    <small dir="ltr">{{ $d->endsAt()->format('H:i') }}</small>
                                    <em>{{ $d->duration_minutes }} د</em>
                                </div>
                                <div class="df-slot-body">
                                    <a href="{{ route('admin.groups.show', $d->project_id) }}" class="df-title">{{ $d->project->title }}</a>
                                    <span class="df-sub">{{ $d->project->group->map(fn ($g) => $g->student?->name)->filter()->implode('، ') }}</span>
                                    <div class="df-chips">
                                        <span class="df-mode is-{{ $d->mode }}"><i class="ti {{ $d->mode_icon }}" aria-hidden="true"></i>{{ $d->place_label }}</span>
                                        @foreach ($d->members as $m)
                                            <span class="df-member"><x-avatar :user="$m->supervisor" class="cell-avatar df-av" />{{ $m->supervisor->name }} <em>{{ $m->role_label }}</em></span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="df-actions">
                                    @if ($d->needsLink() && $d->meeting_url)
                                        <a href="{{ $d->meeting_url }}" target="_blank" rel="noopener" class="btn btn-sm {{ $d->isJoinable() ? 'btn-primary' : 'btn-outline-primary' }}">
                                            <i class="ti ti-video me-1" aria-hidden="true"></i>{{ $d->isJoinable() ? 'انضم الآن' : 'رابط الاجتماع' }}
                                        </a>
                                    @endif
                                    <a href="{{ $d->googleCalendarUrl() }}" target="_blank" rel="noopener" class="btn-action" title="أضف إلى تقويم Google" aria-label="أضف إلى تقويم Google">
                                        <i class="ti ti-brand-google" aria-hidden="true"></i>
                                    </a>
                                    <a href="{{ route('defenses.ics', $d->id) }}" class="btn-action" title="ملف التقويم (.ics) — Outlook وتقويم الجوال" aria-label="تنزيل ملف التقويم">
                                        <i class="ti ti-calendar-down" aria-hidden="true"></i>
                                    </a>
                                    <button type="button" class="btn-action" title="تعديل الموعد" aria-label="تعديل موعد {{ $d->project->title }}"
                                        data-schedule='@json($meta($d->project))' data-defense='@json($defenseMeta($d))'>
                                        <i class="ti ti-pencil" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn-action btn-action--danger" title="إلغاء المناقشة" aria-label="إلغاء مناقشة {{ $d->project->title }}"
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
                <x-empty-state icon="ti-calendar" title="لا مناقشات قادمة"
                    text="جدول مناقشات المشاريع المكتملة من تبويب «بانتظار الجدولة»." class="py-6" />
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
                            $d->status === Defense::CANCELLED => ['ملغاة', 'is-muted'],
                            $d->status === Defense::DONE => ['منتهية · ' . rtrim(rtrim(number_format((float) $d->project->grade, 2, '.', ''), '0'), '.'), 'is-done'],
                            default => ['بانتظار الدرجة · ' . $d->members->whereNotNull('grade')->count() . ' من ' . $d->members->count(), 'is-wait'],
                        };
                    @endphp
                    <a href="{{ route('admin.groups.show', $d->project_id) }}" class="df-past-row">
                        <span class="df-past-date" dir="ltr">{{ $d->starts_at->format('Y-m-d H:i') }}</span>
                        <b>{{ $d->project->title }}</b>
                        <span class="df-sub">{{ $d->place_label }} · {{ $d->members->map(fn ($m) => $m->supervisor->name)->implode('، ') }}</span>
                        <span class="df-status {{ $tone }}">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="dist-panel">
                <x-empty-state icon="ti-history" title="لا مناقشات سابقة" text="ستظهر هنا المناقشات بعد موعدها والملغاة." class="py-6" />
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

                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="defenseModalTitle">جدولة المناقشة</h2>
                        <div class="df-form-project" data-f="project"></div>
                        <select class="form-select form-select-sm df-pick" data-f="pick" aria-label="المشروع" hidden>
                            @foreach ($awaiting as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>

                <div class="modal-body">
                    @error('date')
                        <div class="alert alert-danger py-2">{{ $message }}</div>
                    @enderror

                    <fieldset class="df-fieldset">
                        <legend>الموعد</legend>
                        <div class="row g-3">
                            <div class="col-sm-5">
                                <label class="form-label required" for="df-date">التاريخ</label>
                                <input type="date" id="df-date" name="date" class="form-control @error('date') is-invalid @enderror"
                                    min="{{ today()->format('Y-m-d') }}" value="{{ old('date') }}" required>
                            </div>
                            <div class="col-sm-4">
                                <label class="form-label required" for="df-time">الوقت</label>
                                <input type="time" id="df-time" name="time" step="900" class="form-control @error('time') is-invalid @enderror"
                                    value="{{ old('time', '10:00') }}" required>
                                @error('time')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-sm-3">
                                <label class="form-label" for="df-duration">المدة</label>
                                <select id="df-duration" name="duration_minutes" class="form-select">
                                    @foreach (Defense::DURATIONS as $m)
                                        <option value="{{ $m }}" @selected((int) old('duration_minutes', 45) === $m)>{{ $m }} دقيقة</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="df-fieldset">
                        <legend>المكان</legend>
                        <div class="df-modes" role="radiogroup" aria-label="نوع المناقشة">
                            @foreach (Defense::MODES as $key => $m)
                                <label class="df-mode-opt">
                                    <input type="radio" name="mode" value="{{ $key }}" @checked(old('mode', $activeRooms->count() ? 'in_person' : 'online') === $key)>
                                    <span><i class="ti {{ $m['icon'] }}" aria-hidden="true"></i>{{ $m['label'] }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="df-when" data-when="room">
                            <label class="form-label required" for="df-room">القاعة</label>
                            <select id="df-room" name="room_id" class="form-select @error('room_id') is-invalid @enderror">
                                <option value="">اختر قاعة…</option>
                                @foreach ($activeRooms as $r)
                                    <option value="{{ $r->id }}" @selected((int) old('room_id') === $r->id)>
                                        {{ $r->name }}{{ $r->location ? ' — ' . $r->location : '' }}{{ $r->capacity ? ' · ' . $r->capacity . ' مقعداً' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('room_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @if (! $activeRooms->count())
                                <div class="form-hint">لا قاعات بعد — أضفها من زر «القاعات» أعلى الصفحة.</div>
                            @endif
                        </div>

                        {{-- Google Meet بلا ربط حساب: يُنشأ الاجتماع في تبويب جديد ويُلصق رابطه --}}
                        <div class="df-when" data-when="link">
                            <label class="form-label required" for="df-link">رابط الاجتماع</label>
                            <div class="df-link-row">
                                <input type="url" id="df-link" name="meeting_url" dir="ltr" inputmode="url"
                                    class="form-control @error('meeting_url') is-invalid @enderror"
                                    placeholder="https://meet.google.com/abc-defg-hij" value="{{ old('meeting_url') }}">
                                <a href="https://meet.google.com/new" target="_blank" rel="noopener" class="btn btn-outline-primary df-meet">
                                    <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M15 12 17.3 14 20 16.2V7.8L17.3 10zM3 7.5V16.5A1.5 1.5 0 0 0 4.5 18H13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 13 6H4.5A1.5 1.5 0 0 0 3 7.5z"/></svg>
                                    إنشاء Google Meet
                                </a>
                            </div>
                            @error('meeting_url')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div class="form-hint">يُفتح Google Meet في تبويب جديد — انسخ رابط الاجتماع والصقه هنا. يقبل أيضاً روابط Zoom وTeams.</div>
                        </div>
                    </fieldset>

                    <fieldset class="df-fieldset">
                        <legend>اللجنة</legend>
                        <div class="df-committee">
                            <div class="df-committee-fixed">
                                <small>المشرف</small>
                                <b data-f="supervisor">—</b>
                            </div>
                            <div class="df-committee-pick">
                                <label class="form-label required" for="df-examiner">الممتحن</label>
                                <select id="df-examiner" name="examiner_id" class="form-select @error('examiner_id') is-invalid @enderror" required>
                                </select>
                                @error('examiner_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                <div class="form-hint">مرتّبون: من تخصص المشروع أولاً، ثم الأقلّ مناقشاتٍ قادمة.</div>
                            </div>
                        </div>
                    </fieldset>

                    <label class="form-label" for="df-notes">ملاحظات للفريق واللجنة <span class="text-secondary">(اختيارية)</span></label>
                    <textarea id="df-notes" name="notes" rows="2" class="form-control" maxlength="1000"
                        placeholder="مثال: عرض تقديمي 15 دقيقة ثم أسئلة اللجنة. أحضروا نسخة مطبوعة من التقرير.">{{ old('notes') }}</textarea>
                </div>

                <div class="modal-footer">
                    <span class="df-foot-note"><i class="ti ti-bell" aria-hidden="true"></i> يُشعَر الفريق واللجنة على المنصّة وبالبريد</span>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary" data-f="submit">
                        <i class="ti ti-calendar-check me-1" aria-hidden="true"></i> جدولة
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
                            <h2 class="modal-title" id="planModalTitle">جدول مقترح لـ{{ $planned->count() }} مناقشات</h2>
                            <div class="df-form-project">لا شيء يُحفظ قبل الاعتماد. كل مناقشة حضورية {{ $slotMinutes }} دقيقة، بلا تعارض في القاعات ولا اللجان.</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body p-0">
                        <table class="table df-plan-table mb-0">
                            <thead><tr><th>المشروع</th><th>الموعد</th><th>القاعة</th><th>الممتحن</th></tr></thead>
                            <tbody>
                                @foreach ($planned->sortBy(fn ($x) => $x['starts_at']) as $pid => $x)
                                    <tr>
                                        <td>
                                            <b>{{ $projectsMeta[$pid]['title'] }}</b>
                                            <small>المشرف {{ $projectsMeta[$pid]['supervisor'] }}</small>
                                            @foreach (['date' => $x['starts_at']->format('Y-m-d'), 'time' => $x['starts_at']->format('H:i'), 'room_id' => $x['room_id'], 'examiner_id' => $x['examiner_id']] as $k => $v)
                                                <input type="hidden" name="items[{{ $pid }}][{{ $k }}]" value="{{ $v }}">
                                            @endforeach
                                        </td>
                                        <td>{{ $x['starts_at']->translatedFormat('l j F') }}<small dir="ltr">{{ $x['starts_at']->format('H:i') }}–{{ $x['starts_at']->copy()->addMinutes($x['duration_minutes'])->format('H:i') }}</small></td>
                                        <td>{{ $x['room'] }}</td>
                                        <td>{{ $x['examiner'] }}@if ($x['same_specialize'])<small>من تخصص المشروع</small>@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if ($planned->count() < $awaiting->count())
                            <p class="df-plan-left"><i class="ti ti-info-circle" aria-hidden="true"></i> {{ $awaiting->count() - $planned->count() }} مشاريع بلا خانة متاحة ضمن أوقات الدوام — جدولها يدوياً.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <span class="df-foot-note"><i class="ti ti-bell" aria-hidden="true"></i> يُشعَر كل فريق ولجنته عند الاعتماد</span>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-checks me-1" aria-hidden="true"></i>اعتماد الجدول</button>
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
                    <h2 class="modal-title" id="cancelModalTitle">إلغاء المناقشة</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">تُلغى مناقشة «<b data-f="cancel-title"></b>» ويعود المشروع إلى «بانتظار الجدولة». يُشعَر الفريق واللجنة.</p>
                    <label class="form-label" for="cancel-reason">السبب <span class="text-secondary">(يصل إليهم)</span></label>
                    <textarea id="cancel-reason" name="reason" rows="2" class="form-control" maxlength="300" placeholder="مثال: تعارض مع امتحانات القسم"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">تراجع</button>
                    <button type="submit" class="btn btn-danger">إلغاء المناقشة</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════ نافذة القاعات ═══════════ --}}
    <div class="modal fade" id="roomsModal" tabindex="-1" aria-labelledby="roomsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="roomsModalTitle">قاعات المناقشة</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('admin.defenses.rooms.store', ['tab' => $tab]) }}" class="df-room-add">
                        @csrf
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="اسم القاعة — مثال: قاعة 204" value="{{ old('name') }}" required maxlength="60">
                        <input type="text" name="location" class="form-control" placeholder="المبنى / الطابق" value="{{ old('location') }}" maxlength="120">
                        <input type="number" name="capacity" class="form-control" placeholder="السعة" min="1" max="1000" value="{{ old('capacity') }}">
                        <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1" aria-hidden="true"></i>إضافة</button>
                        @error('name')<div class="invalid-feedback d-block w-100">{{ $message }}</div>@enderror
                    </form>

                    @forelse ($rooms as $r)
                        <div class="df-room {{ $r->is_active ? '' : 'is-off' }}">
                            <span class="df-room-icon"><i class="ti ti-door" aria-hidden="true"></i></span>
                            <span class="df-room-body">
                                <b>{{ $r->name }}</b>
                                <small>{{ collect([$r->location, $r->capacity ? $r->capacity . ' مقعداً' : null, $r->upcoming_count ? $r->upcoming_count . ' مناقشات قادمة' : null, $r->is_active ? null : 'معطّلة'])->filter()->implode(' · ') ?: '—' }}</small>
                            </span>
                            <form method="post" action="{{ route('admin.defenses.rooms.toggle', $r->id) }}">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $r->is_active ? 'تعطيل' : 'تفعيل' }}</button>
                            </form>
                            <form method="post" action="{{ route('admin.defenses.rooms.destroy', $r->id) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-action--danger" title="حذف القاعة" aria-label="حذف {{ $r->name }}"><i class="ti ti-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-secondary text-center my-3">لا قاعات بعد.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        (function () {
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
            function fillExaminers(p, selected) {
                var sel = field('examiner_id');
                sel.innerHTML = '';
                var list = EXAMINERS.filter(function (e) { return e.id !== p.supervisor_id; })
                    .sort(function (a, b) {
                        var sa = a.specialize_id === p.specialize_id ? 0 : 1, sb = b.specialize_id === p.specialize_id ? 0 : 1;
                        return sa - sb || a.load - b.load || a.name.localeCompare(b.name, 'ar');
                    });
                var groups = [['من تخصص المشروع', list.filter(function (e) { return e.specialize_id === p.specialize_id; })],
                              ['تخصصات أخرى', list.filter(function (e) { return e.specialize_id !== p.specialize_id; })]];
                groups.forEach(function (g) {
                    if (!g[1].length) return;
                    var og = document.createElement('optgroup');
                    og.label = g[0];
                    g[1].forEach(function (e) {
                        var o = document.createElement('option');
                        o.value = e.id;
                        o.textContent = e.name + (e.load ? ' — ' + e.load + ' مناقشات قادمة' : ' — لا مناقشات قادمة');
                        if (String(e.id) === String(selected)) o.selected = true;
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
                modalEl.querySelector('.modal-title').textContent = edit ? 'تعديل موعد المناقشة' : 'جدولة المناقشة';
                f('submit').lastChild.textContent = edit ? ' حفظ التعديل' : ' جدولة';
                f('project').textContent = p.title + (p.team ? ' · ' + p.team : '');
                f('supervisor').textContent = p.supervisor || '—';

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
                fillExaminers(p, keepOld ? @json(old('examiner_id')) : (d && d.examiner_id));
                syncMode();
                modal.show();
            }

            var pick = f('pick');
            document.querySelectorAll('[data-schedule]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (pick) pick.hidden = true;
                    open(JSON.parse(btn.dataset.schedule), btn.dataset.defense ? JSON.parse(btn.dataset.defense) : null, false);
                });
            });

            // خانة فارغة في المخطِّط: الوقت معلوم، والمشروع يُختار من المنتظرين
            document.querySelectorAll('[data-slot-date]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (!pick || !pick.options.length) return;
                    var slot = { date: btn.dataset.slotDate, time: btn.dataset.slotTime };
                    pick.hidden = false;
                    pick.onchange = function () { open(PROJECTS[pick.value], { date: field('date').value, time: field('time').value, mode: (form.querySelector('input[name="mode"]:checked') || {}).value, room_id: field('room_id').value }, false); };
                    open(PROJECTS[pick.value], slot, false);
                });
            });

            // خطأ من الخادم: تُفتح النافذة من جديد بما أُدخل
            @if ($errors->hasAny(['date', 'time', 'duration_minutes', 'mode', 'room_id', 'meeting_url', 'examiner_id', 'notes']) && old('project_id'))
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
