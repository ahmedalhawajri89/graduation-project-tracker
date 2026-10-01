@extends('layouts.admin.admin')
@section('title', __('مشاريع منجزة'))

@section('crumbs')
    <x-crumb :href="route('student.dashboard')">{{ __('لوحتي') }}</x-crumb>
    <x-crumb>{{ __('مشاريع منجزة') }}</x-crumb>
@endsection

@section('content')

    @php
        $own = $specializeId && (int) $specializeId === (int) $ownSpecialize;
        $hasFilters = request()->filled('q') || request()->filled('supervisor');
        // التبويب يغيّر النطاق ويُسقط المشرف: مشرف تخصص آخر لا معنى له هنا
        $tabUrl = fn ($spec) => route('student.projects.explore', array_filter([
            'specialize' => $spec,
            'q' => request('q'),
        ]));
    @endphp

    <x-page-header :title="__('مشاريع منجزة')"
        :subtitle="__('قبل أن تقدّم فكرتك: ما الذي نُفّذ في تخصصك، ومع أيّ مشرف.')" />

    <div class="filter-bar mb-3">
        <div class="filter-tabs" role="group" aria-label="{{ __('نطاق التخصص') }}">
            <a href="{{ $tabUrl(null) }}" class="filter-tab {{ $own ? 'is-active' : '' }}">{{ __('تخصصي') }}</a>
            <a href="{{ $tabUrl('all') }}" class="filter-tab {{ is_null($specializeId) ? 'is-active' : '' }}">{{ __('كل التخصصات') }}</a>
        </div>

        <form method="GET" action="{{ route('student.projects.explore') }}" class="filter-form">
            @if (is_null($specializeId))
                <input type="hidden" name="specialize" value="all">
            @endif

            <div class="filter-field filter-field--search">
                <label class="form-label" for="explore-q">{{ __('بحث') }}</label>
                <div class="filter-search-box">
                    <i class="ti ti-search filter-search-icon" aria-hidden="true"></i>
                    <input type="search" id="explore-q" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="{{ __('كلمة من فكرتك — مثال: التسرّب، الاحتيال، التوصية…') }}">
                </div>
            </div>

            {{-- المشرف بعدد ما أنجزه: «من أشرف على ما يشبه فكرتي؟» --}}
            <div class="filter-field">
                <label class="form-label" for="explore-supervisor">{{ __('المشرف') }}</label>
                <select id="explore-supervisor" name="supervisor" class="form-select">
                    <option value="">{{ __('كل المشرفين') }}</option>
                    @foreach ($supervisors as $row)
                        <option value="{{ $row->supervisor_id }}" @selected(request('supervisor') == $row->supervisor_id)>
                            {{ $row->supervisor->name }} ({{ $row->n }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-filter me-1" aria-hidden="true"></i>
                    {{ __('تصفية') }}
                </button>
                @if ($hasFilters)
                    <a href="{{ $tabUrl(is_null($specializeId) ? 'all' : null) }}" class="btn btn-outline-secondary"
                        title="{{ __('مسح البحث والمشرف') }}" aria-label="{{ __('مسح البحث والمشرف') }}">
                        <i class="ti ti-x" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    @php
        $n = $projects->total();
        $countLabel = match (true) {
            $n === 1 => __('مشروع منجز واحد'),
            $n === 2 => __('مشروعان منجزان'),
            $n >= 3 && $n <= 10 => __(':n مشاريع منجزة', ['n' => $n]),
            default => __(':n مشروعاً منجزاً', ['n' => $n]),
        };
    @endphp
    <p class="dist-head-note mb-3">
        {{ $countLabel }}
        {{ $own ? __('في تخصصك') : ($specializeId ? '' : __('في كل التخصصات')) }}
        — {{ __('المكتملة وحدها؛ مشاريع زملائك الجارية لا تُعرض.') }}
    </p>

    @if ($projects->isEmpty())
        <div class="dist-panel">
            @if ($own && ! $hasFilters)
                <x-empty-state icon="ti-telescope" :title="__('لا مشاريع منجزة في تخصصك بعد')"
                    :text="__('قد تكون من أوائل الدفعات. اطّلع على ما أُنجز في التخصصات الأخرى.')" class="py-6">
                    <x-slot:action>
                        <a href="{{ $tabUrl('all') }}" class="btn btn-primary">{{ __('كل التخصصات') }}</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-empty-state icon="ti-search-off" :title="__('لا مشاريع مطابقة')"
                    :text="$hasFilters ? __('فكرتك لم تُنفَّذ بهذه الكلمات — جيّد. جرّب كلمة أخرى للتأكّد.') : __('لا مشاريع منجزة بعد.')"
                    class="py-6">
                    @if ($own)
                        <x-slot:action>
                            <a href="{{ $tabUrl('all') }}" class="btn btn-outline-secondary">{{ __('ابحث في كل التخصصات') }}</a>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            @endif
        </div>
    @else
        {{-- بلا درجات ولا أسماء طلاب: السؤال «ماذا نُفّذ» لا «كم أخذوا» --}}
        <div class="group-grid">
            @foreach ($projects as $project)
                <article class="group-card">
                    <header class="group-card-head">
                        <div class="group-card-title">
                            <span>{{ $project->title }}</span>
                            <small>{{ $project->project_type->name }}</small>
                        </div>
                    </header>

                    @if ($project->description)
                        <p class="explore-desc">{{ \Illuminate\Support\Str::limit($project->description, 160) }}</p>
                    @endif

                    <div class="group-card-facts">
                        <span class="group-fact">
                            <i class="ti ti-user-star" aria-hidden="true"></i>
                            {{ $project->supervisor->name ?? '—' }}
                        </span>
                        <span class="group-fact">
                            <i class="ti ti-calendar" aria-hidden="true"></i>
                            {{ $project->semester->label ?? '—' }}
                        </span>
                        <span class="group-fact">
                            <i class="ti ti-users" aria-hidden="true"></i>
                            {{ __(':n أعضاء', ['n' => $project->group_count]) }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $projects->links('pagination::bootstrap-5') }}
        </div>
    @endif

@endsection
