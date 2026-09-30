{{--
    محضر مناقشة مشروع تخرّج — صفحة مستقلّة بمقاس A4 تُطبع أو تُحفظ PDF.
    قبل اكتمال درجات اللجنة: «مسودة» بعلامة مائية، والدرجات الناقصة فارغة
    لتُكتب وتُوقَّع يدوياً يوم المناقشة.

    @param \App\Models\Defense $defense
    @param \App\Models\Project $project
    @param bool $draft
    @param string $back
--}}
@php
    $fmt = fn ($g) => is_null($g) ? '' : rtrim(rtrim(number_format((float) $g, 2, '.', ''), '0'), '.');
    $team = $project->group->sortBy(fn ($g) => $g->type === 'leader' ? 0 : 1);
    $isolate = fn ($s) => "\u{2066}" . $s . "\u{2069}";
@endphp
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>محضر مناقشة — {{ $project->title }}</title>
    <link rel="icon" href="{{ asset('assets/img/takharruj-logo.svg') }}">
    <link href="{{ asset('assets/fonts/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/tabler-icons/tabler-icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --ink: #18181b; --soft: #3f3f46; --mute: #71717a; --line: #d4d4d8; --hair: #e4e4e7;
            --brand: #1d4ed8; --brand-soft: #eff6ff; --paper: #fff; --desk: #f4f4f5;
        }
        * { box-sizing: border-box; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body { margin: 0; background: var(--desk); color: var(--ink); font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; font-size: 13px; line-height: 1.7; }

        /* شريط الأدوات: خارج الورقة، لا يُطبع */
        .mn-bar { position: sticky; top: 0; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 16px; background: rgba(255, 255, 255, .92); border-bottom: 1px solid var(--hair); backdrop-filter: blur(8px); }
        .mn-bar a, .mn-bar button { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 10px; border: 1px solid var(--line); background: #fff; color: var(--soft); font: inherit; font-weight: 600; text-decoration: none; cursor: pointer; }
        .mn-bar button { border-color: var(--brand); background: var(--brand); color: #fff; }
        .mn-bar small { color: var(--mute); text-align: center; }

        /* الورقة */
        .mn-page { position: relative; width: 210mm; min-height: 297mm; margin: 24px auto; padding: 16mm 16mm 14mm; background: var(--paper); box-shadow: 0 20px 50px -30px rgba(9, 9, 11, .35); overflow: hidden; }
        .mn-draft { position: absolute; inset: 0; display: grid; place-items: center; pointer-events: none; font-size: 120px; font-weight: 800; color: rgba(29, 78, 216, .06); transform: rotate(-24deg); }

        .mn-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-bottom: 12px; border-bottom: 2px solid var(--ink); }
        .mn-brand { display: flex; align-items: center; gap: 10px; }
        .mn-brand svg { width: 40px; height: 40px; }
        .mn-brand b { display: block; font-size: 18px; line-height: 1.2; }
        .mn-brand small { color: var(--mute); font-size: 11px; }
        .mn-ref { text-align: left; font-size: 11px; color: var(--mute); line-height: 1.6; }
        .mn-ref b { color: var(--ink); font-variant-numeric: tabular-nums; }

        .mn-title { margin: 18px 0 4px; text-align: center; font-size: 21px; font-weight: 800; letter-spacing: 0; }
        .mn-sub { margin: 0 0 18px; text-align: center; color: var(--mute); }
        .mn-flag { display: inline-block; margin-inline-start: 8px; padding: 1px 10px; border-radius: 99px; background: #fef3c7; color: #92400e; font-size: 11px; font-weight: 700; vertical-align: middle; }

        .mn-sec { margin-top: 16px; break-inside: avoid; }
        .mn-sec h2 { display: flex; align-items: center; gap: 6px; margin: 0 0 8px; font-size: 13.5px; font-weight: 800; color: var(--brand); }
        .mn-grid { display: grid; grid-template-columns: minmax(0, 1fr); border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
        .mn-grid div { display: flex; gap: 10px; padding: 8px 12px; border-bottom: 1px solid var(--hair); }
                .mn-grid div.is-wide { grid-column: 1 / -1; border-inline-end: 0; }
        .mn-grid div:nth-last-child(-n+1) { border-bottom: 0; }
        .mn-grid dt { flex: none; width: 88px; color: var(--mute); }
        .mn-grid dd { margin: 0; font-weight: 600; min-width: 0; }
        .mn-grid dd em { font-style: normal; font-weight: 500; color: var(--mute); }
        .mn-lead { padding: 0 6px; margin-inline-start: 4px; border-radius: 99px; background: var(--brand-soft); color: var(--brand); font-size: 10.5px; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 9px 10px; border: 1px solid var(--line); text-align: start; vertical-align: top; }
        thead th { background: var(--desk); font-size: 12px; color: var(--soft); white-space: nowrap; }
        td.mn-num { width: 96px; text-align: center; font-size: 16px; font-weight: 800; font-variant-numeric: tabular-nums; }
        td.mn-num:empty::after { content: ''; display: block; height: 22px; }
        td.mn-sign { width: 130px; }
        td small { display: block; color: var(--mute); font-size: 11px; }
        td.mn-notes { font-size: 12px; color: var(--soft); white-space: pre-line; }
        tr { break-inside: avoid; }

        .mn-result { display: flex; align-items: center; gap: 18px; margin-top: 12px; padding: 14px 16px; border: 1.5px solid var(--ink); border-radius: 12px; break-inside: avoid; }
        .mn-result b { font-size: 34px; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; min-width: 70px; text-align: center; }
        .mn-result span { flex: 1; }
        .mn-result strong { display: block; font-size: 14px; }
        .mn-result em { font-style: normal; color: var(--mute); }
        .mn-result.is-draft b { color: var(--line); }

        .mn-signs { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; margin-top: 28px; break-inside: avoid; }
        .mn-signs div { padding-top: 34px; border-top: 1px dashed var(--mute); text-align: center; color: var(--mute); font-size: 12px; }

        .mn-foot { position: absolute; inset-inline: 16mm; bottom: 9mm; display: flex; justify-content: space-between; gap: 10px; padding-top: 6px; border-top: 1px solid var(--hair); font-size: 10.5px; color: var(--mute); }

        @media (max-width: 860px) {
            .mn-page { width: auto; min-height: 0; margin: 12px; padding: 20px 16px 60px; }
            .mn-grid { grid-template-columns: 1fr; }
            .mn-grid div:nth-child(odd) { border-inline-end: 0; }
            .mn-table-wrap { overflow-x: auto; }
            table { min-width: 560px; }
            .mn-draft { font-size: 64px; }
            .mn-bar-long { display: none; }
            .mn-bar small { font-size: 11px; }
        }
        @page { size: A4; margin: 0; }
        @media print {
            body { background: #fff; }
            .mn-bar { display: none; }
            .mn-page { width: 210mm; min-height: 297mm; margin: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="mn-bar">
        <a href="{{ $back }}"><i class="ti ti-arrow-right" aria-hidden="true"></i>رجوع</a>
        <small>{{ $draft ? 'مسودة — تكتمل حين ترصد اللجنة كل الدرجات' : 'محضر مكتمل' }}</small>
        <button type="button" onclick="window.print()"><i class="ti ti-printer" aria-hidden="true"></i>طباعة<span class="mn-bar-long"> / حفظ PDF</span></button>
    </div>

    <main class="mn-page">
        @if ($draft)<div class="mn-draft" aria-hidden="true">مسودة</div>@endif

        <header class="mn-head">
            <div class="mn-brand">
                <svg viewBox="0 0 40 40" aria-hidden="true">
                    <rect width="40" height="40" rx="11" fill="#1d4ed8" />
                    <path d="M10.5 20h19" stroke="#fff" stroke-opacity=".55" stroke-width="2" stroke-linecap="round" />
                    <circle cx="10.5" cy="20" r="3.4" fill="#fff" />
                    <circle cx="20" cy="20" r="3.4" fill="#fff" />
                    <circle cx="29.5" cy="20" r="3.4" fill="#1d4ed8" stroke="#fff" stroke-width="2" />
                </svg>
                <div><b>تخرُّج</b><small>منصّة متابعة مشاريع التخرّج</small></div>
            </div>
            <div class="mn-ref">
                رقم المحضر <b dir="ltr">DEF-{{ str_pad($defense->id, 4, '0', STR_PAD_LEFT) }}</b><br>
                @if ($project->semester){{ $project->semester->label }}<br>@endif
                أُصدر <b dir="ltr">{{ now()->format('Y-m-d') }}</b>
            </div>
        </header>

        <h1 class="mn-title">محضر مناقشة مشروع تخرّج @if ($draft)<span class="mn-flag">مسودة</span>@endif</h1>
        <p class="mn-sub">اجتمعت لجنة المناقشة المبيّنة أدناه وناقشت المشروع، ورصدت درجاتها كما يلي.</p>

        <section class="mn-sec">
            <h2><i class="ti ti-file-description" aria-hidden="true"></i> المشروع</h2>
            <dl class="mn-grid">
                <div class="is-wide"><dt>العنوان</dt><dd>{{ $project->title }}</dd></div>
                <div><dt>النوع</dt><dd>{{ $project->project_type->name ?? '—' }}</dd></div>
                <div><dt>التخصص</dt><dd>{{ $project->project_type?->specialize?->name ?? '—' }}</dd></div>
                <div class="is-wide"><dt>الفريق</dt><dd>
                    @foreach ($team as $g)<span>{{ $g->student?->name }}@if ($g->type === 'leader')<span class="mn-lead">القائد</span>@endif</span>{{ $loop->last ? '' : '، ' }}@endforeach
                </dd></div>
            </dl>
        </section>

        <section class="mn-sec">
            <h2><i class="ti ti-calendar-event" aria-hidden="true"></i> المناقشة</h2>
            <dl class="mn-grid">
                <div><dt>التاريخ</dt><dd>{{ $defense->starts_at->translatedFormat('l j F Y') }}</dd></div>
                <div><dt>الوقت</dt><dd>{{ $isolate($defense->starts_at->format('H:i') . '–' . $defense->endsAt()->format('H:i')) }} <em>· {{ $defense->duration_minutes }} دقيقة</em></dd></div>
                <div class="is-wide"><dt>المكان</dt><dd>{{ $defense->mode_label }} · {{ $defense->place_label }}@if ($defense->room?->location) <em>— {{ $defense->room->location }}</em>@endif</dd></div>
            </dl>
        </section>

        <section class="mn-sec">
            <h2><i class="ti ti-users-group" aria-hidden="true"></i> لجنة المناقشة ودرجاتها</h2>
            <div class="mn-table-wrap">
                <table>
                    <thead>
                        <tr><th>العضو</th><th>الدرجة من 100</th><th>الملاحظات</th><th>التوقيع</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($defense->members as $m)
                            <tr>
                                <td><b>{{ $m->supervisor->name }}</b><small>{{ $m->role_label }}</small></td>
                                <td class="mn-num">{{ $fmt($m->grade) }}</td>
                                <td class="mn-notes">{{ $m->comments }}</td>
                                <td class="mn-sign"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mn-result {{ $draft ? 'is-draft' : '' }}">
                <b>{{ $draft ? '—' : $fmt($project->grade) }}</b>
                <span>
                    <strong>الدرجة النهائية — متوسط درجات اللجنة</strong>
                    @if ($draft)
                        <em>تُحسب حين يرصد كل الأعضاء درجاتهم.</em>
                    @else
                        <em>التقدير: {{ $project->grade_label }} ·
                            {{ $project->isGradeLocked() ? 'اعتُمدت بتاريخ ' . $isolate($project->grade_locked_at->format('Y-m-d')) : 'لم تُعتمد بعد' }}</em>
                    @endif
                </span>
            </div>
        </section>

        <div class="mn-signs">
            <div>رئيس القسم</div>
            <div>ختم القسم</div>
        </div>

        <footer class="mn-foot">
            <span>أُصدر من منصّة تخرُّج — {{ $isolate(now()->format('Y-m-d H:i')) }}</span>
            <span dir="ltr">DEF-{{ str_pad($defense->id, 4, '0', STR_PAD_LEFT) }}</span>
        </footer>
    </main>
</body>
</html>
