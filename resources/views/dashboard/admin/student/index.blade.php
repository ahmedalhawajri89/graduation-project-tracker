@extends('layouts.admin.admin')
@section('title', 'بيانات الطلاب')

@section('crumbs')
    <x-crumb>بيانات الطلاب</x-crumb>
@endsection

@section('content')

    @php
        // الفلاتر تُمرَّر إلى DataTables وإلى التصدير معاً
        $filterQuery = array_filter([
            'group' => $currentGroupFilter,
            'specialize' => $currentSpecialize,
        ]);
    @endphp

    {{-- شريحة الفلتر النشط: القادم من بطاقة تخصص كان يصل إلى صفحة لا
         تقول إنها مُصفّاة إلا بقائمة منسدلة مختارة في زاوية الشريط --}}
    @if ($currentSpecializeName)
        <div class="scope-chip">
            <i class="ti ti-filter" aria-hidden="true"></i>
            <span>مُصفّى حسب التخصص: <b>{{ $currentSpecializeName }}</b></span>
            <a href="{{ route('admin.students.index', array_diff_key($filterQuery, ['specialize' => ''])) }}"
                class="scope-chip-clear" title="إزالة تصفية التخصص" aria-label="إزالة تصفية التخصص">
                <i class="ti ti-x" aria-hidden="true"></i>
            </a>
        </div>
    @endif

    <x-page-header title="بيانات الطلاب"
        subtitle="{{ $countAll }} طالباً · {{ $countInGroup }} في فرق · {{ $countNoGroup }} بلا فريق">
        <x-slot:actions>
            {{-- فعل أساسي واحد بالحبر، وما عداه ثانوي بحدّ شعرة —
                 كان أزرق وأخضر يتنافسان --}}
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة طالب
            </button>

            <button type="button" class="btn btn-outline-primary btn-import" data-bs-toggle="modal"
                data-bs-target="#importModal">
                <i class="ti ti-upload me-1" aria-hidden="true"></i>
                استيراد
            </button>

            <a href="{{ route('admin.students.export', $filterQuery) }}" class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                تصدير
            </a>
        </x-slot:actions>
    </x-page-header>

    @include('dashboard.admin._import-report')

    {{-- ═══ التصفية ═══
         لوحة التحكم تربط «الطلاب بلا فريق» إلى هنا، ولم تكن هناك
         وسيلة لعزلهم من بين الـ٥٠٠. --}}
    <div class="filter-bar mb-3">
        <div class="filter-tabs" role="group" aria-label="تصفية حسب الانضمام">
            <a href="{{ route('admin.students.index', array_diff_key($filterQuery, ['group' => ''])) }}"
                class="filter-tab {{ is_null($currentGroupFilter) ? 'is-active' : '' }}">
                الكل
                <span class="filter-count">{{ $countAll }}</span>
            </a>
            <a href="{{ route('admin.students.index', array_merge($filterQuery, ['group' => 'in'])) }}"
                class="filter-tab {{ $currentGroupFilter === 'in' ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: #047857"></span>
                في فريق
                <span class="filter-count">{{ $countInGroup }}</span>
            </a>
            <a href="{{ route('admin.students.index', array_merge($filterQuery, ['group' => 'none'])) }}"
                class="filter-tab {{ $currentGroupFilter === 'none' ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: #b45309"></span>
                بلا فريق
                <span class="filter-count">{{ $countNoGroup }}</span>
            </a>
        </div>

        <form action="{{ route('admin.students.index') }}" method="get" class="filter-form">
            @if ($currentGroupFilter)
                <input type="hidden" name="group" value="{{ $currentGroupFilter }}">
            @endif

            {{-- البحث أولاً وأعرض: مع ٥٠٠ طالب هو الأداة الأساسية،
                 والتخصص مُرشِّح ثانوي. حقل DataTables بلا \u200Ename\u200E فلا
                 يُرسل مع النموذج، والسكربت يمنع Enter من الإرسال
                 لأن البحث فوريّ. --}}
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
                    {{-- الموقوفة تبقى هنا: طلابها موجودون ولا بدّ من
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
                    <a href="{{ route('admin.students.index') }}" class="btn btn-ghost-secondary">
                        <i class="ti ti-x me-1" aria-hidden="true"></i>
                        مسح
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table" id="dataTable-1">
                {{-- عمود «#» حُذف: عدّاد صفوف يتغيّر مع كل صفحة وترتيب،
                     ليس مُعرِّفاً — والأحرف الأولى تحلّ محلّه كمرساة.
                     والبريد انتقل إلى خليّة الهوية مع الاسم. --}}
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>الرقم الجامعي</th>
                        <th>التخصص</th>
                        <th>رقم الجوال</th>
                        <th>المشروع</th>
                        {{-- كان فارغاً: عمود بلا عنوان ولا محتوى ظاهر
                             يُقرأ كعطل لا كتصميم --}}
                        <th class="w-1">إجراءات</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.student.import_modal')
    @include('dashboard.admin.student.create_modal')
    @include('dashboard.admin.student.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'الطالب',
        'delete_controller_name' => 'admin.students',
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.students.getData', $filterQuery),
    'searchPlaceholder' => 'ابحث بالاسم أو الرقم الجامعي أو البريد…',
    // الفرز على عمودين فقط: الاسم والرقم الجامعي. لن يرتّب أحد ٥٠٠
    // طالب حسب الجوال أو التخصص، والأسهم على كل عمود ضجيج في الرأس.
    'columnsData' => "[
                {data: 'identity', name: 'name'},
                {data: 'university_id', name: 'university_id'},
                {data: 'specialize.name', name: 'specialize.name', orderable: false},
                {data: 'phone', name: 'phone', orderable: false},
                {data: 'isGroup', name: 'isGroup', orderable: false, searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false},
            ]",
])
