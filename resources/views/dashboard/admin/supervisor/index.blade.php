@extends('layouts.admin.admin')
@section('title', 'بيانات المشرفين')

@section('crumbs')
    <x-crumb>بيانات المشرفين</x-crumb>
@endsection

@section('content')

    @php
        // الفلاتر تُمرَّر إلى DataTables وإلى التصدير معاً
        $filterQuery = array_filter([
            'load' => $currentLoad,
            'specialize' => $currentSpecialize,
        ]);
    @endphp

    {{-- شريحة الفلتر النشط: القادم من بطاقة تخصص كان يصل إلى صفحة لا
         تقول إنها مُصفّاة إلا بقائمة منسدلة مختارة في زاوية الشريط --}}
    @if ($currentSpecializeName)
        <div class="scope-chip">
            <i class="ti ti-filter" aria-hidden="true"></i>
            <span>مُصفّى حسب التخصص: <b>{{ $currentSpecializeName }}</b></span>
            <a href="{{ route('admin.supervisors.index', array_diff_key($filterQuery, ['specialize' => ''])) }}"
                class="scope-chip-clear" title="إزالة تصفية التخصص" aria-label="إزالة تصفية التخصص">
                <i class="ti ti-x" aria-hidden="true"></i>
            </a>
        </div>
    @endif

    <x-page-header title="بيانات المشرفين"
        subtitle="{{ $countAll }} مشرفاً · {{ $countFree }} متاح · {{ $countOver }} تجاوز حدّه">
        <x-slot:actions>
            {{-- فعل أساسي واحد بالحبر، وما عداه ثانوي بحدّ شعرة —
                 كان أزرق وأخضر يتنافسان --}}
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="offcanvas"
                data-bs-target="#createDrawer">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة مشرف
            </button>

            <button type="button" class="btn btn-outline-primary btn-import" data-bs-toggle="modal"
                data-bs-target="#importModal">
                <i class="ti ti-upload me-1" aria-hidden="true"></i>
                استيراد
            </button>

            <a href="{{ route('admin.supervisors.export', $filterQuery) }}" class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                تصدير
            </a>
        </x-slot:actions>
    </x-page-header>

    @include('dashboard.admin._import-report')

    {{-- ═══ التصفية ═══
         السؤال الذي تُفتح هذه الصفحة من أجله عند توزيع مشروع جديد هو
         «من يستطيع استقبال مجموعة أخرى؟». الحدّ \u200Emax_group\u200E كان مُخزَّناً
         ولا يُعرض، فالجواب كان يُستخرج يدوياً. --}}
    <div class="filter-bar mb-3">
        <div class="filter-tabs" role="group" aria-label="تصفية حسب عبء الإشراف">
            <a href="{{ route('admin.supervisors.index', array_diff_key($filterQuery, ['load' => ''])) }}"
                class="filter-tab {{ is_null($currentLoad) ? 'is-active' : '' }}">
                الكل
                <span class="filter-count">{{ $countAll }}</span>
            </a>
            <a href="{{ route('admin.supervisors.index', array_merge($filterQuery, ['load' => 'free'])) }}"
                class="filter-tab {{ $currentLoad === 'free' ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: #047857"></span>
                متاح
                <span class="filter-count">{{ $countFree }}</span>
            </a>
            <a href="{{ route('admin.supervisors.index', array_merge($filterQuery, ['load' => 'full'])) }}"
                class="filter-tab {{ $currentLoad === 'full' ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: #b45309"></span>
                مكتمل
                <span class="filter-count">{{ $countFull }}</span>
            </a>
            <a href="{{ route('admin.supervisors.index', array_merge($filterQuery, ['load' => 'over'])) }}"
                class="filter-tab {{ $currentLoad === 'over' ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: #be123c"></span>
                تجاوز الحد
                <span class="filter-count">{{ $countOver }}</span>
            </a>
        </div>

        <form action="{{ route('admin.supervisors.index') }}" method="get" class="filter-form">
            @if ($currentLoad)
                <input type="hidden" name="load" value="{{ $currentLoad }}">
            @endif

            {{-- البحث أولاً وأعرض: هو الأداة الأساسية، والتخصص مُرشِّح.
                 حقل DataTables بلا \u200Ename\u200E فلا يُرسل مع النموذج، والسكربت
                 يمنع Enter من الإرسال لأن البحث فوريّ. --}}
            <div class="filter-field filter-field--search">
                {{-- المُعرِّف يضعه السكربت على الحقل بعد نقله --}}
                <label class="form-label" for="dt-search-input">بحث</label>
                <div class="filter-search-box">
                    <i class="ti ti-search filter-search-icon" aria-hidden="true"></i>
                    <div id="dt-search-slot"></div>
                </div>
            </div>

            <div class="filter-field">
                <label class="form-label" for="f-spec">التخصص</label>
                <select name="specialize" id="f-spec" class="form-select">
                    <option value="">كل التخصصات</option>
                    {{-- الموقوفة تبقى هنا: مشرفوها موجودون ولا بدّ من
                         الوصول إليهم --}}
                    @foreach ($filterSpecializes as $spec)
                        <option value="{{ $spec->id }}" @selected($currentSpecialize === $spec->id)>
                            {{ $spec->name }}@if ($spec->isArchived()) (موقوف)@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-filter me-1" aria-hidden="true"></i>
                    تطبيق
                </button>
                @if (count($filterQuery))
                    <a href="{{ route('admin.supervisors.index') }}" class="btn btn-ghost-secondary">
                        <i class="ti ti-x me-1" aria-hidden="true"></i>
                        مسح
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            {{-- عمود «#» حُذف: عدّاد صفوف يتغيّر مع كل صفحة وترتيب،
                 ليس مُعرِّفاً. والجنس حُذف: لا قرار يُبنى عليه في هذه
                 الشاشة. والبريد انتقل إلى خليّة الهوية مع الاسم. --}}
            <table class="table table-vcenter card-table" id="dataTable-1">
                <thead>
                    <tr>
                        <th>المشرف</th>
                        <th>الرقم الجامعي</th>
                        <th>التخصص</th>
                        <th>رقم الجوال</th>
                        <th class="w-1">عبء الإشراف</th>
                        <th class="w-1" title="مراحل سلّمتها فرقه ولم يراجعها">بانتظار مراجعته</th>
                        <th class="w-1">إجراءات</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.supervisor.import_modal')
    @include('dashboard.admin.supervisor.create_modal')
    @include('dashboard.admin.supervisor.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'المشرف',
        'delete_controller_name' => 'admin.supervisors',
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.supervisors.getData', $filterQuery),
    'searchPlaceholder' => 'ابحث بالاسم أو الرقم الجامعي أو البريد…',
    // الفرز على ثلاثة أعمدة: الاسم والرقم الجامعي والعبء. لن يرتّب
    // أحد المشرفين حسب الجوال أو التخصص.
    'columnsData' => "[
                {data: 'identity', name: 'name'},
                {data: 'university_id', name: 'university_id'},
                {data: 'specialize.name', name: 'specialize.name', orderable: false},
                {data: 'phone', name: 'phone', orderable: false},
                {data: 'projects_count', name: 'projects_count', searchable: false},
                {data: 'pending_reviews_count', name: 'pending_reviews_count', searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false},
            ]",
])
