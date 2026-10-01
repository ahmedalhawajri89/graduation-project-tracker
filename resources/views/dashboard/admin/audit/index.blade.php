@extends('layouts.admin.admin')
@section('title', __('سجلّ التدقيق'))

@section('crumbs')
    <x-crumb>{{ __('سجلّ التدقيق') }}</x-crumb>
@endsection

{{--
    سجلّ التدقيق — خطّ زمني.

    كان قائمة مسطّحة: أيقونة القلم لكل حدث تقريباً، والتغيير نفسه غائب، و«منذ
    يومين» بلا تجميع، وحقول تاريخ بصيغة أجنبية. صار: ملخّص لما يجري الآن، ومدد
    سريعة، ثم الأحداث مجمّعة باليوم على خطّ واحد، لكل فئة لونها وأيقونتها، وما
    تغيّر شارات في السطر نفسه. السجلّ للقراءة فقط — لا مسار يعدّله.
--}}

@section('content')

    @php
        $filterQuery = array_filter([
            'scope' => $scope,
            'action' => $currentAction,
            'role' => $currentRole,
            'period' => $period === 'custom' ? null : $period,
            'from' => $period === 'custom' ? request('from') : null,
            'to' => $period === 'custom' ? request('to') : null,
        ]);

        $tabs = [
            [null, __('الكل'), $countAll, null],
            ['grade', __('الدرجات'), $countGrade, 'grade'],
            ['work', __('سير العمل'), $countWork, 'work'],
            ['defense', __('المناقشات'), $countDefense ?? 0, 'work'],
            ['lifecycle', __('الحذف والاسترجاع'), $countLifecycle, 'danger'],
        ];

        // شارات الفلاتر المطبّقة — كلٌّ يُزال وحده
        $applied = [];
        if ($currentAction && isset($actions[$currentAction])) {
            $applied[] = [__('الحدث: :value', ['value' => $actions[$currentAction]]), array_diff_key($filterQuery, ['action' => 1])];
        }
        if ($currentRole && isset($roles[$currentRole])) {
            $applied[] = [__('الفاعل: :value', ['value' => $roles[$currentRole]]), array_diff_key($filterQuery, ['role' => 1])];
        }
        if ($period === 'custom' && (request('from') || request('to'))) {
            $applied[] = [__('من :from إلى :to', ['from' => request('from') ?: '…', 'to' => request('to') ?: '…']), array_diff_key($filterQuery, ['from' => 1, 'to' => 1])];
        }

        $days = $logs->getCollection()->groupBy(fn ($log) => $log->created_at?->toDateString());
    @endphp

    <x-page-header title="{{ __('سجلّ التدقيق') }}">
        <x-slot:actions>
            <span class="at-readonly" title="{{ __('لا يمكن تعديل السجلّ أو حذفه من النظام') }}">
                <i class="ti ti-lock" aria-hidden="true"></i>
                {{ __('للقراءة فقط') }}
            </span>
            <a href="{{ route('admin.audit.export', $filterQuery) }}" class="btn btn-outline-primary">
                <i class="ti ti-file-spreadsheet me-1" aria-hidden="true"></i>
                {{ __('تصدير') }}
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- ═══ ما يجري الآن ═══ --}}
    <section class="at-summary mb-4" aria-label="{{ __('ملخّص النشاط') }}">
        <div class="at-stat">
            <span class="at-stat-n">{{ $summary['today'] }}</span>
            <span class="at-stat-l">{{ __('حدثاً اليوم') }}</span>
        </div>
        <div class="at-stat">
            <span class="at-stat-n">{{ $summary['week'] }}</span>
            <span class="at-stat-l">{{ __('في آخر 7 أيام') }}</span>
        </div>
        <div class="at-stat" data-cat="grade">
            <span class="at-stat-n">{{ $summary['grades'] }}</span>
            <span class="at-stat-l">{{ __('حدث درجات في 30 يوماً') }}</span>
        </div>
        <div class="at-stat">
            @if ($summary['topActor'])
                <span class="at-stat-n at-stat-name">{{ $summary['topActor']->actor_name }}</span>
                <span class="at-stat-l">
                    {{ __('الأنشط هذا الأسبوع · :n أحداث', ['n' => $summary['topActor']->total]) }}
                </span>
            @else
                <span class="at-stat-n">—</span>
                <span class="at-stat-l">{{ __('لا نشاط هذا الأسبوع') }}</span>
            @endif
        </div>
    </section>

    {{-- ═══ التصفية ═══ --}}
    <div class="filter-bar at-filters mb-3">
        <div class="filter-tabs" role="group" aria-label="{{ __('فئة الأحداث') }}">
            @foreach ($tabs as [$key, $label, $count, $cat])
                <a href="{{ route('admin.audit.index', array_merge(array_diff_key($filterQuery, ['scope' => 1]), $key ? ['scope' => $key] : [])) }}"
                    class="filter-tab {{ $scope === $key ? 'is-active' : '' }} {{ $count === 0 && $scope !== $key ? 'is-empty' : '' }}">
                    @if ($cat)
                        <span class="filter-dot at-dot" data-cat="{{ $cat }}"></span>
                    @endif
                    {{ $label }}
                    <span class="filter-count">{{ $count }}</span>
                </a>
            @endforeach
        </div>

        <form action="{{ route('admin.audit.index') }}" method="get" class="at-filter-form" data-at-filters>
            @if ($scope)
                <input type="hidden" name="scope" value="{{ $scope }}">
            @endif

            <div class="at-periods" role="radiogroup" aria-label="{{ __('المدّة') }}">
                @foreach (['' => __('الكل')] + \App\Models\AuditLog::periods() + ['custom' => __('مخصّص')] as $key => $label)
                    <label>
                        <input type="radio" name="period" value="{{ $key }}" @checked((string) $period === (string) $key)>
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            <div class="at-range" data-at-range @if ($period !== 'custom') hidden @endif>
                <label>
                    <span>{{ __('من') }}</span>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}" lang="{{ app()->getLocale() }}">
                </label>
                <label>
                    <span>{{ __('إلى') }}</span>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}" lang="{{ app()->getLocale() }}">
                </label>
            </div>

            <select name="action" class="form-select at-select" aria-label="{{ __('الحدث') }}">
                <option value="">{{ __('كل الأحداث') }}</option>
                @foreach ($actions as $key => $label)
                    <option value="{{ $key }}" @selected($currentAction === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="role" class="form-select at-select" aria-label="{{ __('الفاعل') }}">
                <option value="">{{ __('كل الأدوار') }}</option>
                @foreach ($roles as $key => $label)
                    <option value="{{ $key }}" @selected($currentRole === $key)>{{ $label }}</option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary at-apply">
                <i class="ti ti-filter me-1" aria-hidden="true"></i>
                {{ __('تطبيق') }}
            </button>
        </form>

        @if ($applied)
            <div class="at-applied">
                @foreach ($applied as [$text, $without])
                    <a href="{{ route('admin.audit.index', $without) }}" class="at-applied-chip" title="{{ __('إزالة') }}">
                        {{ $text }}
                        <i class="ti ti-x" aria-hidden="true"></i>
                    </a>
                @endforeach
                <a href="{{ route('admin.audit.index') }}" class="at-applied-clear">{{ __('مسح الكل') }}</a>
            </div>
        @endif
    </div>

    {{-- ═══ الخطّ الزمني ═══ --}}
    <div class="at-timeline">
        @forelse ($days as $date => $dayLogs)
            <section class="at-day">
                <h2 class="at-day-head">
                    {{ \App\Support\AuditPresenter::dayLabel(\Illuminate\Support\Carbon::parse($date)) }}
                    <span>{{ $dayLogs->count() }} {{ $dayLogs->count() === 1 ? __('حدث') : __('أحداث') }}</span>
                </h2>
                <div class="at-list">
                    @foreach ($dayLogs as $log)
                        <x-audit-row :log="$log" />
                    @endforeach
                </div>
            </section>
        @empty
            <div class="card">
                <x-empty-state icon="ti-history" title="{{ __('لا أحداث مطابقة') }}"
                    text="{{ __('لم يُسجَّل حدث ضمن هذه التصفية. السجلّ يبدأ من أول تغيير يمسّ درجةً أو مشروعاً أو حساباً.') }}"
                    class="py-6" />
            </div>
        @endforelse

        @if ($logs->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {!! $logs->links() !!}
            </div>
        @endif
    </div>

@endsection

@push('js')
    <script>
        // المدّة: السريعة تُطبَّق فوراً، و«مخصّص» يُظهر حقلَي التاريخ
        (function () {
            var form = document.querySelector('[data-at-filters]');
            if (!form) return;
            var range = form.querySelector('[data-at-range]');
            form.querySelectorAll('input[name="period"]').forEach(function (radio) {
                radio.addEventListener('change', function () {
                    var custom = radio.value === 'custom';
                    range.hidden = !custom;
                    range.querySelectorAll('input').forEach(function (i) { i.disabled = !custom; });
                    if (!custom) form.submit();
                    else range.querySelector('input').focus();
                });
            });
            // المدى المخفي لا يُرسل — وإلا عُدّ «مخصّصاً» بتاريخين فارغين
            if (range.hidden) range.querySelectorAll('input').forEach(function (i) { i.disabled = true; });
        })();
    </script>
@endpush
