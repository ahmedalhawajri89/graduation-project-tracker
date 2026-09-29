{{--
    بطاقة «مناقشتك» — تظهر من جدولة المناقشة حتى رصد درجتها.

    الموعد بعدّ تنازلي، والمكان (القاعة أو رابط الاجتماع وزر «انضم» قبل
    الموعد بربع ساعة)، واللجنة بأسمائها، وملاحظات الإدارة، وإضافة الموعد إلى
    التقويم. بعد انتهائها: «نوقش — بانتظار درجة اللجنة».

    @param \App\Models\Defense $defense
    @param \App\Models\Project $project
--}}

@php
    $ended = $defense->endsAt()->isPast();
    $live = ! $ended && $defense->starts_at->isPast();
    $state = match (true) {
        $ended => ['is-done', 'نوقش المشروع — بانتظار درجة اللجنة'],
        $live => ['is-live', 'المناقشة جارية الآن'],
        $defense->starts_at->isToday() => ['is-today', 'اليوم'],
        $defense->starts_at->isTomorrow() => ['is-soon', 'غداً'],
        default => ['', 'قادمة'],
    };
    $msDone = $project->milestones->where('is_done', true)->count();
    $msTotal = $project->milestones->count();
@endphp

<section class="sdf mb-4" id="defense" aria-labelledby="sdf-title">
    <header class="sdf-head">
        <h2 id="sdf-title"><i class="ti ti-presentation" aria-hidden="true"></i> مناقشتك</h2>
        <span class="sdf-state {{ $state[0] }}">{{ $state[1] }}</span>
    </header>

    <div class="sdf-body">
        {{-- الموعد والعدّ التنازلي --}}
        <div class="sdf-when">
            <div class="sdf-date">
                <b>{{ $defense->starts_at->format('j') }}</b>
                <span>{{ $defense->starts_at->translatedFormat('F') }}</span>
            </div>
            <div class="sdf-when-body">
                <strong>{{ $defense->starts_at->translatedFormat('l j F Y') }}</strong>
                <span dir="ltr" class="sdf-range">{{ $defense->starts_at->format('H:i') }}–{{ $defense->endsAt()->format('H:i') }}</span>
                <span class="sdf-dur">{{ $defense->duration_minutes }} دقيقة</span>
                @unless ($ended || $live)
                    <span class="sdf-countdown" data-countdown="{{ $defense->starts_at->toIso8601String() }}" aria-live="polite"></span>
                @endunless
            </div>
        </div>

        {{-- المكان --}}
        <div class="sdf-where">
            <span class="df-mode is-{{ $defense->mode }}"><i class="ti {{ $defense->mode_icon }}" aria-hidden="true"></i>{{ $defense->mode_label }}</span>
            @if ($defense->needsRoom())
                <span class="sdf-room">
                    <b>{{ $defense->room?->name ?? 'قاعة غير محدّدة' }}</b>
                    @if ($defense->room?->location)<small>{{ $defense->room->location }}</small>@endif
                </span>
            @endif
            @if ($defense->needsLink() && $defense->meeting_url && ! $ended)
                <a href="{{ $defense->meeting_url }}" target="_blank" rel="noopener" class="btn {{ $defense->isJoinable() ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                    <i class="ti ti-video me-1" aria-hidden="true"></i>{{ $defense->isJoinable() ? 'انضم الآن' : 'رابط الاجتماع' }}
                </a>
            @endif
        </div>

        {{-- اللجنة --}}
        <div class="sdf-committee">
            <small>اللجنة</small>
            <ul>
                @foreach ($defense->members as $m)
                    <li>
                        <x-avatar :user="$m->supervisor" class="cell-avatar sdf-av" />
                        <span><b>{{ $m->supervisor->name }}</b><em>{{ $m->role_label }}</em></span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @if ($defense->notes)
        <p class="sdf-notes"><i class="ti ti-info-circle" aria-hidden="true"></i> {{ $defense->notes }}</p>
    @endif

    @unless ($ended)
        <footer class="sdf-foot">
            {{-- قبل المناقشة: ما يُطمئن الفريق أنه جاهز --}}
            <ul class="sdf-ready">
                <li class="{{ $msTotal && $msDone === $msTotal ? 'is-ok' : '' }}">
                    <i class="ti {{ $msTotal && $msDone === $msTotal ? 'ti-circle-check' : 'ti-circle-dashed' }}" aria-hidden="true"></i>
                    المراحل {{ $msDone }} من {{ $msTotal }}
                </li>
                <li class="{{ $project->files->count() ? 'is-ok' : '' }}">
                    <i class="ti {{ $project->files->count() ? 'ti-circle-check' : 'ti-circle-dashed' }}" aria-hidden="true"></i>
                    {{ $project->files->count() ? $project->files->count() . ' ملفات مرفوعة تراها اللجنة' : 'لا ملفات — اللجنة تحضّر من ملفاتكم' }}
                </li>
            </ul>
            <div class="sdf-cal">
                <a href="{{ route('defenses.ics', $defense->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-calendar-down me-1" aria-hidden="true"></i>ملف التقويم
                </a>
                <a href="{{ $defense->googleCalendarUrl() }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-brand-google me-1" aria-hidden="true"></i>تقويم Google
                </a>
            </div>
        </footer>
    @endunless
</section>

@push('js')
    <script>
        // العدّ التنازلي: «بعد 3 أيام و4 ساعات» — يُحدَّث كل دقيقة
        (function () {
            var el = document.querySelector('[data-countdown]');
            if (!el) return;
            var at = new Date(el.dataset.countdown).getTime();
            var plural = function (n, one, two, few, many) { return n === 1 ? one : n === 2 ? two : (n >= 3 && n <= 10 ? n + ' ' + few : n + ' ' + many); };
            function tick() {
                var s = Math.max(0, Math.floor((at - Date.now()) / 1000));
                var d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60);
                var parts = [];
                if (d) parts.push(plural(d, 'يوم', 'يومين', 'أيام', 'يوماً'));
                if (h) parts.push(plural(h, 'ساعة', 'ساعتين', 'ساعات', 'ساعة'));
                if (!d && m) parts.push(plural(m, 'دقيقة', 'دقيقتين', 'دقائق', 'دقيقة'));
                el.textContent = s <= 0 ? 'حان الموعد' : 'بعد ' + parts.join(' و');
            }
            tick();
            setInterval(tick, 30000);
        })();
    </script>
@endpush
