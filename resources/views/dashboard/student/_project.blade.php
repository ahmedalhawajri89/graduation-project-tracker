@php
    $graded = ! is_null($project->grade);

    // موضع المشروع على مساره
    $stages = [
        ['key' => 'request', 'label' => 'تقديم الطلب'],
        ['key' => 'accept', 'label' => 'موافقة المشرف'],
        ['key' => 'work', 'label' => 'التنفيذ والمتابعة'],
        ['key' => 'grade', 'label' => 'التقييم'],
    ];
    // موضع الخطوة **الحالية**: ما قبلها منجز. كان يعطي رقم آخر خطوة
    // منجزة، فالمشروع المقبول يعرض «موافقة المشرف» كأنها لم تتمّ
    $reached = match ($project->status) {
        'request' => 2,
        'accept' => 3,
        'complete' => $graded ? 5 : 4,
        default => 0,
    };
@endphp

@include('dashboard.student._next-actions', ['project' => $project])

@include('dashboard.project._stat-strip', ['project' => $project])

{{-- المسار — نفس \u200E.rail\u200E المستعمل في صفحة الأدمن --}}
<section class="rail-card mb-4">
    <ol class="rail-steps">
        @foreach ($stages as $i => $stage)
            @php $n = $i + 1; @endphp
            <li class="rail-step {{ $n < $reached ? 'is-done' : ($n === $reached ? 'is-current' : '') }}">
                <span class="rail-node" aria-hidden="true">
                    @if ($n < $reached)
                        <i class="ti ti-check"></i>
                    @endif
                </span>
                <span class="rail-label">{{ $stage['label'] }}</span>
            </li>
        @endforeach
    </ol>
</section>

{{-- التقييم — نفس \u200E.grade-panel\u200E، وكان تدرّجاً بنفسجياً في \u200Estyle\u200E --}}
@if ($graded)
    <section class="grade-panel mb-4">
        <div class="grade-score">
            <span class="grade-num">{{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }}</span>
            <span class="grade-of">من 100</span>
        </div>
        <div class="grade-body">
            <div class="grade-head">
                التقييم النهائي
                <span class="grade-label">{{ $project->grade_label }}</span>
                @if ($project->isGradeLocked())
                    <span class="grade-locked">
                        <i class="ti ti-lock-check" aria-hidden="true"></i>
                        معتمدة
                    </span>
                @endif
            </div>
            @if ($project->evaluation_note)
                <p class="grade-note">{{ $project->evaluation_note }}</p>
            @endif
            <div class="grade-date">قُيّم بتاريخ {{ $project->evaluated_at?->format('Y-m-d') }}</div>
        </div>
    </section>
@endif

{{-- عمودان: المتن ما يُعمل عليه، والجانب ما يُرجَع إليه.
     كان كل شيء عموداً واحداً بالوزن نفسه. --}}
<div class="work-grid">

    <div class="work-main">
        @include('dashboard.project._milestones', ['project' => $project])
        @include('dashboard.project._files', ['project' => $project, 'role' => 'student'])
        {{-- النقاش تبويب مستقلّ في الشريط الجانبي — كان هنا يطول بالرسائل --}}
    </div>

    {{-- بطاقتان بدل أربع: المواعيد في الشريط أعلاه، والردود في الجرس
         و«ماذا عليّ الآن» — كان الجانب ١٢٧٠ بكسل والمتن ٧٤٠ --}}
    <aside class="work-side">
        <div class="ctx-card" id="project-facts">
            <div class="ctx-head">
                <i class="ti ti-briefcase" aria-hidden="true"></i>
                مشروع التخرج
            </div>
            <div class="proj-title-cell">
                <h3>{{ $project->title }}</h3>
                <span>{{ $project->project_type->name }} · قُدّم {{ $project->created_at->format('Y-m-d') }}</span>
            </div>
            @if ($project->description)
                @include('dashboard.project._clamp', ['text' => $project->description])
            @endif

            {{-- سحب الطلب: القائد وحده وقبل ردّ المشرف — كان الفريق يعلق إن لم يردّ --}}
            @if ($project->status === 'request'
                && $project->group->contains(fn ($g) => $g->type === 'leader' && (int) $g->student_id === (int) auth('student')->id()))
                <form action="{{ route('student.project.withdraw', $project->id) }}" method="POST" class="ctx-form">
                    @csrf
                    <p class="withdraw-note">لم يردّ المشرف بعد؟ يمكنك سحب الطلب وتقديمه لمشرف آخر.</p>
                    <button type="submit" class="btn btn-outline-danger w-100"
                        onclick="return confirm('سحب الطلب يحذفه ويحرّر أعضاء الفريق، ويُبلَّغون بذلك. لا تراجع عنه. متابعة؟')">
                        <i class="ti ti-arrow-back-up me-1" aria-hidden="true"></i>
                        سحب الطلب
                    </button>
                </form>
            @endif
        </div>

        @include('dashboard.project._team-card', ['project' => $project, 'role' => 'student'])
    </aside>
</div>
