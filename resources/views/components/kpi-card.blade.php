@props([
    'icon' => 'ti-chart-bar',
    'tone' => 'neutral',
    'value' => 0,
    'label' => '',
    'sub' => null,
    'href' => null,
    'trend' => null, // نسبة التغيّر مقارنة بالفترة السابقة (موجب/سالب) أو null
])

@php
    $trendVal = is_numeric($trend) ? (int) $trend : null;
    $trendClass = $trendVal > 0 ? 'bg-green-lt text-green' : ($trendVal < 0 ? 'bg-red-lt text-red' : 'bg-secondary-lt text-secondary');
    $trendIcon = $trendVal > 0 ? 'ti-trending-up' : ($trendVal < 0 ? 'ti-trending-down' : 'ti-minus');
@endphp

{{--
    بطاقة مؤشّر أداء موحّدة (KPI).
    <x-kpi-card icon="ti-school" tone="blue" :value="$student_count"
                label="عدد الطلاب" :href="route('admin.students.index')"
                sub="منضمّ لفرق: 120 · بدون: 30" />
--}}

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'card card-link card-link-pop']) }}>
@else
    <div {{ $attributes->merge(['class' => 'card']) }}>
@endif

    <div class="card-body">
        <div class="d-flex align-items-center">
            {{-- محايدة افتراضياً: حين يكون لون واحد ملوّناً تعرف العين أين تنظر.
                 tone="accent" للبطاقة التي تحتاج تدخّلاً فعلياً وحدها. --}}
            <span class="kpi-icon {{ $tone === 'accent' ? 'kpi-icon--accent' : '' }} me-3">
                <i class="ti {{ $icon }}"></i>
            </span>
            <div class="me-auto">
                <div class="d-flex align-items-center gap-2">
                    <div class="h1 mb-0 lh-1">{{ $value }}</div>
                    @if (! is_null($trendVal))
                        <span class="badge {{ $trendClass }}" title="مقارنة بآخر لقطة">
                            <i class="ti {{ $trendIcon }}"></i>
                            {{ $trendVal > 0 ? '+' : '' }}{{ $trendVal }}%
                        </span>
                    @endif
                </div>
                <div class="text-secondary mt-1">{{ $label }}</div>
            </div>
            @if ($href)
                <i class="ti ti-chevron-left text-secondary align-self-start"></i>
            @endif
        </div>

        @if ($sub)
            <div class="mt-3 pt-2 border-top text-secondary small">{{ $sub }}</div>
        @endif
    </div>

@if ($href)
    </a>
@else
    </div>
@endif
