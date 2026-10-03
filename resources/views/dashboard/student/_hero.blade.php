{{--
    بطاقة المشروع — أول ما يراه الطالب: ما مشروعي، ومن يشرف عليه، وأين وصل.

    كانت ترويسة تحيّة، ثم شريط أرقام قيمه «—» و«لم يُحدَّد»، ثم لوح مسار بعرض
    الصفحة لمعلومة واحدة — والعنوان والمشرف مدفونان في العمود الجانبي.

    @param \App\Models\Project  $project
    @param \App\Models\Student  $student
    @param \App\Models\Semester $semester
    @param array                $steps    خطوات المسار [key, label]
    @param int                  $reached  موضع الخطوة الحالية
    @param int                  $unreadMsgs
--}}

@php
    $progress = $project->progress;
    $daysLeft = $project->days_left;
    $members = $project->group->sortBy(fn ($m) => $m->type === 'leader' ? 0 : 1)->values();
    $sem = $semester->parts();
    $firstName = \Illuminate\Support\Str::of($student->name)->explode(' ')->first();
    $active = in_array($project->status, ['accept', 'complete'], true);

    // المناقشة المجدولة (إن وُجدت) — تحلّ محلّ «اكتمل المشروع» في الشريحة
    $defense = \App\Support\DefenseScheduler::enabled() && $project->status === 'complete'
        ? $project->defense()->where('status', 'scheduled')->with(['room', 'members.supervisor'])->first()
        : null;

    $deadline = match (true) {
        $defense && $defense->endsAt()->isPast() => ['is-done', 'ti-presentation', __('نوقش المشروع — بانتظار درجة اللجنة')],
        (bool) $defense => [$defense->starts_at->isToday() ? 'is-warn' : '', 'ti-presentation',
            __('مناقشتك :when', ['when' => $defense->starts_at->isToday() ? __('اليوم') : ($defense->starts_at->isTomorrow() ? __('غداً') : $defense->starts_at->translatedFormat('l j F'))])
            . ' · ' . $defense->starts_at->format('H:i') . ' · ' . $defense->place_label],
        $project->status === 'complete' && is_null($project->grade) => ['is-done', 'ti-rosette-discount-check', __('اكتمل المشروع — بانتظار موعد المناقشة')],
        $project->status === 'complete' => ['is-done', 'ti-rosette-discount-check', __('اكتمل المشروع')],
        $project->status === 'request' => ['', 'ti-hourglass', __('بانتظار ردّ المشرف')],
        is_null($daysLeft) => ['', 'ti-calendar', __('لا موعد نهائي بعد')],
        $daysLeft < 0 => ['is-late', 'ti-alarm', __('تأخّر التسليم :n يوماً', ['n' => abs($daysLeft)])],
        $daysLeft === 0 => ['is-warn', 'ti-alarm', __('التسليم النهائي اليوم')],
        default => [$daysLeft <= 7 ? 'is-warn' : '', 'ti-calendar-due', __('التسليم النهائي بعد :n يوماً', ['n' => $daysLeft])],
    };
@endphp

