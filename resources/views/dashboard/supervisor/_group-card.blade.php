@php
    $days = $project->days_left;
    $overdue = $project->milestones->contains(
        fn ($m) => $m->isLate()
    );
    $ungraded = $project->status === 'complete' && is_null($project->grade);
    $progress = $project->progress;

    // المرحلة الحالية: أول غير منجزة — العلاقة مرتّبة بالموعد
    $current = $project->milestones->first(fn ($m) => ! $m->is_done);

    $members = $project->group->sortByDesc(fn ($g) => $g->type === 'leader')->values();
    $leader = $members->first(fn ($g) => $g->type === 'leader')?->student;

    // آخر نشاط: أحدث رسالة أو ملف أو مرحلة أُنجزت
    $lastAt = collect([
        $project->comments->first()?->created_at,
        $project->last_file_at ? \Illuminate\Support\Carbon::parse($project->last_file_at) : null,
        $project->milestones->max('done_at'),
    ])->filter()->max();
@endphp

{{-- بطاقة لا صفّ جدول: لثلاث إلى ستّ مجموعات يُهدر الجدول العرض
     ويضغط شريط التقدّم في عمود --}}
<article class="group-card {{ $overdue ? 'is-late' : '' }}"
    data-status="{{ $project->status }}"
    data-search="{{ mb_strtolower($project->title . ' ' . $project->project_type->name) }}">

    <header class="group-card-head">
        <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}" class="group-card-title">
            <span>{{ $project->title }}</span>
            <small>{{ $project->project_type->name }}</small>
        </a>

        {{-- الحلقة بدل الشارة: «مقبول» معروفة لكل مجموعة في هذه الصفحة --}}
        @if ($project->status === 'complete')
            <x-status-badge :status="$project->status" />
        @else
            <span class="pct-ring {{ is_null($progress) ? 'is-empty' : '' }}" style="--p: {{ $progress ?? 0 }}"
                title="{{ is_null($progress) ? 'لا مراحل بعد' : 'الإنجاز ' . $progress . '%' }}">
                <svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="17" /><circle cx="20" cy="20" r="17" pathLength="100" /></svg>
                <b>{{ is_null($progress) ? '—' : $progress . '%' }}</b>
            </span>
        @endif
    </header>

    <div class="group-card-team">
        <span class="avatar-stack">
            @foreach ($members->take(4) as $member)
                <x-avatar :user="$member->student" />
            @endforeach
            @if ($members->count() > 4)
                <span class="cell-avatar avatar-more" dir="ltr">+{{ $members->count() - 4 }}</span>
            @endif
        </span>
        <span class="group-card-team-text">
            {{ $members->count() }} {{ $members->count() === 1 ? 'عضو' : 'أعضاء' }}
            @if ($leader)
                <small>القائد: {{ $leader->name }}</small>
            @endif
        </span>
    </div>

    {{-- المرحلة الحالية: ما يعمل عليه الفريق الآن، لا نسبة وحدها --}}
    {{-- تسليم ينتظر مراجعتك يسبق كل شيء: هو العمل الذي عليك أنت --}}
    @php
        $reviewing = $project->milestones->first(fn ($m) => $m->isSubmitted());
        $current = $reviewing ?? $current;
        $stageTone = match (true) {
            ! $current => '',
            $current->isSubmitted() => 'is-review',
            $current->needsRevision() => 'is-revision',
            $current->isLate() => 'is-late',
            default => '',
        };
    @endphp
    <div class="group-card-stage {{ $stageTone }}">
        @if ($current)
            <i class="ti {{ ['is-review' => 'ti-inbox', 'is-revision' => 'ti-pencil', 'is-late' => 'ti-alert-triangle'][$stageTone] ?? 'ti-flag' }}" aria-hidden="true"></i>
            <span class="group-card-stage-title">{{ $current->title }}</span>
            @if ($stageTone === 'is-review')
                <a href="{{ route('supervisor.projects.show', $project->id) }}#milestone-{{ $current->id }}" class="group-card-stage-due is-link">راجِع التسليم</a>
            @elseif ($stageTone === 'is-revision')
                <span class="group-card-stage-due">مطلوب تعديل</span>
            @elseif ($current->due_date)
                <span class="group-card-stage-due">
                    {{ $stageTone === 'is-late' ? 'متأخّرة منذ ' . $current->due_date->diffInDays(today()) . ' يوماً' : $current->due_date->translatedFormat('j F') }}
                </span>
            @endif
        @elseif ($project->milestones->isNotEmpty())
            <i class="ti ti-circle-check" aria-hidden="true"></i>
            <span class="group-card-stage-title">أنجزت كل المراحل</span>
        @else
            <i class="ti ti-route" aria-hidden="true"></i>
            <span class="group-card-stage-title is-muted">لم تُضَف مراحل بعد</span>
            <a href="{{ route('supervisor.plan') }}" class="group-card-stage-due is-link">خطة المراحل</a>
        @endif
    </div>

    <div class="group-card-facts">
        <span class="group-fact {{ ! is_null($days) && $days < 0 ? 'is-late' : (! is_null($days) && $days <= 7 ? 'is-warn' : '') }}">
            <i class="ti ti-calendar-due" aria-hidden="true"></i>
            @if (is_null($days))
                بلا موعد نهائي
            @elseif ($days < 0)
                تأخّر {{ abs($days) }} يوماً
            @elseif ($days === 0)
                التسليم اليوم
            @else
                التسليم بعد {{ $days }} يوماً
            @endif
        </span>

        @if ($ungraded)
            <span class="group-fact is-warn">
                <i class="ti ti-award" aria-hidden="true"></i>
                بلا تقييم
            </span>
        @elseif (! is_null($project->grade))
            <span class="group-fact">
                <i class="ti ti-award" aria-hidden="true"></i>
                {{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }}/100
            </span>
        @endif

        <span class="group-fact">
            <i class="ti ti-activity" aria-hidden="true"></i>
            {{ $lastAt ? 'آخر نشاط ' . $lastAt->diffForHumans() : 'لا نشاط بعد' }}
        </span>
    </div>

    <footer class="group-card-actions">
        <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}">
            <i class="ti ti-layout-dashboard" aria-hidden="true"></i>
            فتح المشروع
        </a>
        <a href="{{ route('supervisor.discussion', $project->id) }}">
            <i class="ti ti-messages" aria-hidden="true"></i>
            النقاش
            @if ($unread)
                <span class="sidebar-count">{{ $unread }}</span>
            @endif
        </a>
    </footer>
</article>
