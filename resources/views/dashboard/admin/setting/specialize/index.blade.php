@extends('layouts.admin.admin')
@section('title', __('التخصصات'))

@section('crumbs')
    <x-crumb>{{ __('التخصصات') }}</x-crumb>
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
        $subtitle = $total . ' ' . ($total == 1 ? __('تخصص نشط') : ($total == 2 ? __('تخصصان نشطان') : __('تخصصات نشطة')));
        if ($incomplete->count() && ! $showArchived) {
            $subtitle .= ' · ' . __(':n غير مكتمل', ['n' => $incomplete->count()]);
        }
        if ($countArchived) {
            $subtitle .= ' · ' . __(':n موقوف', ['n' => $countArchived]);
        }
    @endphp

    <x-page-header :title="__('التخصصات')" :subtitle="$subtitle">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                {{ __('إضافة تخصص') }}
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ الفصل عبر التخصصات النشطة ═══ --}}
    @unless ($showArchived)
        <section class="sp-summary mb-4" aria-label="{{ __('ملخّص التخصصات') }}">
            <a href="{{ route('admin.students.index', ['group' => 'none']) }}" class="sp-sum">
                <span class="sp-sum-n">{{ $summary['withoutTeam'] }}<small>/{{ $summary['students'] }}</small></span>
                <span class="sp-sum-l">{{ __('طالب بلا فريق') }}</span>
            </a>
            <a href="{{ route('admin.supervisors.index') }}" class="sp-sum {{ $summary['seatsFree'] === 0 ? 'is-bad' : '' }}">
                <span class="sp-sum-n">{{ $summary['seatsFree'] }}<small>/{{ $summary['seatsTotal'] }}</small></span>
                <span class="sp-sum-l">{{ __('مقعد إشراف متاح') }}</span>
            </a>
            <a href="{{ route('admin.groups.index') }}" class="sp-sum">
                <span class="sp-sum-n">{{ $summary['running'] }}</span>
                <span class="sp-sum-l">{{ __('مشروعاً جارياً هذا الفصل') }}</span>
            </a>
            <div class="sp-sum {{ $summary['ready'] < $countActive ? 'is-warn' : 'is-ok' }}">
                <span class="sp-sum-n">{{ $summary['ready'] }}<small>/{{ $countActive }}</small></span>
                <span class="sp-sum-l">
                    <i class="ti {{ $summary['ready'] < $countActive ? 'ti-alert-triangle' : 'ti-circle-check' }}" aria-hidden="true"></i>
                    {{ $summary['ready'] < $countActive ? __('جاهزة — والباقي ينقصه ما يلزم') : __('جاهزة لتسجيل المشاريع') }}
                </span>
            </div>
        </section>
    @endunless

    {{-- التبويبان يظهران حين يوجد موقوف فعلاً — وإلا فهما ضجيج --}}
    @if ($countArchived)
        <div class="filter-bar mb-3">
            <div class="filter-tabs" role="group" aria-label="{{ __('تصفية التخصصات') }}">
                <a href="{{ route('admin.specialize.index') }}"
                    class="filter-tab {{ $showArchived ? '' : 'is-active' }}">
                    {{ __('النشطة') }}
                    <span class="filter-count">{{ $countActive }}</span>
                </a>
                <a href="{{ route('admin.specialize.index', ['view' => 'archived']) }}"
                    class="filter-tab {{ $showArchived ? 'is-active' : '' }}">
                    <span class="filter-dot" style="background: #a1a1aa"></span>
                    {{ __('الموقوفة') }}
                    <span class="filter-count">{{ $countArchived }}</span>
                </a>
            </div>
        </div>
    @endif

    @if ($specializes->count())
        <div class="sp-grid">
            @foreach ($specializes as $spec)
                @php
                    $missing = [];
                    if ($spec->projects_count === 0) {
                        $missing[] = __('لا أنواع مشاريع');
                    }
                    if ($spec->supervisors_available_count === 0) {
                        $missing[] = __('لا مشرفين متاحين');
                    }

                    // نفس شرط حارس الحذف في المتحكّم — يُعرض هنا مسبقاً
                    // بدل أن يأتي المنع مفاجأةً بعد النقر
                    $hasPeople = $spec->students_count > 0 || $spec->supervisors_count > 0;

                    $inTeam = $spec->students_in_team_count;
                    $noTeam = max(0, $spec->students_count - $inTeam);
                    $teamPct = $spec->students_count ? round($inTeam / $spec->students_count * 100) : 0;
                    $seatsFree = max(0, $spec->seats_total - $spec->seats_used);
                    $seatPct = $spec->seats_total ? min(100, round($spec->seats_used / $spec->seats_total * 100)) : 0;

                    $state = $spec->isArchived() ? 'archived' : (count($missing) ? 'incomplete' : 'ready');
                    // حرف الكلمة المميِّزة: «تكنولوجيا» و«علوم» بادئة على أكثر الأسماء، و«ال» كذلك
                    $word = preg_split('/\s+/u', trim(preg_replace('/^(تكنولوجيا|علوم|هندسة|نظم)\s+/u', '', $spec->name)))[0] ?? '';
                    $mono = mb_substr(preg_replace('/^ال/u', '', $word) ?: $spec->name, 0, 1);
                @endphp

                <article class="sp-card is-{{ $state }}" style="--h: {{ ($spec->id * 67) % 360 }}">
                    <header class="sp-head">
                        <span class="sp-mono" aria-hidden="true">{{ $mono }}</span>
                        <div class="sp-title">
                            <h3>{{ $spec->name }}</h3>
                            <span class="sp-state">
                                @if ($state === 'archived')
                                    <i class="ti ti-archive" aria-hidden="true"></i>
                                    {{ __('موقوف منذ :date', ['date' => $spec->archived_at->translatedFormat('j F Y')]) }}
                                @elseif ($state === 'incomplete')
                                    <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                                    {{ __('غير مكتمل') }}
                                @else
                                    <i class="ti ti-circle-check" aria-hidden="true"></i>
                                    {{ __('جاهز لتسجيل المشاريع') }}
                                @endif
                            </span>
                        </div>

                        <div class="dropdown">
                            <button type="button" class="btn-action" data-bs-toggle="dropdown" aria-expanded="false"
                                title="{{ __('إجراءات') }}" aria-label="{{ __('إجراءات :name', ['name' => $spec->name]) }}">
                                <i class="ti ti-dots" aria-hidden="true"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                                    data-id="{{ $spec->id }}" data-name="{{ $spec->name }}">
                                    <i class="ti ti-pencil me-2" aria-hidden="true"></i> {{ __('تعديل الاسم') }}
                                </a>
                                <a href="{{ route('admin.specialize.projects.index', $spec->id) }}" class="dropdown-item">
                                    <i class="ti ti-category me-2" aria-hidden="true"></i> {{ __('إدارة أنواع المشاريع') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                @if ($spec->isArchived())
                                    <form action="{{ route('admin.specialize.restore', $spec->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ti ti-arrow-back-up me-2" aria-hidden="true"></i> {{ __('استئناف التخصص') }}
                                        </button>
                                    </form>
                                @elseif ($hasPeople)
                                    {{-- الحذف ممنوع لأن المفاتيح تُيتّم مئات السجلات. الإيقاف هو المخرج. --}}
                                    <a href="#" class="dropdown-item btn-archive" data-bs-toggle="modal"
                                        data-bs-target="#archiveModal" data-id="{{ $spec->id }}"
                                        data-name="{{ $spec->name }}" data-students="{{ $spec->students_count }}"
                                        data-supervisors="{{ $spec->supervisors_count }}">
                                        <i class="ti ti-archive me-2" aria-hidden="true"></i> {{ __('إيقاف التخصص') }}
                                    </a>
                                @else
                                    {{-- فارغ تماماً: الحذف هنا لا يُيتّم شيئاً --}}
                                    <a href="#" class="dropdown-item text-danger btn-delete" data-bs-toggle="modal"
                                        data-bs-target="#deleteModal" data-id="{{ $spec->id }}" data-name="{{ $spec->name }}">
                                        <i class="ti ti-trash me-2" aria-hidden="true"></i> {{ __('حذف') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </header>

                    {{-- ثلاثة مقاييس، كلٌّ رابط إلى قائمته المصفّاة --}}
                    <div class="sp-metrics">
                        <a href="{{ route('admin.students.index', ['specialize' => $spec->id]) }}" class="sp-metric">
                            <span class="sp-metric-top">
                                <span class="sp-metric-n">{{ $spec->students_count }}</span>
                                <span class="sp-metric-l">{{ __('طالب') }}</span>
                            </span>
                            <span class="sp-bar" aria-hidden="true"><span style="width: {{ $teamPct }}%"></span></span>
                            <span class="sp-metric-sub">{{ __(':n في فرق', ['n' => $inTeam]) }}</span>
                        </a>

                        <a href="{{ route('admin.supervisors.index', ['specialize' => $spec->id]) }}"
                            class="sp-metric {{ $spec->supervisors_available_count === 0 || ($spec->seats_total && ! $seatsFree) ? 'is-bad' : '' }}">
                            <span class="sp-metric-top">
                                <span class="sp-metric-n">{{ $spec->supervisors_count }}</span>
                                <span class="sp-metric-l">{{ __('مشرف') }}</span>
                            </span>
                            <span class="sp-bar is-seats" aria-hidden="true"><span style="width: {{ $seatPct }}%"></span></span>
                            {{-- السعة في الشريط، والنصّ الكامل عند المرور — الخلية ضيّقة --}}
                            <span class="sp-metric-sub" title="{{ __(':free مقعد متاح من :total (:used مشغول)', ['free' => $seatsFree, 'total' => $spec->seats_total, 'used' => $spec->seats_used]) }}">
                                {{ $seatsFree }} {{ $seatsFree === 1 ? __('مقعد متاح') : __('مقعداً متاحاً') }}
                            </span>
                        </a>

                        <a href="{{ route('admin.groups.index') }}" class="sp-metric">
                            <span class="sp-metric-top">
                                <span class="sp-metric-n">{{ $spec->running_count }}</span>
                                <span class="sp-metric-l">{{ __('مشروع جارٍ') }}</span>
                            </span>
                            <span class="sp-metric-sub">{{ __('هذا الفصل') }}</span>
                        </a>
                    </div>

                    @if ($noTeam && ! $spec->isArchived())
                        <a href="{{ route('admin.students.index', ['specialize' => $spec->id, 'group' => 'none']) }}" class="sp-callout">
                            <i class="ti ti-user-exclamation" aria-hidden="true"></i>
                            {{ __(':n طالباً بلا فريق', ['n' => $noTeam]) }}
                            <i class="ti ti-chevron-left" aria-hidden="true"></i>
                        </a>
                    @endif

                    {{-- الأنواع بأسمائها وأحجام فرقها وما يجري عليها --}}
                    <div class="sp-types">
                        <div class="sp-types-head">
                            <span>{{ __('أنواع المشاريع') }}</span>
                            <a href="{{ route('admin.specialize.projects.index', $spec->id) }}">{{ __('إدارة') }}</a>
                        </div>
                        @forelse ($spec->projects as $type)
                            <div class="sp-type">
                                <span class="sp-type-name">{{ $type->name }}</span>
                                <span class="sp-type-size" title="{{ __('حجم الفريق') }}">
                                    <i class="ti ti-users" aria-hidden="true"></i>
                                    {{ $type->min }}–{{ $type->max }}
                                </span>
                                <span class="sp-type-count">{{ __(':n مشروع', ['n' => $type->current_count]) }}</span>
                            </div>
                        @empty
                            <a href="{{ route('admin.specialize.projects.index', $spec->id) }}#add" class="sp-type-empty">
                                <i class="ti ti-plus" aria-hidden="true"></i>
                                {{ __('أضف أول نوع مشروع — بدونه لا يسجّل طلابه مشاريعهم') }}
                            </a>
                        @endforelse
                    </div>

                    @if ($state === 'archived')
                        <p class="sp-note">
                            {{ __('لا يُسجَّل عليه أحد جديد، ومن فيه يعمل كما كان.') }}
                        </p>
                    @elseif ($state === 'incomplete')
                        {{-- التحذير يحمل مخرجه --}}
                        <div class="sp-warn">
                            <i class="ti ti-alert-triangle" aria-hidden="true"></i>
                            <div>
                                {{ __(':missing — طلابه لا يستطيعون تسجيل مشروع.', ['missing' => implode(__(' و'), $missing)]) }}
                                <span class="sp-warn-fix">
                                    @if ($spec->projects_count === 0)
                                        <a href="{{ route('admin.specialize.projects.index', $spec->id) }}#add">{{ __('أضف نوع مشروع') }}</a>
                                    @endif
                                    @if ($spec->supervisors_available_count === 0)
                                        <a href="{{ route('admin.supervisors.index', ['specialize' => $spec->id]) }}">{{ __('راجع مشرفيه') }}</a>
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach

            @unless ($showArchived)
                <button type="button" class="sp-add btn-create" data-bs-toggle="modal" data-bs-target="#createModal">
                    <i class="ti ti-plus" aria-hidden="true"></i>
                    <b>{{ __('إضافة تخصص') }}</b>
                    <span>{{ __('ثم أضف أنواع مشاريعه، وسجّل عليه الطلاب والمشرفين') }}</span>
                </button>
            @endunless
        </div>
    @elseif ($showArchived)
        <div class="card">
            <x-empty-state icon="ti-archive" title="{{ __('لا توجد تخصصات موقوفة') }}" class="py-6">
                <x-slot:action>
                    <a href="{{ route('admin.specialize.index') }}" class="btn btn-outline-primary">
                        {{ __('العودة إلى النشطة') }}
                    </a>
                </x-slot:action>
            </x-empty-state>
        </div>
    @else
        {{-- الطلاب والمشرفون يُعيدان إلى هنا حين لا يوجد تخصص نشط، فلا
             بدّ أن تقول الصفحة ما المطلوب فعله --}}
        <div class="card">
            <x-empty-state icon="ti-category" title="{{ __('لا توجد تخصصات نشطة') }}"
                text="{{ __('التخصص أساس كل شيء: الطالب والمشرف يُسجَّلان عليه، وأنواع المشاريع تُعرَّف تحته. أضف تخصصاً، أو استأنف واحداً موقوفاً.') }}"
                class="py-6">
                <x-slot:action>
                    <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="ti ti-plus me-1" aria-hidden="true"></i>
                        {{ __('إضافة تخصص') }}
                    </button>
                </x-slot:action>
            </x-empty-state>
        </div>
    @endif

    @include('dashboard.admin.setting.specialize.create_modal')
    @include('dashboard.admin.setting.specialize.edit_modal')
    @include('dashboard.admin.setting.specialize.archive_modal')
    @include('dashboard.component.delete_modal', [
        'delete_title' => __('التخصص'),
        'delete_controller_name' => 'admin.specialize',
        'delete_note' => __('تُحذف معه أنواع المشاريع المعرَّفة تحته. لا يمكن حذف تخصص عليه طلاب أو مشرفون.'),
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
