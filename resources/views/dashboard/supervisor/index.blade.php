@extends('layouts.admin.admin')
@section('title', 'لوحتي')

@section('crumbs')
    <x-crumb>لوحتي</x-crumb>
@endsection

@section('content')

    @php
        $groups = $supervisor->projectsAccept;
        $groupsCount = $groups->count();
        $seatsLeft = max(0, $supervisor->max_group - $groupsCount);
        $completedCount = $groups->where('status', 'complete')->count();

        // من حالة المشروع لا من الإشعار: إشعار مقروء كان يُخفي طلباً معلّقاً
        $requests = $supervisor->pendingRequests()->with(['group.student', 'project_type'])->oldest()->get();
        $pendingRequests = $requests->count();
        $seatsExact = $supervisor->seatsLeft();

        // الترتيب يصير ذا معنى: المتأخّر أولاً، ثم الأقرب موعداً.
        // كان ترتيب العلاقة — فالمتأخّر والمستقرّ سواء.
        $ranked = $groups->sortBy(function ($project) {
            $overdue = $project->milestones->contains(
                fn ($m) => ! $m->is_done && $m->due_date && $m->due_date->isPast()
            );
            $days = $project->days_left;

            return [$overdue ? 0 : 1, is_null($days) ? 9999 : $days];
        })->values();
    @endphp

    <x-page-header title="أهلاً، {{ $supervisor->name }}" subtitle="{{ $semester->name }}" />

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
                        'seatsLeft' => $seatsExact,
                        'pending' => $pendingRequests,
                        'compact' => true,
                    ])
                @endforeach
            </div>
        </section>
    @endif

    {{-- شريط بدل أربع بطاقات بارتفاع ١٤٠ بكسل لأربعة أرقام --}}
    <div class="stat-strip mb-4">
        <div class="stat-cell">
            <span class="stat-label">مجموعاتي هذا الفصل</span>
            <span class="stat-value">{{ $groupsCount }}</span>
        </div>
        <div class="stat-cell">
            <span class="stat-label">المقاعد المتبقية</span>
            <span class="stat-value {{ $seatsLeft === 0 ? 'is-late' : '' }}">{{ $seatsLeft }}</span>
            {{-- «٠ من ٣» تُخفي مشرفاً بأربع مجموعات: التجاوز حالة يعرفها الأدمن فليعرفها صاحبها --}}
            <span class="stat-sub">
                من {{ $supervisor->max_group }}@if ($groupsCount > $supervisor->max_group) · تجاوزتَ الحدّ بـ{{ $groupsCount - $supervisor->max_group }}@endif
            </span>
        </div>
        <a href="{{ route('supervisor.showNotification') }}" class="stat-cell">
            <span class="stat-label">طلبات بانتظار ردّك</span>
            <span class="stat-value {{ $pendingRequests > 0 ? 'is-late' : '' }}">{{ $pendingRequests }}</span>
        </a>
        <div class="stat-cell">
            <span class="stat-label">مشاريع مكتملة</span>
            <span class="stat-value">{{ $completedCount }}</span>
        </div>
    </div>

    @if ($groupsCount === 0)
        <div class="card">
            <x-empty-state icon="ti-users-group" title="لا مجموعات بعد"
                text="حين تقبل طلب مشروع، تظهر مجموعته هنا لتتابعها: المراحل والملفات والنقاش والتقييم."
                class="py-6">
                @if ($pendingRequests > 0)
                    <x-slot:action>
                        <a href="{{ route('supervisor.showNotification') }}" class="btn btn-primary">
                            <i class="ti ti-inbox me-1" aria-hidden="true"></i>
                            مراجعة {{ $pendingRequests }} طلباً
                        </a>
                    </x-slot:action>
                @endif
            </x-empty-state>
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
                        <span class="filter-count">{{ $completedCount }}</span>
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
                @include('dashboard.supervisor._group-card', ['project' => $project])
            @endforeach
        </div>

        <div class="card d-none" id="groups-no-results">
            <x-empty-state icon="ti-search-off" title="لا نتائج مطابقة"
                text="جرّب كلمة أخرى أو اعرض كل المجموعات." class="py-5" />
        </div>
    @endif

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
