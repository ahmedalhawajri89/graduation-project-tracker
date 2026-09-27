@extends('layouts.admin.admin')
@section('title', 'لوحتي')

@section('crumbs')
    <x-crumb>لوحتي</x-crumb>
@endsection

@section('content')

    @php
        $groupsCount = $kpi['groups'];
        $pendingRequests = $requests->count();

        // سطر الحال تحت الاسم: ما يعرفه المشرف في نظرة، بلا أرقام صفرية
        $summary = [$semester->name];
        $summary[] = $groupsCount ? ($groupsCount === 1 ? 'مجموعة واحدة' : $groupsCount . ' مجموعات') : 'لا مجموعات بعد';
        $summary[] = $kpi['seats'] > 0
            ? ($kpi['seats'] === 1 ? 'مقعد متبقٍ' : $kpi['seats'] . ' مقاعد متبقية') . ' من ' . $kpi['max']
            : 'اكتملت مقاعدك';

        // حلقة السعة: المقبول من الحدّ
        $capPct = $kpi['max'] > 0 ? min(100, (int) round($groupsCount * 100 / $kpi['max'])) : 100;
    @endphp

    <x-page-header title="أهلاً، {{ $supervisor->name }}" subtitle="{{ implode(' · ', $summary) }}">
        <x-slot:actions>
            <a href="{{ route('supervisor.discussion') }}" class="btn btn-outline-secondary">
                <i class="ti ti-messages me-1" aria-hidden="true"></i>
                النقاش
                @if ($kpi['unread'])
                    <span class="sidebar-count ms-1">{{ $kpi['unread'] }}</span>
                @endif
            </a>
            <a href="{{ route('supervisor.plan', ['new' => 1]) }}#stage-new" class="btn btn-primary">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                مرحلة جديدة
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- مؤشرات تقود إلى فعل — كانت أربعة أرقام أغلبها أصفار --}}
    <div class="stat-strip dash-kpis mb-4">
        <div class="stat-cell kpi-cap">
            <span class="kpi-ring" style="--p: {{ $capPct }}" aria-hidden="true">
                <svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="17" /><circle cx="20" cy="20" r="17" pathLength="100" /></svg>
            </span>
            <span class="kpi-cap-body">
                <span class="stat-label">السعة</span>
                <span class="stat-value">{{ $groupsCount }}<small>/ {{ $kpi['max'] }}</small></span>
                <span class="stat-sub">
                    @if ($groupsCount > $kpi['max'])
                        تجاوزتَ الحدّ بـ{{ $groupsCount - $kpi['max'] }}
                    @elseif ($kpi['seats'] > 0)
                        {{ $kpi['seats'] === 1 ? 'مقعد متبقٍ' : $kpi['seats'] . ' مقاعد متبقية' }}
                    @else
                        اكتملت المقاعد
                    @endif
                </span>
            </span>
        </div>

        <div class="stat-cell">
            <span class="stat-label">متوسط الإنجاز</span>
            @if (is_null($kpi['avg']))
                <span class="stat-value is-muted">—</span>
                <span class="stat-sub">لا مراحل بعد</span>
            @else
                <span class="stat-value">{{ $kpi['avg'] }}%</span>
                <span class="ms-progress kpi-bar"><span style="width: {{ $kpi['avg'] }}%"></span></span>
            @endif
        </div>

        <div class="stat-cell">
            <span class="stat-label">مراحل متأخّرة</span>
            <span class="stat-value {{ $kpi['late'] ? 'is-late' : '' }}">{{ $kpi['late'] }}</span>
            <span class="stat-sub">
                @if ($kpi['late'])
                    في {{ $kpi['lateGroups'] === 1 ? 'مجموعة واحدة' : $kpi['lateGroups'] . ' مجموعات' }}
                @else
                    كل شيء في موعده
                @endif
            </span>
        </div>

        <a href="{{ route('supervisor.discussion') }}" class="stat-cell">
            <span class="stat-label">رسائل جديدة</span>
            <span class="stat-value {{ $kpi['unread'] ? 'is-brand' : '' }}">{{ $kpi['unread'] }}</span>
            <span class="stat-sub">{{ $kpi['unread'] ? 'في النقاش — افتحه' : 'لا جديد في النقاش' }}</span>
        </a>
    </div>

    @include('dashboard.supervisor._next-actions', [
        'groups' => $groups,
        'pendingRequests' => $pendingRequests,
    ])

    {{-- الطلبات قرارات لا إشعارات: تُعرض فوق المجموعات بزرّيها --}}
    @if ($pendingRequests)
        <section class="mb-4" aria-labelledby="pending-title">
            <div class="req-section-head">
                <h2 id="pending-title">
                    طلبات إشراف بانتظار ردّك
                    <span class="sidebar-count">{{ $pendingRequests }}</span>
                </h2>
                @if ($pendingRequests > 2)
                    <a href="{{ route('supervisor.showNotification') }}" class="ctx-head-link">كلّها ({{ $pendingRequests }})</a>
                @endif
            </div>
            <div class="req-list">
                @foreach ($requests->take(2) as $request)
                    @include('dashboard.supervisor._request-card', [
                        'project' => $request,
                        'seatsLeft' => $kpi['seats'],
                        'pending' => $pendingRequests,
                        'compact' => true,
                    ])
                @endforeach
            </div>
        </section>
    @endif

    <div class="dash-grid">
        <div class="dash-main">
            <div class="dash-section-head">
                <h2>مجموعاتي</h2>
                @if ($kpi['completed'])
                    <span class="dash-section-meta">{{ $kpi['completed'] }} مكتملة</span>
                @endif
            </div>

            @if ($groupsCount === 0)
                {{-- البداية: ثلاث خطوات بدل «لا مجموعات» وحدها --}}
                <div class="card dash-start">
                    <h3>ابدأ فصلك في ثلاث خطوات</h3>
                    <ol class="dash-steps">
                        <li>
                            <span class="dash-step-n">1</span>
                            <div>
                                <a href="{{ route('supervisor.plan') }}">جهّز خطة المراحل</a>
                                <p>مواعيد الفصل وقوالبه مرّة واحدة — تصل كل مجموعة تقبلها.</p>
                            </div>
                        </li>
                        <li>
                            <span class="dash-step-n">2</span>
                            <div>
                                <a href="{{ route('supervisor.showNotification') }}">راجع طلبات الإشراف</a>
                                <p>{{ $pendingRequests ? $pendingRequests . ' بانتظار ردّك الآن.' : 'تظهر هنا حين يرسلها الطلاب.' }}</p>
                            </div>
                        </li>
                        <li>
                            <span class="dash-step-n">3</span>
                            <div>
                                <span>تابع مجموعاتك</span>
                                <p>التقدّم والملفات والنقاش والتقييم — كلها من هذه الصفحة.</p>
                            </div>
                        </li>
                    </ol>
                </div>
            @else
                {{-- التصفية تظهر حين تستحقّ: أربع مجموعات تُمسح بالعين --}}
                @if ($groupsCount > 4)
                    <div class="filter-bar mb-3">
                        <div class="filter-tabs" role="group" aria-label="تصفية حسب الحالة">
                            <button type="button" class="filter-tab is-active" data-status-filter="all">
                                الكل
                                <span class="filter-count">{{ $groupsCount }}</span>
                            </button>
                            <button type="button" class="filter-tab" data-status-filter="accept">
                                <span class="filter-dot" style="background: {{ config('statuses.map.accept.hex') }}"></span>
                                قيد التنفيذ
                                <span class="filter-count">{{ $groups->where('status', 'accept')->count() }}</span>
                            </button>
                            <button type="button" class="filter-tab" data-status-filter="complete">
                                <span class="filter-dot" style="background: {{ config('statuses.map.complete.hex') }}"></span>
                                مكتملة
                                <span class="filter-count">{{ $kpi['completed'] }}</span>
                            </button>
                        </div>

                        <div class="filter-form">
                            <div class="filter-field filter-field--search">
                                <label class="form-label" for="groups-search">بحث</label>
                                <div class="filter-search-box">
                                    <i class="ti ti-search filter-search-icon" aria-hidden="true"></i>
                                    <input type="search" id="groups-search" class="form-control"
                                        placeholder="ابحث بعنوان المشروع أو نوعه…" aria-label="بحث في المجموعات">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="group-grid" id="groups-grid">
                    @foreach ($ranked as $project)
                        @include('dashboard.supervisor._group-card', [
                            'project' => $project,
                            'unread' => $unread[$project->id] ?? 0,
                        ])
                    @endforeach
                </div>

                <div class="card d-none" id="groups-no-results">
                    <x-empty-state icon="ti-search-off" title="لا نتائج مطابقة"
                        text="جرّب كلمة أخرى أو اعرض كل المجموعات." class="py-5" />
                </div>
            @endif
        </div>

        <aside class="dash-side">
            @include('dashboard.supervisor._upcoming')
            @include('dashboard.project._activity', [
                'emptyText' => $groups->isEmpty() ? 'يظهر هنا ما تفعله مجموعاتك: ملفات ومراحل ورسائل.' : 'لا نشاط بعد في مجموعاتك.',
            ])
        </aside>
    </div>

@endsection

@push('js')
    <script>
        // تصفية وبحث على البطاقات — كانت على صفوف جدول
        (function () {
            var grid = document.getElementById('groups-grid');
            if (!grid) return;

            var cards = Array.prototype.slice.call(grid.querySelectorAll('.group-card'));
            var search = document.getElementById('groups-search');
            var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-status-filter]'));
            var noResults = document.getElementById('groups-no-results');
            var status = 'all';

            function apply() {
                var q = search ? search.value.trim().toLowerCase() : '';
                var visible = 0;

                cards.forEach(function (card) {
                    var okStatus = status === 'all' || card.dataset.status === status;
                    var okText = q === '' || card.dataset.search.indexOf(q) !== -1;
                    var show = okStatus && okText;

                    card.classList.toggle('d-none', !show);
                    if (show) visible++;
                });

                grid.classList.toggle('d-none', visible === 0);
                if (noResults) noResults.classList.toggle('d-none', visible > 0);
            }

            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    tabs.forEach(function (t) { t.classList.remove('is-active'); });
                    tab.classList.add('is-active');
                    status = tab.dataset.statusFilter;
                    apply();
                });
            });

            if (search) search.addEventListener('input', apply);
        })();
    </script>
@endpush
