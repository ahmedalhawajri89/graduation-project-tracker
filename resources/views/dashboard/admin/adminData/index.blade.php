@extends('layouts.admin.admin')
@section('title', 'مسؤولين النظام')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إدارة البيانات</div>
                <h2 class="page-title">مسؤولين النظام</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                    data-bs-target="#createModal">
                    <i class="ti ti-plus me-1"></i>
                    إضافة مسؤول
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
                        <th>اسم الآدمن</th>
                        <th>البريد الالكتروني</th>
                        <th>رقم الجوال</th>
                        <th>الجنس</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.adminData.create_modal')
    @include('dashboard.admin.adminData.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'المسؤول',
        'delete_controller_name' => 'admin.administrators',
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.administrators.getData'),
    'columnsData' => "[
                {data: 'DT_RowIndex', 'orderable': false, 'searchable': false},
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email',
                },
                {
                    data: 'phone',
                    name: 'phone'
                },
                {
                    data: 'gender',
                    name: 'gender'
                },
                {
                    name: 'actions',
                    data: 'actions',
                    orderable: false,
                    searchable: false
                },

            ]",
])
