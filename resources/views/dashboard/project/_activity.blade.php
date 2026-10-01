{{--
    آخر النشاط — للمشرف عبر مجموعاته، وللطالب في مشروعه.

    @param \Illuminate\Support\Collection $activity  من \App\Support\ProjectActivity::recent
    @param string                         $emptyText
    @param bool                           $showProject  اسم المشروع في كل سطر (المشرف: مجموعات عدّة)
--}}

@php $showProject = $showProject ?? true; @endphp

<section class="ctx-card dash-panel" aria-labelledby="activity-title">
    <h2 class="ctx-head" id="activity-title">
        <i class="ti ti-activity" aria-hidden="true"></i>
        {{ __('آخر النشاط') }}
    </h2>

    @if ($activity->isEmpty())
        <p class="dash-panel-empty">{{ $emptyText }}</p>
    @else
        <ol class="activity-feed">
            @foreach ($activity as $event)
                <li>
                    <a href="{{ $event['href'] }}" class="activity-item">
                        <span class="activity-icon {{ $event['tone'] ?? '' }}" aria-hidden="true"><i class="ti {{ $event['icon'] }}"></i></span>
                        <span class="activity-body">
                            <span class="activity-text">{{ $event['text'] }}</span>
                            <span class="activity-meta">
                                @if ($showProject)
                                    {{ $event['project']->title }} ·
                                @endif
                                <time datetime="{{ $event['at']->toIso8601String() }}">{{ $event['at']->diffForHumans() }}</time>
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
</section>
