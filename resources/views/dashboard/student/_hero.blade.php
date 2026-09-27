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

    $deadline = match (true) {
        $project->status === 'complete' => ['is-done', 'ti-rosette-discount-check', 'اكتمل المشروع'],
        $project->status === 'request' => ['', 'ti-hourglass', 'بانتظار ردّ المشرف'],
        is_null($daysLeft) => ['', 'ti-calendar', 'لا موعد نهائي بعد'],
        $daysLeft < 0 => ['is-late', 'ti-alarm', 'تأخّر التسليم ' . abs($daysLeft) . ' يوماً'],
        $daysLeft === 0 => ['is-warn', 'ti-alarm', 'التسليم النهائي اليوم'],
        default => [$daysLeft <= 7 ? 'is-warn' : '', 'ti-calendar-due', 'التسليم النهائي بعد ' . $daysLeft . ' يوماً'],
    };
@endphp

<section class="stu-hero mb-4" aria-labelledby="hero-title">
    <div class="stu-hero-top">
        <div class="stu-hero-main">
            <p class="stu-hero-hello">
                أهلاً، {{ $firstName }}
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
                    {{ $members->count() }} {{ $members->count() === 1 ? 'عضو' : 'أعضاء' }}
                </a>
            </div>
        </div>

        {{-- الإنجاز: حلقة بنسبته — وبلا مراحل كلمة تشرح لا «—» --}}
        <div class="stu-hero-progress">
            @if ($active && ! is_null($progress))
                <span class="pct-ring is-xl" style="--p: {{ $progress }}" role="img" aria-label="الإنجاز {{ $progress }}%">
                    <svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="17" /><circle cx="20" cy="20" r="17" pathLength="100" /></svg>
                    <b>{{ $progress }}%</b>
                </span>
                <small>{{ $project->milestones->where('is_done', true)->count() }} من {{ $project->milestones->count() }} مراحل</small>
            @elseif ($active)
                <span class="hero-progress-empty" aria-hidden="true"><i class="ti ti-route"></i></span>
                <small>بانتظار خطة المشرف</small>
            @else
                <span class="hero-progress-empty" aria-hidden="true"><i class="ti ti-hourglass"></i></span>
                <small>قيد المراجعة</small>
            @endif
        </div>
    </div>

    {{-- المسار مضغوطاً: كان لوحاً كاملاً بعرض الصفحة --}}
    <ol class="hero-rail" aria-label="مسار المشروع">
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

    <div class="stu-hero-foot">
        <span class="hero-chip {{ $deadline[0] }}">
            <i class="ti {{ $deadline[1] }}" aria-hidden="true"></i>
            {{ $deadline[2] }}
            @if ($project->date_line && $active && $project->status !== 'complete')
                <small>{{ $project->date_line->format('Y-m-d') }}</small>
            @endif
        </span>

        <div class="stu-hero-actions">
            <a href="{{ route('student.discussion') }}" class="btn btn-outline-secondary">
                <i class="ti ti-messages me-1" aria-hidden="true"></i>
                النقاش
                @if ($unreadMsgs)
                    <span class="sidebar-count ms-1">{{ $unreadMsgs }}</span>
                @endif
            </a>
            @if ($active && ! $project->is_locked)
                <a href="#files" class="btn btn-primary">
                    <i class="ti ti-upload me-1" aria-hidden="true"></i>
                    رفع ملف
                </a>
            @endif
        </div>
    </div>
</section>
