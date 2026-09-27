{{-- آخر النشاط عبر المجموعات: ما حدث منذ آخر زيارة، بلا فتح كل مشروع --}}
<section class="ctx-card dash-panel" aria-labelledby="activity-title">
    <h2 class="ctx-head" id="activity-title">
        <i class="ti ti-activity" aria-hidden="true"></i>
        آخر النشاط
    </h2>

    @if ($activity->isEmpty())
        <p class="dash-panel-empty">
            {{ $groups->isEmpty() ? 'يظهر هنا ما تفعله مجموعاتك: ملفات ومراحل ورسائل.' : 'لا نشاط بعد في مجموعاتك.' }}
        </p>
    @else
        <ol class="activity-feed">
            @foreach ($activity as $event)
                <li>
                    <a href="{{ $event['href'] }}" class="activity-item">
                        <span class="activity-icon" aria-hidden="true"><i class="ti {{ $event['icon'] }}"></i></span>
                        <span class="activity-body">
                            <span class="activity-text">{{ $event['text'] }}</span>
                            <span class="activity-meta">
                                {{ $event['project']->title }} ·
                                <time datetime="{{ $event['at']->toIso8601String() }}">{{ $event['at']->diffForHumans() }}</time>
                            </span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
</section>
