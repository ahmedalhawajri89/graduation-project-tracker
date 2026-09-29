{{-- القادم خلال أسبوعين: مراحل الخطة والمواعيد النهائية — وبلا خطة، دعوة إليها --}}
<section class="ctx-card dash-panel" aria-labelledby="upcoming-title">
    <h2 class="ctx-head" id="upcoming-title">
        <i class="ti ti-calendar-event" aria-hidden="true"></i>
        القادم
        <span class="dash-panel-hint">أسبوعان</span>
        <a href="{{ route('supervisor.plan') }}" class="ctx-head-link">الخطة</a>
    </h2>

    @if (! $hasPlan && $upcoming->isEmpty())
        <div class="plan-invite">
            <span class="plan-invite-icon" aria-hidden="true"><i class="ti ti-route"></i></span>
            <h3>ارسم خطة الفصل مرّة واحدة</h3>
            <p>كل مرحلة بموعدها وقالبها تصل مجموعاتك كلها — ومن تقبله لاحقاً.</p>
            <a href="{{ route('supervisor.plan', ['new' => 1]) }}#stage-new" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                أول مرحلة
            </a>
        </div>
    @elseif ($upcoming->isEmpty())
        <p class="dash-panel-empty">لا مواعيد خلال الأسبوعين القادمين.</p>
    @else
        <ol class="upcoming-list">
            @foreach ($upcoming as $item)
                @php $d = (int) today()->diffInDays($item['date'], false); @endphp
                <li>
                    <a href="{{ $item['href'] }}" class="upcoming-item {{ $item['kind'] === 'defense' ? 'is-defense' : '' }}"
                        @if ($item['kind'] === 'defense' && str_starts_with($item['href'], 'https://calendar.google.com')) target="_blank" rel="noopener" title="أضف إلى تقويم Google" @endif>
                        <span class="upcoming-date {{ $d <= 2 ? 'is-soon' : '' }}">
                            <b>{{ $item['date']->format('j') }}</b>
                            <small>{{ $item['date']->translatedFormat('M') }}</small>
                        </span>
                        <span class="upcoming-body">
                            <span class="upcoming-title">
                                @if ($item['kind'] === 'defense')
                                    <i class="ti ti-presentation" aria-hidden="true"></i>
                                @endif
                                {{ $item['title'] }}
                            </span>
                            <span class="upcoming-meta">
                                {{ $d === 0 ? 'اليوم' : ($d === 1 ? 'غداً' : ($d === 2 ? 'بعد يومين' : 'بعد ' . $d . ($d <= 10 ? ' أيام' : ' يوماً'))) }}
                                @if ($item['kind'] === 'defense')
                                    · {{ $item['meta'] }} · {{ $item['role'] }}
                                @endif
                                @if ($item['kind'] === 'stage' && $item['total'])
                                    · أنجزتها {{ $item['done'] }} من {{ $item['total'] }}
                                @endif
                            </span>
                            @if ($item['kind'] === 'stage' && $item['total'])
                                <span class="ms-progress upcoming-bar"><span style="width: {{ (int) round($item['done'] * 100 / $item['total']) }}%"></span></span>
                            @endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ol>
    @endif
</section>
