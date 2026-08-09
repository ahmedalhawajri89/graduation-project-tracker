@extends('layouts.admin.admin')
@section('title', 'رسائل واستفسار')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إدارة البيانات</div>
                <h2 class="page-title">رسائل واستفسار</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم المرسل</th>
                        <th>البريد الالكتروني</th>
                        <th>الموضوع</th>
                        <th>الرسالة</th>
                        <th>التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($messages as $message)
                        <tr class="{{ !$message->is_read ? 'bg-primary-lt bg-opacity-10' : '' }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $message->name }}
                                @if (!$message->is_read)
                                    <span class="badge bg-red text-red-fg ms-1">جديد</span>
                                @endif
                            </td>
                            <td>{{ $message->email }}</td>
                            <td>{{ $message->subject }}</td>
                            <td>{{ $message->message }}</td>
                            <td>
                                <button type='button' class='btn btn-danger btn-delete' data-bs-toggle='modal'
                                    data-bs-target='#deleteModal' data-id='{{ $message->id }}'
                                    data-name='{{ $message->subject }}' title='حذف'>
                                    <i class="ti ti-trash me-1"></i>
                                    حذف الرسالة
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {!! $messages->links() !!}
        </div>
    </div>

    @include('dashboard.component.delete_modal', [
        'delete_title' => 'الرسالة',
        'delete_controller_name' => 'admin.contact',
    ])

@endsection
