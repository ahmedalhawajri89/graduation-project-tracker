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

    {{-- ======= Hero ======= --}}
    <section id="hero" class="hero">
        {{-- خلفية مرسومة لا صورة: وهج بلون الهوية خلف العنوان، ونقاط تتلاشى نحو
             الحواف، وضوء خافت يتبع المؤشّر — حادّة بأيّ دقّة، ولا وزن لها على
             التحميل، ولا تنافس العنوان على التباين كما تفعل الصورة --}}
        <div class="hero-bg" aria-hidden="true">
            <span class="hero-glow"></span>
            <span class="hero-dots"></span>
        </div>

        {{-- شبكة الأعمدة الظاهرة — هي خلفية الهيرو --}}
        <div class="hero-rules" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>

        <div class="container">
            <div class="hero-grid">
                <div class="hero-copy">
                    <span class="badge stagger d1">
                        <span class="dot"></span>
                        <span data-i18n="hero.badge">منصة إدارة مشاريع التخرج للجامعات</span>
                    </span>

                    <h1 class="stagger d2" data-i18n="hero.title" data-i18n-html>
                        <span class="ink-line"><span>تتبّع مشروع تخرجك</span></span>
                        <span class="ink-line"><span>من الفكرة <span class="text-gradient">إلى المناقشة</span></span></span>
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

                    {{-- أرقام حقيقية من قاعدة البيانات — شريط أفقي تحت الأزرار
                         (كانت عموداً جانبياً، والعمود صار للقطة المنتج) --}}
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

                {{-- بنتو حيّ: خمس بلاطات، كلٌّ ويدجت مبنيّة بالكود لا صورة — حادّة بأيّ
                     دقّة وتُترجم. تحكي قصّة واحدة في حلقة: الفريق يتناقش ويُسلّم، والمشرف
                     يعتمد فيرتفع الإنجاز، والأدوار تتوزّع، والدرجة تُرصد. المرور يوقفها،
                     ولمن أوقف الحركة تظهر الحالة الأخيرة ثابتة. انظر initBento في premium.js --}}
                <div class="hero-visual">
                    <div class="bento" data-bento aria-label="لمحة من المنصة" data-i18n-aria="bento.aria">

                        {{-- ١) الإنجاز: حلقة ترتفع حين تُعتمد المرحلة --}}
                        <article class="bento-tile is-progress">
                            <header class="bento-head">
                                <span class="bento-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v3M12 18v3M3 12h3M18 12h3"/><circle cx="12" cy="12" r="5"/></svg></span>
                                <span data-i18n="bento.progress">نسبة الإنجاز</span>
                            </header>
                            <div class="bento-ring" data-ring style="--p: 38">
                                <svg viewBox="0 0 120 120" aria-hidden="true">
                                    <circle cx="60" cy="60" r="50" />
                                    <circle cx="60" cy="60" r="50" pathLength="100" />
                                </svg>
                                <b><span data-ring-num>38</span>%</b>
                            </div>
                            <div class="bento-project">
                                <b data-i18n="bento.project">كشف الرسائل الاحتيالية</b>
                                <small><span data-ring-done>2</span> <span data-i18n="bento.of">من 5 مراحل</span></small>
                            </div>
                        </article>

                        {{-- ٢) المرحلة: مفتوحة ← سُلّمت ← اعتُمدت --}}
                        <article class="bento-tile is-stage">
                            <header class="bento-head">
                                <span class="bento-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 21V4h11l-2 4 2 4H5"/></svg></span>
                                <span data-i18n="bento.stage">المرحلة الحالية</span>
                            </header>
                            <b class="bento-stage-title" data-i18n="bento.stageTitle">الفصل الثاني — الدراسات السابقة</b>
                            <ol class="bento-steps" data-steps>
                                <li class="is-on"><i></i><span data-i18n="bento.s1">مفتوحة</span></li>
                                <li><i></i><span data-i18n="bento.s2">سُلّمت</span></li>
                                <li><i></i><span data-i18n="bento.s3">اعتُمدت</span></li>
                            </ol>
                        </article>

                        {{-- ٣) نقاش الفريق: رسالة تُكتب حرفاً حرفاً --}}
                        <article class="bento-tile is-chat">
                            <header class="bento-head">
                                <span class="bento-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
                                <span data-i18n="bento.chat">نقاش الفريق</span>
                                <span class="bento-lock" data-i18n="bento.private">خاص</span>
                            </header>
                            <div class="bento-msg">
                                <span class="bento-avatar">دع</span>
                                <p class="bento-bubble"><span data-typed></span><i class="bento-caret" aria-hidden="true"></i></p>
                            </div>
                            {{-- مصدر النصّ المكتوب — يُترجم كغيره، ويقرؤه السكربت في كل دورة --}}
                            <span class="bento-src" data-type-src data-i18n="bento.msg" hidden>@آية راجعي الفصل الثاني قبل الخميس</span>
                            <span class="bento-src" data-type-at data-i18n="bento.at" hidden>@آية</span>
                        </article>

                        {{-- ٤) الأدوار: تتوزّع على الفريق --}}
                        <article class="bento-tile is-roles">
                            <header class="bento-head">
                                <span class="bento-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M21.5 20a6.5 6.5 0 0 0-4-6"/></svg></span>
                                <span data-i18n="bento.roles">أدوار الفريق</span>
                            </header>
                            <ul class="bento-roles" data-roles>
                                <li style="--h: 217"><span class="bento-avatar">دع</span><em data-i18n="bento.r1">واجهات</em></li>
                                <li style="--h: 262"><span class="bento-avatar">حس</span><em data-i18n="bento.r2">الخادم</em></li>
                                <li style="--h: 160"><span class="bento-avatar">آي</span><em data-i18n="bento.r3">التوثيق</em></li>
                            </ul>
                        </article>

                        {{-- ٥) الدرجة: تُرصد في النهاية --}}
                        <article class="bento-tile is-grade">
                            <header class="bento-head">
                                <span class="bento-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="9" r="6"/><path d="m8.5 14.5-1.5 7 5-3 5 3-1.5-7"/></svg></span>
                                <span data-i18n="bento.grade">التقييم النهائي</span>
                            </header>
                            <div class="bento-grade">
                                <b data-grade>—</b><small>/100</small>
                            </div>
                            {{-- قبل الرصد «لم تُرصد بعد» لا «0»، فلا تبدو البطاقة معطّلة --}}
                            <span class="bento-grade-label is-pending" data-i18n="bento.pending">لم تُرصد بعد</span>
                            <span class="bento-grade-label is-final" data-i18n="bento.excellent">ممتاز</span>
                        </article>
                    </div>
                </div>
            </div>

            {{-- مسار المشروع الحقيقي كما تعرّفه config/statuses.php --}}
            <div class="stage">
                <div class="stage-head">
                    <span class="stage-label" data-i18n="hero.stageLabel">مسار المشروع في المنصة</span>
                    <span class="stage-project" data-i18n="hero.stageProject">من التقديم إلى الدرجة النهائية</span>
                </div>

                <div class="rail">
                    {{-- المسار يمتد بين مركزَي العقدتين الأولى والأخيرة، فطوله ٧٥٪ من
                         الشريط. العقدة الثالثة عند ٦٢٫٥٪ منه = ٦٦٫٦٪ من المسار --}}
                    <span class="rail-line" style="--progress: 66.67%" aria-hidden="true"></span>
                    <ol class="rail-steps">
                        <li class="step-node done">
                            <span class="node" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="step-name" data-i18n="hero.step1">تقديم الطلب</span>
                            <span class="step-meta" data-i18n="hero.step1m">الطالب وفريقه</span>
                        </li>
                        <li class="step-node done">
                            <span class="node" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="step-name" data-i18n="hero.step2">موافقة المشرف</span>
                            <span class="step-meta" data-i18n="hero.step2m">قبول أو رفض مسبّب</span>
                        </li>
                        <li class="step-node current" aria-current="step">
                            <span class="node" aria-hidden="true"></span>
                            <span class="step-name" data-i18n="hero.step3">متابعة التنفيذ</span>
                            <span class="step-meta" data-i18n="hero.step3m">مراحل وملفات ونقاش</span>
                        </li>
                        <li class="step-node">
                            <span class="node" aria-hidden="true"></span>
                            <span class="step-name" data-i18n="hero.step4">المناقشة والتقييم</span>
                            <span class="step-meta" data-i18n="hero.step4m">درجة نهائية موثّقة</span>
                        </li>
                    </ol>
                </div>
            </div>
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

                <div class="svc-grid reveal">
                    {{-- ١) الفريق والمشرف — كبيرة --}}
                    <article class="svc-tile is-wide">
                        <div class="svc-copy">
                            <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                            <h3 data-i18n="svc.1.title">فريقك ومشرفك في دقائق</h3>
                            <p data-i18n="svc.1.text">اختر زملاءك من المتاحين في تخصصك، وشاهد المقاعد المتبقية لكل مشرف — وتنبّهك المنصة إن كانت فكرتك نُفّذت من قبل.</p>
                        </div>
                        <div class="svc-scene" aria-hidden="true">
                            <div class="scene-team">
                                <span class="scene-avatars"><i>دع</i><i>حس</i><i>آي</i><i class="is-add">+</i></span>
                                <span class="scene-chip"><b>2</b> <span data-i18n="svc.1.seats">مقاعد متبقية لدى المشرف</span></span>
                            </div>
                            <div class="scene-alert">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                                <span data-i18n="svc.1.similar">فكرة مشابهة نُفّذت في 2023 — راجعها قبل التقديم</span>
                            </div>
                        </div>
                    </article>

                    {{-- ٢) التسليم والمراجعة — كبيرة --}}
                    <article class="svc-tile is-wide">
                        <div class="svc-copy">
                            <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5M12 3v12"/></svg></span>
                            <h3 data-i18n="svc.2.title">سلّم مرحلتك، واستلم ملاحظة لا رفضاً</h3>
                            <p data-i18n="svc.2.text">تسلّم كل مرحلة بملف وملاحظة، ويعتمدها مشرفك أو يطلب تعديلاً بسببه الواضح — وكل جولة محفوظة.</p>
                        </div>
                        <div class="svc-scene" aria-hidden="true">
                            <ol class="scene-steps">
                                <li class="is-done"><i></i><span data-i18n="svc.2.s1">سُلّمت</span></li>
                                <li class="is-warn"><i></i><span data-i18n="svc.2.s2">مطلوب تعديل</span></li>
                                <li class="is-ok"><i></i><span data-i18n="svc.2.s3">اعتُمدت</span></li>
                            </ol>
                            <div class="scene-note">
                                <b data-i18n="svc.2.noteT">ملاحظة المشرف</b>
                                <span data-i18n="svc.2.note">ينقص مخطط الكيانات والعلاقات في الفصل الثالث.</span>
                            </div>
                        </div>
                    </article>

                    {{-- ٣–٦) أدوات يومية --}}
                    <article class="svc-tile">
                        <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6h11M9 12h11M9 18h11"/><path d="m3 6 1 1 2-2M3 12l1 1 2-2M3 18l1 1 2-2"/></svg></span>
                        <h3 data-i18n="svc.3.title">خطة مراحل بقوالبها</h3>
                        <p data-i18n="svc.3.text">يضعها المشرف مرّة بمواعيدها وقوالبها، فتصل كل مجموعاته.</p>
                    </article>
                    <article class="svc-tile">
                        <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M9 10h.01M13 10h.01"/></svg></span>
                        <h3 data-i18n="svc.4.title">نقاش خاص بالفريق</h3>
                        <p data-i18n="svc.4.text">قناة لا يراها المشرف، و@ لتنبيه زميل بعينه.</p>
                    </article>
                    <article class="svc-tile">
                        <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M9 14h6M9 17h3"/></svg></span>
                        <h3 data-i18n="svc.5.title">ملاحظات على الملفات</h3>
                        <p data-i18n="svc.5.text">«صفحة 3 ينقصها المرجع» — على الملف نفسه، حتى تُعالَج.</p>
                    </article>
                    <article class="svc-tile">
                        <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M15 9h3M15 13h3M6 16c.6-1.5 1.7-2 3-2s2.4.5 3 2"/></svg></span>
                        <h3 data-i18n="svc.6.title">توزيع الأدوار</h3>
                        <p data-i18n="svc.6.text">مَن على الواجهات ومَن على الخادم — يراه الفريق والمشرف.</p>
                    </article>

                    {{-- ٧–٨) --}}
                    <article class="svc-tile is-half">
                        <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg></span>
                        <div>
                            <h3 data-i18n="svc.7.title">مستكشف المشاريع السابقة</h3>
                            <p data-i18n="svc.7.text">تصفّح مشاريع الدفعات السابقة بأنواعها ومشرفيها، واستلهم فكرتك.</p>
                        </div>
                    </article>
                    <article class="svc-tile is-half">
                        <span class="svc-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="6"/><path d="m8.5 14.5-1.5 7 5-3 5 3-1.5-7"/></svg></span>
                        <div>
                            <h3 data-i18n="svc.8.title">درجة معتمدة لا تتغيّر</h3>
                            <p data-i18n="svc.8.text">يرصد مشرفك الدرجة من 100 مع ملاحظاته، ويُحسب التقدير منها تلقائياً، وتُقفل بعد اعتمادها.</p>
                        </div>
                    </article>
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

                {{-- لوحتان: ضمانات الثقة (داكنة) وراحة كل يوم (فاتحة). كانت ست بطاقات متطابقة
                     تخلط ما يُعتمد عليه عند الاعتراض بما يريح في الاستعمال، وكل ميزة ادّعاء
                     نصّي — الآن لكلٍّ دليل مرسوم من شكلها الحقيقي في المنصة --}}
                <div class="fx reveal">
                    <section class="fx-panel is-trust" aria-labelledby="fx-trust-title">
                        <header class="fx-panel-head">
                            <span class="fx-kicker" data-i18n="features.trust.label">ضمانات الثقة</span>
                            <h3 id="fx-trust-title" class="fx-panel-title" data-i18n="features.trust.text">ما يُعتمد عليه عند الاعتراض والتدقيق</h3>
                        </header>
                        <ul class="fx-list">
                            <li class="fx-item">
                                <div class="fx-text">
                                    <span class="fx-head"><span class="fx-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span><span class="cell-index">01</span></span>
                                    <h3 data-i18n="features.1.title">خصوصية الفريق</h3>
                                    <p data-i18n="features.1.text">نقاش الفريق الداخلي لا يراه المشرف ولا الإدارة — يكتب الطلاب بحرّية بدل الهروب إلى واتساب.</p>
                                </div>
                                <div class="fx-proof fx-chat" aria-hidden="true">
                                    <span class="fx-bubble"><i>نب</i><span data-i18n="features.p1.msg">ننهي فصل التحليل الليلة؟</span></span>
                                    <span class="fx-seen"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="features.p1.seen">مرئي للفريق فقط</span></span>
                                    <span class="fx-nope"><span><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg><span data-i18n="features.p1.sup">المشرف</span></span><span><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg><span data-i18n="features.p1.adm">الإدارة</span></span></span>
                                </div>
                            </li>
                            <li class="fx-item">
                                <div class="fx-text">
                                    <span class="fx-head"><span class="fx-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg></span><span class="cell-index">02</span></span>
                                    <h3 data-i18n="features.3.title">سجلّ لا يُعدَّل</h3>
                                    <p data-i18n="features.3.text">الدرجة تُقفل بعد اعتمادها، وكل قرار مهم يُحفظ في سجلّ تدقيق — مرجع واضح عند أيّ اعتراض.</p>
                                </div>
                                <div class="fx-proof fx-log" aria-hidden="true">
                                    <span class="fx-log-row"><i class="fx-dot-amber"></i><b data-i18n="features.p3.l1">اعتماد درجة</b><small dir="ltr">14:32</small></span>
                                    <span class="fx-log-row"><i class="fx-dot-rose"></i><b data-i18n="features.p3.l2">فكّ اعتماد — بسبب مكتوب</b><small dir="ltr">09:05</small></span>
                                    <span class="fx-seen"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="features.p3.lock">لا تعديل ولا حذف</span></span>
                                </div>
                            </li>
                            <li class="fx-item">
                                <div class="fx-text">
                                    <span class="fx-head"><span class="fx-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h7"/><path d="M8 9h8M8 13h5"/><circle cx="18" cy="18" r="3"/><path d="m20.2 20.2 1.8 1.8"/></svg></span><span class="cell-index">03</span></span>
                                    <h3 data-i18n="features.4.title">ملفات محمية</h3>
                                    <p data-i18n="features.4.text">ملفات المشروع والتسليمات على قرص خاص، لا يُنزلها إلا الفريق ومشرفه والإدارة.</p>
                                </div>
                                <div class="fx-proof fx-file" aria-hidden="true">
                                    <span class="fx-file-row"><i>PDF</i><span dir="ltr">final-report.pdf</span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg></span>
                                    <span class="fx-seen"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="features.p4.who">تنزيل للفريق ومشرفه والإدارة فقط</span></span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <section class="fx-panel is-daily" aria-labelledby="fx-daily-title">
                        <header class="fx-panel-head">
                            <span class="fx-kicker" data-i18n="features.daily.label">راحة كل يوم</span>
                            <h3 id="fx-daily-title" class="fx-panel-title" data-i18n="features.daily.text">ما يجعل العمل اليومي أخفّ</h3>
                        </header>
                        <ul class="fx-list">
                            <li class="fx-item">
                                <div class="fx-text">
                                    <span class="fx-head"><span class="fx-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/></svg></span><span class="cell-index">04</span></span>
                                    <h3 data-i18n="features.2.title">إشعارات بلا ضجيج</h3>
                                    <p data-i18n="features.2.text">يصلك التنبيه حين يخصّك الأمر: طلب تعديل، أو ذكرك زميل، أو اعتُمدت مرحلة — لا مع كل رسالة.</p>
                                </div>
                                <div class="fx-proof fx-notes" aria-hidden="true">
                                    <span class="fx-note is-on"><i class="is-warn"></i><span data-i18n="features.p2.a">مطلوب تعديل في «الفصل الثالث»</span></span>
                                    <span class="fx-note is-on"><i class="is-brand"></i><span data-i18n="features.p2.b">ذكرك زميل في نقاش الفريق</span></span>
                                    <span class="fx-note is-off"><i></i><span data-i18n="features.p2.c">رسالة عادية في النقاش</span><small data-i18n="features.p2.muted">بلا تنبيه</small></span>
                                </div>
                            </li>
                            <li class="fx-item">
                                <div class="fx-text">
                                    <span class="fx-head"><span class="fx-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg></span><span class="cell-index">05</span></span>
                                    <h3 data-i18n="features.5.title">عربية أولاً</h3>
                                    <p data-i18n="features.5.text">واجهة عربية كاملة من اليمين إلى اليسار، بخطوط مصمّمة للقراءة، والإنجليزية بنقرة.</p>
                                </div>
                                {{-- المفتاح يقلب الجملة واتجاهها حيّاً — كما يفعل مبدّل اللغة في المنصة --}}
                                <div class="fx-proof fx-lang" data-fx-lang>
                                    <button type="button" class="fx-lang-toggle" aria-pressed="false" data-i18n-aria="features.p5.btn" aria-label="تبديل اللغة في المثال">
                                        <span>ع</span><span>EN</span>
                                    </button>
                                    <span class="fx-lang-line" dir="rtl" data-ar="مرحباً بك في مشروعك" data-en="Welcome to your project">مرحباً بك في مشروعك</span>
                                </div>
                            </li>
                            <li class="fx-item">
                                <div class="fx-text">
                                    <span class="fx-head"><span class="fx-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg></span><span class="cell-index">06</span></span>
                                    <h3 data-i18n="features.6.title">على الجوال كما الحاسوب</h3>
                                    <p data-i18n="features.6.text">سلّم مرحلة أو ردّ على مشرفك من هاتفك — كل صفحة مصمّمة للشاشة الصغيرة.</p>
                                </div>
                                <div class="fx-proof fx-phone" aria-hidden="true">
                                    <span class="fx-phone-frame">
                                        <span class="fx-phone-notch"></span>
                                        <small data-i18n="features.p6.stage">الفصل الثالث</small>
                                        <span class="fx-phone-bar"><i></i></span>
                                        <span class="fx-phone-btn" data-i18n="features.p6.btn">تسليم المرحلة</span>
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </section>
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

                <ol class="journey reveal">
                    <li class="journey-step">
                        <span class="journey-node"><span class="cell-index">01</span></span>
                        <h3 data-i18n="how.1.title">ادخل إلى المنصة</h3>
                        <p data-i18n="how.1.text">ببريدك أو رقمك الجامعي — حسابك جاهز من إدارة القسم.</p>
                    </li>
                    <li class="journey-step">
                        <span class="journey-node"><span class="cell-index">02</span></span>
                        <h3 data-i18n="how.2.title">كوّن فريقك وقدّم فكرتك</h3>
                        <p data-i18n="how.2.text">اختر زملاءك ومشرفاً لديه مقاعد، واكتب فكرتك بعد أن تتأكد أنها لم تُنفَّذ.</p>
                    </li>
                    <li class="journey-step">
                        <span class="journey-node"><span class="cell-index">03</span></span>
                        <h3 data-i18n="how.3.title">سلّم مراحلك</h3>
                        <p data-i18n="how.3.text">مرحلة بعد مرحلة: تسليم، فاعتماد أو تعديل بملاحظة، ونقاش مع فريقك ومشرفك.</p>
                    </li>
                    <li class="journey-step is-accent">
                        <span class="journey-node"><span class="cell-index">04</span></span>
                        <h3 data-i18n="how.4.title">ناقش واستلم درجتك</h3>
                        <p data-i18n="how.4.text">بعد المناقشة يرصد مشرفك درجتك مع ملاحظاته، وتُعتمد فلا تتغيّر.</p>
                    </li>
                </ol>
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

                {{-- مستكشف الأدوار: تبويب لكل دور، ولوحة بمشهد من شاشته الحقيقية وما يفعله
                     وما لا يراه، وتحتها خطّ يربط الأدوار بما ينتقل بينها. كانت ثلاث بطاقات
                     متطابقة بثلاثة أسطر لكلٍّ — قائمة ميزات لا تجربة مختلفة لكل دور.
                     بلا JavaScript تظهر اللوحات الثلاث متتالية (premium.js › initRoles) --}}
                <div class="rx reveal" data-role-explorer>
                    <div class="rx-tabs" role="tablist" aria-label="أدوار المنصة" data-i18n-aria="roles.kicker">
                        <button type="button" role="tab" class="rx-tab" id="rx-tab-student" aria-controls="rx-panel-student"
                            aria-selected="true" data-role="student">
                            <span class="rx-tab-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></span>
                            <span class="rx-tab-text">
                                <b data-i18n="roles.student.name">الطالب</b>
                                <small data-i18n="roles.student.role">الفريق والتسليم والنقاش</small>
                            </span>
                        </button>
                        <button type="button" role="tab" class="rx-tab" id="rx-tab-supervisor" aria-controls="rx-panel-supervisor"
                            aria-selected="false" data-role="supervisor">
                            <span class="rx-tab-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg></span>
                            <span class="rx-tab-text">
                                <b data-i18n="roles.supervisor.name">المشرف</b>
                                <small data-i18n="roles.supervisor.role">التخطيط والمراجعة والتقييم</small>
                            </span>
                        </button>
                        <button type="button" role="tab" class="rx-tab" id="rx-tab-admin" aria-controls="rx-panel-admin"
                            aria-selected="false" data-role="admin">
                            <span class="rx-tab-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="21" y1="4" x2="14" y2="4"/><line x1="10" y1="4" x2="3" y2="4"/><line x1="21" y1="12" x2="12" y2="12"/><line x1="8" y1="12" x2="3" y2="12"/><line x1="21" y1="20" x2="16" y2="20"/><line x1="12" y1="20" x2="3" y2="20"/><line x1="14" y1="2" x2="14" y2="6"/><line x1="8" y1="10" x2="8" y2="14"/><line x1="16" y1="18" x2="16" y2="22"/></svg></span>
                            <span class="rx-tab-text">
                                <b data-i18n="roles.admin.name">الإدارة</b>
                                <small data-i18n="roles.admin.role">ضبط النظام وتنظيم الفصل</small>
                            </span>
                        </button>
                    </div>

                    <section class="rx-panel" id="rx-panel-student" role="tabpanel" aria-labelledby="rx-tab-student" data-panel="student">
                        <div class="rx-scene" aria-hidden="true">
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
                                <span class="rx-progress" aria-hidden="true"><i style="width: 60%"></i></span>
                                <div class="rx-avatars" aria-hidden="true"><i>سا</i><i>نب</i><i>يو</i></div>
                            </div>
                        </div>
                        <div class="rx-info">
                            <h3 class="rx-title"><span data-i18n="roles.student.name">الطالب</span></h3>
                            <ul class="rx-caps">
                                <li>
                                    <b data-i18n="roles.student.c1.t">تكوين الفريق</b>
                                    <span data-i18n="roles.student.c1.d">زملاؤك من تخصصك، ومشرف لديه مقاعد متاحة</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.student.c2.t">تسليم المراحل</b>
                                    <span data-i18n="roles.student.c2.d">ملف وملاحظة لكل مرحلة، وإعادة بعد التعديل</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.student.c3.t">نقاش الفريق</b>
                                    <span data-i18n="roles.student.c3.d">قناة خاصة بالفريق، و@ لتنبيه زميل بعينه</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.student.c4.t">الأدوار والتذكير</b>
                                    <span data-i18n="roles.student.c4.d">يوزّع القائد المسؤوليات، ويصل تذكير قبل كل موعد</span>
                                </li>
                            </ul>
                            <p class="rx-private"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="roles.student.private">نقاش الفريق الخاص لا يصل المشرف ولا الإدارة</span></p>
                        </div>
                    </section>

                    <section class="rx-panel" id="rx-panel-supervisor" role="tabpanel" aria-labelledby="rx-tab-supervisor" data-panel="supervisor">
                        <div class="rx-scene" aria-hidden="true">
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
                        </div>
                        <div class="rx-info">
                            <h3 class="rx-title"><span data-i18n="roles.supervisor.name">المشرف</span></h3>
                            <ul class="rx-caps">
                                <li>
                                    <b data-i18n="roles.supervisor.c1.t">قبول الطلبات</b>
                                    <span data-i18n="roles.supervisor.c1.d">حسب مقاعده، مع تنبيه للفكرة المشابهة</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.supervisor.c2.t">خطة المراحل</b>
                                    <span data-i18n="roles.supervisor.c2.d">مواعيد وقوالب تصل كل مجموعاته مرّة واحدة</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.supervisor.c3.t">المراجعة</b>
                                    <span data-i18n="roles.supervisor.c3.d">اعتماد، أو «مطلوب تعديل» بسبب مكتوب</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.supervisor.c4.t">الدرجة</b>
                                    <span data-i18n="roles.supervisor.c4.d">رصد بالتقدير والملاحظات، ثم اعتماد يقفلها</span>
                                </li>
                            </ul>
                            <p class="rx-private"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="roles.supervisor.private">يرى مجموعاته وحدها — ونقاش الفريق الخاص يبقى للفريق</span></p>
                        </div>
                    </section>

                    <section class="rx-panel" id="rx-panel-admin" role="tabpanel" aria-labelledby="rx-tab-admin" data-panel="admin">
                        <div class="rx-scene" aria-hidden="true">
                            <div class="rx-card">
                                <span class="rx-card-label" data-i18n="roles.scene.a.health">متابعة الفرق</span>
                                <div class="rx-health">
                                    <span class="is-alert"><b>3</b><small data-i18n="roles.scene.a.late">مرحلة فات موعدها</small></span>
                                    <span><b>0</b><small data-i18n="roles.scene.a.review">تسليم ينتظر المشرف</small></span>
                                    <span class="is-alert"><b>2</b><small data-i18n="roles.scene.a.idle">فريق متوقّف</small></span>
                                    <span><b>0</b><small data-i18n="roles.scene.a.roles">فريق بلا أدوار</small></span>
                                </div>
                            </div>
                        </div>
                        <div class="rx-info">
                            <h3 class="rx-title"><span data-i18n="roles.admin.name">الإدارة</span></h3>
                            <ul class="rx-caps">
                                <li>
                                    <b data-i18n="roles.admin.c1.t">إعداد الفصل</b>
                                    <span data-i18n="roles.admin.c1.d">التخصصات وأنواع المشاريع وحدود الفرق والفصول</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.admin.c2.t">الحسابات</b>
                                    <span data-i18n="roles.admin.c2.d">استيراد الطلاب والمشرفين من Excel دفعة واحدة</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.admin.c3.t">متابعة الفرق</b>
                                    <span data-i18n="roles.admin.c3.d">المتأخّر والمتوقّف وما ينتظر المشرف، بنقرة</span>
                                </li>
                                <li>
                                    <b data-i18n="roles.admin.c4.t">سجلّ التدقيق</b>
                                    <span data-i18n="roles.admin.c4.d">كل قرار مسجّل، وفتح الدرجة المعتمدة بسبب مكتوب</span>
                                </li>
                            </ul>
                            <p class="rx-private"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="roles.admin.private">ترى كل شيء إلا نقاش الفرق الخاص — وكل قرار لها في السجلّ</span></p>
                        </div>
                    </section>

                    {{-- كيف تتصل الأدوار: ما ينتقل من كل دور إلى التالي — وزرّ الجولة التلقائية --}}
                    <div class="rx-foot">
                    <ol class="rx-flow" aria-label="كيف تتصل الأدوار" data-i18n-aria="roles.flow.label">
                        <li class="rx-node" data-node="student">
                            <span class="rx-node-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></span>
                            <b data-i18n="roles.student.name">الطالب</b>
                        </li>
                        <li class="rx-edge" data-edge="student"><span data-i18n="roles.flow.1">يسلّم المرحلة ويعدّل</span></li>
                        <li class="rx-node" data-node="supervisor">
                            <span class="rx-node-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg></span>
                            <b data-i18n="roles.supervisor.name">المشرف</b>
                        </li>
                        <li class="rx-edge" data-edge="supervisor"><span data-i18n="roles.flow.2">يعتمد ويرصد الدرجة</span></li>
                        <li class="rx-node" data-node="admin">
                            <span class="rx-node-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="21" y1="4" x2="14" y2="4"/><line x1="10" y1="4" x2="3" y2="4"/><line x1="21" y1="12" x2="12" y2="12"/><line x1="8" y1="12" x2="3" y2="12"/><line x1="21" y1="20" x2="16" y2="20"/><line x1="12" y1="20" x2="3" y2="20"/><line x1="14" y1="2" x2="14" y2="6"/><line x1="8" y1="10" x2="8" y2="14"/><line x1="16" y1="18" x2="16" y2="22"/></svg></span>
                            <b data-i18n="roles.admin.name">الإدارة</b>
                            <small data-i18n="roles.flow.3">تتابع الفصل كلّه</small>
                        </li>
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
            $showcase = \Illuminate\Support\Facades\Cache::remember('site_showcase_v4', 3600, function () {
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
                    ->take(4)
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

                    {{-- أرقام إجمالية حقيقية — المتوسط العام لا درجة فريق بعينه --}}
                    <div class="sc-stats reveal">
                        <div class="sc-stat"><b>{{ $showStats['done'] }}</b><span data-i18n="show.stat.done">مشروعاً مكتملاً</span></div>
                        @if ($showStats['avg'])
                            <div class="sc-stat"><b>{{ $showStats['avg'] }}</b><span data-i18n="show.stat.avg">متوسط الدرجات</span></div>
                        @endif
                        <div class="sc-stat"><b>{{ $showStats['specs'] }}</b><span data-i18n="show.stat.specs">تخصصات</span></div>
                        @if ($showStats['stages'])
                            <div class="sc-stat"><b>{{ $showStats['stages'] }}</b><span data-i18n="show.stat.stages">مراحل معتمدة لكل مشروع</span></div>
                        @endif
                    </div>

                    <div class="sc-grid is-{{ $showProjects->count() }} reveal d1">
                        @foreach ($showProjects as $project)
                            @php
                                $sem = $project->semester?->parts();
                                $weeks = $project->first_due && $project->last_due
                                    ? max(1, (int) ceil(\Illuminate\Support\Carbon::parse($project->first_due)->diffInDays(\Illuminate\Support\Carbon::parse($project->last_due)) / 7))
                                    : null;
                            @endphp
                            <article class="sc-card {{ $loop->first ? 'is-featured' : '' }}">
                                <div class="sc-top">
                                    <span class="sc-type" dir="auto">{{ $project->project_type->name }}</span>
                                    <span class="sc-done">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                        <span data-i18n="state.done">مكتمل</span>
                                    </span>
                                </div>

                                <h3 class="sc-title" dir="auto">{{ $project->bare_title }}</h3>
                                @if ($project->description)
                                    <p class="sc-desc" dir="auto">{{ \Illuminate\Support\Str::limit($project->description, $loop->first ? 170 : 110) }}</p>
                                @endif

                                @if ($loop->first && $project->relationLoaded('milestones') && $project->milestones->count())
                                    <ol class="sc-path">
                                        @foreach ($project->milestones->take(6) as $stage)
                                            <li>
                                                <span class="sc-path-node" aria-hidden="true">
                                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                                </span>
                                                <span class="sc-path-title" dir="auto">{{ $stage->title }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                @endif

                                {{-- ما مرّ به على المنصة: مراحله المعتمدة ومدّتها --}}
                                @if ($project->stages_done)
                                    <div class="sc-journey">
                                        <span class="sc-steps" aria-hidden="true">
                                            @for ($i = 0; $i < min($project->stages_done, 8); $i++)
                                                <i></i>
                                            @endfor
                                        </span>
                                        <span class="sc-journey-text">
                                            <b>{{ $project->stages_done }}</b> <span data-i18n="show.stages">مراحل معتمدة</span>
                                            @if ($weeks)
                                                · <b>{{ $weeks }}</b> <span data-i18n="show.weeks">أسابيع</span>
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                <div class="sc-meta">
                                    <span class="sc-team" title="{{ $project->group_count }}">
                                        <span class="sc-dots" aria-hidden="true">
                                            @for ($i = 0; $i < min($project->group_count, 5); $i++)
                                                <i></i>
                                            @endfor
                                        </span>
                                        {{ $project->group_count }} <span data-i18n="unit.members">أعضاء</span>
                                    </span>
                                    @if ($sem)
                                        <span>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                            {{ $sem['term'] }}
                                            {{-- السنة معزولة LTR: داخل نصّ عربي تنقلب «2021–22» إلى «22–2021» --}}
                                            @if ($sem['year'])<span class="sc-sep">·</span><bdi dir="ltr">&#x2066;{{ $sem['year'] }}&#x2069;</bdi>@endif
                                        </span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
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

                <div class="lifecycle is-vs reveal">
                    <div class="vs-head" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span data-i18n="lc.before">قبل</span>
                        <span data-i18n="lc.after">مع تخرُّج</span>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">01</span>
                        <h3 class="lc-title" data-i18n="lc.1.title">التنسيق</h3>
                        <p class="vs-before"><span class="vs-mark" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></span><span data-i18n="lc.1.before">مجموعة واتساب يقرؤها الجميع وتضيع فيها المهام</span></p>
                        <p class="vs-after"><span class="vs-mark" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="lc.1.after">نقاش فريق خاص، و@ لتنبيه زميل بعينه</span></p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">02</span>
                        <h3 class="lc-title" data-i18n="lc.2.title">الملفات</h3>
                        <p class="vs-before"><span class="vs-mark" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></span><span data-i18n="lc.2.before">نسخ متضاربة بين البريد والرسائل</span></p>
                        <p class="vs-after"><span class="vs-mark" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="lc.2.after">ملف واحد، وملاحظاته عليه حتى تُعالَج</span></p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">03</span>
                        <h3 class="lc-title" data-i18n="lc.3.title">المواعيد</h3>
                        <p class="vs-before"><span class="vs-mark" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></span><span data-i18n="lc.3.before">جدول يُرسل مرّة ثم يضيع بين الرسائل</span></p>
                        <p class="vs-after"><span class="vs-mark" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="lc.3.after">خطة مراحل بمواعيدها وقوالبها تصل كل مجموعة</span></p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">04</span>
                        <h3 class="lc-title" data-i18n="lc.4.title">الملاحظات</h3>
                        <p class="vs-before"><span class="vs-mark" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></span><span data-i18n="lc.4.before">«مرفوض» بلا سبب، وتخمين ما المطلوب</span></p>
                        <p class="vs-after"><span class="vs-mark" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="lc.4.after">«مطلوب تعديل» بسبب واضح، وكل جولة محفوظة</span></p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">05</span>
                        <h3 class="lc-title" data-i18n="lc.5.title">المسؤوليات</h3>
                        <p class="vs-before"><span class="vs-mark" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></span><span data-i18n="lc.5.before">لا أحد يعرف مَن فعل ماذا حتى المناقشة</span></p>
                        <p class="vs-after"><span class="vs-mark" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="lc.5.after">أدوار معلنة يراها الفريق والمشرف</span></p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">06</span>
                        <h3 class="lc-title" data-i18n="lc.6.title">الدرجة</h3>
                        <p class="vs-before"><span class="vs-mark" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg></span><span data-i18n="lc.6.before">كشف ورقي يتغيّر ولا أثر لمن غيّره</span></p>
                        <p class="vs-after"><span class="vs-mark" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="lc.6.after">درجة معتمدة مقفلة، وسجلّ لكل قرار</span></p>
                    </div>
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

                {{-- النقاط الست على دورة الفصل: قبله، في بدايته، وحتى إغلاقه --}}
                <ol class="dx-phases">
                        <li class="dx-phase reveal d1">
                            <div class="dx-phase-head">
                                <span class="dx-node">1</span>
                                <div>
                                    <small data-i18n="dept.p1.when">قبل الفصل</small>
                                    <h3 data-i18n="dept.p1.name">التجهيز</h3>
                                </div>
                            </div>
                            <div class="dx-item">
                                <span class="dx-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                                <div class="dx-text">
                                    <b data-i18n="dept.6.title">فصول دراسية تُفتح وتُغلق</b>
                                    <span data-i18n="dept.6.text">تفتح فصلاً جديداً للتقديم وتغلق السابق، فتبقى مشاريع كل دفعة في فصلها.</span>
                                    <div class="dx-proof" aria-hidden="true">
                                        <span class="dx-chip is-closed"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><span data-i18n="dept.pf.term2">الفصل الثاني</span> <bdi dir="ltr">&#x2066;2021–22&#x2069;</bdi></span>
                                        <span class="dx-chip is-open"><i></i><span data-i18n="dept.file.term">الفصل الأول</span> <bdi dir="ltr">&#x2066;2022–23&#x2069;</bdi></span>
                                    </div>
                                </div>
                            </div>
                            <div class="dx-item">
                                <span class="dx-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5M12 3v12"/></svg></span>
                                <div class="dx-text">
                                    <b data-i18n="dept.2.title">استيراد الطلاب والمشرفين من ملف Excel</b>
                                    <span data-i18n="dept.2.text">ترفع كشف الدفعة مرة واحدة فتُنشأ الحسابات كلها بلا إدخال يدوي.</span>
                                    <div class="dx-proof" aria-hidden="true">
                                        <span class="dx-file-chip"><i>XLS</i><bdi dir="ltr">students.xlsx</bdi></span>
                                        <span class="dx-arrow"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg></span>
                                        <span class="dx-count"><bdi dir="ltr">312</bdi> <span data-i18n="dept.pf.accounts">حساباً</span></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dx-phase reveal d2">
                            <div class="dx-phase-head">
                                <span class="dx-node">2</span>
                                <div>
                                    <small data-i18n="dept.p2.when">بداية الفصل</small>
                                    <h3 data-i18n="dept.p2.name">التوزيع</h3>
                                </div>
                            </div>
                            <div class="dx-item">
                                <span class="dx-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 2 10 5-10 5L2 7z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/></svg></span>
                                <div class="dx-text">
                                    <b data-i18n="dept.3.title">أنواع مشاريع بحدود فريق لكل تخصص</b>
                                    <span data-i18n="dept.3.text">تضبط لكل تخصص أنواع مشاريعه والحد الأدنى والأقصى لأعضاء الفريق.</span>
                                    <div class="dx-proof" aria-hidden="true">
                                        <span class="dx-type" data-i18n="dept.pf.type">مشروع برمجي</span>
                                        <span class="dx-seats"><i class="on"></i><i class="on"></i><i class="on"></i><i></i><i></i></span>
                                        <span class="dx-range"><bdi dir="ltr">3–5</bdi> <span data-i18n="dept.pf.members">أعضاء</span></span>
                                    </div>
                                </div>
                            </div>
                            <div class="dx-item">
                                <span class="dx-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
                                <div class="dx-text">
                                    <b data-i18n="dept.1.title">توزيع المشرفين بحدّ أقصى لكل واحد</b>
                                    <span data-i18n="dept.1.text">تحدد للمشرف عدد المجموعات التي يقبلها، والنظام يرفض ما زاد تلقائياً.</span>
                                    <div class="dx-proof" aria-hidden="true">
                                        <span class="dx-meter"><i style="width:80%"></i></span>
                                        <span class="dx-count"><bdi dir="ltr">4 / 5</bdi> <span data-i18n="dept.pf.groups">مجموعات</span></span>
                                        <span class="dx-reject"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg><span data-i18n="dept.pf.sixth">السادسة مرفوضة</span></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dx-phase reveal d3">
                            <div class="dx-phase-head">
                                <span class="dx-node">3</span>
                                <div>
                                    <small data-i18n="dept.p3.when">حتى نهايته</small>
                                    <h3 data-i18n="dept.p3.name">المتابعة والإغلاق</h3>
                                </div>
                            </div>
                            <div class="dx-item">
                                <span class="dx-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg></span>
                                <div class="dx-text">
                                    <b data-i18n="dept.5.title">سجلّ تدقيق لكل قرار</b>
                                    <span data-i18n="dept.5.text">كل اعتماد وفتح درجة وتغيير مهم يُحفظ باسم صاحبه ووقته.</span>
                                    <div class="dx-proof" aria-hidden="true">
                                        <span class="dx-log"><i></i><span data-i18n="dept.pf.audit">اعتماد فكرة مشروع</span><small><bdi dir="ltr">09:14</bdi></small><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg></span>
                                    </div>
                                </div>
                            </div>
                            <div class="dx-item">
                                <span class="dx-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/></svg></span>
                                <div class="dx-text">
                                    <b data-i18n="dept.4.title">تصدير كشف المجموعات إلى Excel</b>
                                    <span data-i18n="dept.4.text">تُخرج كشفاً بالمجموعات ومشرفيها ودرجاتها في أي لحظة من الفصل.</span>
                                    <div class="dx-proof" aria-hidden="true">
                                        <span class="dx-btn"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5M12 15V3"/></svg><span data-i18n="dept.pf.export">تنزيل الكشف</span><bdi dir="ltr">.xlsx</bdi></span>
                                    </div>
                                </div>
                            </div>
                        </li>
                </ol>
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