<section class="stu-hero mb-4" aria-labelledby="hero-title">
    <div class="stu-hero-top">
        <div class="stu-hero-main">
            <p class="stu-hero-hello">
                {{ __('أهلاً، :name', ['name' => $firstName]) }}
                <span>· {{ $sem['term'] }}@if ($sem['year']) <span dir="ltr">{{ $sem['year'] }}</span>@endif</span>
            </p>

            <div class="stu-hero-title">
                <h1 id="hero-title">{{ $project->title }}</h1>
                <x-status-badge :status="$project->status" />
            </div>

            <div class="stu-hero-meta">
                @if ($project->project_type->name)
                    <span class="hero-meta-item">
                        <i class="ti ti-category" aria-hidden="true"></i>
                        {{ $project->project_type->name }}
                    </span>
                @endif
                @if ($project->supervisor)
                    <span class="hero-meta-item">
                        <x-avatar :user="$project->supervisor" class="ctx-avatar hero-meta-avatar is-supervisor" />
                        {{ $project->supervisor->name }}
                    </span>
                @endif
                <a href="#team" class="hero-meta-item hero-team">
                    <span class="avatar-stack" aria-hidden="true">
                        @foreach ($members->take(4) as $member)
                            <x-avatar :user="$member->student" />
                        @endforeach
                        @if ($members->count() > 4)
                            <span class="cell-avatar avatar-more" dir="ltr">+{{ $members->count() - 4 }}</span>
                        @endif
                    </span>
                    {{ $members->count() === 1 ? __(':n عضو', ['n' => 1]) : __(':n أعضاء', ['n' => $members->count()]) }}
                </a>
            </div>
        </div>

        {{-- الإنجاز: حلقة بنسبته — وبلا مراحل كلمة تشرح لا «—» --}}
        <div class="stu-hero-progress">
            @if ($active && ! is_null($progress))
                <span class="pct-ring is-xl" style="--p: {{ $progress }}" role="img" aria-label="{{ __('الإنجاز :p%', ['p' => $progress]) }}">
                    <svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="17" /><circle cx="20" cy="20" r="17" pathLength="100" /></svg>
                    <b>{{ $progress }}%</b>
                </span>
                <small>{{ __(':done من :total مراحل', ['done' => $project->milestones->where('is_done', true)->count(), 'total' => $project->milestones->count()]) }}</small>
            @elseif ($active)
                <span class="hero-progress-empty" aria-hidden="true"><i class="ti ti-route"></i></span>
                <small>{{ __('بانتظار خطة المشرف') }}</small>
            @else
                <span class="hero-progress-empty" aria-hidden="true"><i class="ti ti-hourglass"></i></span>
                <small>{{ __('قيد المراجعة') }}</small>
            @endif
        </div>
    </div>

    {{-- المسار مضغوطاً، وعليه «رحلة المشروع» كما في الصفحة الرئيسية: فريقك
         واقف عند خطوتك الحالية، والمشرف عند موافقته، واللجنة عند المناقشة،
         والقبعة عند الدرجة. حين تتقدّم خطوة يمشي الفريق إليها (journey-rail في
         public/js/dialog.js يحفظ آخر خطوة رآها). بعد الدرجة يرمي قبعاته. --}}
    @php
        $railAt = min($reached, count($steps)) - 1; // موضع الفريق (من صفر)
        $graduated = $reached > count($steps);
        $cast = $members->take(3)->values()->map(fn ($m, $i) => $m->student?->gender === 'female'
            ? 'hj-girl' : ($i % 2 ? 'hj-boy2' : 'hj-boy'));
    @endphp
    <div class="hero-journey" style="--n: {{ count($steps) }}" data-journey-rail
        data-journey-key="journey:{{ $project->id }}" data-journey-at="{{ $railAt }}">
        @if ($railAt >= 0)
            <div class="rail-cast" aria-hidden="true">
                <svg class="rail-actor is-supervisor" style="--i: 1" viewBox="-30 -98 60 102"><use href="#hj-supervisor" /></svg>
                <svg class="rail-actor is-committee" style="--i: 3" viewBox="-44 -70 88 74"><use href="#hj-committee" /></svg>
                @unless ($railAt === count($steps) - 1)
                    <svg class="rail-actor is-cap" style="--i: {{ count($steps) - 1 }}" viewBox="-18 -12 36 26"><use href="#hj-cap" /></svg>
                @endunless
                {{-- عند المشرف أو اللجنة يقف الفريق قبل الخطوة، وهما بعدها --}}
                <span class="rail-team {{ $graduated ? 'is-graduated' : '' }} {{ in_array($railAt, [1, 3], true) ? 'is-meeting' : '' }}" style="--i: {{ $railAt }}">
                    @foreach ($cast as $who)
                        <svg class="rail-member" viewBox="-30 -104 60 108">
                            <use href="#{{ $who }}" />
                            @if ($graduated)<g transform="translate(0 {{ $who === 'hj-girl' ? -70 : -73 }})"><g class="rail-member-cap"><use href="#hj-cap" /></g></g>@endif
                        </svg>
                    @endforeach
                </span>
            </div>
        @endif

        <ol class="hero-rail" aria-label="{{ __('مسار المشروع') }}">
            @foreach ($steps as $i => $step)
                @php $n = $i + 1; @endphp
                <li class="{{ $n < $reached ? 'is-done' : ($n === $reached ? 'is-current' : '') }}"
                    @if ($n === $reached) aria-current="step" @endif>
                    <span class="hero-rail-node" aria-hidden="true">
                        @if ($n < $reached)
                            <i class="ti ti-check"></i>
                        @endif
                    </span>
                    <span class="hero-rail-label">{{ $step['label'] }}</span>
                </li>
            @endforeach
        </ol>
    </div>

    <div class="stu-hero-foot">
        <span class="hero-chip {{ $deadline[0] }}">
            <i class="ti {{ $deadline[1] }}" aria-hidden="true"></i>
            {{ $deadline[2] }}
            @if ($project->date_line && $active && $project->status !== 'complete')
                <small>{{ $project->date_line->format('Y-m-d') }}</small>
            @endif
        </span>
        @if ($defense)
            {{-- رابط الاجتماع حين تكون عن بُعد أو مدمجة، وإضافتها إلى التقويم --}}
            <span class="hero-defense-links">
                @if ($defense->needsLink() && $defense->meeting_url)
                    <a href="{{ $defense->meeting_url }}" target="_blank" rel="noopener" class="btn btn-sm {{ $defense->isJoinable() ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="ti ti-video me-1" aria-hidden="true"></i>{{ $defense->isJoinable() ? __('انضم الآن') : __('رابط الاجتماع') }}
                    </a>
                @endif
                <a href="{{ $defense->googleCalendarUrl() }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-calendar-plus me-1" aria-hidden="true"></i>{{ __('تقويم Google') }}
                </a>
            </span>
        @endif

        <div class="stu-hero-actions">
            <a href="{{ route('student.discussion') }}" class="btn btn-outline-secondary">
                <i class="ti ti-messages me-1" aria-hidden="true"></i>
                {{ __('النقاش') }}
                @if ($unreadMsgs)
                    <span class="sidebar-count ms-1">{{ $unreadMsgs }}</span>
                @endif
            </a>
            @if ($active && ! $project->is_locked)
                <a href="#files" class="btn btn-primary">
                    <i class="ti ti-upload me-1" aria-hidden="true"></i>
                    {{ __('رفع ملف') }}
                </a>
            @endif
        </div>
    </div>
