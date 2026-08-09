@extends('layouts.admin.admin')
@section('title', 'بيانات المجموعات')

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">إدارة البيانات</div>
                <h2 class="page-title">بيانات المجموعات</h2>
            </div>
        </div>
    </div>

    @include('dashboard.admin.group.filter')

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان المشروع</th>
                        <th>قائد الفريق</th>
                        <th>المشرف</th>
                        <th>الحالة</th>
                        <th>الدرجة</th>
                        <th>التحكم</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <a href="{{ route('admin.groups.show', $project->id) }}" class="fw-bold text-reset">
                                    {{ $project->title }}
                                </a>
                                <div class="text-secondary small">{{ $project->project_type->name }}</div>
                            </td>
                            <td>{{ $project->group->where('type', 'leader')->first()->student->name ?? '—' }}</td>
                            <td>{{ $project->supervisor->name }}</td>
                            <td><x-status-badge :status="$project->status" /></td>
                            <td>
                                @if (!is_null($project->grade))
                                    <span class="badge bg-purple-lt text-purple">
                                        {{ rtrim(rtrim(number_format($project->grade, 2), '0'), '.') }}
                                        ({{ $project->grade_label }})
                                    </span>
                                @else
                                    <span class="text-secondary small">—</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.groups.show', $project->id) }}"
                                    class="btn btn-icon btn-ghost-primary" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="عرض التفاصيل">
                                    <i class="ti ti-eye"></i>
                                </a>

                                <a href="{{ route('admin.groups.edit', $project->id) }}"
                                    class="btn btn-icon btn-ghost-primary" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="تعديل">
                                    <i class="ti ti-pencil"></i>
                                </a>

                                <a type='button' class='btn btn-icon btn-ghost-danger btn-delete' data-bs-toggle='modal'
                                    data-bs-target='#deleteModal' data-id='{{ $project->id }}'
                                    data-name='{{ $project->title }}' title='حذف'>
                                    <i class="ti ti-trash"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
            {!! $projects->appends(request()->input())->links() !!}
        </div>
    </div>

    @include('dashboard.component.delete_modal', [
        'delete_title' => 'المجموعة',
        'delete_controller_name' => 'admin.groups',
    ])

@endsection
