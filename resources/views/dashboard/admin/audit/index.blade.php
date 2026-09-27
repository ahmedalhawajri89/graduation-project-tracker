@extends('layouts.admin.admin')
@section('title', 'سجلّ التدقيق')

@section('crumbs')
    <x-crumb>سجلّ التدقيق</x-crumb>
@endsection

@section('content')

    @php
        $filterQuery = array_filter([
            'scope' => $scope,
            'action' => $currentAction,
            'role' => $currentRole,
            'from' => request('from'),
            'to' => request('to'),
        ]);
    @endphp

    <x-page-header title="سجلّ التدقيق" subtitle="{{ $countAll }} حدثاً مسجَّلاً">
        <x-slot:actions>
            <a href="{{ route('admin.audit.export', $filterQuery) }}" class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                تصدير
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- السجلّ للقراءة فقط: لا زرّ إضافة ولا تعديل ولا حذف في الصفحة
         كلها، ولا مسار لأيٍّ منها. سجلّ يُعدَّل ليس سجلّاً. --}}
    <div class="hint-bar mb-3">
        <i class="ti ti-lock" aria-hidden="true"></i>
        <span>
            السجلّ للقراءة فقط ولا يمكن تعديله أو حذفه من النظام.
            يُسجَّل فيه ما يمسّ الدرجات ودورة حياة المشاريع والحسابات.
        </span>
    </div>

    <div class="filter-bar mb-3">
        <div class="filter-tabs" role="group" aria-label="تصفية الأحداث">
            <a href="{{ route('admin.audit.index', array_diff_key($filterQuery, ['scope' => ''])) }}"
                class="filter-tab {{ is_null($scope) ? 'is-active' : '' }}">
                الكل
                <span class="filter-count">{{ $countAll }}</span>
            </a>
            <a href="{{ route('admin.audit.index', array_merge($filterQuery, ['scope' => 'grade'])) }}"
                class="filter-tab {{ $scope === 'grade' ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: #b45309"></span>
                الدرجات
                <span class="filter-count">{{ $countGrade }}</span>
            </a>
            <a href="{{ route('admin.audit.index', array_merge($filterQuery, ['scope' => 'lifecycle'])) }}"
                class="filter-tab {{ $scope === 'lifecycle' ? 'is-active' : '' }}">
                <span class="filter-dot" style="background: #be123c"></span>
                الحذف والاسترجاع
                <span class="filter-count">{{ $countLifecycle }}</span>
            </a>
        </div>

        <form action="{{ route('admin.audit.index') }}" method="get" class="filter-form">
            @if ($scope)
                <input type="hidden" name="scope" value="{{ $scope }}">
            @endif

            <div class="filter-field">
                <label class="form-label" for="f-action">الحدث</label>
                <select name="action" id="f-action" class="form-select">
                    <option value="">كل الأحداث</option>
                    @foreach ($actions as $key => $label)
                        <option value="{{ $key }}" @selected($currentAction === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label class="form-label" for="f-role">الفاعل</label>
                <select name="role" id="f-role" class="form-select">
                    <option value="">كل الأدوار</option>
                    @foreach ($roles as $key => $label)
                        <option value="{{ $key }}" @selected($currentRole === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label class="form-label" for="f-from">من تاريخ</label>
                <input type="date" name="from" id="f-from" class="form-control" value="{{ request('from') }}">
            </div>

            <div class="filter-field">
                <label class="form-label" for="f-to">إلى تاريخ</label>
                <input type="date" name="to" id="f-to" class="form-control" value="{{ request('to') }}">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-filter me-1" aria-hidden="true"></i>
                    تطبيق
                </button>
                @if (count($filterQuery))
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-ghost-secondary">
                        <i class="ti ti-x me-1" aria-hidden="true"></i>
                        مسح
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card">
        @if ($logs->count())
            <div class="audit-list">
                @foreach ($logs as $log)
                    <x-audit-row :log="$log" />
                @endforeach
            </div>

            @if ($logs->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {!! $logs->links() !!}
                </div>
            @endif
        @else
            <x-empty-state icon="ti-history" title="لا أحداث مطابقة"
                text="لم يُسجَّل حدث ضمن هذه التصفية. السجلّ يبدأ من أول تغيير يمسّ درجةً أو مشروعاً أو حساباً."
                class="py-6" />
        @endif
    </div>

@endsection
