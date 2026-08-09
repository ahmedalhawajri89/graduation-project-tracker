@extends('layouts.admin.admin')
@section('title', 'ادارة تخصصات النظام')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إعدادات النظام</div>
                <h2 class="page-title">ادارة تخصصات النظام</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                    data-bs-target="#createModal">
                    <i class="ti ti-plus me-1"></i>
                    إضافة تخصص
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped" id="dataTable-1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>التخصص</th>
                        <th>أنواع المشاريع</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.setting.specialize.create_modal')
    @include('dashboard.admin.setting.specialize.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'التخصص',
        'delete_controller_name' => 'admin.specialize',
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.specialize.getData'),
    'columnsData' => "[
                {data: 'DT_RowIndex', 'orderable': false, 'searchable': false},
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'projects_count',
                    name: 'projects_count',
                    searchable: false
                },
                {
                    name: 'actions',
                    data: 'actions',
                    orderable: false,
                    searchable: false
                },

            ]",
])
