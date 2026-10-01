@extends('layouts.admin.admin')
@section('title', __('مسؤولو النظام'))

@section('crumbs')
    <x-crumb>{{ __('مسؤولو النظام') }}</x-crumb>
@endsection

@section('content')

    <x-page-header title="{{ __('مسؤولو النظام') }}"
        subtitle="{{ $countAll == 1 ? __(':n حساب بصلاحية كاملة على المنصّة', ['n' => $countAll]) : __(':n حسابات بصلاحية كاملة على المنصّة', ['n' => $countAll]) }}">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="offcanvas"
                data-bs-target="#createDrawer">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                {{ __('إضافة مسؤول') }}
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- حساب واحد يعني نقطة فشل مفردة: لا استرجاع ولا «نسيت كلمة
         المرور» لحساب فُقد. التنبيه يظهر حين يكون ذلك واقعاً فعلاً. --}}
    @if ($countAll <= 1)
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
            <i class="ti ti-alert-triangle fs-2" aria-hidden="true"></i>
            <div>
                <strong>{{ __('حساب مسؤول واحد فقط.') }}</strong>
                {{ __('إن فُقدت كلمة مروره تعذّر الدخول إلى لوحة التحكم — لا يوجد استرجاع. يُنصح بإضافة حساب ثانٍ لشخص تثق به.') }}
            </div>
        </div>
    @endif

    {{-- الجدول صغير، فلا تبويبات ولا مُرشِّحات — البحث وحده يكفي --}}
    <div class="filter-bar mb-3">
        <div class="filter-form">
            <div class="filter-field filter-field--search">
                {{-- المُعرِّف يضعه السكربت على الحقل بعد نقله --}}
                <label class="form-label" for="dt-search-input">{{ __('بحث') }}</label>
                <div class="filter-search-box">
                    <i class="ti ti-search filter-search-icon" aria-hidden="true"></i>
                    <div id="dt-search-slot"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            {{-- عمود «#» حُذف: عدّاد صفوف لا مُعرِّف. و«الجنس» حُذف: لا
                 قرار يُبنى عليه هنا. والبريد انتقل إلى خليّة الاسم. --}}
            <table class="table table-vcenter card-table" id="dataTable-1">
                <thead>
                    <tr>
                        <th>{{ __('المسؤول') }}</th>
                        <th>{{ __('رقم الجوال') }}</th>
                        <th class="w-1">{{ __('عضو منذ') }}</th>
                        <th class="w-1">{{ __('إجراءات') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    @include('dashboard.admin.adminData.create_modal')
    @include('dashboard.admin.adminData.edit_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => __('المسؤول'),
        'delete_controller_name' => 'admin.administrators',
        'delete_note' => __('سيفقد هذا الحساب صلاحيته فوراً ولا يمكن التراجع. لا يمكن حذف حسابك أنت ولا آخر حساب مسؤول.'),
    ])

@endsection


@include('dashboard.component.datatables_style_script', [
    'urlData' => route('admin.administrators.getData'),
    'searchPlaceholder' => __('ابحث بالاسم أو البريد…'),
    'columnsData' => "[
                {data: 'identity', name: 'name'},
                {data: 'phone', name: 'phone', orderable: false},
                {data: 'created_at', name: 'created_at', searchable: false},
                {data: 'actions', name: 'actions', orderable: false, searchable: false},
            ]",
])
