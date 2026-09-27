@extends('layouts.admin.admin')
@section('title', 'التخصصات')

@section('crumbs')
    <x-crumb>التخصصات</x-crumb>
@endsection

@section('content')

    @php
        // تخصص ينقصه نوع مشروع أو مشرف متاح = طريق مسدود أمام طلابه
        $incomplete = $specializes->filter(
            fn ($s) => $s->projects_count === 0 || $s->supervisors_available_count === 0
        );

        // يُبنى هنا لا داخل الخاصيّة: موجِّهات Blade لا تُصرَّف داخل
        // قيمة خاصيّة مكوّن — تُطبع كما هي نصّاً.
        $total = $countActive;
        $subtitle = $total . ' ' . ($total == 1 ? 'تخصص نشط' : ($total == 2 ? 'تخصصان نشطان' : 'تخصصات نشطة'));
        if ($incomplete->count() && ! $showArchived) {
            $subtitle .= ' · ' . $incomplete->count() . ' غير مكتمل';
        }
        if ($countArchived) {
            $subtitle .= ' · ' . $countArchived . ' موقوف';
        }
    @endphp

    <x-page-header title="التخصصات" :subtitle="$subtitle">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة تخصص
            </button>
        </x-slot:actions>
    </x-page-header>

    @if ($incomplete->count() && ! $showArchived)
        <div class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
            <i class="ti ti-alert-triangle fs-2" aria-hidden="true"></i>
            <div>
                <strong>{{ $incomplete->count() }} {{ $incomplete->count() == 1 ? 'تخصص' : 'تخصصات' }} غير مكتمل.</strong>
                لا يستطيع طلابه تسجيل مشروع: تسجيل المشروع يتطلّب نوع مشروع ومشرفاً متاحاً معاً.
            </div>
        </div>
    @endif

    {{-- التبويبان يظهران حين يوجد موقوف فعلاً — وإلا فهما ضجيج --}}
    @if ($countArchived)
        <div class="filter-bar mb-3">
            <div class="filter-tabs" role="group" aria-label="تصفية التخصصات">
                <a href="{{ route('admin.specialize.index') }}"
                    class="filter-tab {{ $showArchived ? '' : 'is-active' }}">
                    النشطة
                    <span class="filter-count">{{ $countActive }}</span>
                </a>
                <a href="{{ route('admin.specialize.index', ['view' => 'archived']) }}"
                    class="filter-tab {{ $showArchived ? 'is-active' : '' }}">
                    <span class="filter-dot" style="background: #a1a1aa"></span>
                    الموقوفة
                    <span class="filter-count">{{ $countArchived }}</span>
                </a>
            </div>
        </div>
    @endif

    @if ($specializes->count())
        <div class="spec-grid">
            @foreach ($specializes as $spec)
                @php
                    $missing = [];
                    if ($spec->projects_count === 0) {
                        $missing[] = 'لا أنواع مشاريع';
                    }
                    if ($spec->supervisors_available_count === 0) {
                        $missing[] = 'لا مشرفين متاحين';
                    }

                    // نفس شرط حارس الحذف في المتحكّم — يُعرض هنا مسبقاً
                    // بدل أن يأتي المنع مفاجأةً بعد النقر
                    $hasPeople = $spec->students_count > 0 || $spec->supervisors_count > 0;
                @endphp

                <article
                    class="spec-card {{ $spec->isArchived() ? 'is-archived' : (count($missing) ? 'is-incomplete' : '') }}">
                    <header class="spec-head">
                        <h3 class="spec-name">
                            {{ $spec->name }}
                            @if ($spec->isArchived())
                                <span class="spec-archived">موقوف</span>
                            @endif
                        </h3>

                        <div class="btn-group">
                            @if ($spec->isArchived())
                                <form action="{{ route('admin.specialize.restore', $spec->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn-action" title="استئناف التخصص"
                                        aria-label="استئناف التخصص">
                                        <i class="ti ti-arrow-back-up" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @elseif ($hasPeople)
                                {{-- الحذف ممنوع لأن المفاتيح تُيتّم مئات
                                     السجلات. الإيقاف هو المخرج. --}}
                                <button type="button" class="btn-action btn-archive" data-bs-toggle="modal"
                                    data-bs-target="#archiveModal" data-id="{{ $spec->id }}"
                                    data-name="{{ $spec->name }}" data-students="{{ $spec->students_count }}"
                                    data-supervisors="{{ $spec->supervisors_count }}" title="إيقاف التخصص"
                                    aria-label="إيقاف التخصص">
                                    <i class="ti ti-archive" aria-hidden="true"></i>
                                </button>
                            @else
                                {{-- فارغ تماماً: الحذف هنا لا يُيتّم شيئاً --}}
                                <button type="button" class="btn-action btn-action--danger btn-delete"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $spec->id }}"
                                    data-name="{{ $spec->name }}" title="حذف" aria-label="حذف">
                                    <i class="ti ti-trash" aria-hidden="true"></i>
                                </button>
                            @endif

                            <a class="btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="{{ $spec->id }}" data-name="{{ $spec->name }}" title="تعديل" aria-label="تعديل">
                                <i class="ti ti-pencil" aria-hidden="true"></i>
                            </a>
                        </div>
                    </header>

                    {{-- كل رقم مدخل إلى قائمته المُصفّاة، لا رقم يُقرأ
                         ويُنسى — الفلاتر موجودة في صفحتَي الطلاب
                         والمشرفين أصلاً --}}
                    <div class="spec-stats">
                        <a href="{{ route('admin.students.index', ['specialize' => $spec->id]) }}" class="spec-stat">
                            <span class="spec-n">{{ $spec->students_count }}</span>
                            <span class="spec-l">طالب</span>
                        </a>

                        <a href="{{ route('admin.supervisors.index', ['specialize' => $spec->id]) }}"
                            class="spec-stat {{ $spec->supervisors_available_count === 0 ? 'is-zero' : '' }}">
                            <span class="spec-n">{{ $spec->supervisors_count }}</span>
                            <span class="spec-l">مشرف</span>
                        </a>

                        <a href="{{ route('admin.specialize.projects.index', $spec->id) }}{{ $spec->projects_count === 0 ? '#add' : '' }}"
                            class="spec-stat {{ $spec->projects_count === 0 ? 'is-zero' : '' }}">
                            <span class="spec-n">{{ $spec->projects_count }}</span>
                            {{-- كانت «مشروع» — وهي أنواع مشاريع لا مشاريع --}}
                            <span class="spec-l">نوع مشروع</span>
                        </a>
                    </div>

                    {{-- الأنواع بأسمائها وأحجام فرقها: كانت تُكتشف
                         بالنقر والانتقال إلى صفحة أخرى لكل تخصص --}}
                    @if ($spec->projects->count())
                        <div class="spec-types">
                            @foreach ($spec->projects as $type)
                                <a href="{{ route('admin.specialize.projects.index', $spec->id) }}" class="spec-type"
                                    title="حجم الفريق: من {{ $type->min }} إلى {{ $type->max }}">
                                    {{ $type->name }}
                                    <span class="spec-type-size">{{ $type->min }}–{{ $type->max }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($spec->isArchived())
                        <p class="spec-note">
                            <i class="ti ti-archive" aria-hidden="true"></i>
                            موقوف منذ {{ $spec->archived_at->translatedFormat('j F Y') }} — لا يُسجَّل عليه أحد
                            جديد، ومن فيه يعمل كما كان.
                        </p>
                    @elseif (count($missing))
                        {{-- التحذير يحمل مخرجه: كان يصف العطل ويترك الأدمن
                             يبحث عن الصفحة التي يُصلَح فيها --}}
                        <div class="spec-warn">
                            <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                            <div>
                                {{ implode(' و', $missing) }} — طلابه لا يستطيعون تسجيل مشروع.
                                <span class="spec-warn-fix">
                                    @if ($spec->projects_count === 0)
                                        <a href="{{ route('admin.specialize.projects.index', $spec->id) }}#add">
                                            أضف نوع مشروع
                                        </a>
                                    @endif
                                    @if ($spec->supervisors_available_count === 0)
                                        <a href="{{ route('admin.supervisors.index', ['specialize' => $spec->id]) }}">
                                            راجع مشرفيه
                                        </a>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    @elseif ($showArchived)
        <div class="card">
            <x-empty-state icon="ti-archive" title="لا توجد تخصصات موقوفة" class="py-6">
                <x-slot:action>
                    <a href="{{ route('admin.specialize.index') }}" class="btn btn-outline-primary">
                        العودة إلى النشطة
                    </a>
                </x-slot:action>
            </x-empty-state>
        </div>
    @else
        {{-- الطلاب والمشرفون يُعيدان إلى هنا حين لا يوجد تخصص نشط، فلا
             بدّ أن تقول الصفحة ما المطلوب فعله --}}
        <div class="card">
            <x-empty-state icon="ti-category" title="لا توجد تخصصات نشطة"
                text="التخصص أساس كل شيء: الطالب والمشرف يُسجَّلان عليه، وأنواع المشاريع تُعرَّف تحته. أضف تخصصاً، أو استأنف واحداً موقوفاً."
                class="py-6">
                <x-slot:action>
                    <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="ti ti-plus me-1" aria-hidden="true"></i>
                        إضافة تخصص
                    </button>
                </x-slot:action>
            </x-empty-state>
        </div>
    @endif

    @include('dashboard.admin.setting.specialize.create_modal')
    @include('dashboard.admin.setting.specialize.edit_modal')
    @include('dashboard.admin.setting.specialize.archive_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => 'التخصص',
        'delete_controller_name' => 'admin.specialize',
        'delete_note' => 'تُحذف معه أنواع المشاريع المعرَّفة تحته. لا يمكن حذف تخصص عليه طلاب أو مشرفون.',
    ])

@endsection

@push('js')
    <script>
        // كان إعادة فتح النافذة عند فشل التحقّق يأتي ضمن تضمين
        // \u200Edatatables_style_script\u200E، وقد زال مع الجدول. بدونه يعود
        // المستخدم إلى صفحة تبدو كأن شيئاً لم يحدث، ورسالة الخطأ
        // مخفيّة داخل نافذة مغلقة.
        @if ($errors->any() && old('submit') == 'create')
            new bootstrap.Modal(document.getElementById('createModal')).show();
        @endif
        @if ($errors->any() && old('submit') == 'update')
            new bootstrap.Modal(document.getElementById('editModal')).show();
        @endif
    </script>
@endpush
