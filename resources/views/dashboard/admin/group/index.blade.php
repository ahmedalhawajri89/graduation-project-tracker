@extends('layouts.admin.admin')
@section('title', __('بيانات المجموعات'))

@section('crumbs')
    <x-crumb>{{ __('المجموعات') }}</x-crumb>
@endsection

@section('content')

    @php
        // الفلاتر تُمرَّر إلى DataTables كما تُمرَّر إلى التصدير
        $filterQuery = array_filter([
            'semester' => $currentSemesterId,
            'supervisor' => request('supervisor'),
            'type' => request('type'),
            'status' => $currentStatus,
            'issue' => $currentIssue,
        ]);
    @endphp

    <x-page-header title="{{ __('بيانات المجموعات') }}"
        subtitle="{{ __(':n مجموعة ضمن التصفية الحالية', ['n' => $totalFiltered]) }}">
        <x-slot:actions>
            {{-- يظهر فقط حين يوجد محذوف، بعدده --}}
            @if ($trashedCount > 0)
                <a href="{{ route('admin.groups.trash') }}" class="btn btn-ghost-secondary">
                    <i class="ti ti-trash me-1" aria-hidden="true"></i>
                    {{ __('المحذوفات') }}
                    <span class="filter-count ms-1">{{ $trashedCount }}</span>
                </a>
            @endif
            <a href="{{ route('admin.groups.export', ['semester' => $currentSemesterId]) }}"
                class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                {{ __('تصدير Excel') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    @include('dashboard.admin.group.filter')

    @if ($currentIssue)
        @php [$issueTitle, $issueText, $issueIcon] = \App\Support\TeamHealth::issues()[$currentIssue]; @endphp
        <div class="issue-banner mb-3" role="status">
            <i class="ti {{ $issueIcon }}" aria-hidden="true"></i>
            <span>
                <b>{{ $issueTitle }}</b>
                {{ $issueText }}
            </span>
            <a href="{{ route('admin.groups.index', array_diff_key($filterQuery, ['issue' => ''])) }}" class="issue-banner-clear">
                {{ __('إلغاء التصفية') }}
                <i class="ti ti-x" aria-hidden="true"></i>
            </a>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            {{-- عمود «#» حُذف: عدّاد صفوف يتغيّر مع كل صفحة وترتيب،
                 ليس مُعرِّفاً. ونوع المشروع انتقل إلى خليّة المشروع
                 لأنه صفة له لا عموداً مستقلّاً. --}}
            <table class="table table-vcenter card-table" id="dataTable-1">
                <thead>
                    <tr>
                        <th>{{ __('المشروع') }}</th>
                        <th>{{ __('قائد الفريق') }}</th>
                        <th class="w-1">{{ __('الفريق') }}</th>
                        <th>{{ __('المشرف') }}</th>
                        <th>{{ __('الحالة') }}</th>
                        <th>{{ __('الدرجة') }}</th>
                        <th class="w-1">{{ __('إجراءات') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.component.delete_modal', [
        'delete_title' => __('المجموعة'),
        'delete_controller_name' => 'admin.groups',
        'delete_note' => __('تنتقل المجموعة إلى المحذوفات، ويمكن استرجاعها من هناك.'),
    ])

@endsection

@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.groups.getData', $filterQuery),
    'searchPlaceholder' => __('ابحث باسم المشروع…'),
    // الفرز على العنوان والحالة والدرجة فقط. قائد الفريق وحجمه تُحسبان
    // من علاقات، وفرزهما يحتاج ربطاً لا يستحقّه عمود لن يُرتَّب به أحد.
    'columnsData' => "[
                {data: 'identity', name: 'title'},
                {data: 'leader', name: 'leader', orderable: false, searchable: false},
                {data: 'team', name: 'team', orderable: false, searchable: false},
                {data: 'supervisor_name', name: 'supervisor.name', orderable: false},
                {data: 'status', name: 'status', searchable: false},
                {data: 'grade', name: 'grade', searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false},
            ]",
])
