{{--
    شريط المشروع — للطالب وللمشرف.

    @param \App\Models\Project $project  بـ milestones
--}}

@php $daysLeft = $project->days_left; @endphp

{{-- شريط الحالة: ثلاث خانات لا يتكرّر ما فيها في موضع آخر.
     حجم الفريق في بطاقته، والحالة في المسار تحته. --}}
<div class="stat-strip is-3 mb-4">
    <div class="stat-cell">
        <span class="stat-label">نسبة الإنجاز</span>
        <span class="stat-value">{{ is_null($project->progress) ? '—' : $project->progress . '%' }}</span>
        @if ($project->milestones->count())
            <span class="stat-sub">
                {{ $project->milestones->where('is_done', true)->count() }} من {{ $project->milestones->count() }} مراحل
            </span>
        @endif
    </div>
    {{-- «المتبقّي للتسليم» لا «الموعد النهائي»: التسمية كانت تعد
         بتاريخ والقيمة تعطي مدّة --}}
    <div class="stat-cell">
        <span class="stat-label">المتبقّي للتسليم</span>
        <span class="stat-value {{ ! is_null($daysLeft) && $daysLeft < 0 && $project->status !== 'complete' ? 'is-late' : '' }}">
            @if ($project->status === 'complete')
                اكتمل
            @elseif (is_null($daysLeft))
                لم يُحدَّد
            @elseif ($daysLeft < 0)
                تأخّر {{ abs($daysLeft) }} يوماً
            @elseif ($daysLeft === 0)
                اليوم
            @else
                {{ $daysLeft }} يوماً
            @endif
        </span>
        @if ($project->date_line)
            <span class="stat-sub">{{ $project->date_line->format('Y-m-d') }}</span>
        @endif
    </div>
    {{-- المرحلة التالية بدل «حالة المشروع»: الحالة يقولها المسار تحته،
         والتالية لا تُقال في موضع آخر --}}
    @php
        $next = $project->milestones->first(fn ($m) => ! $m->is_done);
    @endphp
    <a href="#milestones" class="stat-cell">
        <span class="stat-label">المرحلة التالية</span>
        <span class="stat-value is-text">
            {{ $next?->title ?? ($project->milestones->count() ? 'أُنجزت كلّها' : 'لم تُحدَّد') }}
        </span>
        @if ($next?->due_date)
            <span class="stat-sub {{ $next->due_date->isPast() ? 'text-danger' : '' }}">
                {{ $next->due_date->isPast() ? 'فات موعدها ' : 'حتى ' }}{{ $next->due_date->format('Y-m-d') }}
            </span>
        @endif
    </a>
</div>
