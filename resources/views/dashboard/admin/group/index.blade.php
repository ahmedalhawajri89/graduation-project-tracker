@extends('layouts.admin.admin')
@section('title', 'بيانات المجموعات')

@section('crumbs')
    <x-crumb>المجموعات</x-crumb>
@endsection

@section('content')

    @php
        // الفلاتر تُمرَّر إلى DataTables كما تُمرَّر إلى التصدير
        $filterQuery = array_filter([
            'semester' => $currentSemesterId,
            'supervisor' => request('supervisor'),
            'type' => request('type'),
            'status' => $currentStatus,
        ]);
    @endphp

    <x-page-header title="بيانات المجموعات"
        subtitle="{{ $totalFiltered }} مجموعة ضمن التصفية الحالية">
        <x-slot:actions>
            {{-- يظهر فقط حين يوجد محذوف، بعدده --}}
            @if ($trashedCount > 0)
                <a href="{{ route('admin.groups.trash') }}" class="btn btn-ghost-secondary">
                    <i class="ti ti-trash me-1" aria-hidden="true"></i>
                    المحذوفات
                    <span class="filter-count ms-1">{{ $trashedCount }}</span>
                </a>
            @endif
            <a href="{{ route('admin.groups.export', ['semester' => $currentSemesterId]) }}"
                class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                تصدير Excel
            </a>
        </x-slot:actions>
    </x-page-header>

    @include('dashboard.admin.group.filter')

    <div class="card">
        <div class="table-responsive">
            {{-- عمود «#» حُذف: عدّاد صفوف يتغيّر مع كل صفحة وترتيب،
                 ليس مُعرِّفاً. ونوع المشروع انتقل إلى خليّة المشروع
                 لأنه صفة له لا عموداً مستقلّاً. --}}
            <table class="table table-vcenter card-table" id="dataTable-1">
                <thead>
                    <tr>
                        <th>المشروع</th>
                        <th>قائد الفريق</th>
                        <th class="w-1">الفريق</th>
                        <th>المشرف</th>
                        <th>الحالة</th>
                        <th>الدرجة</th>
                        <th class="w-1">إجراءات</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.component.delete_modal', [
        'delete_title' => 'المجموعة',
        'delete_controller_name' => 'admin.groups',
        'delete_note' => 'تنتقل المجموعة إلى المحذوفات، ويمكن استرجاعها من هناك.',
    ])

@endsection

@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.groups.getData', $filterQuery),
    'searchPlaceholder' => 'ابحث باسم المشروع…',
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
