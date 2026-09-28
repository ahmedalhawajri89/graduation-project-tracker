@props([
    'series' => null,   // مجموعة StatSnapshot مرتّبة تصاعدياً بالتاريخ
    'min' => 7,         // أقلّ عدد نقاط يستحق خطاً — دونه يبقى القسم مخفياً
    'id' => 'trend',
])

{{--
    الاتجاه خلال الفصل — رسمان صغيران متجاوران، لكلٍّ مقياسه.

    كان خطّا «المجموعات» (≈٢٠) و«الطلاب بلا فريق» (≈٤٠٠) على محور واحد،
    فينبسط خطّ المجموعات عند الصفر ولا يُقرأ منه شيء. الآن: لكل مقياس رسمه،
    فوقه الرقم الحالي وتغيّره في المدّة — واتجاه «الأفضل» يختلف: المجموعات
    تحسُن صعوداً، والطلاب بلا فريق نزولاً. ومحدِّد مدّة واحد فوقهما معاً.
--}}

@php
    $rows = collect($series ?? [])->values();
    $enough = $rows->count() >= $min;
@endphp

@if ($enough)
    @php
        $points = $rows->map(fn ($r) => [
            'd' => \Illuminate\Support\Carbon::parse($r->date)->locale('ar')->translatedFormat('j M'),
            'full' => \Illuminate\Support\Carbon::parse($r->date)->locale('ar')->translatedFormat('l j F'),
            'groups' => (int) $r->groups,
            'none' => (int) $r->not_has_group,
        ])->values();

        $metrics = [
            ['key' => 'groups', 'title' => 'مجموعات نشطة', 'unit' => 'مجموعة', 'better' => 'up', 'icon' => 'ti-users-group'],
            ['key' => 'none', 'title' => 'طلاب بلا فريق', 'unit' => 'طالب', 'better' => 'down', 'icon' => 'ti-user-exclamation'],
        ];

        // المدد الأقصر من المتوفّر وحدها، ثم «كل الفصل» — والافتراضي الكل
        $ranges = collect([7, 30])->filter(fn ($n) => $n < $rows->count())
            ->map(fn ($n) => ['days' => $n, 'label' => $n . ' يوماً'])
            ->push(['days' => $rows->count(), 'label' => 'كل الفصل'])
            ->values();
        $default = $rows->count();
    @endphp

    <div class="tc" id="{{ $id }}" data-range="{{ $default }}">
        <div class="tc-toolbar">
            <div class="tc-range" role="group" aria-label="المدّة">
                @foreach ($ranges as $r)
                    <button type="button" data-days="{{ $r['days'] }}" aria-pressed="{{ $r['days'] === $default ? 'true' : 'false' }}">
                        {{ $r['label'] }}
                    </button>
                @endforeach
            </div>
            <span class="tc-note">لقطة كل ليلة</span>
        </div>

        <div class="tc-grid">
            @foreach ($metrics as $m)
                <section class="tc-card" data-metric="{{ $m['key'] }}" data-better="{{ $m['better'] }}">
                    <header class="tc-head">
                        <span class="tc-title">
                            <i class="ti {{ $m['icon'] }}" aria-hidden="true"></i>
                            {{ $m['title'] }}
                        </span>
                        <span class="tc-now">
                            <b class="tc-value">{{ $points->last()[$m['key']] }}</b>
                            <span class="tc-unit">{{ $m['unit'] }}</span>
                        </span>
                        {{-- التغيّر بسهم وكلمة لا بلون وحده --}}
                        <span class="tc-delta" data-delta></span>
                    </header>

                    <div class="tc-plot">
                        <canvas role="img" aria-label="{{ $m['title'] }} خلال المدّة المختارة"></canvas>
                    </div>

                    {{-- البيانات نفسها جدولاً — لمن لا يقرأ الرسم --}}
                    <details class="tc-table">
                        <summary>عرض البيانات</summary>
                        <table>
                            <thead><tr><th>اليوم</th><th>{{ $m['title'] }}</th></tr></thead>
                            <tbody>
                                @foreach ($points->reverse() as $p)
                                    <tr><td>{{ $p['full'] }}</td><td>{{ $p[$m['key']] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                </section>
            @endforeach
        </div>
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
                var root = document.getElementById(@json($id));
                if (!root || typeof Chart === 'undefined') return;

                var points = @json($points);
                var metrics = @json(collect($metrics)->keyBy('key'));

                var ds = getComputedStyle(document.documentElement);
                function tok(name, fallback) { return ds.getPropertyValue(name).trim() || fallback; }
                var ink = tok('--ds-ink', '#09090b');
                var mute = tok('--ds-ink-mute', '#5c5c66');
                var rule = tok('--ds-border', '#e4e4e7');
                var surface = tok('--ds-surface', '#ffffff');
                var brand = tok('--ds-brand-600', '#2563eb');
                var font = "'IBM Plex Sans Arabic', sans-serif";
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                // خطّ عمودي يتبع المؤشّر ويثبت على أقرب يوم
                var crosshair = {
                    id: 'tcCrosshair',
                    afterDatasetsDraw: function (chart) {
                        var active = chart.tooltip && chart.tooltip.getActiveElements();
                        if (!active || !active.length) return;
                        var x = active[0].element.x, a = chart.chartArea, c = chart.ctx;
                        c.save();
                        c.beginPath();
                        c.moveTo(x, a.top);
                        c.lineTo(x, a.bottom);
                        c.lineWidth = 1;
                        c.strokeStyle = mute;
                        c.setLineDash([3, 3]);
                        c.stroke();
                        c.restore();
                    },
                };

                function fillFor(ctx, area) {
                    var g = ctx.createLinearGradient(0, area.top, 0, area.bottom);
                    g.addColorStop(0, brand + '26');
                    g.addColorStop(1, brand + '00');
                    return g;
                }

                var charts = [];

                root.querySelectorAll('.tc-card').forEach(function (card) {
                    var key = card.dataset.metric;
                    var m = metrics[key];

                    var chart = new Chart(card.querySelector('canvas'), {
                        type: 'line',
                        data: { labels: [], datasets: [{
                            data: [],
                            borderColor: brand,
                            borderWidth: 2,
                            tension: 0.3,
                            fill: 'start',
                            backgroundColor: function (c) {
                                return c.chart.chartArea ? fillFor(c.chart.ctx, c.chart.chartArea) : 'transparent';
                            },
                            // نقطة النهاية وحدها — «أين نحن الآن» — بحلقة من لون السطح
                            pointRadius: function (c) { return c.dataIndex === c.dataset.data.length - 1 ? 4 : 0; },
                            pointBackgroundColor: brand,
                            pointBorderColor: surface,
                            pointBorderWidth: 2,
                            pointHoverRadius: 5,
                            pointHoverBorderWidth: 2,
                            pointHitRadius: 14,
                        }] },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: reduce ? false : { duration: 700, easing: 'easeOutQuart' },
                            interaction: { mode: 'index', intersect: false },
                            layout: { padding: { top: 6, left: 2, right: 6 } },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    rtl: true,
                                    textDirection: 'rtl',
                                    backgroundColor: ink,
                                    padding: { x: 12, y: 9 },
                                    cornerRadius: 10,
                                    displayColors: false,
                                    caretSize: 0,
                                    titleFont: { family: font, size: 15, weight: '700' },
                                    bodyFont: { family: font, size: 12 },
                                    titleMarginBottom: 3,
                                    // القيمة أولاً وبارزة، والتاريخ بعدها
                                    callbacks: {
                                        title: function (items) { return items[0].formattedValue + ' ' + m.unit; },
                                        label: function (item) { return item.chart.$full[item.dataIndex]; },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    border: { display: false },
                                    ticks: {
                                        color: mute,
                                        font: { family: font, size: 11 },
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: 5,
                                    },
                                },
                                y: {
                                    position: 'right',
                                    grace: '15%',
                                    border: { display: false },
                                    grid: { color: rule, drawTicks: false },
                                    ticks: {
                                        color: mute,
                                        font: { family: font, size: 11 },
                                        padding: 8,
                                        maxTicksLimit: 4,
                                        precision: 0,
                                    },
                                },
                            },
                        },
                        plugins: [crosshair],
                    });

                    charts.push({ card: card, chart: chart, key: key, better: card.dataset.better });
                });

                function render(days) {
                    var slice = points.slice(-days);
                    var first = slice[0], last = slice[slice.length - 1];

                    charts.forEach(function (c) {
                        c.chart.data.labels = slice.map(function (p) { return p.d; });
                        c.chart.data.datasets[0].data = slice.map(function (p) { return p[c.key]; });
                        c.chart.$full = slice.map(function (p) { return p.full; });
                        c.chart.update(reduce ? 'none' : undefined);

                        var diff = last[c.key] - first[c.key];
                        var el = c.card.querySelector('[data-delta]');
                        var good = diff === 0 ? null : (diff > 0) === (c.better === 'up');
                        el.className = 'tc-delta ' + (good === null ? 'is-flat' : good ? 'is-good' : 'is-bad');
                        el.textContent = '';
                        var icon = document.createElement('i');
                        icon.className = 'ti ' + (diff > 0 ? 'ti-arrow-up-right' : diff < 0 ? 'ti-arrow-down-right' : 'ti-minus');
                        icon.setAttribute('aria-hidden', 'true');
                        el.appendChild(icon);
                        el.appendChild(document.createTextNode(
                            (diff > 0 ? '+' : diff < 0 ? '−' : '') + Math.abs(diff)
                            + ' منذ ' + first.d
                            + (good === null ? '' : good ? ' · تحسّن' : ' · تراجع')
                        ));
                    });
                }

                root.querySelectorAll('.tc-range button').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        root.querySelectorAll('.tc-range button').forEach(function (b) {
                            b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
                        });
                        render(parseInt(btn.dataset.days, 10));
                    });
                });

                render(parseInt(root.dataset.range, 10));
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
