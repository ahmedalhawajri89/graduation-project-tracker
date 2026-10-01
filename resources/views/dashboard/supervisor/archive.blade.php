@extends('layouts.admin.admin')
@section('title', __('أرشيف مشاريعي'))

@section('crumbs')
    <x-crumb :href="route('supervisor.dashboard')">{{ __('لوحتي') }}</x-crumb>
    <x-crumb>{{ __('أرشيف مشاريعي') }}</x-crumb>
@endsection

@section('content')

    @php $fmtGrade = fn ($g) => rtrim(rtrim(number_format($g, 2), '0'), '.'); @endphp

    <x-page-header title="{{ __('أرشيف مشاريعي') }}"
        subtitle="{{ __('ما أشرفت عليه في الفصول السابقة — مجموعات هذا الفصل في لوحتك.') }}" />

    {{-- البحث وحده: التجميع بالفصل يغني عن قائمة الفصول --}}
    <form method="GET" action="{{ route('supervisor.projects.archive') }}" class="filter-bar mb-4">
        <div class="filter-form">
            <div class="filter-field filter-field--search">
                <label class="form-label" for="archive-q">{{ __('بحث') }}</label>
                <div class="filter-search-box">
                    <i class="ti ti-search filter-search-icon" aria-hidden="true"></i>
                    <input type="search" id="archive-q" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="{{ __('عنوان المشروع، أو اسم طالب أو رقمه الجامعي…') }}">
                </div>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-search me-1" aria-hidden="true"></i>
                    {{ __('بحث') }}
                </button>
                @if (request()->filled('q'))
                    <a href="{{ route('supervisor.projects.archive') }}" class="btn btn-outline-secondary"
                        title="{{ __('مسح البحث') }}" aria-label="{{ __('مسح البحث') }}">
                        <i class="ti ti-x" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    @forelse ($bySemester as $projects)
        @php
            $graded = $projects->whereNotNull('grade');
            $avg = $graded->count() ? $fmtGrade($graded->avg('grade')) : null;
        @endphp
        <section class="dist-panel mb-4">
            <div class="dist-head">
                <span>{{ $projects->first()->semester->label ?? __('فصل محذوف') }}</span>
                <span class="dist-head-note">
                    {{ $projects->count() === 1 ? __(':n مشروع', ['n' => 1]) : __(':n مشاريع', ['n' => $projects->count()]) }}
                    @if ($avg)
                        · {{ __('متوسط الدرجات :avg', ['avg' => $avg]) }}
                    @endif
                </span>
            </div>

            @foreach ($projects as $project)
                <a href="{{ route('supervisor.projects.show', ['project' => $project->id]) }}" class="file-row archive-row">
                    <span class="file-body">
                        <span class="file-name">{{ $project->title }}</span>
                        <span class="file-meta">
                            {{ $project->project_type->name }}
                            · {{ $project->group->map(fn ($g) => $g->student?->name)->filter()->implode(__('، ')) ?: __('بلا أعضاء') }}
                        </span>
                    </span>

                    @if (! is_null($project->grade))
                        <span class="grade-pill">
                            {{ $fmtGrade($project->grade) }}
                            <small>{{ $project->grade_label }}</small>
                        </span>
                    @else
                        {{-- مقبول لم يكتمل في فصله، أو مكتمل بلا درجة — كلاهما يستحقّ النظر --}}
                        <span class="archive-nograde">
                            {{ $project->status === 'complete' ? __('بلا درجة') : __('لم يكتمل') }}
                        </span>
                    @endif

                    <i class="ti ti-chevron-left archive-go" aria-hidden="true"></i>
                </a>
            @endforeach
        </section>
    @empty
        <div class="dist-panel">
            @if (request()->filled('q'))
                <x-empty-state icon="ti-search-off" title="{{ __('لا نتائج مطابقة') }}"
                    text="{{ __('لا مشروع سابق بهذا العنوان أو الطالب.') }}" class="py-6" />
            @else
                <x-empty-state icon="ti-archive" title="{{ __('لا مشاريع من فصول سابقة بعد') }}"
                    text="{{ __('ما تشرف عليه هذا الفصل يظهر هنا بعد انتهائه.') }}" class="py-6">
                    <x-slot:action>
                        <a href="{{ route('supervisor.dashboard') }}" class="btn btn-primary">{{ __('مجموعات هذا الفصل') }}</a>
                    </x-slot:action>
                </x-empty-state>
            @endif
        </div>
    @endforelse

@endsection
