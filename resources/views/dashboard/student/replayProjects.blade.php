@extends('layouts.admin.admin')
@section('title', 'الإشعارات')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">لوحة الطالب</div>
                <h2 class="page-title">قائمة الاشعارات</h2>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="ti ti-bell me-2"></i>
                قائمة الاشعارات
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان المشروع</th>
                        <th>اسم المشرف</th>
                        <th>نص الرسالة</th>
                        <th>تاريخ الاشعار</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (auth()->user()->notifications as $notification)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $notification->data['project'] }}</td>
                            <td>{{ $notification->data['supervisor_name'] }}</td>
                            <td>{{ $notification->data['msg'] }}</td>
                            <td>{{ $notification->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop
