@extends('layouts.admin.admin')
@section('title', 'بيانات المشرفين')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إدارة البيانات</div>
                <h2 class="page-title">بيانات المشرفين</h2>
            </div>
            <div class="col-auto d-flex gap-2">
                <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                    data-bs-target="#createModal">
                    <i class="ti ti-plus me-1"></i>
                    إضافة مشرف
                </button>

                <button type="button" class="btn btn-outline-success btn-import" data-bs-toggle="modal"
                    data-bs-target="#importModal">
                    <i class="ti ti-file-spreadsheet me-1"></i>
                    رفع ملف اكسل
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
                        <th>اسم المشرف</th>
                        <th>الرقم الجامعي</th>
                        <th>التخصص</th>
                        <th>البريد الالكتروني</th>
                        <th>رقم الجوال</th>
                        <th>الجنس</th>
                        <th>عدد المجموعات</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.supervisor.import_modal')
    @include('dashboard.admin.supervisor.create_modal')
    @include('dashboard.admin.supervisor.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'المشرف',
        'delete_controller_name' => 'admin.supervisors',
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.supervisors.getData'),
    'columnsData' => "[
                {data: 'DT_RowIndex', 'orderable': false, 'searchable': false},
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'university_id',
                    name: 'university_id'
                },
                {
                    data: 'specialize.name',
                    name: 'specialize.name'
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
