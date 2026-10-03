<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>تخرُّج | منصة متابعة مشاريع التخرج</title>
    <meta name="description" content="تخرُّج (Takharruj) — منصة لتتبع مشاريع التخرج وإدارة الفرق والمشرفين ومتابعة مراحل المشروع من الفكرة إلى المناقشة." />
    <link rel="icon" href="{{ asset('assets/img/takharruj-logo.svg') }}" />

    {{-- خطوط مستضافة محلياً: لا اعتماد على طرف ثالث ولا تأخّر في ظهور النص --}}
    <link rel="preload" href="{{ asset('assets/fonts/Almarai-800-arabic.woff2') }}" as="font" type="font/woff2" crossorigin />
    <link rel="preload" href="{{ asset('assets/fonts/InterTight-var-latin.woff2') }}" as="font" type="font/woff2" crossorigin />
    <link rel="preload" href="{{ asset('assets/fonts/IBMPlexSansArabic-400-arabic.woff2') }}" as="font" type="font/woff2" crossorigin />
    <link href="{{ asset('assets/fonts/fonts.css') }}" rel="stylesheet" />

    <link href="{{ asset('assets/css/premium.css') }}?v={{ filemtime(public_path('assets/css/premium.css')) }}" rel="stylesheet" />
    <noscript>
        <style>
            .reveal, .stagger, .stage { opacity: 1 !important; transform: none !important; filter: none !important; }
            .ink-line > span { transform: none !important; }
            .step-body { max-height: none !important; }
        </style>
    </noscript>
</head>

