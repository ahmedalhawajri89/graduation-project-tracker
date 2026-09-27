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

    <link href="{{ asset('assets/css/premium.css') }}" rel="stylesheet" />
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
                    <li><a href="#faq" data-i18n="nav.faq">الأسئلة</a></li>
                    <li><a href="#contact" data-i18n="nav.contact">اتصل بنا</a></li>
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
                        <span data-i18n="nav.login">تسجيل دخول</span>
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
            <a href="#faq" data-i18n="nav.faq">الأسئلة</a>
            <a href="#contact" data-i18n="nav.contact">اتصل بنا</a>
            @if (auth()->guard('admin')->check() || auth()->guard('supervisor')->check() || auth()->guard('student')->check())
                <a href="{{ route('login') }}" class="btn btn-primary" data-i18n="nav.dashboard">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary" data-i18n="nav.login">تسجيل دخول</a>
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
        {{-- شبكة الأعمدة الظاهرة — هي خلفية الهيرو --}}
        <div class="hero-rules" aria-hidden="true">
            <span></span><span></span><span></span><span></span><span></span><span></span>
        </div>

        <div class="container">
            <div class="hero-grid">
                <div class="hero-copy">
                    <span class="badge stagger d1">
                        <span class="dot"></span>
                        <span data-i18n="hero.badge">منصة ذكية لإدارة مشاريع التخرج</span>
                    </span>

                    <h1 class="stagger d2" data-i18n="hero.title" data-i18n-html>
                        <span class="ink-line"><span>تتبّع مشروع تخرجك</span></span>
                        <span class="ink-line"><span>من الفكرة <span class="text-gradient">إلى المناقشة</span></span></span>
                    </h1>

                    <p class="hero-sub stagger d3" data-i18n="hero.sub">
                        منصة تخرُّج لإدارة الفرق، اختيار المشرفين، ومتابعة مراحل المشروع
                        بتجربة عصرية وسلسة.
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
                </div>

                {{-- أرقام حقيقية من قاعدة البيانات --}}
                <div class="hero-figures stagger d4">
                    <span class="figures-label" data-i18n="hero.figuresLabel">المنصة اليوم</span>
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
                            <span class="step-name" data-i18n="hero.step4">التقييم والمناقشة</span>
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
                        <h2 class="reveal" data-i18n="about.title">منصة تخرُّج</h2>
                        <p class="reveal d1" data-i18n="about.text">
                            تخرُّج منصة مستقلة لإدارة مشاريع التخرج من أول تكوين الفريق واختيار المشرف، مروراً باعتماد
                            الفكرة ومتابعة المراحل، وصولاً إلى المناقشة والتقييم النهائي — كل ذلك في مكان واحد
                            وبسير عمل واضح لكل طرف.
                        </p>

                        <div class="pillars reveal d2">
                            <div class="pillar">
                                <span class="pillar-num">01</span>
                                <span class="pillar-text" data-i18n="about.p1">إدارة الفرق الطلابية والمشرفين</span>
                            </div>
                            <div class="pillar">
                                <span class="pillar-num">02</span>
                                <span class="pillar-text" data-i18n="about.p2">متابعة مراحل المشروع ونِسب الإنجاز</span>
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

                    {{-- لوح يعرض المنصة كما هي فعلاً: تخصصات بأعداد حقيقية --}}
                    <div class="about-panel reveal d1">
                        <div class="panel-head">
                            <span data-i18n="about.panelTitle">التخصصات على المنصة</span>
                            <span class="panel-dot" aria-hidden="true"></span>
                        </div>
                        @php
                            $panelSpecializes = \Illuminate\Support\Facades\Cache::remember('site_panel_specializes', 3600, function () {
                                return \App\Models\Specialize::withCount('students')->orderByDesc('students_count')->take(3)->get();
                            });
                            $panelMax = max(1, optional($panelSpecializes->first())->students_count ?? 1);
                        @endphp
                        @forelse ($panelSpecializes as $index => $specialize)
                            <div class="panel-row">
                                <span class="panel-row-name">{{ $specialize->name }}</span>
                                <span class="panel-row-meta">{{ $specialize->students_count }} <span data-i18n="unit.students">طالباً</span></span>
                                <span class="panel-bar" aria-hidden="true">
                                    <i @class(['accent' => $index === 0]) style="width: {{ round($specialize->students_count / $panelMax * 100) }}%"></i>
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
        </section>

        {{-- ======= Services ======= --}}
        <section id="services" class="section">
            <div class="container">
                <div class="section-head" data-num="02">
                    <div class="section-index reveal"><span data-i18n="services.kicker">الخدمات</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="services.title">كل ما يحتاجه مشروعك في مكان واحد</h2>
                        <p class="reveal d1" data-i18n="services.text">
                            نظام متكامل يدير كافة العمليات من تسجيل الدخول واختيار الفريق والمشرف، وصولاً إلى مناقشة المشروع وتقييمه.
                        </p>
                    </div>
                </div>

                <div class="cell-grid cols-3 reveal">
                    <article class="cell">
                        <span class="cell-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <h3 data-i18n="services.1.title">تكوين الفريق واختيار المشرف</h3>
                        <p data-i18n="services.1.text">اختر زملاءك من قائمة الطلاب المتاحين في تخصصك، وشاهد المقاعد المتبقية لكل مشرف قبل تقديم طلبك.</p>
                    </article>

                    <article class="cell">
                        <span class="cell-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/></svg>
                        </span>
                        <h3 data-i18n="services.2.title">تتبع مراحل بنسبة إنجاز</h3>
                        <p data-i18n="services.2.text">مراحل يحددها مشرفك مع نسبة إنجاز مباشرة وموعد نهائي بعدّاد أيام — تعرف أين تقف في كل لحظة.</p>
                    </article>

                    <article class="cell">
                        <span class="cell-icon" aria-hidden="true">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></svg>
                        </span>
                        <h3 data-i18n="services.3.title">نقاش وملفات في مكان واحد</h3>
                        <p data-i18n="services.3.text">نقاش مدمج مع مشرفك، ورفع ملفات المشروع بأمان، وإشعار فوري لكل تحديث — بلا مجموعات واتساب مبعثرة.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ======= Features ======= --}}
        <section id="features" class="section">
            <div class="container">
                <div class="section-head" data-num="03">
                    <div class="section-index reveal"><span data-i18n="features.kicker">المميزات</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="features.title">منصة صُممت لنجاح مشروعك</h2>
                        <p class="reveal d1" data-i18n="features.text">
                            يهدف النظام إلى إدارة كافة العمليات من مرحلة تسجيل الدخول واختيار الفريق والمشرف
                            وصولاً إلى مرحلة مناقشة المشروع وتقييمه.
                        </p>
                    </div>
                </div>

                <div class="cell-grid cols-2 reveal">
                    <article class="cell">
                        <div class="cell-index">01</div>
                        <h3 data-i18n="features.1.title">مراحل مشروع بنسبة إنجاز مباشرة</h3>
                        <p data-i18n="features.1.text">مشرفك يحدد مراحل مشروعك بتواريخ استحقاق، وأنت تتابع نسبة الإنجاز على خط زمني مرئي يميز المنجز والمتأخر تلقائياً.</p>
                    </article>
                    <article class="cell">
                        <div class="cell-index">02</div>
                        <h3 data-i18n="features.2.title">مستكشف المشاريع السابقة</h3>
                        <p data-i18n="features.2.text">تصفّح مشاريع الدفعات السابقة بأنواعها ومشرفيها — استلهم فكرتك وتأكد أنها غير منفّذة قبل التقديم.</p>
                    </article>
                    <article class="cell">
                        <div class="cell-index">03</div>
                        <h3 data-i18n="features.3.title">نقاش مدمج وإشعارات فورية</h3>
                        <p data-i18n="features.3.text">اسأل مشرفك وناقش فريقك داخل صفحة المشروع نفسها، واستلم إشعاراً لكل رد أو مرحلة تُنجز أو ملف يُرفع.</p>
                    </article>
                    <article class="cell">
                        <div class="cell-index">04</div>
                        <h3 data-i18n="features.4.title">تقييم إلكتروني بدرجة وتقدير</h3>
                        <p data-i18n="features.4.text">بعد اكتمال مشروعك يرصد المشرف درجتك النهائية مع التقدير وملاحظاته الختامية — وتصلك النتيجة بإشعار فوري.</p>
                    </article>
                </div>
            </div>
        </section>

        {{-- ======= How it works ======= --}}
        <section id="how" class="section">
            <div class="container">
                <div class="section-head" data-num="04">
                    <div class="section-index reveal"><span data-i18n="how.kicker">كيف يعمل النظام؟</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="how.title">أربع خطوات من الفكرة إلى الدرجة</h2>
                        <p class="reveal d1" data-i18n="how.text">
                            حسابك يُنشأ من إدارة المنصة — لا حاجة للتسجيل، فقط سجّل دخولك وابدأ.
                        </p>
                    </div>
                </div>

                <div class="cell-grid cols-4 reveal">
                    <div class="cell">
                        <div class="cell-index">01</div>
                        <h3 data-i18n="how.1.title">سجّل دخولك</h3>
                        <p data-i18n="how.1.text">ببريدك أو رقمك الجامعي — الحسابات جاهزة مسبقاً من إدارة المنصة.</p>
                    </div>
                    <div class="cell">
                        <div class="cell-index">02</div>
                        <h3 data-i18n="how.2.title">كوّن فريقك وقدّم فكرتك</h3>
                        <p data-i18n="how.2.text">اختر زملاءك من قائمة المتاحين، واختر مشرفاً لديه مقاعد، واكتب فكرة مشروعك.</p>
                    </div>
                    <div class="cell">
                        <div class="cell-index">03</div>
                        <h3 data-i18n="how.3.title">تابع وناقش وارفع</h3>
                        <p data-i18n="how.3.text">بعد موافقة المشرف: مراحل بنسبة إنجاز، نقاش مباشر، ملفات، وإشعار لكل جديد.</p>
                    </div>
                    <div class="cell is-accent">
                        <div class="cell-index">04</div>
                        <h3 data-i18n="how.4.title">ناقش واستلم تقييمك</h3>
                        <p data-i18n="how.4.text">بعد المناقشة يرصد مشرفك درجتك النهائية بالتقدير وملاحظاته — وتصلك فوراً.</p>
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
                            تخرُّج مبنية حول ثلاثة أدوار متكاملة، لكل منها لوحة تحكم وصلاحيات تناسب مهامه،
                            بحيث يعرف كل طرف ما عليه بالضبط في كل مرحلة.
                        </p>
                    </div>
                </div>

                <div class="cell-grid cols-3 reveal">
                    <article class="cell role-cell">
                        <div class="role-head">
                            <span class="role-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></span>
                            <h3 data-i18n="roles.student.name">الطالب</h3>
                            <p class="role-tag" data-i18n="roles.student.role">تكوين الفريق وتقديم الفكرة</p>
                        </div>
                        <ul class="role-points">
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.student.p1">اختيار زملاء الفريق من قائمة المتاحين</span></li>
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.student.p2">اختيار مشرف لديه مقاعد شاغرة</span></li>
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.student.p3">متابعة المراحل والتعليقات ورفع الملفات</span></li>
                        </ul>
                    </article>

                    <article class="cell role-cell">
                        <div class="role-head">
                            <span class="role-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg></span>
                            <h3 data-i18n="roles.supervisor.name">المشرف</h3>
                            <p class="role-tag" data-i18n="roles.supervisor.role">المتابعة والاعتماد والتقييم</p>
                        </div>
                        <ul class="role-points">
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.supervisor.p1">استعراض طلبات الفرق واعتماد الأفكار</span></li>
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.supervisor.p2">تحديث نِسب الإنجاز لكل مرحلة</span></li>
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.supervisor.p3">رصد التقييم النهائي بعد المناقشة</span></li>
                        </ul>
                    </article>

                    <article class="cell role-cell">
                        <div class="role-head">
                            <span class="role-icon" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="21" y1="4" x2="14" y2="4"/><line x1="10" y1="4" x2="3" y2="4"/><line x1="21" y1="12" x2="12" y2="12"/><line x1="8" y1="12" x2="3" y2="12"/><line x1="21" y1="20" x2="16" y2="20"/><line x1="12" y1="20" x2="3" y2="20"/><line x1="14" y1="2" x2="14" y2="6"/><line x1="8" y1="10" x2="8" y2="14"/><line x1="16" y1="18" x2="16" y2="22"/></svg></span>
                            <h3 data-i18n="roles.admin.name">الإدارة</h3>
                            <p class="role-tag" data-i18n="roles.admin.role">ضبط النظام وتنظيم الفصل</p>
                        </div>
                        <ul class="role-points">
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.admin.p1">إدارة التخصصات وأنواع المشاريع والفصول</span></li>
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.admin.p2">إضافة المشرفين وتوزيع المجموعات</span></li>
                            <li><span class="role-check" aria-hidden="true"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></span><span data-i18n="roles.admin.p3">ضبط الحد الأقصى لأعضاء الفريق</span></li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        {{-- ======= Showcase — مشاريع حقيقية من قاعدة البيانات ======= --}}
        @php
            $showcase = \Illuminate\Support\Facades\Cache::remember('site_showcase', 3600, function () {
                return \App\Models\Project::whereIn('status', ['accept', 'complete'])
                    ->with(['project_type', 'semester'])
                    ->withCount('group')
                    ->latest()
                    ->take(4)
                    ->get();
            });
        @endphp
        @if ($showcase->isNotEmpty())
            <section id="showcase" class="section">
                <div class="container">
                    <div class="section-head" data-num="06">
                        <div class="section-index reveal"><span data-i18n="show.kicker">من المنصة</span></div>
                        <div class="section-head-grid">
                            <h2 class="reveal" data-i18n="show.title">مشاريع تُتابَع على تخرُّج الآن</h2>
                            <p class="reveal d1" data-i18n="show.text">
                                ليست أمثلة مصنوعة — هذه مشاريع مسجّلة فعلاً على المنصة بأنواعها وفصولها وأحجام فرقها.
                            </p>
                        </div>
                    </div>

                    <div class="showcase reveal">
                        @foreach ($showcase as $project)
                            <article class="project-cell">
                                <div class="project-top">
                                    <span class="project-type">{{ $project->project_type->name }}</span>
                                    <span class="project-state {{ $project->status === 'complete' ? 'done' : '' }}">
                                        <i aria-hidden="true"></i>
                                        @if ($project->status === 'complete')
                                            <span data-i18n="state.done">مكتمل</span>
                                        @else
                                            <span data-i18n="state.progress">قيد التنفيذ</span>
                                        @endif
                                    </span>
                                </div>
                                <h3 class="project-title">{{ $project->title }}</h3>
                                <div class="project-meta">
                                    <span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                        {{ $project->group_count }} <span data-i18n="unit.members">أعضاء</span>
                                    </span>
                                    <span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        {{ $project->semester->name }}
                                    </span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- ======= Lifecycle ======= --}}
        <section id="lifecycle" class="section">
            <div class="container">
                <div class="section-head" data-num="07">
                    <div class="section-index reveal"><span data-i18n="lc.kicker">دورة حياة المشروع</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="lc.title">مراحل واضحة، من الفكرة إلى الإنجاز</h2>
                        <p class="reveal d1" data-i18n="lc.text">
                            أي مشروع، بغض النظر عن طبيعته ومدته وحجم نشاطاته، يمر بمراحل محددة لتحقيق أهدافه في فترة زمنية محددة.
                        </p>
                    </div>
                </div>

                <div class="lifecycle reveal">
                    <div class="lc-row">
                        <span class="lc-num">01</span>
                        <h3 class="lc-title" data-i18n="lc.1.title">التفكير في المشروع</h3>
                        <p class="lc-text" data-i18n="lc.1.text">تحديد الفكرة ونطاقها والتأكد من جدواها قبل تقديمها للمشرف.</p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">02</span>
                        <h3 class="lc-title" data-i18n="lc.2.title">التخطيط</h3>
                        <p class="lc-text" data-i18n="lc.2.text">تقسيم العمل إلى مراحل بمواعيد استحقاق وتوزيع المهام على الفريق.</p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">03</span>
                        <h3 class="lc-title" data-i18n="lc.3.title">التنفيذ والمتابعة</h3>
                        <p class="lc-text" data-i18n="lc.3.text">إنجاز المراحل ورفع الملفات ومناقشة المشرف مع تحديث نسبة الإنجاز.</p>
                    </div>
                    <div class="lc-row">
                        <span class="lc-num">04</span>
                        <h3 class="lc-title" data-i18n="lc.4.title">المناقشة والتقييم</h3>
                        <p class="lc-text" data-i18n="lc.4.text">عرض المشروع أمام اللجنة ورصد الدرجة النهائية مع التقدير والملاحظات.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= FAQ ======= --}}
        <section id="faq" class="section">
            <div class="container">
                <div class="section-head" data-num="08">
                    <div class="section-index reveal"><span data-i18n="faq.kicker">الأسئلة الشائعة</span></div>
                    <div class="section-head-grid">
                        <h2 class="reveal" data-i18n="faq.title">كل ما يسأله الطلاب قبل البدء</h2>
                        <p class="reveal d1" data-i18n="faq.text">
                            إجابات مباشرة من واقع النظام — ولأي سؤال آخر تواصل معنا من قسم الاتصال بالأسفل.
                        </p>
                    </div>
                </div>

                <div class="faq reveal">
                    @foreach ([
                        ['كم عضواً يتكون منه الفريق؟', 'حسب نوع المشروع الذي يحدده قسمك — كل نوع له حد أدنى وأقصى يظهران أمامك في نموذج التقديم، والنظام لا يقبل فريقاً خارج الحدود.'],
                        ['كيف أقدم طلب مشروع؟', 'سجّل دخولك ← اختر نوع المشروع ومشرفاً لديه مقاعد متاحة ← اختر أعضاء فريقك من القائمة ← اكتب العنوان والوصف وأرسل. سيصل طلبك للمشرف فوراً.'],
                        ['كيف أعرف رد المشرف على طلبي؟', 'يصلك إشعار داخل النظام فور القبول أو الرفض — تجده في جرس الإشعارات وفي لوحتك الرئيسية مع كل تحديث لاحق على مشروعك.'],
                        ['ماذا لو رُفض مشروعي؟', 'يصلك سبب الرفض الذي كتبه المشرف مع الإشعار، ويفتح النظام لك نموذج تقديم جديد مباشرة — عدّل فكرتك أو اختر مشرفاً آخر وأعد الإرسال.'],
                        ['كيف يُقيَّم مشروعي النهائي؟', 'خلال التنفيذ تتابع نسبة إنجاز مراحلك أولاً بأول، وبعد المناقشة يرصد مشرفك الدرجة النهائية من 100 مع التقدير وملاحظاته — وتظهر في لوحتك مع إشعار لكل الفريق.'],
                    ] as $i => [$question, $answer])
                        <div class="faq-item">
                            <button type="button" class="faq-btn" aria-expanded="false" aria-controls="faq-body-{{ $i + 1 }}">
                                <span data-i18n="faq.{{ $i + 1 }}.title">{{ $question }}</span>
                                <span class="faq-sign" aria-hidden="true"></span>
                            </button>
                            <div class="faq-body" id="faq-body-{{ $i + 1 }}">
                                <p data-i18n="faq.{{ $i + 1 }}.text">{{ $answer }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ======= For departments ======= --}}
        <section id="departments" class="section">
            <div class="container">
                <div class="section-head" data-num="09">
                    <div class="section-index reveal"><span data-i18n="dept.kicker">للأقسام والكليات</span></div>
                </div>

                <div class="dept-grid">
                    <div>
                        <h2 class="reveal" data-i18n="dept.title">ملف الفصل الدراسي كاملاً في مكان واحد</h2>
                        <p class="reveal d1" style="color: var(--ink-mute); font-size: 15.5px; line-height: 1.95; margin-top: 18px;" data-i18n="dept.text">
                            بدل جداول متفرقة ومجموعات محادثة، تعطي تخرُّج القسمَ صورةً واحدة: من قدّم،
                            ومن وافق، وأين وصل كل فريق، ومن لم يلتحق بمجموعة بعد.
                        </p>
                    </div>

                    <div class="dept-points reveal d1">
                        <div class="dept-point">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>
                                <b data-i18n="dept.1.title">توزيع المشرفين بحدّ أقصى لكل واحد</b>
                                <span data-i18n="dept.1.text">تحدد للمشرف عدد المجموعات التي يقبلها، والنظام يرفض ما زاد تلقائياً.</span>
                            </span>
                        </div>
                        <div class="dept-point">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>
                                <b data-i18n="dept.2.title">استيراد الطلاب والمشرفين من ملف Excel</b>
                                <span data-i18n="dept.2.text">ترفع كشف الدفعة مرة واحدة فتُنشأ الحسابات كلها بلا إدخال يدوي.</span>
                            </span>
                        </div>
                        <div class="dept-point">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>
                                <b data-i18n="dept.3.title">أنواع مشاريع بحدود فريق لكل تخصص</b>
                                <span data-i18n="dept.3.text">تضبط لكل تخصص أنواع مشاريعه والحد الأدنى والأقصى لأعضاء الفريق.</span>
                            </span>
                        </div>
                        <div class="dept-point">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>
                                <b data-i18n="dept.4.title">تصدير كشف المجموعات إلى Excel</b>
                                <span data-i18n="dept.4.text">تُخرج كشفاً بالمجموعات ومشرفيها ودرجاتها في أي لحظة من الفصل.</span>
                            </span>
                        </div>
                    </div>
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

                <div class="contact-grid reveal">
                    <aside class="contact-aside">
                        <h3 data-i18n="contact.asideTitle">قبل أن تكتب</h3>
                        <p data-i18n="contact.asideText">
                            حسابك يُنشأ من إدارة المنصة، فإن لم تستطع الدخول برقمك الجامعي راجع إدارة قسمك أولاً.
                            وللأسئلة حول المواعيد وأنواع المشاريع، اكتب لنا هنا.
                        </p>
                        <div class="contact-point">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                            <span data-i18n="contact.pointMail">الرد خلال يوم عمل واحد</span>
                        </div>
                        <div class="contact-point">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            <span data-i18n="contact.pointHours">من الأحد إلى الخميس</span>
                        </div>
                    </aside>

                    <form action="{{ route('site.send') }}" method="post" class="contact-form">
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
                        @if ($errors->any())
                            <p class="form-alert err" role="alert">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                {{ $errors->first() }}
                            </p>
                        @endif

                        <div class="form-row">
                            <div class="field">
                                <label for="cf-name" data-i18n="form.name">الاسم</label>
                                <input id="cf-name" type="text" name="name" required
                                    class="@error('name') is-invalid @enderror" value="{{ old('name', $name) }}"
                                    @if ($name) readonly @endif />
                            </div>
                            <div class="field">
                                <label for="cf-email" data-i18n="form.email">البريد الإلكتروني</label>
                                <input id="cf-email" type="email" name="email" required
                                    class="@error('email') is-invalid @enderror" value="{{ old('email', $email) }}"
                                    @if ($email) readonly @endif />
                            </div>
                        </div>
                        <div class="field">
                            <label for="cf-subject" data-i18n="form.subject">الموضوع</label>
                            <input id="cf-subject" type="text" name="subject" required
                                class="@error('subject') is-invalid @enderror" value="{{ old('subject') }}" />
                        </div>
                        <div class="field">
                            <label for="cf-message" data-i18n="form.message">الرسالة</label>
                            <textarea id="cf-message" name="message" rows="5" required
                                class="@error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                            <span data-i18n="form.send">إرسال</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>


    {{-- ======= Closing CTA ======= --}}
    <section class="cta-band">
        <div class="container cta-inner">
            <div>
                <h2 data-i18n="cta.title">جاهز تبدأ مشروع تخرجك؟</h2>
                <p data-i18n="cta.text">
                    حسابك جاهز مسبقاً من إدارة المنصة — سجّل دخولك برقمك الجامعي، كوّن فريقك،
                    واختر مشرفك. بقية الطريق تتابعها من لوحتك.
                </p>
            </div>
            <div class="cta-actions">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <span data-i18n="cta.button">تسجيل الدخول</span>
                </a>
                <a href="#contact" class="btn-link">
                    <span data-i18n="cta.secondary">عندي سؤال</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                </a>
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
                    <a href="#roles" data-i18n="roles.student.name">الطالب</a>
                    <a href="#roles" data-i18n="roles.supervisor.name">المشرف</a>
                    <a href="#roles" data-i18n="roles.admin.name">الإدارة</a>
                </nav>

                <nav class="footer-col" aria-label="Help">
                    <div class="footer-col-title" data-i18n="footer.colHelp">المساعدة</div>
                    <a href="{{ route('login') }}" data-i18n="nav.login">تسجيل دخول</a>
                    <a href="#faq" data-i18n="nav.faq">الأسئلة الشائعة</a>
                    <a href="#contact" data-i18n="nav.contact">اتصل بنا</a>
                    <span class="footer-note" data-i18n="footer.noSignup">الحسابات تُنشأ من الإدارة</span>
                </nav>
            </div>

            <div class="footer-bar">
                <span data-i18n="footer.rights">جميع الحقوق محفوظة — تخرُّج ©</span>
                <div class="footer-meta">
                    @if ($currentSemester = \App\Models\Semester::current())
                        <span class="footer-semester">
                            <i aria-hidden="true"></i>
                            <span>{{ $currentSemester->name }}</span>
                        </span>
                    @endif
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
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </a>

    <script src="{{ asset('assets/js/premium.js') }}"></script>
</body>

</html>
