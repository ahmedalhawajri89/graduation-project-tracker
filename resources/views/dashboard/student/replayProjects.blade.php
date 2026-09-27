@extends('layouts.admin.admin')
@section('title', 'الإشعارات')

@section('crumbs')
    <x-crumb :href="route('student.dashboard')">لوحتي</x-crumb>
    <x-crumb>الإشعارات</x-crumb>
@endsection

@section('content')

    @php
        // كل إشعار بفئته وأيقونته ومرسِله — ومجموعاً بيومه
        $items = $notifications->map(fn ($n) => \App\Support\NotificationView::present($n) + [
            'id' => $n->id,
            'at' => $n->created_at,
            'project' => $n->data['project'] ?? null,
            'new' => in_array($n->id, $newIds, true),
        ]);
        $groups = $items->groupBy(fn ($i) => \App\Support\NotificationView::dayGroup($i['at']));
        $counts = $items->countBy('source');
    @endphp

    <x-page-header title="الإشعارات"
        subtitle="{{ count($newIds) ? count($newIds) . ' جديد منذ آخر زيارة' : 'ردود المشرف وتحديثات مشروعك' }}" />

    @if ($items->isEmpty())
        <div class="card notif-empty-page">
            <span class="notif-empty-icon" aria-hidden="true"><i class="ti ti-bell"></i></span>
            <h2>لا إشعارات بعد</h2>
            <p>ستصلك هنا ردود المشرف على مقترحك وتسليماتك، ومواعيد المراحل، وتحديثات الإدارة.</p>
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-right me-1" aria-hidden="true"></i>
                العودة إلى لوحتي
            </a>
        </div>
    @else
        {{-- التصفية بالمصدر: ما قاله المشرف غير ما غيّرته الإدارة --}}
        <div class="filter-tabs notif-tabs mb-3" role="group" aria-label="تصفية الإشعارات">
            <button type="button" class="filter-tab is-active" data-notif-filter="all">
                الكل <span class="filter-count">{{ $items->count() }}</span>
            </button>
            @if (count($newIds))
                <button type="button" class="filter-tab" data-notif-filter="new">
                    الجديدة <span class="filter-count">{{ count($newIds) }}</span>
                </button>
            @endif
            <button type="button" class="filter-tab" data-notif-filter="supervisor">
                من المشرف <span class="filter-count">{{ $counts['supervisor'] ?? 0 }}</span>
            </button>
            @if ($counts['team'] ?? 0)
                <button type="button" class="filter-tab" data-notif-filter="team">
                    من الفريق <span class="filter-count">{{ $counts['team'] }}</span>
                </button>
            @endif
            <button type="button" class="filter-tab" data-notif-filter="admin">
                من الإدارة <span class="filter-count">{{ $counts['admin'] ?? 0 }}</span>
            </button>
        </div>

        <div class="notif-feed" id="notif-feed">
            @foreach ($groups as $day => $dayItems)
                <section class="notif-day" data-notif-day>
                    <h2 class="notif-day-title">{{ $day }}</h2>
                    <ol class="notif-list">
                        @foreach ($dayItems as $item)
                            <li data-source="{{ $item['source'] }}" data-new="{{ $item['new'] ? 1 : 0 }}">
                                <a href="{{ $item['href'] }}" class="notif-card {{ $item['new'] ? 'is-new' : '' }}">
                                    <span class="notif-card-icon {{ $item['tone'] }}" aria-hidden="true">
                                        <i class="ti {{ $item['icon'] }}"></i>
                                    </span>
                                    <span class="notif-card-body">
                                        <span class="notif-card-top">
                                            <b>{{ $item['title'] }}</b>
                                            @if ($item['new'])
                                                <span class="notif-new">جديد</span>
                                            @endif
                                            <time datetime="{{ $item['at']->toIso8601String() }}"
                                                title="{{ $item['at']->format('Y-m-d H:i') }}">
                                                {{ $item['at']->isToday() ? $item['at']->format('H:i') : $item['at']->diffForHumans() }}
                                            </time>
                                        </span>
                                        <span class="notif-card-text">{{ $item['body'] }}</span>
                                        <span class="notif-card-meta">
                                            <i class="ti {{ $item['source'] === 'admin' ? 'ti-building' : 'ti-user' }}" aria-hidden="true"></i>
                                            {{ $item['sender'] }}
                                            @if ($item['project'])
                                                · {{ $item['project'] }}
                                            @endif
                                        </span>
                                    </span>
                                    <i class="ti ti-chevron-left notif-card-go" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
            <p class="notif-none d-none" id="notif-none">لا إشعارات في هذا التصنيف.</p>
        </div>
    @endif

@endsection

@push('js')
    <script>
        (function () {
            var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-notif-filter]'));
            if (!tabs.length) return;
            var days = Array.prototype.slice.call(document.querySelectorAll('[data-notif-day]'));
            var none = document.getElementById('notif-none');

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('is-active'); });
                    tab.classList.add('is-active');
                    var f = tab.dataset.notifFilter, shown = 0;

                    days.forEach(function (day) {
                        var visible = 0;
                        day.querySelectorAll('li').forEach(function (li) {
                            var ok = f === 'all' || (f === 'new' ? li.dataset.new === '1' : li.dataset.source === f);
                            li.classList.toggle('d-none', !ok);
                            if (ok) visible++;
                        });
                        // يوم بلا ما يطابق يختفي بعنوانه
                        day.classList.toggle('d-none', visible === 0);
                        shown += visible;
                    });
                    none.classList.toggle('d-none', shown > 0);
                });
            });
        })();
    </script>
@endpush
