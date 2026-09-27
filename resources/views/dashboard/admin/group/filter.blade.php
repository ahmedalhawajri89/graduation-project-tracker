@php
    $statusMap = config('statuses.map');
    $statusOrder = config('statuses.order');
    $totalAll = $statusCounts->sum();

    // هل يوجد فلتر مطبَّق غير الفصل الدراسي؟
    $hasFilters = request()->filled('supervisor') || request()->filled('type') || request()->filled('status');
@endphp

<div class="filter-bar mb-3">

    {{-- أزرار الحالة: كانت الصفحة تستثني المعلّق والمرفوض تماماً.
         كل زر يحمل عدده ضمن الفلاتر المطبَّقة، فتعرف قبل النقر. --}}
    <div class="filter-tabs" role="group" aria-label="تصفية حسب الحالة">
        <a href="{{ route('admin.groups.index', array_merge(request()->except(['status', 'page']), [])) }}"
            class="filter-tab {{ is_null($currentStatus) ? 'is-active' : '' }}">
            الكل
            <span class="filter-count">{{ $totalAll }}</span>
        </a>
        @foreach ($statusOrder as $st)
            <a href="{{ route('admin.groups.index', array_merge(request()->except('page'), ['status' => $st])) }}"
                class="filter-tab {{ $currentStatus === $st ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: {{ $statusMap[$st]['hex'] }}"></span>
                {{ __('site.' . $st) }}
                <span class="filter-count">{{ $statusCounts[$st] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form action="{{ route('admin.groups.index') }}" method="get" class="filter-form">
        @if ($currentStatus)
            <input type="hidden" name="status" value="{{ $currentStatus }}">
        @endif

        {{-- البحث أولاً وأعرض: هو الأداة الأساسية، والقوائم مُرشِّحات.
             حقل DataTables بلا \u200Ename\u200E فلا يُرسل مع النموذج، والسكربت
             يمنع Enter من إرسال النموذج لأن البحث فوريّ. --}}
        <div class="filter-field filter-field--search">
            {{-- المُعرِّف يضعه السكربت على الحقل بعد نقله --}}
            <label class="form-label" for="dt-search-input">بحث</label>
            <div class="filter-search-box">
                <i class="ti ti-search filter-search-icon" aria-hidden="true"></i>
                <div id="dt-search-slot"></div>
            </div>
        </div>

        <div class="filter-field">
            <label class="form-label" for="f-semester">الفصل الدراسي</label>
            <select name="semester" id="f-semester" class="form-select">
                @foreach ($semesters as $sem)
                    <option value="{{ $sem->id }}" @selected($currentSemesterId == $sem->id)>{{ $sem->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-field">
            <label class="form-label" for="f-supervisor">المشرف</label>
            <select name="supervisor" id="f-supervisor" class="form-select">
                <option value="">كل المشرفين</option>
                @foreach ($supervisors as $supervisor)
                    <option value="{{ $supervisor->id }}" @selected(request()->supervisor == $supervisor->id)>{{ $supervisor->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-field">
            <label class="form-label" for="f-type">نوع المشروع</label>
            <select name="type" id="f-type" class="form-select">
                <option value="">كل الأنواع</option>
                @foreach ($project_type as $type)
                    <option value="{{ $type->id }}" @selected(request()->type == $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-filter me-1" aria-hidden="true"></i>
                تطبيق
            </button>

            {{-- مسح الفلاتر: لم يكن هناك طريق للعودة إلا بتصفير كل قائمة يدوياً --}}
            @if ($hasFilters)
                <a href="{{ route('admin.groups.index', ['semester' => $currentSemesterId]) }}"
                    class="btn btn-ghost-secondary" title="إزالة كل الفلاتر">
                    <i class="ti ti-x me-1" aria-hidden="true"></i>
                    مسح
                </a>
            @endif

            {{-- زرّ التصدير كان هنا وفي ترويسة الصفحة معاً — الفعل نفسه
                 مرتين في شاشة واحدة. بقي في الترويسة مع بقية الأفعال. --}}
        </div>
    </form>
</div>
