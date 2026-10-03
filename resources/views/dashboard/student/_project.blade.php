@php
    $graded = ! is_null($project->grade);

    // المناقشة (مجدولة أو منتهية): بطاقتها وخطوتها في المسار
    $defense = \App\Support\DefenseGrading::defenseFor($project)?->load(['room', 'members.supervisor']);

    // موضع المشروع على مساره — المناقشة خطوة بين التنفيذ والدرجة
    $steps = [
        ['key' => 'request', 'label' => __('تقديم الطلب')],
        ['key' => 'accept', 'label' => __('موافقة المشرف')],
        ['key' => 'work', 'label' => __('التنفيذ والمتابعة')],
        ['key' => 'defense', 'label' => __('المناقشة')],
        ['key' => 'grade', 'label' => __('الدرجة')],
    ];
    // موضع الخطوة **الحالية**: ما قبلها منجز. المكتمل ينتظر مناقشته (4)،
    // وبعد أن تجري ينتظر درجة اللجنة (5)، والمقيَّم أنهى المسار (6)
    $reached = match ($project->status) {
        'request' => 2,
        'accept' => 3,
        'complete' => $graded ? 6 : ($defense && $defense->endsAt()->isPast() ? 5 : 4),
        default => 0,
    };
    $active = in_array($project->status, ['accept', 'complete'], true);
@endphp

{{-- البطاقة الرئيسية بدل ترويسة التحيّة وشريط الأرقام ولوح المسار --}}
@include('dashboard.student._hero', [
    'project' => $project,
    'student' => $student,
    'semester' => $semester,
    'steps' => $steps,
    'reached' => $reached,
    'unreadMsgs' => $unreadMsgs ?? 0,
])

{{-- بطاقة المناقشة: من جدولتها حتى رصد درجتها --}}
@if ($defense && ! $graded)
    @include('dashboard.student._defense', ['defense' => $defense, 'project' => $project])
@endif

@include('dashboard.student._next-actions', ['project' => $project])

{{-- التقييم — نفس \u200E.grade-panel\u200E، وكان تدرّجاً بنفسجياً في \u200Estyle\u200E --}}
@if ($graded)
    <section class="grade-panel mb-4">
        <div class="grade-score">
            <span class="grade-num">{{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }}</span>
            <span class="grade-of">{{ __('من 100') }}</span>
        </div>
        <div class="grade-body">
            <div class="grade-head">
                {{ __('التقييم النهائي') }}
                <span class="grade-label">{{ $project->grade_label }}</span>
                @if ($project->isGradeLocked())
                    <span class="grade-locked">
                        <i class="ti ti-lock-check" aria-hidden="true"></i>
                        {{ __('معتمدة') }}
                    </span>
                @endif
            </div>
            @if ($project->evaluation_note)
                <p class="grade-note">{{ $project->evaluation_note }}</p>
            @endif
            <div class="grade-date">{{ __('قُيّم بتاريخ :date', ['date' => $project->evaluated_at?->format('Y-m-d')]) }}</div>
        </div>
    </section>
@endif

{{-- لحظة التخرّج: أول مرة يفتح فيها الطالب لوحته بعد رصد درجته — نافذة
     احتفال بقصاصات ملوّنة وفريقه يرمي قبعاته والدرجة تُعدّ. مرة واحدة لكل
     مشروع (يحفظها المتصفّح)، وتُغلق بـ Esc أو بالزرّ أو بالنقر خارجها. --}}
