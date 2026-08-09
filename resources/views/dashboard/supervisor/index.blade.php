@extends('layouts.admin.admin')
@section('title', 'الصفحة الرئيسية')

@section('content')

    @php
        $groupsCount = $supervisor->projectsAccept->count();
        $seatsLeft = max(0, $supervisor->max_group - $groupsCount);
        $completedCount = $supervisor->projectsAccept->where('status', 'complete')->count();
        $pendingRequests = auth()->user()->unreadNotifications
            ->where('type', 'App\Notifications\SuperVisorRequestProjectNotify')
            ->count();
    @endphp

    {{-- ===== ترحيب ===== --}}
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">لوحة المشرف — الفصل: {{ $semester->name }}</div>
                <h2 class="page-title">أهلاً، {{ $supervisor->name }} 👋</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                @if ($pendingRequests > 0)
                    <a href="{{ route('supervisor.showNotification') }}" class="btn btn-primary">
                        <i class="ti ti-inbox me-1"></i>
                        {{ $pendingRequests }} طلب بانتظار ردّك
                    </a>
                @endif
                <a href="{{ route('supervisor.profile.edit') }}" class="btn btn-outline-primary">
                    <i class="ti ti-user-edit me-1"></i>
                    الملف الشخصي
                </a>
            </div>
        </div>
    </div>

    {{-- ===== مؤشرات سريعة ===== --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg bg-blue-lt text-blue rounded-3 me-3">
                        <i class="ti ti-users-group fs-2"></i>
                    </span>
                    <div>
                        <div class="h1 mb-0 lh-1">{{ $groupsCount }}</div>
                        <div class="text-secondary mt-1">مجموعاتي هذا الفصل</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg {{ $seatsLeft > 0 ? 'bg-green-lt text-green' : 'bg-red-lt text-red' }} rounded-3 me-3">
                        <i class="ti ti-armchair fs-2"></i>
                    </span>
                    <div>
                        <div class="h1 mb-0 lh-1">{{ $seatsLeft }}</div>
                        <div class="text-secondary mt-1">مقاعد متبقية من {{ $supervisor->max_group }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg bg-yellow-lt text-yellow rounded-3 me-3">
                        <i class="ti ti-clock-hour-4 fs-2"></i>
                    </span>
                    <div>
                        <div class="h1 mb-0 lh-1">{{ $pendingRequests }}</div>
                        <div class="text-secondary mt-1">طلبات معلقة</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg bg-purple-lt text-purple rounded-3 me-3">
                        <i class="ti ti-rosette-discount-check fs-2"></i>
                    </span>
                    <div>
                        <div class="h1 mb-0 lh-1">{{ $completedCount }}</div>
                        <div class="text-secondary mt-1">مشاريع مكتملة</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== جدول المجموعات: مقارنة التقدم ===== --}}
    <div class="card">
        <div class="card-header flex-wrap gap-2">
            <h3 class="card-title mb-0">
                <i class="ti ti-users-group me-2"></i>
                مجموعاتي
            </h3>
            <div class="card-actions d-flex flex-wrap gap-2 align-items-center">
                <div class="btn-group" role="group" aria-label="فلترة حسب الحالة">
                    <button type="button" class="btn btn-sm btn-outline-primary active" data-status-filter="all">الكل</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-status-filter="accept">قيد التنفيذ</button>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-status-filter="complete">مكتملة</button>
                </div>
                <input type="search" id="groups-search" class="form-control form-control-sm" style="max-width: 220px"
                    placeholder="ابحث بعنوان المشروع.." aria-label="بحث في المجموعات">
            </div>
        </div>

        @if ($supervisor->projectsAccept->count() === 0)
            <div class="empty py-5">
                <div class="empty-icon"><i class="ti ti-users-group fs-1"></i></div>
                <p class="empty-title">لا توجد مجموعات بعد</p>
                <p class="empty-subtitle text-secondary">
                    عندما تقبل طلبات المشاريع ستظهر مجموعاتك هنا.
                </p>
                @if ($pendingRequests > 0)
                    <div class="empty-action">
                        <a href="{{ route('supervisor.showNotification') }}" class="btn btn-primary">
                            <i class="ti ti-inbox me-1"></i>
                            مراجعة الطلبات المعلقة
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>المشروع</th>
                            <th>الحالة</th>
                            <th style="min-width: 160px">التقدم</th>
                            <th>الفريق</th>
                            <th>الموعد النهائي</th>
                            <th class="w-1">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($supervisor->projectsAccept as $project)
                            @php
                                $dl = $project->days_left;
                                $dlClass = is_null($dl) ? 'text-secondary' : ($dl < 0 || $dl <= 7 ? 'text-red' : ($dl <= 14 ? 'text-yellow' : 'text-secondary'));
                            @endphp
                            <tr class="groups-item"
                                data-title="{{ mb_strtolower($project->title . ' ' . $project->project_type->name) }}"
                                data-status="{{ $project->status }}">
                                <td>
                                    <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}"
                                        class="fw-bold text-reset d-block">
                                        {{ $project->title }}
                                    </a>
                                    <span class="text-secondary small">{{ $project->project_type->name }}</span>
                                </td>
                                <td><x-status-badge :status="$project->status" /></td>
                                <td>
                                    @if (is_null($project->progress))
                                        <span class="text-secondary small">لا مراحل بعد</span>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-fill" style="height: 8px">
                                                <div class="progress-bar bg-primary" role="progressbar"
                                                    style="width: {{ $project->progress }}%"
                                                    aria-valuenow="{{ $project->progress }}" aria-valuemin="0"
                                                    aria-valuemax="100"></div>
                                            </div>
                                            <span class="small text-secondary tabular-nums" style="min-width: 34px">
                                                {{ $project->progress }}%
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-blue-lt text-blue">
                                        <i class="ti ti-users me-1"></i>{{ $project->group->count() }}
                                    </span>
                                </td>
                                <td>
                                    @if ($project->date_line)
                                        <span class="{{ $dlClass }} small">
                                            {{ $project->date_line->format('Y-m-d') }}
                                            @if (!is_null($dl))
                                                ({{ $dl < 0 ? 'انقضى' : 'متبقي ' . $dl . ' يوم' }})
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-secondary small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        <i class="ti ti-settings me-1"></i>
                                        إدارة
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="empty py-4 d-none" id="groups-no-results">
                <div class="empty-icon"><i class="ti ti-search-off fs-1"></i></div>
                <p class="empty-subtitle text-secondary">لا توجد نتائج مطابقة للبحث/الفلتر.</p>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            // ===== بحث وفلترة صفوف المجموعات =====
            (function () {
                var search = document.getElementById('groups-search');
                var items = Array.prototype.slice.call(document.querySelectorAll('.groups-item'));
                var noResults = document.getElementById('groups-no-results');
                var filterBtns = Array.prototype.slice.call(document.querySelectorAll('[data-status-filter]'));
                var currentStatus = 'all';

                function apply() {
                    var q = (search ? search.value : '').trim().toLowerCase();
                    var visible = 0;
                    items.forEach(function (el) {
                        var matchText = el.getAttribute('data-title').indexOf(q) !== -1;
                        var matchStatus = currentStatus === 'all' || el.getAttribute('data-status') === currentStatus;
                        var show = matchText && matchStatus;
                        el.classList.toggle('d-none', !show);
                        if (show) visible++;
                    });
                    if (noResults) noResults.classList.toggle('d-none', visible > 0 || items.length === 0);
                }

                if (search) search.addEventListener('input', apply);

                filterBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        filterBtns.forEach(function (b) { b.classList.remove('active'); });
                        btn.classList.add('active');
                        currentStatus = btn.getAttribute('data-status-filter');
                        apply();
                    });
                });
            })();
        </script>
    @endpush

@stop