<body>
    {{-- رابط تخطٍّ لمستخدمي لوحة المفاتيح: أول ما يستقبل التركيز --}}
    <a href="#hero" class="skip-link" data-i18n="a11y.skip">تخطَّ إلى المحتوى</a>

    {{-- ======= Header ======= --}}
    <header class="site-header">
        <nav class="nav-bar" aria-label="Main navigation">
            <a href="#hero" class="brand">
                {{-- العلامة من استعارة المنتج: عقد على مسار، آخرها يمتلئ عند المرور --}}
                <span class="brand-mark" aria-hidden="true">
                    <svg width="30" height="30" viewBox="0 0 32 32" fill="none">
                        <path class="rail-fill" d="M6 16h20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="6" cy="16" r="3.2" fill="currentColor"/>
                        <circle cx="16" cy="16" r="3.2" fill="currentColor"/>
                        <circle class="node-last" cx="26" cy="16" r="3.2" stroke-width="2"/>
                    </svg>
                </span>
                <span class="brand-name" data-i18n="brand.name">تخرُّج</span>
            </a>

            <div class="nav-wrap">
                <span class="nav-pill" aria-hidden="true"></span>
                <ul class="nav-links">
                    <li><a href="#hero" data-i18n="nav.home">الرئيسية</a></li>
                    <li><a href="#about" data-i18n="nav.about">عن المنصة</a></li>
                    <li><a href="#services" data-i18n="nav.services">الخدمات</a></li>
                    <li><a href="#features" data-i18n="nav.features">الميزات</a></li>
                    <li><a href="#roles" data-i18n="nav.roles">الأدوار</a></li>
                    <li><a href="#faq" data-i18n="nav.faq">الأسئلة الشائعة</a></li>
                    <li><a href="#contact" data-i18n="nav.contact">تواصل معنا</a></li>
                </ul>
            </div>

            <div class="nav-actions">
                {{-- الخياران ظاهران معاً: الزائر يرى لغته الحالية وما سينتقل إليه --}}
                <div class="lang-switch" role="group" aria-label="Language">
                    <span class="lang-thumb" aria-hidden="true"></span>
                    <button type="button" data-locale="ar" aria-pressed="true">ع</button>
                    <button type="button" data-locale="en" aria-pressed="false">EN</button>
                </div>

                @if (auth()->guard('admin')->check() || auth()->guard('supervisor')->check() || auth()->guard('student')->check())
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm nav-login">
                        <span data-i18n="nav.dashboard">لوحة التحكم</span>
                        <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    </a>
                    <a href="{{ route('logout') }}" class="btn btn-ghost btn-sm nav-login"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span data-i18n="nav.logout">تسجيل خروج</span>
                    </a>
                    <form action="{{ route('logout') }}" method="post" id="logout-form">@csrf</form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm nav-login">
                        <span data-i18n="nav.login">تسجيل الدخول</span>
                        <svg class="btn-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    </a>
                @endif

                <button type="button" class="menu-toggle" aria-label="Menu" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </nav>

        <div class="mobile-menu">
            <a href="#hero" data-i18n="nav.home">الرئيسية</a>
            <a href="#about" data-i18n="nav.about">عن المنصة</a>
            <a href="#services" data-i18n="nav.services">الخدمات</a>
            <a href="#features" data-i18n="nav.features">الميزات</a>
            <a href="#roles" data-i18n="nav.roles">الأدوار</a>
            <a href="#faq" data-i18n="nav.faq">الأسئلة الشائعة</a>
            <a href="#contact" data-i18n="nav.contact">تواصل معنا</a>
            @if (auth()->guard('admin')->check() || auth()->guard('supervisor')->check() || auth()->guard('student')->check())
                <a href="{{ route('login') }}" class="btn btn-primary" data-i18n="nav.dashboard">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary" data-i18n="nav.login">تسجيل الدخول</a>
            @endif
        </div>
    </header>

    {{-- أرقام حقيقية من قاعدة البيانات، مع كاش ساعة — يستعملها الهيرو وقسم الإحصاءات معاً --}}
    @php
        $siteStats = \Illuminate\Support\Facades\Cache::remember('site_public_stats', 3600, function () {
            return [
                'specializes' => \App\Models\Specialize::count(),
                'supervisors' => \App\Models\Supervisor::count(),
                'projects' => \App\Models\Project::whereIn('status', ['accept', 'complete'])->count(),
                'students' => \App\Models\Student::count(),
            ];
        });
        // قيم افتراضية لو كانت القاعدة فارغة (بيئة تجريبية)
        $siteStats['specializes'] = $siteStats['specializes'] ?: 3;
        $siteStats['supervisors'] = $siteStats['supervisors'] ?: 18;
        $siteStats['projects'] = $siteStats['projects'] ?: 120;
        $siteStats['students'] = $siteStats['students'] ?: 400;
    @endphp

    {{-- ======= Hero: رحلة المشروع ======= --}}
    {{-- بملء الشاشة: العنوان في الأعلى، وتحته مشهد مرسوم بالكود — مسار يعبر الشاشة
         بستّ محطات (الفكرة، الفريق، المشرف، المراحل، المناقشة، التخرّج)، وفريق من
         ثلاثة طلاب يمشي عليه، والمشرف واللجنة واقفون عند محطّاتهم، وعند كل محطة
         بطاقة حقيقية من المنصة، وفي النهاية تُرمى قبعات التخرّج. الشخصيات SVG
         حادّة بأيّ دقّة وبلا وزن صور. الحركة في initJourney (premium.js)،
         ولمن أوقف الحركة يظهر المشهد الأخير ثابتاً. --}}
    <section id="hero" class="hero hero--journey">
        <div class="hero-bg" aria-hidden="true">
            <span class="hero-glow"></span>
            <span class="hero-aurora"><i></i><i></i><i></i></span>
            <span class="hero-dots"></span>
        </div>

        <div class="container hj-top">
            <span class="badge stagger d1">
                <span class="dot"></span>
                <span data-i18n="hero.badge">منصة إدارة مشاريع التخرج للجامعات</span>
            </span>

            <h1 class="stagger d2" data-i18n="hero.title" data-i18n-html>
                <span class="ink-line"><span>تتبّع مشروع تخرجك</span></span>
                <span class="ink-line"><span>من الفكرة <span class="text-gradient">إلى <span class="rot" data-rot="المناقشة|الدرجة|التخرّج">المناقشة</span></span></span></span>
            </h1>

            <p class="hero-sub stagger d3" data-i18n="hero.sub">
                فريقك ومشرفك ومراحل مشروعك ودرجتك — تتابعها كلها من لوحة واحدة، ويتابعها مشرفك معك.
            </p>

            <div class="hero-ctas stagger d4">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <span data-i18n="hero.cta1">ابدأ الآن</span>
                </a>
                <a href="#about" class="btn-link">
                    <span data-i18n="hero.cta2">اكتشف المزيد</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </a>
            </div>

            {{-- أرقام حقيقية من قاعدة البيانات --}}
            <div class="hero-figures stagger d5">
                <div class="figure">
                    <div class="figure-num" data-count="{{ $siteStats['projects'] }}">0</div>
                    <div class="figure-label" data-i18n="hero.fact1">مشروع يُتابَع على المنصة</div>
                </div>
                <div class="figure">
                    <div class="figure-num" data-count="{{ $siteStats['supervisors'] }}">0</div>
                    <div class="figure-label" data-i18n="hero.fact2">مشرف أكاديمي</div>
                </div>
                <div class="figure">
                    <div class="figure-num" data-count="{{ $siteStats['students'] }}">0</div>
                    <div class="figure-label" data-i18n="hero.fact3">طالب وطالبة</div>
                </div>
            </div>
        </div>

        {{-- المشهد — زخرفي لقارئ الشاشة، والمحطات نفسها مذكورة نصّاً في القائمة الخفية --}}
        <div class="hj-scene" data-journey>
            <ol class="hj-sr">
                <li data-i18n="journey.s1">الفكرة</li><li data-i18n="journey.s2">الفريق</li><li data-i18n="journey.s3">موافقة المشرف</li>
                <li data-i18n="journey.s4">المراحل</li><li data-i18n="journey.s5">المناقشة</li><li data-i18n="journey.s6">التخرّج</li>
            </ol>

            <svg class="hj-svg" viewBox="0 0 1200 300" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="hj-trail" x1="0" x2="1">
                        <stop offset="0" stop-color="#2563eb" />
                        <stop offset="1" stop-color="#7c3aed" />
                    </linearGradient>
                    <radialGradient id="hj-halo">
                        <stop offset="0" stop-color="#2563eb" stop-opacity=".28" />
                        <stop offset="1" stop-color="#2563eb" stop-opacity="0" />
                    </radialGradient>

                    @include('partials.cast')
                </defs>

                {{-- العالم كلّه يُرسم يساراً→يميناً، ويُعكس في العربية فيمشي الفريق من اليمين --}}
                <g class="hj-world">
                    <path class="hj-ground" d="M0 262 H1200" />
                    {{-- المسار: خلفية متقطّعة، وفوقها أثر يمتلئ خلف الفريق --}}
                    <path class="hj-path" d="M40 220 C 170 220, 200 150, 300 150 S 450 230, 560 220 S 700 140, 800 150 S 950 230, 1040 210 S 1140 160, 1170 150" />
                    <path class="hj-trail" d="M40 220 C 170 220, 200 150, 300 150 S 450 230, 560 220 S 700 140, 800 150 S 950 230, 1040 210 S 1140 160, 1170 150" />

                    {{-- المحطات: يضعها السكربت على المسار --}}
                    <g class="hj-stations">
                        @foreach (['idea', 'team', 'approve', 'stages', 'defense', 'grad'] as $k => $st)
                            <g class="hj-station" data-station="{{ $k }}">
                                <circle class="hj-halo" r="30" fill="url(#hj-halo)" />
                                <circle class="hj-node" r="15" />
                                <g class="hj-ico" transform="translate(-9 -9) scale(.75)">
                                    @switch($st)
                                        @case('idea')<path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5.9 1.2.9 2V16h5.2v-.1c0-.8.3-1.5.9-2A6 6 0 0 0 12 3z" />@break
                                        @case('team')<circle cx="9" cy="8" r="3.5" /><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4-6" />@break
                                        @case('approve')<path d="M20 6 9 17l-5-5" />@break
                                        @case('stages')<path d="M9 6h11M9 12h11M9 18h11M3 6l1 1 2-2M3 12l1 1 2-2M3 18l1 1 2-2" />@break
                                        @case('defense')<path d="M3 4h18M4 4v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4M12 16v4M8 20h8" />@break
                                        @case('grad')<path d="M22 10 12 5 2 10l10 5 10-5zM6 12v5c3 3 9 3 12 0v-5" />@break
                                    @endswitch
                                </g>
                            </g>
                        @endforeach
                    </g>

                    {{-- الواقفون عند محطّاتهم --}}
                    <use href="#hj-supervisor" class="hj-actor" data-at="2" data-dx="34" data-dy="-6" />
                    <use href="#hj-committee" class="hj-actor" data-at="4" data-dx="74" data-dy="-34" />

                    {{-- الفريق يمشي --}}
                    <g class="hj-team">
                        <g class="hj-walker" transform="translate(-30 0)"><use href="#hj-boy2" /></g>
                        <g class="hj-walker" transform="translate(-6 4)"><use href="#hj-girl" /></g>
                        <g class="hj-walker" transform="translate(18 0)"><use href="#hj-boy" /></g>
                        {{-- القبعات تُرمى في المحطة الأخيرة --}}
                        <g class="hj-caps">
                            <use href="#hj-cap" class="hj-cap is-1" x="-28" y="-80" />
                            <use href="#hj-cap" class="hj-cap is-2" x="-4" y="-84" />
                            <use href="#hj-cap" class="hj-cap is-3" x="20" y="-80" />
                        </g>
                    </g>
                </g>
            </svg>

            {{-- أسماء المحطات وبطاقاتها: HTML فوق المشهد — تُترجم وتبقى حادّة --}}
            <div class="hj-labels">
                @foreach (['الفكرة', 'الفريق', 'موافقة المشرف', 'المراحل', 'المناقشة', 'التخرّج'] as $k => $name)
                    <span class="hj-label" data-label="{{ $k }}" data-i18n="journey.s{{ $k + 1 }}">{{ $name }}</span>
                @endforeach
            </div>
            <div class="hj-cards">
                @foreach ([
                    ['is-amber', 'M9 18h6M10 21h4M12 3a6 6 0 0 0-3.5 10.9c.6.5.9 1.2.9 2V16h5.2v-.1c0-.8.3-1.5.9-2A6 6 0 0 0 12 3z', 'فكرة جديدة', 'كشف الأخبار الزائفة بالذكاء الاصطناعي'],
                    ['is-blue', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87', 'اكتمل الفريق', '3 أعضاء · قائدة الفريق آية'],
                    ['is-green', 'M20 6 9 17l-5-5', 'وافق المشرف', 'د. هبة قبلت الطلب'],
                    ['is-blue', 'M9 6h11M9 12h11M9 18h11M3 6l1 1 2-2M3 12l1 1 2-2', 'اعتُمدت المرحلة', 'الفصل الثالث · 4 من 5'],
                    ['is-violet', 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z', 'جُدولت المناقشة', 'الأحد 09:00 · قاعة 204'],
                    ['is-gold', 'M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12zM8.5 14.5 7 22l5-3 5 3-1.5-7.5', 'تخرّجنا! 🎓', 'الدرجة 96 من 100 · ممتاز'],
                ] as $k => [$tone, $icon, $title, $sub])
                    <div class="hj-card" data-card="{{ $k }}">
                        <span class="hj-card-ico {{ $tone }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}" /></svg></span>
                        <span><b data-i18n="journey.c{{ $k + 1 }}">{{ $title }}</b><small data-i18n="journey.c{{ $k + 1 }}s">{{ $sub }}</small></span>
                    </div>
                @endforeach
            </div>
            <div class="hj-confetti" aria-hidden="true"></div>
        </div>
    </section>

    <main>
        {{-- ======= About ======= --}}
        <section id="about" class="section">
            <div class="container">
                <div class="section-head" data-num="01">
                    <div class="section-index reveal"><span data-i18n="about.kicker">عن المنصة</span></div>
                </div>

                <div class="about-grid">
                    <div class="about-copy">
                        <h2 class="reveal" data-i18n="about.title">من تكوين الفريق حتى الدرجة</h2>
                        <p class="reveal d1" data-i18n="about.text">
                            تخرُّج منصة مستقلة لإدارة مشاريع التخرج من أول تكوين الفريق واختيار المشرف، مروراً باعتماد الفكرة ومتابعة المراحل، وصولاً إلى المناقشة والتقييم النهائي — بمسار واضح لكل طرف.
                        </p>

                        <div class="pillars reveal d2">
                            <div class="pillar">
                                <span class="pillar-num">01</span>
                                <span class="pillar-text" data-i18n="about.p1">إدارة الفرق الطلابية والمشرفين</span>
                            </div>
                            <div class="pillar">
                                <span class="pillar-num">02</span>
                                <span class="pillar-text" data-i18n="about.p2">خطط المراحل والتسليم والمراجعة</span>
                            </div>
                            <div class="pillar">
                                <span class="pillar-num">03</span>
                                <span class="pillar-text" data-i18n="about.p3">المناقشة والتقييم ورصد الدرجات</span>
                            </div>
                        </div>

                        <a href="#services" class="link-more reveal d3">
                            <span data-i18n="about.more">المزيد</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        </a>
                    </div>

                    {{-- «المنصة الآن» — أرقام حقيقية لا وصف. كان ثلاثة أشرطة بلا سياق:
                         لا يُعرف من كم، ولا ما وزن كل تخصص من الكل. صار: ثلاثة أرقام
                         تُعدّ عند الظهور، ثم التخصصات بنسبتها من الطلاب — أشرطة على
                         خطّ أساس واحد وبلون واحد: المقارنة بالطول، والاسم يعرّف الشريط --}}
                    @php
                        $panel = \Illuminate\Support\Facades\Cache::remember('site_panel_v2', 3600, function () {
                            $specs = \App\Models\Specialize::withCount('students')->orderByDesc('students_count')->get();
                            $top = $specs->take(3);
                            $rest = $specs->slice(3)->sum('students_count');

                            return [
                                'students' => \App\Models\Student::count(),
                                'supervisors' => \App\Models\Supervisor::count(),
                                'done' => \App\Models\Project::where('status', 'complete')->count(),
                                'rows' => $top->map(fn ($x) => ['name' => $x->name, 'n' => (int) $x->students_count])
                                    ->when($rest > 0, fn ($c) => $c->push(['name' => null, 'n' => (int) $rest]))
                                    ->values()->all(),
                            ];
                        });
                        $panelTotal = max(1, array_sum(array_column($panel['rows'], 'n')));
                        $panelMax = max(1, max(array_column($panel['rows'], 'n') ?: [1]));
                    @endphp
                    <div class="about-panel reveal d1">
                        {{-- الطالبة والمشرف والمسؤول يطلّون فوق اللوحة — من شخصيات الهيرو --}}
                        <svg class="ab-crowd" viewBox="-100 -100 200 104" aria-hidden="true">
                            <use href="#hj-girl" x="-58" /><use href="#hj-supervisor" x="0" /><use href="#hj-admin" x="58" />
                        </svg>
                        <div class="panel-head">
                            <span data-i18n="about.panelTitle">المنصة الآن</span>
                            <span class="panel-live"><i aria-hidden="true"></i><span data-i18n="about.live">مباشر</span></span>
                        </div>

                        <div class="panel-stats">
                            @foreach ([
                                ['students', 'about.statStudents', 'طالب'],
                                ['supervisors', 'about.statSupervisors', 'مشرف'],
                                ['done', 'about.statDone', 'مشروع مكتمل'],
                            ] as [$key, $i18n, $label])
                                <div class="panel-stat">
                                    <b data-count="{{ $panel[$key] }}">{{ number_format($panel[$key]) }}</b>
                                    <span data-i18n="{{ $i18n }}">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="panel-split">
                            <div class="panel-sub" data-i18n="about.split">الطلاب حسب التخصص</div>
                            @forelse ($panel['rows'] as $row)
                                @php $pct = round($row['n'] / $panelTotal * 100); @endphp
                                <div class="panel-row">
                                    @if ($row['name'])
                                        <span class="panel-row-name">{{ $row['name'] }}</span>
                                    @else
                                        <span class="panel-row-name" data-i18n="about.other">تخصصات أخرى</span>
                                    @endif
                                    <span class="panel-row-meta">
                                        <b>{{ $pct }}%</b>
                                        <span><bdi>{{ $row['n'] }}</bdi> <span data-i18n="unit.students">طالباً</span></span>
                                    </span>
                                    <span class="panel-bar" aria-hidden="true">
                                        <i style="--w: {{ round($row['n'] / $panelMax * 100) }}%"></i>
                                    </span>
                                </div>
                            @empty
                                <div class="panel-row">
                                    <span class="panel-row-name" data-i18n="about.panelEmpty">لم تُسجَّل تخصصات بعد</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= Services ======= --}}
        {{-- ما تفعله على المنصة — شبكة بنتو: بلاطتان كبيرتان بمشهد مصغّر ثابت،
             وستّ بلاطات لبقية الأدوات. كانت ثلاث بطاقات بعبارات عامة («يساعدك
             الموقع على توفير الوقت»)، والأدوات الجديدة غائبة عنها --}}
        <section id="services" class="section">
            <div class="container">
                <div class="section-head" data-num="02">
                    <div class="section-index reveal"><span data-i18n="services.kicker">الخدمات</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="services.title">كل ما يحتاجه مشروعك في مكان واحد</h2>
                        <p class="reveal d1" data-i18n="services.text">
                            من تكوين الفريق إلى الدرجة المعتمدة — أدوات تغنيك عن مجموعات واتساب والبريد والملفات المبعثرة.
                        </p>
                    </div>
                </div>

                {{-- قصّة الخدمات: قائمة قصيرة تتقدّم مع التمرير، ومسرح ثابت بجانبها
                     يحرّك كل خدمة بشخصيات الهيرو نفسها (رموز #hj-* — بلا نسخ).
                     كانت ثماني بطاقات بكلام كثير؛ صارت ستّاً: عنوان وسطر، والمشهد
                     يشرح الباقي. الحركة في initServices (premium.js). --}}
                <div class="svx" data-svx>
                    <ol class="svx-list">
                        @foreach ([
                            ['team', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'فريقك ومشرفك في دقائق', 'زملاء من تخصصك، ومشرف بمقاعد متاحة، وتنبيه إن نُفّذت فكرتك.'],
                            ['review', 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12', 'سلّم، واستلم ملاحظة', 'كل مرحلة بملف، والمشرف يعتمدها أو يطلب تعديلاً بسببه.'],
                            ['plan', 'M9 6h11M9 12h11M9 18h11M3 6l1 1 2-2M3 12l1 1 2-2M3 18l1 1 2-2', 'خطة مراحل جاهزة', 'يضعها المشرف مرّة بمواعيدها وقوالبها، فتصل كل مجموعاته.'],
                            ['chat', 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z', 'نقاش حيّ', 'قناة خاصة بالفريق، وأخرى مع المشرف — والرسائل تصل فوراً.'],
                            ['defense', 'M12 15a6 6 0 1 0 0-12 6 6 0 0 0 0 12zM8.5 14.5 7 22l5-3 5 3-1.5-7.5', 'مناقشة ودرجة معتمدة', 'لجنة وموعد وقاعة، ودرجة تُرصد ثم تُقفل.'],
                            ['explore', 'M11 18a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM21 21l-4.3-4.3', 'استلهم من مشاريع سابقة', 'تصفّح مشاريع الدفعات السابقة بأنواعها ومشرفيها.'],
                        ] as $k => [$key, $icon, $title, $line])
                            <li class="svx-item {{ $k === 0 ? 'is-active' : '' }}" data-svx-item="{{ $k }}">
                                <button type="button" class="svx-btn" aria-controls="svx-scene-{{ $key }}">
                                    <span class="svx-num">{{ str_pad($k + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="svx-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}" /></svg></span>
                                    <span class="svx-text">
                                        <b data-i18n="svx.{{ $k + 1 }}.t">{{ $title }}</b>
                                        <span data-i18n="svx.{{ $k + 1 }}.l">{{ $line }}</span>
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ol>

                    <div class="svx-stage-wrap">
                        <div class="svx-stage" aria-hidden="true">
                            <span class="svx-glow"></span>

                            {{-- ١) الفريق: ينضمّون واحداً واحداً، والمقاعد تنقص --}}
                            <div class="svx-scene is-active" id="svx-scene-team" data-scene="0">
                                <div class="sx-card sx-top">
                                    <span class="sx-av is-sup">هب</span>
                                    <span><b data-i18n="svx.s1.sup">د. هبة الشوا</b><small data-i18n="svx.s1.supl">مشرفة · برمجة ذكاء صناعي</small></span>
                                    <span class="sx-seats"><b data-tween data-from="3" data-to="2" data-delay="1500">3</b> <small data-i18n="svx.s1.seats">مقاعد</small></span>
                                </div>
                                <svg class="sx-people" viewBox="0 0 360 150">
                                    <use href="#hj-boy2" class="sx-pop" style="--d: .3s" transform="translate(110 140) scale(1.5)" />
                                    <use href="#hj-girl" class="sx-pop" style="--d: .6s" transform="translate(180 144) scale(1.5)" />
                                    <use href="#hj-boy" class="sx-pop" style="--d: .9s" transform="translate(250 140) scale(1.5)" />
                                </svg>
                                <div class="sx-chips">
                                    <span class="sx-chip sx-in" style="--d: 1.2s" data-i18n="svx.s1.r1">واجهات</span>
                                    <span class="sx-chip sx-in" style="--d: 1.35s" data-i18n="svx.s1.r2">الخادم</span>
                                    <span class="sx-chip sx-in" style="--d: 1.5s" data-i18n="svx.s1.r3">التوثيق</span>
                                </div>
                                <div class="sx-card sx-alert sx-in" style="--d: 2s">
                                    <span class="sx-alert-ico">!</span>
                                    <span data-i18n="svx.s1.alert">فكرة مشابهة نُفّذت في 2023 — راجعها قبل التقديم</span>
                                </div>
                            </div>

                            {{-- ٢) التسليم: الملف يطير إلى المشرف، فيُختم ثم يُعتمد --}}
                            <div class="svx-scene" id="svx-scene-review" data-scene="1">
                                <svg class="sx-people" viewBox="0 0 360 150">
                                    <use href="#hj-girl" transform="translate(70 144) scale(1.5)" />
                                    <use href="#hj-supervisor" transform="translate(290 146) scale(1.35)" />
                                </svg>
                                <div class="sx-file">
                                    <span class="sx-file-ext">PDF</span>
                                    <span class="sx-file-text"><b data-i18n="svx.s2.file">الفصل الثالث</b><small>PDF · 2.4 MB</small></span>
                                </div>
                                <div class="sx-stamp is-warn" data-i18n="svx.s2.warn">مطلوب تعديل</div>
                                <div class="sx-stamp is-ok" data-i18n="svx.s2.ok">اعتُمدت ✓</div>
                                <div class="sx-card sx-progress">
                                    <span data-i18n="svx.s2.prog">نسبة الإنجاز</span>
                                    <b><span data-tween data-from="40" data-to="60" data-delay="3600">40</span>%</b>
                                    <i><em></em></i>
                                </div>
                            </div>

                            {{-- ٣) الخطة: مراحل بمواعيدها تظهر على خطّ زمني --}}
                            <div class="svx-scene" id="svx-scene-plan" data-scene="2">
                                <div class="sx-card sx-plan">
                                    <b class="sx-plan-title" data-i18n="svx.s3.title">خطة مراحل الفصل</b>
                                    <ol class="sx-timeline">
                                        @foreach ([['svx.s3.m1', 'تحليل المتطلبات', '30 يونيو'], ['svx.s3.m2', 'تصميم قاعدة البيانات', '14 يوليو'], ['svx.s3.m3', 'الواجهات', '28 يوليو'], ['svx.s3.m4', 'التطوير والبرمجة', '11 أغسطس'], ['svx.s3.m5', 'الاختبار والتوثيق', '25 أغسطس']] as $m => [$mk, $mt, $md])
                                            <li class="sx-in" style="--d: {{ .25 + $m * .22 }}s">
                                                <i></i><span data-i18n="{{ $mk }}">{{ $mt }}</span><small data-i18n="{{ $mk }}d">{{ $md }}</small>
                                                @if ($m === 1)<em class="sx-tpl">DOCX</em>@endif
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                                <div class="sx-chip is-strong sx-in" style="--d: 1.6s" data-i18n="svx.s3.sent">وصلت إلى 4 مجموعات ✓</div>
                            </div>

                            {{-- ٤) النقاش: فقاعات تُكتب أمامك --}}
                            <div class="svx-scene" id="svx-scene-chat" data-scene="3">
                                <div class="sx-card sx-chat">
                                    <div class="sx-chat-head"><b data-i18n="svx.s4.head">نقاش الفريق</b><span class="sx-lock" data-i18n="svx.s4.lock">🔒 خاص</span></div>
                                    <div class="sx-msg is-in sx-in" style="--d: .3s"><span class="sx-av">آي</span><p data-i18n="svx.s4.m1">رفعت الفصل الثالث ✅</p></div>
                                    <div class="sx-msg is-in sx-in" style="--d: 1.1s"><span class="sx-av is-2">حس</span><p><span class="sx-mention" data-i18n="svx.s4.at">@آية</span> <span data-i18n="svx.s4.m2">ممتاز، أراجعه الليلة</span></p></div>
                                    <div class="sx-msg is-mine sx-in" style="--d: 1.9s"><p data-i18n="svx.s4.m3">وأنا أجهّز العرض التقديمي 🎤</p></div>
                                    <div class="sx-typing sx-in" style="--d: 2.6s"><i></i><i></i><i></i></div>
                                </div>
                            </div>

                            {{-- ٥) المناقشة: لجنة وموعد، ودرجة تُرصد ثم تُقفل --}}
                            <div class="svx-scene" id="svx-scene-defense" data-scene="4">
                                <div class="sx-card sx-top sx-in" style="--d: .2s">
                                    <span class="sx-cal"><b>11</b><small data-i18n="svx.s5.mon">أكتوبر</small></span>
                                    <span><b data-i18n="svx.s5.when">الأحد 09:00 · قاعة 204</b><small data-i18n="svx.s5.who">المشرف + ممتحنان · رئيس اللجنة</small></span>
                                </div>
                                <svg class="sx-people" viewBox="0 0 360 150">
                                    <use href="#hj-committee" transform="translate(180 138) scale(1.5)" />
                                </svg>
                                <div class="sx-card sx-grade sx-in" style="--d: .9s">
                                    <b><span data-tween data-from="0" data-to="96" data-delay="1200">0</span><small>/100</small></b>
                                    <span class="sx-grade-tag" data-i18n="svx.s5.tag">ممتاز · معتمدة 🔒</span>
                                </div>
                            </div>

                            {{-- ٦) الاستكشاف: البحث يُكتب فتُرشَّح المشاريع --}}
                            <div class="svx-scene" id="svx-scene-explore" data-scene="5">
                                <div class="sx-card sx-search">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m21 21-4.3-4.3" /></svg>
                                    <span data-type data-delay="500" data-i18n-type="svx.s6.q">ذكاء</span><i class="sx-caret"></i>
                                </div>
                                <div class="sx-projects">
                                    <div class="sx-proj is-hit"><b data-i18n="svx.s6.p1">كشف الأخبار الزائفة</b><small data-i18n="svx.s6.p1t">ذكاء صناعي · 2023</small></div>
                                    <div class="sx-proj is-miss"><b data-i18n="svx.s6.p2">متجر إلكتروني</b><small data-i18n="svx.s6.p2t">برمجة ويب · 2022</small></div>
                                    <div class="sx-proj is-hit"><b data-i18n="svx.s6.p3">تحليل صور الأشعة</b><small data-i18n="svx.s6.p3t">ذكاء صناعي · 2024</small></div>
                                    <div class="sx-proj is-miss"><b data-i18n="svx.s6.p4">تطبيق حجز عيادات</b><small data-i18n="svx.s6.p4t">تطبيقات جوال · 2023</small></div>
                                </div>
                            </div>

                            {{-- مؤشّر الخدمات: ستّ نقاط تتبع القائمة --}}
                            <div class="svx-dots"><i class="is-on"></i><i></i><i></i><i></i><i></i><i></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= Features ======= --}}
        {{-- كيف بُنيت المنصة لا ماذا تفعل: جودات لا أدوات — كانت تكرّر الخدمات
             (المراحل والنقاش والتقييم) بعبارات أخرى --}}
        <section id="features" class="section">
            <div class="container">
                <div class="section-head" data-num="03">
                    <div class="section-index reveal"><span data-i18n="features.kicker">المميزات</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="features.title">مصمّمة للواقع الأكاديمي</h2>
                        <p class="reveal d1" data-i18n="features.text">
                            تفاصيل لا تُرى في العرض الأول، لكنها ما يجعل الفصل الدراسي يمرّ بلا مفاجآت.
                        </p>
                    </div>
                </div>

                {{-- محور المميزات: جهاز في المنتصف والمميزات الست حوله — كانت لوحتين
                     بثلاث بطاقات مليئة بالكلام. المميزة النشطة (تتبدّل وحدها، والمرور أو
                     النقر يختار) تُعرض حيّة على شاشة الجهاز بدليلها من شكلها الحقيقي.
                     الحركة في initFeatureHub (premium.js). --}}
                <div class="fh reveal" data-fhub>
                    @php
                        $fhub = [
                            ['lock', 'M8 11V7a4 4 0 0 1 8 0v4M6 11h12a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-6a2 2 0 0 1 2-2z', 'features.1.title', 'خصوصية الفريق', 'fh.1', 'نقاش داخلي لا يراه المشرف'],
                            ['log', 'M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6zM9 12l2 2 4-4', 'features.3.title', 'سجلّ لا يُعدَّل', 'fh.2', 'كل قرار محفوظ ومقفل'],
                            ['file', 'M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9zM14 3v6h6', 'features.4.title', 'ملفات محمية', 'fh.3', 'للفريق ومشرفه والإدارة فقط'],
                            ['bell', 'M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.9 1.9 0 0 0 3.4 0', 'features.2.title', 'إشعارات بلا ضجيج', 'fh.4', 'ما يخصّك فقط، لا كل رسالة'],
                            ['lang', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18', 'features.5.title', 'عربية أولاً', 'fh.5', 'والإنجليزية بنقرة'],
                            ['phone', 'M9 2h6a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zM11 18h2', 'features.6.title', 'على الجوال كما الحاسوب', 'fh.6', 'كل صفحة لشاشتك الصغيرة'],
                        ];
                    @endphp

                    <ul class="fh-pills" role="tablist" aria-label="المميزات" data-i18n-aria="features.kicker">
                        @foreach ($fhub as $k => [$key, $icon, $tk, $title, $lk, $line])
                            <li class="fh-pill is-{{ $k < 3 ? 'a' : 'b' }} {{ $k === 0 ? 'is-on' : '' }}" style="--k: {{ $k % 3 }}">
                                <button type="button" role="tab" aria-selected="{{ $k === 0 ? 'true' : 'false' }}" data-fh="{{ $k }}">
                                    <span class="fh-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}" /></svg></span>
                                    <span class="fh-txt"><b data-i18n="{{ $tk }}">{{ $title }}</b><small data-i18n="{{ $lk }}">{{ $line }}</small></span>
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    {{-- الجهاز: إطار حاسوب، وعلى شاشته دليل المميزة النشطة --}}
                    <div class="fh-device" aria-hidden="true">
                        <div class="fh-screen">
                            <div class="fh-bar"><i></i><i></i><i></i><span>takharruj.app</span></div>
                            <div class="fh-view is-on" data-fh-view="0">
                                <div class="fx-proof fx-chat">
                                    <span class="fx-bubble"><i>نب</i><span data-i18n="features.p1.msg">ننهي فصل التحليل الليلة؟</span></span>
                                    <span class="fx-seen"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="features.p1.seen">مرئي للفريق فقط</span></span>
                                    <span class="fx-nope"><span><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg><span data-i18n="features.p1.sup">المشرف</span></span><span><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg><span data-i18n="features.p1.adm">الإدارة</span></span></span>
                                </div>
                            </div>
                            <div class="fh-view" data-fh-view="1">
                                <div class="fx-proof fx-log">
                                    <span class="fx-log-row"><i class="fx-dot-amber"></i><b data-i18n="features.p3.l1">اعتماد درجة</b><small dir="ltr">14:32</small></span>
                                    <span class="fx-log-row"><i class="fx-dot-rose"></i><b data-i18n="features.p3.l2">فكّ اعتماد — بسبب مكتوب</b><small dir="ltr">09:05</small></span>
                                    <span class="fx-seen"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="features.p3.lock">لا تعديل ولا حذف</span></span>
                                </div>
                            </div>
                            <div class="fh-view" data-fh-view="2">
                                <div class="fx-proof fx-file">
                                    <span class="fx-file-row"><i>PDF</i><span dir="ltr">final-report.pdf</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg></span>
                                    <span class="fx-seen"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="features.p4.who">تنزيل للفريق ومشرفه والإدارة فقط</span></span>
                                </div>
                            </div>
                            <div class="fh-view" data-fh-view="3">
                                <div class="fx-proof fx-notes">
                                    <span class="fx-note is-on"><i class="is-warn"></i><span data-i18n="features.p2.a">مطلوب تعديل في «الفصل الثالث»</span></span>
                                    <span class="fx-note is-on"><i class="is-brand"></i><span data-i18n="features.p2.b">ذكرك زميل في نقاش الفريق</span></span>
                                    <span class="fx-note is-off"><i></i><span data-i18n="features.p2.c">رسالة عادية في النقاش</span><small data-i18n="features.p2.muted">بلا تنبيه</small></span>
                                </div>
                            </div>
                            <div class="fh-view" data-fh-view="4">
                                <div class="fx-proof fx-lang" data-fx-lang>
                                    <span class="fx-lang-toggle" aria-pressed="false"><span>ع</span><span>EN</span></span>
                                    <span class="fx-lang-line" dir="rtl" data-ar="مرحباً بك في مشروعك" data-en="Welcome to your project">مرحباً بك في مشروعك</span>
                                </div>
                            </div>
                            <div class="fh-view" data-fh-view="5">
                                <div class="fx-proof fx-phone">
                                    <span class="fx-phone-frame">
                                        <span class="fx-phone-notch"></span>
                                        <small data-i18n="features.p6.stage">الفصل الثالث</small>
                                        <span class="fx-phone-bar"><i></i></span>
                                        <span class="fx-phone-btn" data-i18n="features.p6.btn">تسليم المرحلة</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="fh-base"></div>
                        {{-- الطالبة تشير إلى الشاشة — من شخصيات الهيرو --}}
                        <svg class="fh-buddy" viewBox="-30 -90 60 95"><use href="#hj-girl" /></svg>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= How it works ======= --}}
        {{-- أربع محطات على مسار واحد متّصل — كانت أربعة صناديق منفصلة بالشكل ذاته
             للقسمين قبلها. والخطوات كما تجري فعلاً: تسليم ومراجعة، ودرجة تُعتمد --}}
        <section id="how" class="section">
            <div class="container">
                <div class="section-head" data-num="04">
                    <div class="section-index reveal"><span data-i18n="how.kicker">كيف يعمل النظام؟</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="how.title">أربع خطوات من الفكرة إلى الدرجة</h2>
                        <p class="reveal d1" data-i18n="how.text">
                            حسابك يُنشأ من إدارة القسم — لا تسجيل ولا انتظار، سجّل دخولك وابدأ.
                        </p>
                    </div>
                </div>

                {{-- أربع درجات يصعدها طالب: كل درجة خطوة، وحين يصل إليها تُضاء
                     ويظهر شرحها؛ وفي الأعلى قبعة التخرّج. كانت أربع دوائر على خطّ
                     — المسار الثالث في الصفحة بعد الهيرو والخدمات. الحركة في initStairs. --}}
                <div class="st reveal" data-stairs>
                    <ol class="st-steps">
                        @foreach ([
                            ['how.1.title', 'ادخل إلى المنصة', 'st.1', 'ببريدك أو رقمك الجامعي — حسابك جاهز.'],
                            ['how.2.title', 'كوّن فريقك وقدّم فكرتك', 'st.2', 'زملاء ومشرف بمقاعد، وفكرة لم تُنفَّذ.'],
                            ['how.3.title', 'سلّم مراحلك', 'st.3', 'تسليم، فاعتماد أو تعديل بملاحظة.'],
                            ['how.4.title', 'ناقش واستلم درجتك', 'st.4', 'لجنة ودرجة معتمدة لا تتغيّر.'],
                        ] as $k => [$tk, $title, $lk, $line])
                            <li class="st-step" style="--k: {{ $k }}" data-step="{{ $k }}">
                                <div class="st-copy" style="grid-column: {{ $k + 1 }}">
                                    <span class="st-num">{{ str_pad($k + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <b data-i18n="{{ $tk }}">{{ $title }}</b>
                                    <small data-i18n="{{ $lk }}">{{ $line }}</small>
                                </div>
                                <span class="st-block" style="grid-column: {{ $k + 1 }}"></span>
                            </li>
                        @endforeach
                    </ol>
                    {{-- الطالب الصاعد، وقبعته تظهر في القمّة --}}
                    <div class="st-climber" aria-hidden="true">
                        <svg viewBox="-30 -100 60 105">
                            <use href="#hj-boy" class="st-body" />
                            <use href="#hj-cap" class="st-cap" x="0" y="-74" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= Roles ======= --}}
        <section id="roles" class="section">
            <div class="container">
                <div class="section-head" data-num="05">
                    <div class="section-index reveal"><span data-i18n="roles.kicker">أدوار المنصة</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="roles.title">لكل دور مساحته الخاصة</h2>
                        <p class="reveal d1" data-i18n="roles.text">
                            ثلاثة أدوار، لكلٍّ لوحته وصلاحياته — ويتسلّم كلٌّ من الآخر في الوقت المناسب. اختر دوراً لترى مساحته.
                        </p>
                    </div>
                </div>

                {{-- مستكشف الأدوار بروح الهيرو: كل دور بطاقة بشخصيته (الطالبة، المشرف،
                     المسؤول) في هالة بلون الدور، ولوحة فيها نموذج من شاشته وما يفعله
                     وما لا يراه، وتحتها خطّ يربط الأدوار بما ينتقل بينها.
                     الجولة التلقائية والتبديل في premium.js › initRoles (الخطافات نفسها:
                     role=tab و data-role و data-panel و data-node و data-edge).
                     بلا JavaScript تظهر اللوحات الثلاث متتالية. --}}
                @php
                    $rxIcon = fn ($d) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="' . $d . '"/></svg>';
                    $rxRoles = [
                        'student' => ['hj-girl', 'roles.student.name', 'الطالب', 'roles.student.role', 'الفريق والتسليم والنقاش', 'roles.screen.s', 'لوحة الطالب'],
                        'supervisor' => ['hj-supervisor', 'roles.supervisor.name', 'المشرف', 'roles.supervisor.role', 'التخطيط والمراجعة والتقييم', 'roles.screen.v', 'لوحة المشرف'],
                        'admin' => ['hj-admin', 'roles.admin.name', 'الإدارة', 'roles.admin.role', 'ضبط النظام وتنظيم الفصل', 'roles.screen.a', 'لوحة الإدارة'],
                    ];
                    $rxCaps = [
                        'student' => [
                            ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75', 'c1', 'تكوين الفريق', 'زملاؤك من تخصصك، ومشرف لديه مقاعد متاحة'],
                            ['M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12', 'c2', 'تسليم المراحل', 'ملف وملاحظة لكل مرحلة، وإعادة بعد التعديل'],
                            ['M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z', 'c3', 'نقاش الفريق', 'قناة خاصة بالفريق، و@ لتنبيه زميل بعينه'],
                            ['M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0', 'c4', 'الأدوار والتذكير', 'يوزّع القائد المسؤوليات، ويصل تذكير قبل كل موعد'],
                        ],
                        'supervisor' => [
                            ['M22 12h-6l-2 3h-4l-2-3H2M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z', 'c1', 'قبول الطلبات', 'حسب مقاعده، مع تنبيه للفكرة المشابهة'],
                            ['M3 4h18v18H3zM16 2v4M8 2v4M3 10h18', 'c2', 'خطة المراحل', 'مواعيد وقوالب تصل كل مجموعاته مرّة واحدة'],
                            ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M9 15l2 2 4-4', 'c3', 'المراجعة', 'اعتماد، أو «مطلوب تعديل» بسبب مكتوب'],
                            ['M12 15a7 7 0 1 0 0-14 7 7 0 0 0 0 14zM8.21 13.89 7 23l5-3 5 3-1.21-9.12', 'c4', 'الدرجة', 'رصد بالتقدير والملاحظات، ثم اعتماد يقفلها'],
                        ],
                        'admin' => [
                            ['m12 2 10 5-10 5L2 7zM2 17l10 5 10-5M2 12l10 5 10-5', 'c1', 'إعداد الفصل', 'التخصصات وأنواع المشاريع وحدود الفرق والفصول'],
                            ['M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8M8 17h8M10 9H8', 'c2', 'الحسابات', 'استيراد الطلاب والمشرفين من Excel دفعة واحدة'],
                            ['M22 12h-4l-3 9L9 3l-3 9H2', 'c3', 'متابعة الفرق', 'المتأخّر والمتوقّف وما ينتظر المشرف، بنقرة'],
                            ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4', 'c4', 'سجلّ التدقيق', 'كل قرار مسجّل، وفتح الدرجة المعتمدة بسبب مكتوب'],
                        ],
                    ];
                    $rxPrivate = [
                        'student' => 'نقاش الفريق الخاص لا يصل المشرف ولا الإدارة',
                        'supervisor' => 'يرى مجموعاته وحدها — ونقاش الفريق الخاص يبقى للفريق',
                        'admin' => 'ترى كل شيء إلا نقاش الفرق الخاص — وكل قرار لها في السجلّ',
                    ];
                @endphp
                <div class="rx reveal" data-role-explorer>
                    <div class="rx-tabs" role="tablist" aria-label="أدوار المنصة" data-i18n-aria="roles.kicker">
                        @foreach ($rxRoles as $role => [$who, $nk, $name, $rk, $roleText])
                            <button type="button" role="tab" class="rx-tab is-{{ $role }}" id="rx-tab-{{ $role }}" aria-controls="rx-panel-{{ $role }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-role="{{ $role }}">
                                <span class="rx-tab-art" aria-hidden="true"><svg viewBox="-34 -100 68 106"><use href="#{{ $who }}" /></svg></span>
                                <span class="rx-tab-text">
                                    <b data-i18n="{{ $nk }}">{{ $name }}</b>
                                    <small data-i18n="{{ $rk }}">{{ $roleText }}</small>
                                </span>
                                <span class="rx-tab-num" aria-hidden="true">0{{ $loop->iteration }}</span>
                            </button>
                        @endforeach
                    </div>

                    @foreach ($rxRoles as $role => [$who, $nk, $name, $rk, $roleText, $sk, $screen])
                        <section class="rx-panel is-{{ $role }}" id="rx-panel-{{ $role }}" role="tabpanel" aria-labelledby="rx-tab-{{ $role }}" data-panel="{{ $role }}">
                            {{-- نموذج من شاشة الدور الحقيقية --}}
                            <div class="rx-scene" aria-hidden="true">
                                <div class="rx-screen">
                                    <div class="rx-screen-bar"><i></i><i></i><i></i><span data-i18n="{{ $sk }}">{{ $screen }}</span></div>
                                    <div class="rx-screen-body">
                                        @if ($role === 'student')
                                            <div class="rx-card">
                                                <span class="rx-card-label" data-i18n="roles.scene.s.now">ماذا عليّ الآن</span>
                                                <div class="rx-task">
                                                    <span class="rx-task-dot is-warn" aria-hidden="true"></span>
                                                    <span>
                                                        <b data-i18n="roles.scene.s.stage">الفصل الثالث — التحليل</b>
                                                        <small data-i18n="roles.scene.s.due">آخر موعد بعد يومين</small>
                                                    </span>
                                                    <span class="rx-btn is-primary" data-i18n="roles.scene.s.submit">تسليم المرحلة</span>
                                                </div>
                                            </div>
                                            <div class="rx-card">
                                                <div class="rx-progress-head">
                                                    <span data-i18n="roles.scene.s.progress">إنجاز المشروع</span>
                                                    <b dir="ltr">3 / 5</b>
                                                </div>
                                                <span class="rx-progress" aria-hidden="true"><i style="--w: 60%"></i></span>
                                                <div class="rx-avatars" aria-hidden="true"><i>سا</i><i>نب</i><i>يو</i></div>
                                            </div>
                                        @elseif ($role === 'supervisor')
                                            <div class="rx-card">
                                                <span class="rx-card-label" data-i18n="roles.scene.v.review">بانتظار مراجعتك</span>
                                                <div class="rx-sub">
                                                    <span class="rx-av" aria-hidden="true">تن</span>
                                                    <span>
                                                        <b data-i18n="roles.scene.v.team">فريق «التنبؤ بالتسرب»</b>
                                                        <small data-i18n="roles.scene.v.round">الفصل الثالث · الجولة 2</small>
                                                    </span>
                                                </div>
                                                <span class="rx-file"><i aria-hidden="true">PDF</i> <span dir="ltr">chapter-3.pdf</span></span>
                                                <div class="rx-actions">
                                                    <span class="rx-btn is-ok" data-i18n="roles.scene.v.approve">اعتماد</span>
                                                    <span class="rx-btn is-warn" data-i18n="roles.scene.v.revise">مطلوب تعديل</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="rx-card">
                                                <span class="rx-card-label" data-i18n="roles.scene.a.health">متابعة الفرق</span>
                                                <div class="rx-health">
                                                    <span class="is-alert"><b>3</b><small data-i18n="roles.scene.a.late">مرحلة فات موعدها</small></span>
                                                    <span><b>0</b><small data-i18n="roles.scene.a.review">تسليم ينتظر المشرف</small></span>
                                                    <span class="is-alert"><b>2</b><small data-i18n="roles.scene.a.idle">فريق متوقّف</small></span>
                                                    <span><b>0</b><small data-i18n="roles.scene.a.roles">فريق بلا أدوار</small></span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <svg class="rx-person" viewBox="-34 -100 68 106"><use href="#{{ $who }}" /></svg>
                            </div>

                            <div class="rx-info">
                                <h3 class="rx-title"><span data-i18n="{{ $nk }}">{{ $name }}</span></h3>
                                <ul class="rx-caps">
                                    @foreach ($rxCaps[$role] as [$icon, $ck, $ct, $cd])
                                        <li>
                                            <span class="rx-cap-icon" aria-hidden="true">{!! $rxIcon($icon) !!}</span>
                                            <b data-i18n="roles.{{ $role }}.{{ $ck }}.t">{{ $ct }}</b>
                                            <span data-i18n="roles.{{ $role }}.{{ $ck }}.d">{{ $cd }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="rx-private"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="roles.{{ $role }}.private">{{ $rxPrivate[$role] }}</span></p>
                            </div>
                        </section>
                    @endforeach

                    {{-- كيف تتصل الأدوار: ما ينتقل من كل دور إلى التالي — وزرّ الجولة التلقائية --}}
                    <div class="rx-foot">
                        <ol class="rx-flow" aria-label="كيف تتصل الأدوار" data-i18n-aria="roles.flow.label">
                            @foreach ($rxRoles as $role => [$who, $nk, $name])
                                <li class="rx-node is-{{ $role }}" data-node="{{ $role }}">
                                    <span class="rx-node-face" aria-hidden="true"><svg viewBox="-17 -88 34 34"><use href="#{{ $who }}" /></svg></span>
                                    <b data-i18n="{{ $nk }}">{{ $name }}</b>
                                    @if ($role === 'admin')<small data-i18n="roles.flow.3">تتابع الفصل كلّه</small>@endif
                                </li>
                                @if ($role === 'student')
                                    <li class="rx-edge" data-edge="student"><span data-i18n="roles.flow.1">يسلّم المرحلة ويعدّل</span></li>
                                @elseif ($role === 'supervisor')
                                    <li class="rx-edge" data-edge="supervisor"><span data-i18n="roles.flow.2">يعتمد ويرصد الدرجة</span></li>
                                @endif
                            @endforeach
                        </ol>
                        {{-- محتوى يتحرّك وحده يلزمه إيقاف (WCAG 2.2.2) — يظهر حين يعمل السكربت --}}
                        <button type="button" class="rx-tour" data-rx-tour hidden aria-pressed="true">
                            <svg class="rx-tour-pause" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                            <svg class="rx-tour-play" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.9l10.4-6.5a1 1 0 0 0 0-1.8L9.5 4.6A1 1 0 0 0 8 5.5z"/></svg>
                            <span data-rx-tour-label>إيقاف الجولة</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= Showcase — مشاريع أُنجزت فعلاً ======= --}}
        {{-- المكتملة المقيَّمة وحدها: الصفحة عامّة، فتعرض ما أُنجز لا عمل فرق لم تُسلّم.
             كانت تُسقط كل عنوان بلاحقة «— نسخة N» (بذور العرض) فيبقى مشروعان من تسعة؛
             الآن تُنزع اللاحقة ويُكتفى بعنوان واحد لكل مشروع. وتقول البطاقة ما مرّ به
             المشروع على المنصة (مراحله ومدّتها) — بلا أسماء طلاب ولا درجة فريق بعينه --}}
        @php
            $showcase = \Illuminate\Support\Facades\Cache::remember('site_showcase_v5', 3600, function () {
                $bare = fn ($title) => trim(preg_replace('/\s*—\s*نسخة\s*\d+\s*$/u', '', $title));
                $done = fn ($q) => $q->where('status', 'complete')->whereNotNull('grade');

                $projects = \App\Models\Project::query()->tap($done)
                    ->with(['project_type', 'semester'])
                    ->withCount(['group', 'milestones as stages_done' => fn ($q) => $q->where('is_done', true)])
                    ->withMin('milestones as first_due', 'due_date')
                    ->withMax('milestones as last_due', 'due_date')
                    ->latest()
                    ->take(40)
                    ->get()
                    ->each(fn ($p) => $p->setAttribute('bare_title', $bare($p->title)))
                    ->unique('bare_title')
                    ->take(12)
                    ->values();

                // المميّز يعرض أسماء مراحله المعتمدة — مسار المشروع على المنصة نفسه
                $projects->first()?->load(['milestones' => fn ($q) => $q->where('is_done', true)
                    ->select('id', 'project_id', 'title', 'due_date')]);

                $all = \App\Models\Project::query()->tap($done)->withCount(['milestones as stages_done' => fn ($q) => $q->where('is_done', true)])->get(['id', 'specialize_project_id', 'grade']);

                return [
                    'projects' => $projects,
                    'stats' => [
                        'done' => $all->count(),
                        'avg' => $all->count() ? round($all->avg('grade'), 1) : null,
                        'specs' => \App\Models\SpecializeProject::whereIn('id', $all->pluck('specialize_project_id')->unique())->distinct()->count('specialize_id'),
                        'stages' => $all->count() ? (int) round($all->avg('stages_done')) : null,
                    ],
                ];
            });
            $showProjects = $showcase['projects'];
            $showStats = $showcase['stats'];
        @endphp
        @if ($showProjects->isNotEmpty())
            <section id="showcase" class="section">
                <div class="container">
                    <div class="section-head" data-num="06">
                        <div class="section-index reveal"><span data-i18n="show.kicker">من المنصة</span></div>
                        <div class="section-head-grid">
                            <h2 class="reveal" data-i18n="show.title">مشاريع أُنجزت على تخرُّج</h2>
                            <p class="reveal d1" data-i18n="show.text">
                                ليست أمثلة مصنوعة — مشاريع أكملتها فرق فعلاً على المنصة، مرحلةً بعد مرحلة.
                            </p>
                        </div>
                    </div>

                    {{-- معرض الملصقات: كل مشروع ملصق بغلاف لونه من نوعه ورمزه، يمرّ في شريط
                         متحرك لا ينتهي (يتوقّف عند المرور). كانت أربع بطاقات طويلة بالنصوص.
                         الأرقام الإجمالية حقيقية — المتوسط العام لا درجة فريق بعينه. --}}
                    <div class="pz reveal" data-posters>
                        <div class="pz-stats">
                            <div class="pz-stat"><b data-count="{{ $showStats['done'] }}">0</b><span data-i18n="show.stat.done">مشروعاً مكتملاً</span></div>
                            @if ($showStats['avg'])
                                <div class="pz-stat is-accent"><b>{{ $showStats['avg'] }}</b><span data-i18n="show.stat.avg">متوسط الدرجات</span></div>
                            @endif
                            <div class="pz-stat"><b data-count="{{ $showStats['specs'] }}">0</b><span data-i18n="show.stat.specs">تخصصات</span></div>
                            @if ($showStats['stages'])
                                <div class="pz-stat"><b data-count="{{ $showStats['stages'] }}">0</b><span data-i18n="show.stat.stages">مراحل معتمدة لكل مشروع</span></div>
                            @endif
                            {{-- خرّيجون يرمون قبعاتهم — من شخصيات الهيرو --}}
                            <svg class="pz-grads" viewBox="-80 -100 160 104" aria-hidden="true">
                                <use href="#hj-girl" x="-48" /><use href="#hj-boy" x="0" /><use href="#hj-boy2" x="48" />
                                <use href="#hj-cap" class="pz-cap" x="-48" y="-82" /><use href="#hj-cap" class="pz-cap is-2" x="0" y="-86" /><use href="#hj-cap" class="pz-cap is-3" x="48" y="-86" />
                            </svg>
                        </div>

                        @php
                            $glyphs = [
                                'ai' => 'M12 2a4 4 0 0 1 4 4v1a3 3 0 0 1 3 3v1a3 3 0 0 1-1 2.2V15a3 3 0 0 1-3 3h-1v2h-4v-2H9a3 3 0 0 1-3-3v-1.8A3 3 0 0 1 5 11v-1a3 3 0 0 1 3-3V6a4 4 0 0 1 4-4zM9 11h.01M15 11h.01M9.5 15h5',
                                'web' => 'M3 5h18v14H3zM3 9h18M7 7h.01M10 7h.01',
                                'mobile' => 'M8 2h8a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2zM11 18h2',
                                'work' => 'M4 7h16v13H4zM9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M4 12h16',
                                'research' => 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5zM8 7h8M8 11h6',
                                'code' => 'M8 7l-5 5 5 5M16 7l5 5-5 5M14 4l-4 16',
                            ];
                            $glyphOf = function ($type) {
                                return match (true) {
                                    str_contains($type, 'ذكاء') => 'ai',
                                    str_contains($type, 'ويب') => 'web',
                                    str_contains($type, 'جوال') || str_contains($type, 'تطبيقات') => 'mobile',
                                    str_contains($type, 'تدريب') => 'work',
                                    str_contains($type, 'بحث') || str_contains($type, 'ابحاث') => 'research',
                                    default => 'code',
                                };
                            };
                        @endphp
                        <div class="pz-wrap">
                            <div class="pz-track">
                                @foreach ([false, true] as $copy)
                                    @foreach ($showProjects as $project)
                                        @php
                                            $type = $project->project_type->name ?? '';
                                            $sem = $project->semester?->parts();
                                        @endphp
                                        <article class="pz-card" style="--h: {{ crc32($type) % 360 }}" @if ($copy) aria-hidden="true" @endif>
                                            <div class="pz-cover">
                                                <span class="pz-glyph"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $glyphs[$glyphOf($type)] }}" /></svg></span>
                                                <span class="pz-type" dir="auto">{{ $type }}</span>
                                                <span class="pz-done"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span data-i18n="state.done">مكتمل</span></span>
                                            </div>
                                            <div class="pz-body">
                                                <h3 dir="auto">{{ $project->bare_title }}</h3>
                                                <div class="pz-meta">
                                                    @if ($project->stages_done)
                                                        <span class="pz-steps" aria-hidden="true">@for ($i = 0; $i < min($project->stages_done, 6); $i++)<i></i>@endfor</span>
                                                        <span><b>{{ $project->stages_done }}</b> <span data-i18n="show.stages">مراحل معتمدة</span></span>
                                                    @endif
                                                    @if ($sem)<span class="pz-sem">{{ $sem['term'] }} @if ($sem['year'])<bdi dir="ltr">&#x2066;{{ $sem['year'] }}&#x2069;</bdi>@endif</span>@endif
                                                </div>
                                            </div>
                                        </article>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('login') }}" class="link-more sc-more reveal">
                        <span data-i18n="show.more">ادخل لتتصفّح أرشيف المشاريع كاملاً</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    </a>
                </div>
            </section>
        @endif

        {{-- ======= Lifecycle — قبل تخرُّج وبعدها ======= --}}
        {{-- كانت «دورة حياة المشروع» بنصّ كتاب إدارة مشاريع عامّ («رصد الموارد
             المالية»، «الفئات المستفيدة») — ومسار الخطوات الأربع للمرّة الثالثة بعد
             الهيرو و04. صارت مقارنة تجيب «لماذا أنتقل؟»؛ والرقم والمعرّف كما هما --}}
        <section id="lifecycle" class="section">
            <div class="container">
                <div class="section-head" data-num="07">
                    <div class="section-index reveal"><span data-i18n="lc.kicker">لماذا تخرُّج</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="lc.title">ما يتغيّر حين يصير مشروعك على تخرُّج</h2>
                        <p class="reveal d1" data-i18n="lc.text">
                            ستّ مشكلات يعرفها كل فريق تخرّج، وما تفعله المنصة بكلٍّ منها.
                        </p>
                    </div>
                </div>

                {{-- قبل/بعد بشريط سحب: الفوضى التي يعرفها كل فريق (رسائل وملفات مبعثرة)
                     تحت المنصة المرتّبة، والمقبض يكشف الفرق. كان جدولاً من ستة صفوف
                     بنصوص طويلة. المقبض يُسحب أو يُحرَّك بالأسهم (range)، ويتحرّك وحده
                     مرة حين يظهر القسم ليدلّ على نفسه. الحركة في initCompare. --}}
                <div class="cmp reveal" data-compare style="--pos: 50%">
                    {{-- قبل: فوضى --}}
                    <div class="cmp-side is-before" aria-hidden="true">
                        <span class="cmp-tag" data-i18n="lc.before">قبل</span>
                        <div class="cmp-mess">
                            <span class="mess is-wa" style="--x: 8%; --y: 12%; --r: -6deg"><b>💬 15</b> <span data-i18n="cmp.m1">مين رفع الملف الأخير؟</span></span>
                            <span class="mess is-file" style="--x: 52%; --y: 8%; --r: 5deg">📄 <span dir="ltr">final_v3_FINAL(2).docx</span></span>
                            <span class="mess is-wa" style="--x: 30%; --y: 34%; --r: 3deg"><b>💬</b> <span data-i18n="cmp.m2">الموعد بكرة ولا الأسبوع الجاي؟؟</span></span>
                            <span class="mess is-red" style="--x: 60%; --y: 46%; --r: -4deg">❌ <span data-i18n="cmp.m3">مرفوض</span></span>
                            <span class="mess is-mail" style="--x: 6%; --y: 58%; --r: -3deg">✉️ <span data-i18n="cmp.m4">Re: Re: Fwd: التعديلات</span></span>
                            <span class="mess is-wa" style="--x: 40%; --y: 74%; --r: 6deg"><b>💬</b> <span data-i18n="cmp.m5">مين عليه الواجهات؟</span></span>
                            <span class="mess is-paper" style="--x: 10%; --y: 84%; --r: -7deg">📝 <span data-i18n="cmp.m6">كشف الدرجات (ورقي)</span></span>
                        </div>
                    </div>
                    {{-- مع تخرُّج: مرتّب — كل مشكلة وحلّها --}}
                    <div class="cmp-side is-after">
                        <span class="cmp-tag is-after" data-i18n="lc.after">مع تخرُّج</span>
                        <ul class="cmp-grid">
                            @foreach ([
                                ['lc.1.title', 'التنسيق', 'lc.1.after', 'نقاش فريق خاص، و@ لتنبيه زميل بعينه'],
                                ['lc.2.title', 'الملفات', 'lc.2.after', 'ملف واحد، وملاحظاته عليه حتى تُعالَج'],
                                ['lc.3.title', 'المواعيد', 'lc.3.after', 'خطة مراحل بمواعيدها وقوالبها تصل كل مجموعة'],
                                ['lc.4.title', 'الملاحظات', 'lc.4.after', '«مطلوب تعديل» بسبب واضح، وكل جولة محفوظة'],
                                ['lc.5.title', 'المسؤوليات', 'lc.5.after', 'أدوار معلنة يراها الفريق والمشرف'],
                                ['lc.6.title', 'الدرجة', 'lc.6.after', 'درجة معتمدة مقفلة، وسجلّ لكل قرار'],
                            ] as $k => [$tk, $t, $ak, $a])
                                <li class="cmp-card" style="--k: {{ $k }}">
                                    <span class="cmp-check" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5" /></svg></span>
                                    <span><b data-i18n="{{ $tk }}">{{ $t }}</b><small data-i18n="{{ $ak }}">{{ $a }}</small></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    {{-- المقبض --}}
                    <div class="cmp-handle" aria-hidden="true"><span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 6-6 6 6 6M15 6l6 6-6 6" /></svg></span></div>
                    <input type="range" class="cmp-range" min="4" max="96" value="50" aria-label="قارن قبل ومع تخرُّج" data-i18n-aria="cmp.aria">
                </div>
            </div>
        </section>

        {{-- ======= FAQ ======= --}}
        <section id="faq" class="section">
            <div class="container">
                <div class="section-head" data-num="08">
                    <div class="section-index reveal"><span data-i18n="faq.kicker">الأسئلة الشائعة</span></div>
                </div>

                {{-- details أصلي: يُفتح بلا JavaScript، ولا يقرأ قارئ الشاشة إلا المفتوح.
                     premium.js › initFaq يضيف الحركة والفلاتر والرابط المباشر #faq-N --}}
                <div class="fq">
                    <aside class="fq-side">
                        <h2 class="reveal" data-i18n="faq.title">كل ما يسأله الطلاب قبل البدء</h2>
                        <p class="fq-lead reveal d1" data-i18n="faq.text">
                            إجابات مباشرة من واقع النظام — ولأي سؤال آخر تواصل معنا من قسم الاتصال بالأسفل.
                        </p>
                        <div class="fq-filters reveal d1" role="group" aria-label="تصفية الأسئلة" data-i18n-aria="faq.filters" hidden data-fq-filters>
                            <button type="button" class="fq-filter" data-fq-filter="all" aria-pressed="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg><span data-i18n="faq.cat.all">الكل</span></button>
                            <button type="button" class="fq-filter" data-fq-filter="apply" aria-pressed="false"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4z"/></svg><span data-i18n="faq.cat.apply">التقديم</span></button>
                            <button type="button" class="fq-filter" data-fq-filter="team" aria-pressed="false"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg><span data-i18n="faq.cat.team">الفريق</span></button>
                            <button type="button" class="fq-filter" data-fq-filter="track" aria-pressed="false"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 14 4-4 4 4 5-5"/></svg><span data-i18n="faq.cat.track">المتابعة والتقييم</span></button>
                        </div>
                        <div class="fq-ask reveal d2">
                            <span class="fq-person" aria-hidden="true"><svg viewBox="-30 -98 60 102"><use href="#hj-boy2" /></svg><i>؟</i></span>
                            <span class="fq-ask-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
                            <div>
                                <b data-i18n="faq.ask.title">ما وجدت جوابك؟</b>
                                <span data-i18n="faq.ask.text">اكتب لنا سؤالك ونردّ عليك على بريدك في أقرب وقت.</span>
                            </div>
                            <a href="#contact" class="fq-ask-btn">
                                <span data-i18n="faq.ask.button">راسلنا</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                            </a>
                        </div>
                    </aside>

                    <div class="fq-list reveal">
                        @foreach ([
                            ['cat' => 'team', 'q' => 'كم عضواً يتكون منه الفريق؟', 'a' => 'حسب نوع المشروع الذي يحدده قسمك — كل نوع له حد أدنى وأقصى يظهران أمامك في نموذج التقديم، والنظام لا يقبل فريقاً خارج الحدود.'],
                            ['cat' => 'apply', 'q' => 'كيف أقدم طلب مشروع؟', 'a' => 'أربع خطوات من لوحتك، ويصل طلبك للمشرف فوراً:'],
                            ['cat' => 'apply', 'q' => 'كيف أعرف ردّ المشرف، وماذا لو رُفض طلبي؟', 'a' => 'يصلك إشعار فور القبول أو الرفض، ومعه ملاحظة المشرف إن كتبها. وإن رُفض طلبك يُفتح لك نموذج تقديم جديد مباشرة — عدّل فكرتك أو اختر مشرفاً آخر.'],
                            ['cat' => 'track', 'q' => 'ماذا يعني «مطلوب تعديل» على مرحلة سلّمتها؟', 'a' => 'أن مشرفك راجعها وكتب ما ينقصها — لا أنها رُفضت. عدّل ملفك وأعد التسليم من الصفحة نفسها، وتبقى كل جولة وملاحظتها محفوظة.'],
                            ['cat' => 'team', 'q' => 'من يوزّع الأدوار في الفريق؟', 'a' => 'قائد الفريق: يسند لكل عضو دوره ومسؤولياته، فيراها الفريق كله ويراها المشرف — واضح من على ماذا قبل المناقشة.'],
                            ['cat' => 'team', 'q' => 'هل يرى المشرف نقاش الفريق؟', 'a' => 'لا. للفريق قناة خاصة لا يراها المشرف ولا الإدارة، وقناة ثانية مشتركة مع المشرف للأسئلة والملاحظات.'],
                            ['cat' => 'track', 'q' => 'كيف يُقيَّم مشروعي النهائي؟', 'a' => 'بعد اكتمال المشروع يرصد مشرفك الدرجة من 100 مع ملاحظاته، ويُحسب التقدير منها تلقائياً، ويصل الإشعار للفريق كله. وبعد اعتمادها تُقفل — لا يفتحها إلا الإدارة بسبب مكتوب، ويُسجَّل ذلك.'],
                        ] as $i => $item)
                            <details class="fq-item" name="faq" id="faq-{{ $i + 1 }}" data-cat="{{ $item['cat'] }}" {{ $i === 0 ? 'open' : '' }}>
                                <summary>
                                    <span class="fq-num">{{ sprintf('%02d', $i + 1) }}</span>
                                    <span class="fq-q" data-i18n="faq.{{ $i + 1 }}.title">{{ $item['q'] }}</span>
                                    <span class="fq-sign" aria-hidden="true"></span>
                                </summary>
                                <div class="fq-body">
                                    <p data-i18n="faq.{{ $i + 1 }}.text">{{ $item['a'] }}</p>
                                    @if ($i === 1)
                                        <ol class="fq-steps">
                                        <li><i>1</i><span data-i18n="faq.2.s1">سجّل دخولك</span></li>
                                        <li><i>2</i><span data-i18n="faq.2.s2">اختر النوع ومشرفاً لديه مقاعد</span></li>
                                        <li><i>3</i><span data-i18n="faq.2.s3">اختر أعضاء فريقك</span></li>
                                        <li><i>4</i><span data-i18n="faq.2.s4">اكتب العنوان والوصف وأرسل</span></li>
                                        </ol>
                                    @endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= For departments ======= --}}
        <section id="departments" class="section">
            <div class="container">
                <div class="section-head" data-num="09">
                    <div class="section-index reveal"><span data-i18n="dept.kicker">للأقسام والكليات</span></div>
                </div>

                {{-- الرأس: العنوان، وبجانبه «ملف الفصل» مرسوماً يجيب عن أسئلة الفقرة الأربعة.
                     الأرقام توضيحية كأدلّة القسم 03 — لا بيانات حقيقية للزوّار. --}}
                <div class="dx-head">
                    <div>
                        <h2 class="reveal" data-i18n="dept.title">الفصل الدراسي كله أمام القسم</h2>
                        <p class="dept-lead reveal d1" data-i18n="dept.text">
                            بدل جداول متفرقة ومجموعات محادثة، تعطي تخرُّج القسمَ صورةً واحدة: من قدّم،
                            ومن وافق، وأين وصل كل فريق، ومن لم يلتحق بمجموعة بعد.
                        </p>
                    </div>

                    <div class="dx-file reveal d2" aria-hidden="true">
                        <div class="dx-file-top">
                            <span class="dx-file-name"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg><span data-i18n="dept.file.name">ملف الفصل</span></span>
                            <span class="dx-chip is-open"><i></i><span data-i18n="dept.file.term">الفصل الأول</span> <bdi dir="ltr">&#x2066;2022–23&#x2069;</bdi></span>
                        </div>
                        <div class="dx-row">
                            <span class="dx-q" data-i18n="dept.file.q1">من قدّم؟</span>
                            <span class="dx-bar"><i style="width:100%"></i></span>
                            <b><bdi dir="ltr">42</bdi> <span data-i18n="dept.file.teams">فريقاً</span></b>
                        </div>
                        <div class="dx-row">
                            <span class="dx-q" data-i18n="dept.file.q2">من وافق؟</span>
                            <span class="dx-bar"><i style="width:90%"></i></span>
                            <b><bdi dir="ltr">38 / 42</bdi></b>
                        </div>
                        <div class="dx-row is-stages">
                            <span class="dx-q" data-i18n="dept.file.q3">أين وصل كل فريق؟</span>
                            <span class="dx-stages"><i style="flex:6"></i><i style="flex:14"></i><i style="flex:12"></i><i style="flex:6"></i></span>
                            <span class="dx-legend">
                                <span><i></i><span data-i18n="dept.file.s1">الفكرة</span></span>
                                <span><i></i><span data-i18n="dept.file.s2">التنفيذ</span></span>
                                <span><i></i><span data-i18n="dept.file.s3">التسليم</span></span>
                                <span><i></i><span data-i18n="dept.file.s4">المناقشة</span></span>
                            </span>
                        </div>
                        <div class="dx-row is-warn">
                            <span class="dx-q" data-i18n="dept.file.q4">من لم يلتحق؟</span>
                            <span class="dx-faces"><i></i><i></i><i></i><i>+8</i></span>
                            <b><bdi dir="ltr">11</bdi> <span data-i18n="dept.file.students">طالباً</span></b>
                        </div>
                    </div>
                </div>

                {{-- دورة الفصل: ثلاث محطات (قبله، بدايته، حتى إغلاقه) يمشيها مسؤول القسم،
                     ولكل محطة ميزتان بعنوانيهما — كانت ست بطاقات بأمثلة ونصوص طويلة.
                     الحركة في initSeason (premium.js). --}}
                <div class="ds reveal" data-season>
                    <div class="ds-rail" aria-hidden="true"><span class="ds-fill"></span></div>
                    <ol class="ds-stations">
                        @foreach ([
                            ['dept.p1.when', 'قبل الفصل', 'dept.p1.name', 'التجهيز', 'M3 4h18v18H3zM16 2v4M8 2v4M3 10h18', [
                                ['M3 4h18v18H3zM16 2v4M8 2v4M3 10h18', 'dept.6.title', 'فصول دراسية تُفتح وتُغلق'],
                                ['M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12', 'dept.2.title', 'استيراد الطلاب والمشرفين من ملف Excel'],
                            ]],
                            ['dept.p2.when', 'بداية الفصل', 'dept.p2.name', 'التوزيع', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87', [
                                ['m12 2 10 5-10 5L2 7zM2 17l10 5 10-5M2 12l10 5 10-5', 'dept.3.title', 'أنواع مشاريع بحدود فريق لكل تخصص'],
                                ['M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'dept.1.title', 'توزيع المشرفين بحدّ أقصى لكل واحد'],
                            ]],
                            ['dept.p3.when', 'حتى نهايته', 'dept.p3.name', 'المتابعة والإغلاق', 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4', [
                                ['M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10zM9 12l2 2 4-4', 'dept.5.title', 'سجلّ تدقيق لكل قرار'],
                                ['M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3', 'dept.4.title', 'تصدير كشف المجموعات إلى Excel'],
                            ]],
                        ] as $k => [$wk, $when, $nk, $name, $icon, $items])
                            <li class="ds-st" data-st="{{ $k }}">
                                <span class="ds-node" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}" /></svg></span>
                                <small data-i18n="{{ $wk }}">{{ $when }}</small>
                                <h3 data-i18n="{{ $nk }}">{{ $name }}</h3>
                                <ul class="ds-items">
                                    @foreach ($items as [$ii, $ik, $it])
                                        <li><span class="ds-ico" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $ii }}" /></svg></span><b data-i18n="{{ $ik }}">{{ $it }}</b></li>
                                    @endforeach
                                </ul>
                            </li>
                        @endforeach
                    </ol>
                    <div class="ds-walker" aria-hidden="true"><svg viewBox="-30 -96 60 100"><use href="#hj-admin" /></svg></div>
                </div>
            </div>
        </section>

        {{-- ======= Contact ======= --}}
        <section id="contact" class="section">
            <div class="container">
                <div class="section-head" data-num="10">
                    <div class="section-index reveal"><span data-i18n="contact.kicker">اتصل بنا</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="contact.title">تواصل معنا</h2>
                        <p class="reveal d1" data-i18n="contact.text">عندك سؤال أو اقتراح حول منصة تخرُّج؟ اكتب لنا وسنرد في أقرب وقت.</p>
                    </div>
                </div>

                @php
                    $name = auth('admin')->check() ? auth('admin')->user()->name : (auth('supervisor')->check() ? auth('supervisor')->user()->name : (auth('student')->check() ? auth('student')->user()->name : ''));
                    $email = auth('admin')->check() ? auth('admin')->user()->email : (auth('supervisor')->check() ? auth('supervisor')->user()->email : (auth('student')->check() ? auth('student')->user()->email : ''));
                @endphp

                {{-- الإرسال في الخلفية (premium.js › initContact): تبقى الصفحة في
                     قسمها ويحلّ تأكيد الإرسال محلّ النموذج. كان النموذج يُرسل فتُعاد
                     الصفحة من رأسها، فلا يرى المرسل أن رسالته وصلت. وبلا JavaScript
                     يعود الخادم إلى قسم التواصل نفسه --}}
                <div class="contact-grid reveal">
                    <aside class="contact-aside">
                        <h3 data-i18n="contact.asideTitle">قبل أن تكتب</h3>
                        <p data-i18n="contact.asideText">
                            حسابك يُنشأ من إدارة قسمك، فإن لم تستطع الدخول برقمك الجامعي راجعها أولاً.
                            وللأسئلة حول المواعيد وأنواع المشاريع، اكتب لنا هنا.
                        </p>

                        <ul class="contact-points">
                            <li class="contact-point">
                                <span class="contact-point-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg></span>
                                <span data-i18n="contact.pointMail">الرد على بريدك الإلكتروني</span>
                            </li>
                            <li class="contact-point">
                                <span class="contact-point-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
                                <span data-i18n="contact.pointHours">من الأحد إلى الخميس</span>
                            </li>
                        </ul>

                        <div class="ct-person" aria-hidden="true">
                            <svg viewBox="-32 -98 64 102"><use href="#hj-admin" /></svg>
                            <span class="ct-bubble" data-i18n="contact.bubble">أهلاً! نقرأ كل رسالة 👋</span>
                        </div>

                        <a href="#faq" class="contact-faq">
                            <span>
                                <b data-i18n="contact.faqTitle">ربما الجواب جاهز</b>
                                <span data-i18n="contact.faqText">سبعة أسئلة يسألها كل فريق قبل البدء</span>
                            </span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        </a>
                    </aside>

                    <div class="contact-main">
                        <form action="{{ route('site.send') }}" method="post" class="contact-form" novalidate
                            data-contact-form>
                            @csrf

                            @if (session('success'))
                                <p class="form-alert ok" role="status">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                                    {{ session('success') }}
                                </p>
                            @endif
                            @if (session('fail'))
                                <p class="form-alert err" role="alert">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                    {{ session('fail') }}
                                </p>
                            @endif
                            {{-- خطأ الإرسال في الخلفية (شبكة، أو حدّ المحاولات) --}}
                            <p class="form-alert err" role="alert" data-form-error hidden>
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                <span></span>
                            </p>

                            <div class="form-row">
                                <div class="field">
                                    <label for="cf-name" data-i18n="form.name">الاسم</label>
                                    <input id="cf-name" type="text" name="name" required maxlength="120" autocomplete="name"
                                        class="@error('name') is-invalid @enderror" value="{{ old('name', $name) }}"
                                        @if ($name) readonly @endif />
                                    <small class="field-error" data-error-for="name">@error('name'){{ $message }}@enderror</small>
                                </div>
                                <div class="field">
                                    <label for="cf-email" data-i18n="form.email">البريد الإلكتروني</label>
                                    <input id="cf-email" type="email" name="email" required maxlength="190" autocomplete="email" dir="ltr"
                                        class="@error('email') is-invalid @enderror" value="{{ old('email', $email) }}"
                                        @if ($email) readonly @endif />
                                    <small class="field-error" data-error-for="email">@error('email'){{ $message }}@enderror</small>
                                </div>
                            </div>

                            <div class="field">
                                <label for="cf-subject" data-i18n="form.subject">الموضوع</label>
                                {{-- مواضيع شائعة بنقرة — والكتابة الحرّة متاحة دائماً --}}
                                <div class="subject-chips" role="group" aria-label="مواضيع شائعة" data-i18n-aria="contact.topics">
                                    <button type="button" data-i18n="contact.topic1">مشكلة في الدخول</button>
                                    <button type="button" data-i18n="contact.topic2">سؤال عن المواعيد</button>
                                    <button type="button" data-i18n="contact.topic3">اقتراح للمنصة</button>
                                </div>
                                <input id="cf-subject" type="text" name="subject" required maxlength="200"
                                    class="@error('subject') is-invalid @enderror" value="{{ old('subject') }}" />
                                <small class="field-error" data-error-for="subject">@error('subject'){{ $message }}@enderror</small>
                            </div>

                            <div class="field">
                                <label for="cf-message" data-i18n="form.message">الرسالة</label>
                                <textarea id="cf-message" name="message" rows="5" required maxlength="5000"
                                    class="@error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                                <div class="field-foot">
                                    <small class="field-error" data-error-for="message">@error('message'){{ $message }}@enderror</small>
                                    <small class="field-count" dir="ltr"><bdi data-count-for="message">0</bdi> / 5000</small>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary" data-submit>
                                    <span class="btn-spinner" aria-hidden="true"></span>
                                    <svg class="btn-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                                    <span data-i18n="form.send">إرسال</span>
                                </button>
                                <span class="form-hint" data-i18n="contact.hint">نردّ على بريدك مباشرة</span>
                            </div>
                        </form>

                        {{-- تأكيد الإرسال: يحلّ محلّ النموذج في مكانه --}}
                        <div class="contact-done" data-contact-done hidden tabindex="-1" role="status">
                            <span class="done-mark" aria-hidden="true">
                                <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"/><path d="M15 27l7 7 15-16"/></svg>
                            </span>
                            <h3 data-i18n="contact.doneTitle">وصلت رسالتك</h3>
                            <p>
                                <span data-i18n="contact.doneText">سنردّ عليك في أقرب وقت على</span>
                                <bdi dir="ltr" data-done-email></bdi>
                            </p>
                            <button type="button" class="contact-again" data-contact-again>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                                <span data-i18n="contact.again">إرسال رسالة أخرى</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>


    {{-- ======= Closing CTA ======= --}}
    <section class="cta-band">
        <div class="container">
        <div class="cta-inner">
            <div>
                <h2 data-i18n="cta.title">جاهز تبدأ مشروع تخرجك؟</h2>
                <p data-i18n="cta.text">
                    حسابك جاهز مسبقاً من إدارة المنصة — سجّل دخولك برقمك الجامعي، كوّن فريقك،
                    واختر مشرفك. بقية الطريق تتابعها من لوحتك.
                </p>
                <ol class="cta-steps">
                    <li><i>1</i><span data-i18n="cta.step1">سجّل برقمك الجامعي</span></li>
                    <li><i>2</i><span data-i18n="cta.step2">كوّن فريقك</span></li>
                    <li><i>3</i><span data-i18n="cta.step3">اختر مشرفك</span></li>
                </ol>
            </div>
            <div class="cta-actions">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <span data-i18n="cta.button">تسجيل الدخول</span>
                    <svg class="cta-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </a>
                <a href="#contact" class="btn-link">
                    <span data-i18n="cta.secondary">عندي سؤال</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </a>
            </div>
        </div>
        </div>
    </section>

    {{-- ======= Footer ======= --}}
    <footer class="site-footer">
        <div class="container">
            <div class="footer-cols">
                <div class="footer-about">
                    <div class="footer-brand">
                        <span class="brand-logo" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        </span>
                        <span class="brand-name" data-i18n="brand.name">تخرُّج</span>
                    </div>
                    <p data-i18n="footer.about">
                        منصة لإدارة مشاريع التخرج من تكوين الفريق واعتماد الفكرة، مروراً بمتابعة
                        المراحل والملفات والنقاش، وصولاً إلى المناقشة والتقييم النهائي.
                    </p>
                    {{-- رابط واحد حقيقي: الأربعة السابقة كانت تشير كلها إلى الحساب نفسه --}}
                    @if ($currentSemester = \App\Models\Semester::current())
                        @php($semParts = $currentSemester->parts())
                        <div class="footer-live">
                            <i aria-hidden="true"></i>
                            <span data-i18n="footer.current">الفصل الحالي</span>
                            <b>
                                {{ $semParts['term'] }}
                                {{-- السنة معزولة LTR: داخل نصّ عربي تنقلب «2022\2023» إلى «2023\2022» --}}
                                @if ($semParts['year'])<span class="footer-live-sep">·</span><bdi dir="ltr">&#x2066;{{ $semParts['year'] }}&#x2069;</bdi>@endif
                            </b>
                        </div>
                    @endif
                    <div class="footer-socials">
                        <a href="https://www.facebook.com/jamal.taroush" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                    </div>
                </div>

                <nav class="footer-col" aria-label="Platform">
                    <div class="footer-col-title" data-i18n="footer.colPlatform">المنصة</div>
                    <a href="#about" data-i18n="nav.about">عن المنصة</a>
                    <a href="#services" data-i18n="nav.services">الخدمات</a>
                    <a href="#features" data-i18n="nav.features">الميزات</a>
                    <a href="#how" data-i18n="footer.how">كيف يعمل</a>
                </nav>

                <nav class="footer-col" aria-label="Roles">
                    <div class="footer-col-title" data-i18n="footer.colRoles">الأدوار</div>
                    {{-- كل رابط يفتح تبويب دوره في القسم 05 (premium.js › initRoles) --}}
                    <a href="#roles" data-role-link="student" data-i18n="roles.student.name">الطالب</a>
                    <a href="#roles" data-role-link="supervisor" data-i18n="roles.supervisor.name">المشرف</a>
                    <a href="#roles" data-role-link="admin" data-i18n="roles.admin.name">الإدارة</a>
                </nav>

                <nav class="footer-col" aria-label="Help">
                    <div class="footer-col-title" data-i18n="footer.colHelp">المساعدة</div>
                    <a href="{{ route('login') }}" data-i18n="nav.login">تسجيل الدخول</a>
                    <a href="#faq" data-i18n="nav.faq">الأسئلة الشائعة</a>
                    <a href="#contact" data-i18n="nav.contact">تواصل معنا</a>
                    <span class="footer-note">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                        <span data-i18n="footer.noSignup">الحسابات تُنشأ من الإدارة</span>
                    </span>
                </nav>
            </div>

            <div class="footer-bar">
                <span data-i18n="footer.rights">جميع الحقوق محفوظة — تخرُّج ©</span>
                <div class="footer-meta">
                    <a href="#hero" class="footer-up">
                        <span data-i18n="footer.top">العودة للأعلى</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- عنصر طباعي لا نص يُقرأ --}}
        <div class="footer-wordmark" aria-hidden="true">تخرُّج</div>
    </footer>

    <a href="#hero" class="back-top" aria-label="Back to top">
        {{-- حلقة تقدّم التمرير: --p يحدّثه premium.js --}}
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </a>

    <script src="{{ asset('assets/js/premium.js') }}?v={{ filemtime(public_path('assets/js/premium.js')) }}"></script>
</body>

</html>
