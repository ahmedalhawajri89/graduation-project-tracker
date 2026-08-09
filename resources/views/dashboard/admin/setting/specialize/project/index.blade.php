@extends('layouts.admin.admin')
@section('title', "ادارة انواع المشاريع لتخصص {$specialize->name}")

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إعدادات النظام</div>
                <h2 class="page-title">
                    ادارة انواع المشاريع لتخصص
                    <span class="text-primary">{{ $specialize->name }}</span>
                </h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                    data-bs-target="#createModal">
                    <i class="ti ti-plus me-1"></i>
                    إضافة نوع مشروع
                </button>

                <a class="btn btn-outline-info" href="{{ route('admin.specialize.index') }}">
                    <i class="ti ti-list me-1"></i>
                    العودة لقائمة التخصصات
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped" id="dataTable-1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المشروع</th>
                        <th>الحد الأدنى</th>
                        <th>الحد الأقصى</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.setting.specialize.project.create_modal')
    @include('dashboard.admin.setting.specialize.project.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'نوع المشروع',
        'delete_controller_name' => 'admin.specialize.projects',
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.specialize.projects.getData', $specialize->id),
    'columnsData' => "[
                {data: 'DT_RowIndex', 'orderable': false, 'searchable': false},
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'min',
                    name: 'min'
                },
                {
                    data: 'max',
                    name: 'max'
                },
                {
                    name: 'actions',
                    data: 'actions',
                    orderable: false,
                    searchable: false
                },

            ]",
])
