@extends('layouts.admin.admin')
@section('title', 'الصفحة الرئيسية')

@section('content')
    @php
        $pending = (int) ($project_status['request'] ?? 0);
    @endphp

    <x-page-header pretitle="لوحة التحكم — الفصل: {{ $semester->name }}" title="أهلاً، {{ auth()->user()->name }} 👋">
        <x-slot:actions>
            <a href="{{ route('site.home') }}" class="btn btn-outline-primary">
                <i class="ti ti-world me-1"></i>
                عرض الموقع
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- إجراءات سريعة --}}
    <div class="card mb-4">
        <div class="card-body py-3 d-flex flex-wrap align-items-center gap-2">
            <span class="text-secondary fw-bold me-2">
                <i class="ti ti-bolt me-1"></i>
                إجراءات سريعة:
            </span>
            <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-user-plus me-1"></i> إضافة طالب
            </a>
            <a href="{{ route('admin.supervisors.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-user-star me-1"></i> إضافة مشرف
            </a>
            <a href="{{ route('admin.semesters.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-calendar-plus me-1"></i> فصل دراسي
            </a>
            <a href="{{ route('admin.specialize.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-category-plus me-1"></i> تخصص
            </a>
            <a href="{{ route('admin.groups.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-users-group me-1"></i> المجموعات
            </a>
            <a href="{{ route('admin.contact.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-mail me-1"></i> الرسائل
            </a>
        </div>
    </div>

    {{-- ما الذي يحتاج انتباه؟ --}}
    @if ($not_has_group > 0 || $pending > 0)
        <div class="row row-cards mb-4">
            <div class="col-md-6">
                <x-attention-card icon="ti-user-exclamation" :count="$not_has_group"
                    title="طالب بدون مجموعة" text="لم ينضمّوا إلى أي فريق بعد ويحتاجون متابعة."
                    :href="route('admin.students.index')" tone="warning" />
            </div>
            <div class="col-md-6">
                <x-attention-card icon="ti-clock-hour-4" :count="$pending"
                    title="طلب مشروع بانتظار المراجعة" text="طلبات مشاريع لم يُبتّ فيها بعد لهذا الفصل."
                    :href="route('admin.groups.index')" tone="azure" cta="مراجعة الطلبات" />
            </div>
        </div>
    @endif

    {{-- مؤشّرات رئيسية --}}
    <div class="row row-deck row-cards mb-4">
        <div class="col-6 col-lg-3">
            <x-kpi-card icon="ti-school" tone="blue" :value="$student_count" label="عدد الطلاب"
                :href="route('admin.students.index')" :trend="$trends['students'] ?? null"
                sub="منضمّ لفرق: {{ $has_group }} · بدون مجموعة: {{ $not_has_group }}" />
        </div>
        <div class="col-6 col-lg-3">
            <x-kpi-card icon="ti-user-star" tone="purple" :value="$supervisor_count" label="عدد المشرفين"
                :href="route('admin.supervisors.index')" :trend="$trends['supervisors'] ?? null" />
        </div>
        <div class="col-6 col-lg-3">
            <x-kpi-card icon="ti-users-group" tone="orange" :value="$project_count" label="المجموعات النشطة"
                :href="route('admin.groups.index')" :trend="$trends['groups'] ?? null"
                sub="قيد المراجعة: {{ $pending }} طلب" />
        </div>
        <div class="col-6 col-lg-3">
            <x-kpi-card icon="ti-mail" tone="red" :value="$msg_count" label="رسائل الاستفسار"
                :href="route('admin.contact.index')" :trend="$trends['messages'] ?? null" />
        </div>
    </div>

    @php
        // مصدر واحد للحالات: config/statuses.php
        $statusOrder = config('statuses.order');
        $statusMap = config('statuses.map');
        $statusLabels = [];
        $statusValues = [];
        $statusFills = [];
        $statusTotal = 0;
        foreach ($statusOrder as $st) {
            $count = (int) ($project_status[$st] ?? 0);
            $statusLabels[] = __('site.' . $st);
            $statusValues[] = $count;
            $statusFills[] = $statusMap[$st]['hex'];
            $statusTotal += $count;
        }
    @endphp

    <div class="row row-deck row-cards mb-4">

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-chart-donut me-2"></i>
                        حالات مشاريع الفصل الحالي — {{ $semester->name }}
                    </h3>
                </div>
                <div class="card-body">
                    @if ($statusTotal > 0)
                        <div class="chart-wrap">
                            <div id="chart-status"></div>
                            <div class="chart-skeleton skeleton" data-skel="chart-status"></div>
                        </div>
                    @else
                        <x-empty-state icon="ti-chart-donut" title="لا توجد مشاريع بعد"
                            text="لا توجد مشاريع مسجّلة في هذا الفصل حتى الآن." class="py-5" />
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-chart-bar me-2"></i>
                        عدد الطلبة حسب التخصص
                    </h3>
                </div>
                <div class="card-body">
                    @if ($specializes->count() > 0)
                        <div class="chart-wrap">
                            <div id="chart-specialize"></div>
                            <div class="chart-skeleton skeleton" data-skel="chart-specialize"></div>
                        </div>
                    @else
                        <x-empty-state icon="ti-chart-bar" title="لا توجد تخصصات بعد"
                            text="أضف تخصّصات لعرض توزيع الطلبة." class="py-5" />
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row row-deck row-cards">

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-briefcase me-2"></i>
                        الطلبة حسب نوع المشروع — {{ $semester->name }}
                    </h3>
                    <div class="card-actions">
                        <span class="text-secondary small">{{ $project_types->count() }} أنواع</span>
                    </div>
                </div>
                @if ($project_types->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table table-hover">
                            <thead>
                                <tr>
                                    <th class="w-1">#</th>
                                    <th>اسم المشروع</th>
                                    <th>عدد المشاريع</th>
                                    <th>عدد الطلبة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($project_types as $type)
                                    <tr>
                                        <td class="text-secondary">{{ $loop->iteration }}</td>
                                        <td class="fw-medium">{{ $type->name }}</td>
                                        <td><span class="badge bg-orange-lt">{{ $type->projects_count }}</span></td>
                                        <td><span class="badge bg-blue-lt">{{ $type->projects->sum('group_count') }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <x-empty-state icon="ti-briefcase-off" title="لا توجد مشاريع بعد"
                        text="لم تُسجّل أي مشاريع لهذا الفصل حتى الآن." class="py-5" />
                @endif
            </div>
        </div>

        {{-- ودجت أحدث النشاطات --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-activity me-2"></i>
                        أحدث الطلبات
                    </h3>
                </div>
                @if ($recent_projects->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach ($recent_projects as $project)
                            <div class="list-group-item">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="avatar avatar-sm bg-primary-lt text-primary rounded-3 mt-1">
                                        <i class="ti ti-file-text"></i>
                                    </span>
                                    <div class="me-auto min-w-0">
                                        <div class="fw-medium text-truncate">{{ $project->title }}</div>
                                        <div class="text-secondary small text-truncate">
                                            <i class="ti ti-user-star me-1"></i>{{ $project->supervisor->name ?: 'بلا مشرف' }}
                                        </div>
                                        <div class="text-secondary small mt-1">
                                            <i class="ti ti-clock me-1"></i>{{ $project->created_at?->diffForHumans() }}
                                        </div>
                                    </div>
                                    <x-status-badge :status="$project->status" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-empty-state icon="ti-inbox" title="لا توجد طلبات بعد"
                        text="ستظهر أحدث طلبات المشاريع هنا." class="py-5" />
                @endif
            </div>
        </div>
    </div>

    {{-- آخر رسائل الاستفسار --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-mail me-2"></i>
                آخر رسائل الاستفسار
            </h3>
            <div class="card-actions">
                <a href="{{ route('admin.contact.index') }}" class="btn btn-sm btn-outline-primary">
                    عرض الكل
                </a>
            </div>
        </div>
        @if ($recent_messages->count() > 0)
            <div class="list-group list-group-flush">
                @foreach ($recent_messages as $message)
                    <div class="list-group-item d-flex gap-3">
                        <span class="avatar avatar-sm bg-cyan-lt text-cyan rounded-circle">
                            {{ mb_substr($message->name, 0, 2) }}
                        </span>
                        <div class="min-w-0 me-auto">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="fw-bold">{{ $message->name }}</span>
                                <span class="small text-secondary">{{ $message->email }}</span>
                                <span class="small text-secondary">— {{ $message->created_at?->diffForHumans() }}</span>
                            </div>
                            <div class="fw-medium mt-1">{{ $message->subject }}</div>
                            <div class="text-secondary small text-truncate" style="max-width: 720px;">
                                {{ \Illuminate\Support\Str::limit($message->message, 140) }}
                            </div>
                        </div>
                        <a href="mailto:{{ $message->email }}" class="btn btn-sm btn-outline-primary align-self-center"
                            title="الرد بالبريد">
                            <i class="ti ti-mail-forward"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <x-empty-state icon="ti-mail-off" title="لا توجد رسائل"
                text="ستظهر رسائل التواصل من الموقع العام هنا." class="py-5" />
        @endif
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.45.1/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof ApexCharts === 'undefined') {
                document.querySelectorAll('.chart-skeleton').forEach(function (s) {
                    s.classList.remove('skeleton');
                    s.innerHTML = '<div class="text-center text-secondary small p-4">تعذّر تحميل الرسوم البيانية — تحقّق من الاتصال بالإنترنت.</div>';
                });
                return;
            }

            var baseFont = "'IBM Plex Sans Arabic', Cairo, sans-serif";

            function hideSkeleton(id) {
                var s = document.querySelector('[data-skel="' + id + '"]');
                if (s) s.remove();
            }

            // دونات: حالات المشاريع
            var statusEl = document.getElementById('chart-status');
            if (statusEl) {
                new ApexCharts(statusEl, {
                    chart: { type: 'donut', height: 320, fontFamily: baseFont,
                        events: { mounted: function () { hideSkeleton('chart-status'); } } },
                    series: @json($statusValues),
                    labels: @json($statusLabels),
                    colors: @json($statusFills),
                    legend: { position: 'bottom', fontFamily: baseFont, markers: { radius: 12 } },
                    stroke: { width: 2 },
                    dataLabels: { enabled: true, style: { fontFamily: baseFont } },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '68%',
                                labels: {
                                    show: true,
                                    value: { fontSize: '26px', fontFamily: baseFont, fontWeight: 700 },
                                    total: { show: true, label: 'إجمالي المشاريع', fontFamily: baseFont }
                                }
                            }
                        }
                    },
                    tooltip: { style: { fontFamily: baseFont } },
                    responsive: [{ breakpoint: 480, options: { chart: { height: 280 }, legend: { position: 'bottom' } } }]
                }).render();
            }

            // أعمدة: الطلبة حسب التخصص
            var specEl = document.getElementById('chart-specialize');
            if (specEl) {
                new ApexCharts(specEl, {
                    chart: { type: 'bar', height: 320, fontFamily: baseFont, toolbar: { show: false },
                        events: { mounted: function () { hideSkeleton('chart-specialize'); } } },
                    series: [{ name: 'عدد الطلبة', data: @json($specializes->pluck('students_count')) }],
                    colors: ['#464eea'], // لون الهوية الموحّد

                    plotOptions: { bar: { borderRadius: 6, columnWidth: '48%', distributed: false } },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: @json($specializes->pluck('name')),
                        labels: { style: { fontFamily: baseFont, fontSize: '12px' } },
                        axisBorder: { show: false }, axisTicks: { show: false }
                    },
                    yaxis: { labels: { style: { fontFamily: baseFont } } },
                    grid: { strokeDashArray: 4, borderColor: '#eef0f6' },
                    tooltip: { style: { fontFamily: baseFont } },
                    responsive: [{ breakpoint: 480, options: { chart: { height: 260 } } }]
                }).render();
            }
        });
    </script>
@endpush
