@extends('layouts.admin.admin')
@section('title', 'مشاريع منجزة')

@section('crumbs')
    <x-crumb :href="route('student.dashboard')">لوحتي</x-crumb>
    <x-crumb>مشاريع منجزة</x-crumb>
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

    <x-page-header title="مشاريع منجزة"
        subtitle="قبل أن تقدّم فكرتك: ما الذي نُفّذ في تخصصك، ومع أيّ مشرف." />

    <div class="filter-bar mb-3">
        <div class="filter-tabs" role="group" aria-label="نطاق التخصص">
            <a href="{{ $tabUrl(null) }}" class="filter-tab {{ $own ? 'is-active' : '' }}">تخصصي</a>
            <a href="{{ $tabUrl('all') }}" class="filter-tab {{ is_null($specializeId) ? 'is-active' : '' }}">كل التخصصات</a>
        </div>

        <form method="GET" action="{{ route('student.projects.explore') }}" class="filter-form">
            @if (is_null($specializeId))
                <input type="hidden" name="specialize" value="all">
            @endif

            <div class="filter-field filter-field--search">
                <label class="form-label" for="explore-q">بحث</label>
                <div class="filter-search-box">
                    <i class="ti ti-search filter-search-icon" aria-hidden="true"></i>
                    <input type="search" id="explore-q" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="كلمة من فكرتك — مثال: التسرّب، الاحتيال، التوصية…">
                </div>
            </div>

            {{-- المشرف بعدد ما أنجزه: «من أشرف على ما يشبه فكرتي؟» --}}
            <div class="filter-field">
                <label class="form-label" for="explore-supervisor">المشرف</label>
                <select id="explore-supervisor" name="supervisor" class="form-select">
                    <option value="">كل المشرفين</option>
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
                    تصفية
                </button>
                @if ($hasFilters)
                    <a href="{{ $tabUrl(is_null($specializeId) ? 'all' : null) }}" class="btn btn-outline-secondary"
                        title="مسح البحث والمشرف" aria-label="مسح البحث والمشرف">
                        <i class="ti ti-x" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    @php
        $n = $projects->total();
        $countLabel = match (true) {
            $n === 1 => 'مشروع منجز واحد',
            $n === 2 => 'مشروعان منجزان',
            $n >= 3 && $n <= 10 => $n . ' مشاريع منجزة',
            default => $n . ' مشروعاً منجزاً',
        };
    @endphp
    <p class="dist-head-note mb-3">
        {{ $countLabel }}
        {{ $own ? 'في تخصصك' : ($specializeId ? '' : 'في كل التخصصات') }}
        — المكتملة وحدها؛ مشاريع زملائك الجارية لا تُعرض.
    </p>

    @if ($projects->isEmpty())
        <div class="dist-panel">
            @if ($own && ! $hasFilters)
                <x-empty-state icon="ti-telescope" title="لا مشاريع منجزة في تخصصك بعد"
                    text="قد تكون من أوائل الدفعات. اطّلع على ما أُنجز في التخصصات الأخرى." class="py-6">
                    <x-slot:action>
                        <a href="{{ $tabUrl('all') }}" class="btn btn-primary">كل التخصصات</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-empty-state icon="ti-search-off" title="لا مشاريع مطابقة"
                    text="{{ $hasFilters ? 'فكرتك لم تُنفَّذ بهذه الكلمات — جيّد. جرّب كلمة أخرى للتأكّد.' : 'لا مشاريع منجزة بعد.' }}"
                    class="py-6">
                    @if ($own)
                        <x-slot:action>
                            <a href="{{ $tabUrl('all') }}" class="btn btn-outline-secondary">ابحث في كل التخصصات</a>
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
                            {{ $project->semester->name ?? '—' }}
                        </span>
                        <span class="group-fact">
                            <i class="ti ti-users" aria-hidden="true"></i>
                            {{ $project->group_count }} أعضاء
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
