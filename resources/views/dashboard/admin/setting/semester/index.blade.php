@extends('layouts.admin.admin')
@section('title', 'الفصول الدراسية')

@section('crumbs')
    <x-crumb>الفصول الدراسية</x-crumb>
@endsection

{{--
    الفصول الدراسية — الفصل الحالي، ثم خطّ زمني لكل الفصول.

    كان الأرشيف صفوفاً مفردة («4 مشروعاً في أرشيفه») بأزرار صغيرة، والاسم خاماً
    («2023\2022»)، ولا يقارَن فصل بآخر. الآن: الفصل الحالي بتوزيع حالاته في شريط
    واحد ونسبة الإكمال ومتوسط الدرجة، ثم كل الفصول على خطّ واحد بأرقامها.
    والتفعيل — أخطر مفتاح في النظام — يمرّ بنافذته ويُسجَّل في سجلّ التدقيق.
--}}

@section('content')

    @php
        $statusOrder = config('statuses.order');
        $statusMap = config('statuses.map');
        $statusTotal = $statusCounts->sum();
        $tidy = function ($term) {
            $p = $term->parts();
            // السنة معزولة باتّجاه LTR: داخل نصّ عربي تنقلب «2022–23» إلى «23–2022»
            return $p['year'] ? $p['term'] . ' · ' . "\u{2066}" . $p['year'] . "\u{2069}" : $p['term'];
        };
    @endphp

    <x-page-header title="الفصول الدراسية"
        :subtitle="$active ? 'النظام يعمل الآن على: ' . $tidy($active) : 'لا يوجد فصل مُفعَّل'">
        <x-slot:actions>
            <button type="button" class="btn btn-primary btn-create" data-bs-toggle="modal"
                data-bs-target="#createModal">
                <i class="ti ti-plus me-1" aria-hidden="true"></i>
                إضافة فصل دراسي
            </button>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ الفصل الحالي ═══ --}}
    @if ($active)
        @php $ap = $active->parts(); @endphp
        <section class="tm-now mb-4">
            <div class="tm-now-head">
                <span class="tm-now-label">
                    <span class="term-pulse" aria-hidden="true"></span>
                    الفصل الحالي
                </span>
                <h2 class="tm-now-name">
                    {{ $ap['term'] }}
                    @if ($ap['year'])<span dir="ltr">{{ $ap['year'] }}</span>@endif
                </h2>
                <p class="tm-now-note">التقديم والمجموعات واللوحات والإحصائيات كلها تُقرأ من هذا الفصل.</p>

                <a class="btn-action btn-edit tm-now-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                    data-id="{{ $active->id }}" data-name="{{ $active->name }}" title="تعديل الاسم" aria-label="تعديل الاسم">
                    <i class="ti ti-pencil" aria-hidden="true"></i>
                </a>
            </div>

            <div class="tm-now-kpis">
                <a href="{{ route('admin.groups.index', ['semester' => $active->id]) }}" class="tm-kpi">
                    <span class="tm-kpi-n">{{ $active->projects_count }}</span>
                    <span class="tm-kpi-l">مشروع مسجَّل</span>
                </a>
                <div class="tm-kpi">
                    <span class="tm-kpi-n">{{ is_null($active->completion) ? '—' : $active->completion . '%' }}</span>
                    <span class="tm-kpi-l">اكتمل من المقبولة</span>
                </div>
                <div class="tm-kpi">
                    <span class="tm-kpi-n">{{ $active->avg_grade ?? '—' }}</span>
                    <span class="tm-kpi-l">متوسط الدرجة{{ $active->graded_count ? ' · ' . $active->graded_count . ' مقيَّم' : '' }}</span>
                </div>
                @php $waiting = (int) ($statusCounts['request'] ?? 0); @endphp
                <a href="{{ route('admin.groups.index', ['semester' => $active->id, 'status' => 'request']) }}"
                    class="tm-kpi {{ $waiting ? 'is-warn' : '' }}">
                    <span class="tm-kpi-n">{{ $waiting }}</span>
                    <span class="tm-kpi-l">طلب ينتظر ردّ المشرف</span>
                </a>
            </div>

            {{-- توزيع الحالات في شريط واحد — كانت ستة أرقام متفرّقة --}}
            @if ($statusTotal)
                <div class="tm-dist">
                    <div class="tm-bar" role="img"
                        aria-label="{{ collect($statusOrder)->map(fn ($st) => __('site.' . $st) . ' ' . ($statusCounts[$st] ?? 0))->implode('، ') }}">
                        @foreach ($statusOrder as $st)
                            @if (($statusCounts[$st] ?? 0) > 0)
                                <span style="width: {{ round($statusCounts[$st] / $statusTotal * 100, 2) }}%; background: {{ $statusMap[$st]['hex'] }}"
                                    title="{{ __('site.' . $st) }}: {{ $statusCounts[$st] }}"></span>
                            @endif
                        @endforeach
                    </div>
                    <ul class="tm-legend">
                        @foreach ($statusOrder as $st)
                            <li>
                                <a href="{{ route('admin.groups.index', ['semester' => $active->id, 'status' => $st]) }}">
                                    <span class="tm-dot" style="background: {{ $statusMap[$st]['hex'] }}"></span>
                                    {{ __('site.' . $st) }}
                                    <b>{{ $statusCounts[$st] ?? 0 }}</b>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
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

    {{-- ═══ كل الفصول على خطّ واحد ═══ --}}
    <h2 class="dash-block-title mb-2">
        <i class="ti ti-timeline" aria-hidden="true"></i>
        كل الفصول
        <span class="tm-count">{{ $semesters->count() }}</span>
    </h2>

    <div class="tm-line">
        @foreach ($semesters as $term)
            @php
                $p = $term->parts();
                $hasProjects = $term->projects_count > 0;
                $isActive = (bool) $term->is_active;
            @endphp

            <article class="tm-row {{ $isActive ? 'is-active' : '' }}">
                <span class="tm-node" aria-hidden="true">
                    <i class="ti {{ $isActive ? 'ti-player-play-filled' : ($hasProjects ? 'ti-archive' : 'ti-calendar') }}"></i>
                </span>

                <div class="tm-body">
                    <div class="tm-title">
                        <h3>{{ $p['term'] }}</h3>
                        @if ($p['year'])<span class="tm-year" dir="ltr">{{ $p['year'] }}</span>@endif
                        @if ($isActive)<span class="tm-badge">الحالي</span>@endif
                    </div>

                    @if ($hasProjects)
                        <div class="tm-facts">
                            <a href="{{ route('admin.groups.index', ['semester' => $term->id]) }}" class="tm-fact">
                                <b>{{ $term->projects_count }}</b> مشروعاً
                            </a>
                            <span class="tm-fact"><b>{{ is_null($term->completion) ? '—' : $term->completion . '%' }}</b> اكتمل</span>
                            <span class="tm-fact"><b>{{ $term->avg_grade ?? '—' }}</b> متوسط الدرجة</span>
                        </div>
                        {{-- حجم الفصل مقارنةً بأكبرها — والجزء الداكن ما اكتمل منه --}}
                        <span class="tm-size" aria-hidden="true">
                            <span style="width: {{ round($term->projects_count / $maxProjects * 100) }}%">
                                <span style="width: {{ $term->projects_count ? round($term->complete_count / $term->projects_count * 100) : 0 }}%"></span>
                            </span>
                        </span>
                    @else
                        <p class="tm-empty">لا مشاريع بعد{{ $isActive ? '' : ' — يمكن حذفه' }}</p>
                    @endif
                </div>

                <div class="tm-actions">
                    @unless ($isActive)
                        {{-- التفعيل نافذة تقول ما يتغيّر — لا زرّ يقلب النظام بنقرة --}}
                        <button type="button" class="btn btn-sm btn-outline-primary btn-activate"
                            data-bs-toggle="modal" data-bs-target="#activateModal" data-id="{{ $term->id }}"
                            data-name="{{ $tidy($term) }}" data-projects="{{ $term->projects_count }}">
                            <i class="ti ti-player-play me-1" aria-hidden="true"></i>
                            تفعيل
                        </button>
                    @endunless

                    <div class="dropdown">
                        <button type="button" class="btn-action" data-bs-toggle="dropdown" aria-expanded="false"
                            title="إجراءات" aria-label="إجراءات {{ $term->name }}">
                            <i class="ti ti-dots" aria-hidden="true"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a href="#" class="dropdown-item btn-edit" data-bs-toggle="modal" data-bs-target="#editModal"
                                data-id="{{ $term->id }}" data-name="{{ $term->name }}">
                                <i class="ti ti-pencil me-2" aria-hidden="true"></i> تعديل الاسم
                            </a>
                            @if ($hasProjects)
                                <a href="{{ route('admin.groups.index', ['semester' => $term->id]) }}" class="dropdown-item">
                                    <i class="ti ti-users-group me-2" aria-hidden="true"></i> عرض مجموعاته
                                </a>
                            @endif
                            @if (! $hasProjects && ! $isActive)
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item text-danger btn-delete" data-bs-toggle="modal"
                                    data-bs-target="#deleteModal" data-id="{{ $term->id }}" data-name="{{ $term->name }}">
                                    <i class="ti ti-trash me-2" aria-hidden="true"></i> حذف
                                </a>
                            @elseif (! $isActive)
                                <div class="dropdown-divider"></div>
                                <span class="dropdown-item disabled"
                                    title="يحتوي {{ $term->projects_count }} مشروعاً — حذفه يدمر أرشيف النتائج">
                                    <i class="ti ti-lock me-2" aria-hidden="true"></i> لا يُحذف — فيه أرشيف
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

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
