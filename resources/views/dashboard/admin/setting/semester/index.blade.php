@extends('layouts.admin.admin')
@section('title', 'الفصول الدراسية')

@section('crumbs')
    <x-crumb>الفصول الدراسية</x-crumb>
@endsection

@section('content')

    @php
        $statusOrder = config('statuses.order');
        $statusMap = config('statuses.map');
        $statusTotal = $statusCounts->sum();
    @endphp

    <x-page-header title="الفصول الدراسية"
        :subtitle="$active ? 'النظام يعمل الآن على: ' . $active->name : 'لا يوجد فصل مُفعَّل'">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة فصل دراسي
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ الفصل الحالي ═══
         لم تكن الصفحة تقول أي فصل يعمل النظام عليه إلا بشارة صغيرة في
         صفّ جدول. وهو أهم معلومة فيها: كل تقديم وكل إحصاء وكل لوحة
         تُقرأ من هذا الفصل وحده. --}}
    @if ($active)
        <section class="term-now mb-4">
            <div class="term-now-head">
                <span class="term-now-label">
                    <span class="term-pulse" aria-hidden="true"></span>
                    الفصل الحالي
                </span>
                <h2 class="term-now-name">{{ $active->name }}</h2>
                <p class="term-now-note">
                    التقديم والمجموعات واللوحات والإحصائيات كلها تُقرأ من هذا الفصل.
                </p>
            </div>

            <div class="term-now-stats">
                <a href="{{ route('admin.groups.index', ['semester' => $active->id]) }}" class="term-stat">
                    <span class="term-stat-n">{{ $active->projects_count }}</span>
                    <span class="term-stat-l">مشروع مسجَّل</span>
                </a>

                @foreach ($statusOrder as $st)
                    <a href="{{ route('admin.groups.index', ['semester' => $active->id, 'status' => $st]) }}"
                        class="term-stat">
                        <span class="term-stat-n" style="color: {{ $statusMap[$st]['hex'] }}">
                            {{ $statusCounts[$st] ?? 0 }}
                        </span>
                        <span class="term-stat-l">{{ __('site.' . $st) }}</span>
                    </a>
                @endforeach
            </div>

            <div class="term-now-actions">
                <a class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                    data-id="{{ $active->id }}" data-name="{{ $active->name }}" title="تعديل الاسم"
                    aria-label="تعديل الاسم">
                    <i class="ti ti-pencil" aria-hidden="true"></i>
                </a>
            </div>
        </section>
    @else
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-4" role="alert">
            <i class="ti ti-alert-triangle fs-2" aria-hidden="true"></i>
            <div>
                <strong>لا يوجد فصل مُفعَّل.</strong>
                النظام يعود إلى آخر فصل أُضيف كحلّ مؤقّت. فعّل فصلاً صراحةً حتى تكون الإحصائيات والتقديم على ما تقصده.
            </div>
        </div>
    @endif

    {{-- ═══ الأرشيف ═══ --}}
    @if ($archive->count())
        <h2 class="dash-block-title mb-2">
            <i class="ti ti-archive" aria-hidden="true"></i>
            الفصول السابقة
        </h2>

        <div class="card">
            <div class="term-list">
                @foreach ($archive as $term)
                    @php
                        $hasProjects = $term->projects_count > 0;
                    @endphp

                    <article class="term-row">
                        <div class="term-main">
                            <h3 class="term-name">{{ $term->name }}</h3>
                            @if ($hasProjects)
                                <a href="{{ route('admin.groups.index', ['semester' => $term->id]) }}"
                                    class="term-count">
                                    {{ $term->projects_count }} مشروعاً في أرشيفه
                                </a>
                            @else
                                <span class="term-count is-empty">لا مشاريع</span>
                            @endif
                        </div>

                        <div class="term-actions">
                            {{-- التفعيل كان زرّاً في خليّة يسأل بـ confirm()
                                 المتصفّح: نافذة بلا تنسيق لا تقول ما الفصل
                                 الحالي ولا ماذا يتغيّر --}}
                            <button type="button" class="term-activate btn-activate"
                                data-bs-toggle="modal" data-bs-target="#activateModal" data-id="{{ $term->id }}"
                                data-name="{{ $term->name }}">
                                <i class="ti ti-player-play" aria-hidden="true"></i>
                                تفعيل
                            </button>

                            <div class="btn-group">
                                @if ($hasProjects)
                                    <span class="btn-action is-disabled"
                                        title="لا يمكن حذفه — يحتوي {{ $term->projects_count }} مشروعاً، وحذفه يدمر أرشيف النتائج."
                                        aria-disabled="true">
                                        <i class="ti ti-trash" aria-hidden="true"></i>
                                    </span>
                                @else
                                    <button type="button" class="btn-action btn-action--danger btn-delete"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $term->id }}"
                                        data-name="{{ $term->name }}" title="حذف" aria-label="حذف">
                                        <i class="ti ti-trash" aria-hidden="true"></i>
                                    </button>
                                @endif

                                <a class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="{{ $term->id }}" data-name="{{ $term->name }}" title="تعديل"
                                    aria-label="تعديل">
                                    <i class="ti ti-pencil" aria-hidden="true"></i>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @elseif ($active)
        <div class="card">
            <x-empty-state icon="ti-archive" title="لا فصول سابقة"
                text="هذا أول فصل في النظام. الفصول التي تنتهي تبقى هنا بأرشيف مشاريعها ونتائجها." class="py-5" />
        </div>
    @endif

    @include('dashboard.admin.setting.semester.create_modal')
    @include('dashboard.admin.setting.semester.edit_modal')
    @include('dashboard.admin.setting.semester.activate_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'الفصل الدراسي',
        'delete_controller_name' => 'admin.semesters',
        'delete_note' => 'لا يمكن حذف فصل يحتوي مشاريع، ولا الفصل النشط.',
    ])

@endsection

@push('js')
    <script>
        // إعادة فتح النافذة عند فشل التحقّق — كانت ضمن تضمين
        // \u200Edatatables_style_script\u200E وقد زال مع الجدول
        @if ($errors->any() && old('submit') == 'create')
            new bootstrap.Modal(document.getElementById('createModal')).show();
        @endif
        @if ($errors->any() && old('submit') == 'update')
            new bootstrap.Modal(document.getElementById('editModal')).show();
        @endif
    </script>
@endpush
