@extends('layouts.admin.admin')
@section('title', 'بيانات الطلاب')

@section('content')

    <x-page-header pretitle="إدارة البيانات" title="بيانات الطلاب">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1"></i>
                إضافة طالب
            </button>

            <button type="button" class="btn btn-outline-success btn-import" data-bs-toggle="modal"
                data-bs-target="#importModal">
                <i class="ti ti-file-spreadsheet me-1"></i>
                رفع ملف إكسل
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped" id="dataTable-1">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الطالب</th>
                        <th>الرقم الجامعي</th>
                        <th>التخصص</th>
                        <th>البريد الالكتروني</th>
                        <th>رقم الجوال</th>
                        <th>الجنس</th>
                        <th>الانضمام الى مجموعة</th>
                        <th></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.student.import_modal')
    @include('dashboard.admin.student.create_modal')
    @include('dashboard.admin.student.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'الطالب',
        'delete_controller_name' => 'admin.students',
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.students.getData'),
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
                    data: 'isGroup',
                    name: 'isGroup'
                },
                {
                    name: 'actions',
                    data: 'actions',
                    orderable: false,
                    searchable: false
                },

            ]",
])
