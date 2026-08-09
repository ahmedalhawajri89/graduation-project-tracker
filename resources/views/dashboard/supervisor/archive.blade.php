@extends('layouts.admin.admin')
@section('title', 'أرشيف مشاريعي')

@section('content')

    <x-page-header pretitle="لوحة المشرف" title="أرشيف مشاريعي">
        <x-slot:actions>
            <a href="{{ route('supervisor.dashboard') }}" class="btn btn-outline-primary">
                <i class="ti ti-arrow-right me-1"></i>
                العودة للرئيسية
            </a>
        </x-slot:actions>
    </x-page-header>

    <p class="text-secondary mb-4">
        كل المشاريع التي أشرفت عليها عبر الفصول الدراسية — بدرجاتها ونتائجها. 🗂️
    </p>

    {{-- البحث والفلترة --}}
    <form method="GET" action="{{ route('supervisor.projects.archive') }}" class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="ابحث بعنوان المشروع.." aria-label="بحث في الأرشيف">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="semester" class="form-select" aria-label="فلترة حسب الفصل">
                        <option value="">كل الفصول</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @if (request('semester') == $sem->id) selected @endif>
                                {{ $sem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">
                        <i class="ti ti-filter me-1"></i>
                        بحث
                    </button>
                    @if (request('q') || request('semester'))
                        <a href="{{ route('supervisor.projects.archive') }}" class="btn btn-outline-secondary" title="مسح الفلاتر">
                            <i class="ti ti-x"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- النتائج --}}
    <div class="card">
        @if ($projects->count() === 0)
            <x-empty-state icon="ti-archive-off" title="لا توجد مشاريع مطابقة"
                text="جرّب فلتراً مختلفاً — أو لم تُسند إليك مشاريع بعد." class="py-5" />
        @else
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>المشروع</th>
                            <th>الفصل</th>
                            <th>الحالة</th>
                            <th>الدرجة</th>
                            <th>الفريق</th>
                            <th class="w-1">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>
                                    <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}"
                                        class="fw-bold text-reset d-block">
                                        {{ $project->title }}
                                    </a>
                                    <span class="text-secondary small">{{ $project->project_type->name }}</span>
                                </td>
                                <td>{{ $project->semester->name ?: '—' }}</td>
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
                                    <span class="badge bg-blue-lt text-blue">
                                        <i class="ti ti-users me-1"></i>{{ $project->group_count }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-eye me-1"></i>
                                        عرض
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer d-flex justify-content-center">
                {{ $projects->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@stop
