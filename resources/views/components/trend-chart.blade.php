@props([
    'series' => null,   // مجموعة StatSnapshot مرتّبة تصاعدياً بالتاريخ
    'min' => 7,         // أقلّ عدد نقاط يستحق خطاً — دونه يبقى القسم مخفياً
    'id' => 'trend-chart',
])

@php
    $rows = collect($series ?? []);
    $enough = $rows->count() >= $min;
@endphp

@if ($enough)
    @php
        $labels = $rows->map(fn ($r) => \Illuminate\Support\Carbon::parse($r->date)->format('j M'))->values();
        $groups = $rows->map(fn ($r) => (int) $r->groups)->values();
        $noGroup = $rows->map(fn ($r) => (int) $r->not_has_group)->values();
    @endphp

    <div class="trendchart">
        <canvas id="{{ $id }}" height="190"
            aria-label="تطوّر المجموعات النشطة والطلاب بلا فريق خلال {{ $rows->count() }} يوماً"
            role="img"></canvas>
    </div>

    {{-- مستضافة محلياً لا من CDN: حذفنا ApexCharts تحديداً لأنه خارجي،
         فلا معنى لإعادة الاعتماد الخارجي من باب آخر. --}}
    @once
        @push('js')
            <script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
        @endpush
    @endonce

    @push('js')
        <script>
            (function () {
                var el = document.getElementById(@json($id));
                if (!el || typeof Chart === 'undefined') return;

                // الألوان من توكنز نظام التصميم لا مكتوبة هنا
                var ds = getComputedStyle(document.documentElement);
                var ink = ds.getPropertyValue('--ds-ink').trim() || '#09090b';
                var blue = ds.getPropertyValue('--ds-brand-600').trim() || '#2563eb';
                var rule = ds.getPropertyValue('--ds-border').trim() || '#e4e4e7';
                var mute = ds.getPropertyValue('--ds-ink-mute').trim() || '#5c5c66';
                var font = "'IBM Plex Sans Arabic', sans-serif";

                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                function fill(ctx, hex) {
                    var g = ctx.createLinearGradient(0, 0, 0, 190);
                    g.addColorStop(0, hex + '22');
                    g.addColorStop(1, hex + '00');
                    return g;
                }
                var ctx = el.getContext('2d');

                new Chart(el, {
                    type: 'line',
                    data: {
                        labels: @json($labels),
                        datasets: [
                            {
                                label: 'مجموعات نشطة',
                                data: @json($groups),
                                borderColor: ink,
                                backgroundColor: fill(ctx, ink),
                                fill: true,
                            },
                            {
                                label: 'طلاب بلا فريق',
                                data: @json($noGroup),
                                borderColor: blue,
                                backgroundColor: fill(ctx, blue),
                                fill: true,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: reduce ? false : { duration: 900, easing: 'easeOutQuart' },
                        interaction: { mode: 'index', intersect: false },
                        elements: {
                            line: { borderWidth: 2, tension: 0.35 },
                            point: { radius: 0, hoverRadius: 4, hitRadius: 12 },
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'start',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'line',
                                    boxWidth: 18,
                                    padding: 18,
                                    color: mute,
                                    font: { family: font, size: 12.5 },
                                },
                            },
                            tooltip: {
                                rtl: true,
                                backgroundColor: ink,
                                padding: 10,
                                cornerRadius: 8,
                                displayColors: true,
                                usePointStyle: true,
                                titleFont: { family: font, size: 12.5 },
                                bodyFont: { family: font, size: 12.5 },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { color: rule },
                                ticks: {
                                    color: mute,
                                    font: { family: font, size: 11.5 },
                                    maxRotation: 0,
                                    autoSkipPadding: 24,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: rule, drawTicks: false },
                                ticks: {
                                    color: mute,
                                    font: { family: font, size: 11.5 },
                                    padding: 10,
                                    maxTicksLimit: 4,
                                },
                            },
                        },
                    },
                });
            })();
        </script>
    @endpush
@else
    {{-- لا يُرسم خط من نقطتين. اللقطات تُلتقط ليلياً فيطول الخط تلقائياً. --}}
    <p class="dist-empty">
        يحتاج الرسم {{ $min }} أيام من البيانات على الأقل — تُلتقط لقطة كل ليلة،
        والمتوفّر الآن {{ $rows->count() }}.
    </p>
@endif
