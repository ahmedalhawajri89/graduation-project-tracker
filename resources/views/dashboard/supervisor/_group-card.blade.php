@php
    $days = $project->days_left;
    $overdue = $project->milestones->contains(
        fn ($m) => ! $m->is_done && $m->due_date && $m->due_date->isPast()
    );
    $ungraded = $project->status === 'complete' && is_null($project->grade);
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
        <x-status-badge :status="$project->status" />
    </header>

    <div class="group-card-progress">
        @if (is_null($project->progress))
            <span class="group-card-nostage">لم تُضَف مراحل بعد</span>
        @else
            <span class="ms-progress"><span style="width: {{ $project->progress }}%"></span></span>
            <span class="group-card-pct">{{ $project->progress }}%</span>
        @endif
    </div>

    <div class="group-card-facts">
        <span class="group-fact">
            <i class="ti ti-users" aria-hidden="true"></i>
            {{ $project->group->count() }} أعضاء
        </span>

        <span class="group-fact {{ ! is_null($days) && $days < 0 ? 'is-late' : (! is_null($days) && $days <= 7 ? 'is-warn' : '') }}">
            <i class="ti ti-calendar-due" aria-hidden="true"></i>
            @if (is_null($days))
                بلا موعد
            @elseif ($days < 0)
                تأخّر {{ abs($days) }} يوماً
            @elseif ($days === 0)
                التسليم اليوم
            @else
                {{ $days }} يوماً
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
    </div>

    <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}" class="group-card-go">
        <i class="ti ti-settings" aria-hidden="true"></i>
        إدارة المشروع
    </a>
</article>