@if ($graded)
    @php
        $gradeText = rtrim(rtrim(number_format($project->grade, 2), '0'), '.');
        $party = $project->group->sortBy(fn ($m) => $m->type === 'leader' ? 0 : 1)->take(3)->values()
            ->map(fn ($m, $i) => $m->student?->gender === 'female' ? 'hj-girl' : ($i % 2 ? 'hj-boy2' : 'hj-boy'));
        $partyX = match ($party->count()) { 1 => [0], 2 => [-24, 24], default => [-48, 0, 48] };
    @endphp
    <dialog class="celebrate" data-celebrate="celebrate:{{ $project->id }}:{{ $gradeText }}" aria-labelledby="celebrate-title">
        <div class="celebrate-card">
            <div class="celebrate-confetti" aria-hidden="true"></div>
            <svg class="celebrate-team" viewBox="-84 -116 168 122" aria-hidden="true">
                <ellipse cx="0" cy="2" rx="80" ry="5" fill="#2563eb" opacity=".08" />
                @foreach ($party as $i => $who)
                    <g transform="translate({{ $partyX[$i] }} 0)">
                        <use href="#{{ $who }}" />
                        <g transform="translate(0 {{ $who === 'hj-girl' ? -70 : -73 }})"><g class="celebrate-cap" style="animation-delay: {{ .35 + $i * .12 }}s"><use href="#hj-cap" /></g></g>
                    </g>
                @endforeach
            </svg>
            <p class="celebrate-kicker">{{ __('مبروك!') }}</p>
            <h2 class="celebrate-title" id="celebrate-title">{{ __('رُصدت درجة مشروعك') }}</h2>
            <div class="celebrate-grade">
                <b data-count-to="{{ $gradeText }}">{{ $gradeText }}</b>
                <span>{{ __('من 100') }}</span>
                @if ($project->grade_label)
                    <span class="celebrate-label">{{ $project->grade_label }}</span>
                @endif
            </div>
            <p class="celebrate-text">{{ __('رحلة «:title» اكتملت — من الفكرة حتى الدرجة.', ['title' => $project->title]) }}</p>
            <button type="button" class="btn btn-primary celebrate-ok" data-celebrate-close>{{ __('عرض التفاصيل') }}</button>
        </div>
    </dialog>

    @push('js')
        <script>
            (function () {
                var dlg = document.querySelector('[data-celebrate]');
                if (!dlg || !dlg.showModal) return;
                var key = dlg.dataset.celebrate;
                try { if (localStorage.getItem(key)) return; localStorage.setItem(key, '1'); } catch (e) { return; }
                var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                // قصاصات بألوان المنصة، كلٌّ بمسار وتوقيت مختلف
                var box = dlg.querySelector('.celebrate-confetti');
                var colors = ['#2563eb', '#7c3aed', '#f59e0b', '#10b981', '#ec4899', '#60a5fa'];
                if (!still) for (var i = 0; i < 46; i++) {
                    var c = document.createElement('i');
                    c.style.setProperty('--x', (Math.random() * 100) + '%');
                    c.style.setProperty('--drift', (Math.random() * 120 - 60) + 'px');
                    c.style.setProperty('--spin', (Math.random() * 720 - 360) + 'deg');
                    c.style.setProperty('--d', (1.6 + Math.random() * 1.4) + 's');
                    c.style.setProperty('--delay', (Math.random() * .5) + 's');
                    c.style.background = colors[i % colors.length];
                    if (i % 3 === 0) c.style.borderRadius = '50%';
                    box.appendChild(c);
                }

                // الدرجة تُعدّ من الصفر
                var num = dlg.querySelector('[data-count-to]');
                var to = parseFloat(num.dataset.countTo), dec = (num.dataset.countTo.split('.')[1] || '').length;
                if (!still) {
                    num.textContent = '0';
                    var t0 = null;
                    setTimeout(function () {
                        requestAnimationFrame(function step(now) {
                            if (t0 === null) t0 = now;
                            var k = Math.min(1, (now - t0) / 1200);
                            num.textContent = (to * (1 - Math.pow(1 - k, 3))).toFixed(dec);
                            if (k < 1) requestAnimationFrame(step);
                        });
                    }, 350);
                }

                dlg.querySelector('[data-celebrate-close]').addEventListener('click', function () { dlg.close(); });
                dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });
                dlg.addEventListener('close', function () {
                    var panel = document.querySelector('.grade-panel');
                    if (panel) panel.scrollIntoView({ behavior: still ? 'auto' : 'smooth', block: 'center' });
                });
                setTimeout(function () { dlg.showModal(); dlg.querySelector('[data-celebrate-close]').focus(); }, 500);
            })();
        </script>
    @endpush
@endif

{{-- عمودان: المتن ما يُعمل عليه، والجانب ما يُرجَع إليه --}}
<div class="dash-grid">

    <div class="dash-main">
        {{-- ما يعمل عليه الفريق الآن: كان مخفيّاً في قائمة المراحل بالوزن نفسه --}}
        @if ($active)
            @include('dashboard.student._current-stage', ['project' => $project])
        @endif

        @include('dashboard.project._milestones', ['project' => $project])
        @include('dashboard.project._files', ['project' => $project, 'role' => 'student'])
        {{-- النقاش تبويب مستقلّ في الشريط الجانبي — كان هنا يطول بالرسائل --}}
    </div>

    <aside class="dash-side">
        @include('dashboard.project._activity', [
            'activity' => $activity ?? collect(),
            'showProject' => false,
            'emptyText' => __('يظهر هنا ما يحدث في مشروعكم: ملفات وتسليمات وردود المشرف ورسائل.'),
        ])

        @include('dashboard.project._team-card', ['project' => $project, 'role' => 'student'])

        {{-- العنوان في البطاقة الرئيسية — هنا الوصف وحده، وسحب الطلب --}}
        @if ($project->description || $project->status === 'request')
            <div class="ctx-card" id="project-facts">
                <div class="ctx-head">
                    <i class="ti ti-file-description" aria-hidden="true"></i>
                    {{ __('عن المشروع') }}
                </div>
                @if ($project->description)
                    @include('dashboard.project._clamp', ['text' => $project->description])
                @endif

                {{-- سحب الطلب: القائد وحده وقبل ردّ المشرف — كان الفريق يعلق إن لم يردّ --}}
                @if ($project->status === 'request'
                    && $project->group->contains(fn ($g) => $g->type === 'leader' && (int) $g->student_id === (int) auth('student')->id()))
                    <form action="{{ route('student.project.withdraw', $project->id) }}" method="POST" class="ctx-form">
                        @csrf
                        <p class="withdraw-note">{{ __('لم يردّ المشرف بعد؟ يمكنك سحب الطلب وتقديمه لمشرف آخر.') }}</p>
                        <button type="submit" class="btn btn-outline-danger w-100"
                            data-confirm-title="{{ __('سحب الطلب') }}" data-confirm-ok="{{ __('سحب الطلب') }}"
                            data-confirm="{{ __('سحب الطلب يحذفه ويحرّر أعضاء الفريق، ويُبلَّغون بذلك. لا تراجع عنه. متابعة؟') }}">
                            <i class="ti ti-arrow-back-up me-1" aria-hidden="true"></i>
                            {{ __('سحب الطلب') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </aside>
</div>