</section>

{{-- يمشي الفريق من آخر خطوة رآها الطالب إلى خطوته الآن — مرة عند كل تقدّم،
     لا في كل زيارة. أول زيارة: من البداية. --}}
@push('js')
    <script>
        (function () {
            var rail = document.querySelector('[data-journey-rail]');
            var team = rail && rail.querySelector('.rail-team');
            if (!team) return;
            var at = parseInt(rail.dataset.journeyAt, 10), seen = null;
            try { seen = localStorage.getItem(rail.dataset.journeyKey); localStorage.setItem(rail.dataset.journeyKey, at); } catch (e) {}
            var from = seen === null ? 0 : Math.min(parseInt(seen, 10) || 0, at);
            if (from === at || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            var legs = document.querySelector('.cast-defs');
            team.style.transition = 'none';
            team.style.setProperty('--i', from);
            void team.offsetWidth;
            team.style.transition = '';
            team.style.transitionDuration = Math.min(3.2, .9 * (at - from) + .4) + 's';
            setTimeout(function () {
                team.classList.add('is-walking');
                if (legs) legs.classList.add('is-walking');
                team.style.setProperty('--i', at);
            }, 600);
            team.addEventListener('transitionend', function () {
                team.classList.remove('is-walking');
                if (legs) legs.classList.remove('is-walking');
            }, { once: true });
        })();
    </script>
@endpush
