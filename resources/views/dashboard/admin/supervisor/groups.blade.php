@extends('layouts.admin.admin')
@section('title', "مجموعات {$supervisor->name}")

@section('content')

    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <div class="page-pretitle">{{ $semester->name }}</div>
                <h2 class="page-title">مجموعات {{ $supervisor->name }}</h2>
            </div>
        </div>
    </div>

    <div class="accordion" id="accordion">
        @foreach ($supervisor->projectsAccept as $project)
            <div class="accordion-item">
                <h4 class="accordion-header" id="heading-{{ $project->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapse-{{ $project->id }}" aria-expanded="false"
                        aria-controls="collapse-{{ $project->id }}">
                        {{ $project->project_type->name }}
                        <strong class="text-primary ms-2">{{ $project->title }}</strong>
                    </button>
                </h4>
                <div id="collapse-{{ $project->id }}" class="accordion-collapse collapse" data-bs-parent="#accordion"
                    aria-labelledby="heading-{{ $project->id }}">
                    <div class="accordion-body">
                        @if ($project->description)
                            <h4>وصف المشروع</h4>
                            <p>{{ $project->description }}</p>
                        @endif

                        <div class="card">
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>اسماء الطلبة</th>
                                            <th>الرقم الجامعي</th>
                                            <th>رقم الجوال</th>
                                            <th>تخصص الجامعة</th>
                                            <th>قائد الفريق</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project->group as $group)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $group->student->name }}</td>
                                                <td>{{ $group->student->university_id }}</td>
                                                <td>{{ $group->student->phone }}</td>
                                                <td>{{ $project->title }}</td>
                                                <td>
                                                    @if ($group->type == 'leader')
                                                        <i class="ti ti-check text-success"></i>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

@stop
