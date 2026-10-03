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
