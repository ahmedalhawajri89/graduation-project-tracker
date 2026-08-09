@extends('layouts.admin.admin')
@section('title', 'مستكشف المشاريع')

@section('content')

    <x-page-header pretitle="لوحة الطالب" title="مستكشف المشاريع السابقة">
        <x-slot:actions>
            <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
                <i class="ti ti-arrow-right me-1"></i>
                العودة للرئيسية
            </a>
        </x-slot:actions>
    </x-page-header>

    <p class="text-secondary mb-4">
        تصفّح مشاريع الدفعات السابقة والحالية — استلهم فكرتك وتأكد أنها غير منفّذة من قبل. 💡
    </p>

    {{-- البحث والفلترة --}}
    <form method="GET" action="{{ route('student.projects.explore') }}" class="card mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                            placeholder="ابحث بعنوان المشروع أو وصفه.." aria-label="بحث في المشاريع">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="semester_id" class="form-select" aria-label="فلترة حسب الفصل">
                        <option value="">كل الفصول</option>
                        @foreach ($semesters as $sem)
                            <option value="{{ $sem->id }}" @if (request('semester_id') == $sem->id) selected @endif>
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
                    @if (request('q') || request('semester_id'))
                        <a href="{{ route('student.projects.explore') }}" class="btn btn-outline-secondary" title="مسح الفلاتر">
                            <i class="ti ti-x"></i>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </form>

    {{-- النتائج --}}
    @if ($projects->count() === 0)
        <div class="card">
            <x-empty-state icon="ti-telescope" title="لا توجد مشاريع مطابقة"
                text="جرّب كلمات بحث مختلفة أو غيّر الفصل الدراسي." class="py-5" />
        </div>
    @else
        <div class="row row-deck row-cards">
            @foreach ($projects as $project)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-link-pop h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <span class="badge bg-primary-lt text-primary">
                                    {{ $project->project_type->name }}
                                </span>
                                <x-status-badge :status="$project->status" />
                            </div>

                            <h3 class="card-title mb-2">{{ $project->title }}</h3>

                            @if ($project->description)
                                <p class="text-secondary small mb-3">
                                    {{ \Illuminate\Support\Str::limit($project->description, 140) }}
                                </p>
                            @endif

                            <div class="mt-auto pt-3 border-top d-flex flex-wrap gap-3 text-secondary small">
                                <span title="المشرف">
                                    <i class="ti ti-user-star me-1"></i>{{ $project->supervisor->name ?: '—' }}
                                </span>
                                <span title="الفصل">
                                    <i class="ti ti-calendar me-1"></i>{{ $project->semester->name ?: '—' }}
                                </span>
                                <span title="عدد أعضاء الفريق">
                                    <i class="ti ti-users me-1"></i>{{ $project->group_count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $projects->links('pagination::bootstrap-5') }}
        </div>
    @endif

@stop
