<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>تخرُّج | منصة متابعة مشاريع التخرج</title>
    <meta name="description" content="تخرُّج (Takharruj) — منصة لتتبع مشاريع التخرج وإدارة الفرق والمشرفين ومتابعة مراحل المشروع من الفكرة إلى المناقشة." />
    <link rel="icon" href="{{ asset('assets/img/takharruj-logo.svg') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Outfit:wght@300..700&display=swap" rel="stylesheet" />

    <link href="{{ asset('assets/css/premium.css') }}" rel="stylesheet" />
    <noscript>
        <style>
            .reveal, .stagger, .scene { opacity: 1 !important; transform: none !important; filter: none !important; }
            .step-body { max-height: none !important; }
        </style>
    </noscript>
</head>

<body>
    <div class="cursor-glow" aria-hidden="true"></div>

    {{-- ======= Header ======= --}}
    <header class="site-header">
        <nav class="nav-bar" aria-label="Main navigation">
            <a href="#hero" class="brand">
                <span class="brand-logo" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </span>
                <span>
                    <span class="brand-name" data-i18n="brand.name">تخرُّج</span><br>
                    <small class="brand-sub" data-i18n="brand.sub">منصة متابعة مشاريع التخرج</small>
                </span>
            </a>

            <ul class="nav-links">
                <li><a href="#hero" data-i18n="nav.home">الرئيسية</a></li>
                <li><a href="#about" data-i18n="nav.about">عن المنصة</a></li>
                <li><a href="#services" data-i18n="nav.services">الخدمات</a></li>
                <li><a href="#features" data-i18n="nav.features">الميزات</a></li>
                <li><a href="#roles" data-i18n="nav.roles">الأدوار</a></li>
                <li><a href="#contact" data-i18n="nav.contact">اتصل بنا</a></li>
            </ul>

            <div class="nav-actions">
                <button type="button" class="lang-btn" aria-label="Switch language">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10zM2 12h20"/></svg>
                    <span class="lang-label">EN</span>
                </button>

                @if (auth()->guard('admin')->check() || auth()->guard('supervisor')->check() || auth()->guard('student')->check())
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm shine nav-login" data-magnetic>
                        <span data-i18n="nav.dashboard">لوحة التحكم</span>
                    </a>
                    <a href="{{ route('logout') }}" class="lang-btn nav-login"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span data-i18n="nav.logout">تسجيل خروج</span>
                    </a>
                    <form action="{{ route('logout') }}" method="post" id="logout-form">@csrf</form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm shine nav-login" data-magnetic>
                        <span data-i18n="nav.login">تسجيل دخول</span>
                    </a>
                @endif

                <button type="button" class="menu-toggle" aria-label="Menu" aria-expanded="false">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </nav>

        <div class="mobile-menu glass-strong">
            <a href="#hero" data-i18n="nav.home">الرئيسية</a>
            <a href="#about" data-i18n="nav.about">عن المنصة</a>
            <a href="#services" data-i18n="nav.services">الخدمات</a>
            <a href="#features" data-i18n="nav.features">الميزات</a>
            <a href="#roles" data-i18n="nav.roles">الأدوار</a>
            <a href="#contact" data-i18n="nav.contact">اتصل بنا</a>
            @if (auth()->guard('admin')->check() || auth()->guard('supervisor')->check() || auth()->guard('student')->check())
                <a href="{{ route('login') }}" class="btn btn-primary" data-i18n="nav.dashboard">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary" data-i18n="nav.login">تسجيل دخول</a>
            @endif
        </div>
    </header>

    {{-- ======= Hero ======= --}}
    <section id="hero" class="hero">
        <div class="mesh-bg" aria-hidden="true">
            <div class="mesh-blob blob-a"></div>
            <div class="mesh-blob blob-b"></div>
            <div class="mesh-blob blob-c"></div>
            <div class="grid-overlay"></div>
            <div class="noise-overlay"></div>
        </div>
        <div class="particles" aria-hidden="true"></div>

        <div class="container hero-grid">
            <div class="hero-copy">
                <span class="badge stagger d1">
                    <span class="dot"></span>
                    <span data-i18n="hero.badge">منصة ذكية لإدارة مشاريع التخرج</span>
                </span>

                <h1 class="stagger d2" data-i18n="hero.title" data-i18n-html>
                    تتبّع مشروع تخرجك<br><span class="text-gradient">من الفكرة إلى المناقشة</span>
                </h1>

                <p class="hero-sub stagger d3" data-i18n="hero.sub">
                    منصة تخرُّج لإدارة الفرق، اختيار المشرفين، ومتابعة مراحل المشروع
                    بتجربة عصرية وسلسة.
                </p>

                <div class="hero-ctas stagger d4">
                    <a href="{{ route('login') }}" class="btn btn-primary shine" data-magnetic>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        <span data-i18n="hero.cta1">ابدأ الآن</span>
                    </a>
                    <a href="#about" class="btn btn-ghost" data-magnetic>
                        <span data-i18n="hero.cta2">اكتشف المزيد</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M19 12l-7 7-7-7"/></svg>
                    </a>
                </div>
            </div>

            {{-- 3D scene --}}
            <div class="scene" aria-hidden="true">
                <div class="scene-inner">
                    <div class="dash-card glass-strong gradient-border">
                        <div class="dash-chrome">
                            <div class="dash-dots"><span class="r"></span><span class="y"></span><span class="g"></span></div>
                            <span class="dash-title" data-i18n="hero.dashTitle">لوحة متابعة المشروع</span>
                        </div>

                        <div class="dash-project">
                            <span class="dash-project-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            </span>
                            <span>
                                <span class="dash-project-name" data-i18n="hero.dashProject">نظام تتبع ذكي</span><br>
                                <small class="dash-project-phase" data-i18n="hero.dashPhase">المرحلة: التنفيذ</small>
                            </span>
                            <span class="dash-pct">78%</span>
                        </div>

                        <div class="dash-progress-label">
                            <span data-i18n="hero.progress">تقدم المشروع</span>
                            <b>78%</b>
                        </div>
                        <div class="dash-track"><div class="dash-fill"></div></div>

                        <div class="dash-chart">
                            <span style="--h:34px; animation-delay:1s"></span>
                            <span style="--h:54px; animation-delay:1.08s"></span>
                            <span style="--h:28px; animation-delay:1.16s"></span>
                            <span style="--h:66px; animation-delay:1.24s"></span>
                            <span style="--h:46px; animation-delay:1.32s"></span>
                            <span style="--h:72px; animation-delay:1.4s"></span>
                            <span style="--h:58px; animation-delay:1.48s"></span>
                        </div>
                    </div>

                    <div class="float-card float-approved glass">
                        <div class="float-row">
                            <span class="float-icon green">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                            </span>
                            <span>
                                <span class="float-title" data-i18n="hero.approved">تمت الموافقة على المشروع</span>
                                <span class="float-sub" data-i18n="hero.approvedSub">المشرف الأكاديمي · قبل دقيقتين</span>
                            </span>
                        </div>
                    </div>

                    <div class="float-card float-team glass">
                        <div class="team-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <span data-i18n="hero.team">أعضاء الفريق</span>
                        </div>
                        <div class="avatars">
                            <span class="av1">A</span><span class="av2">B</span><span class="av3">C</span><span class="av4">D</span><span class="av-more">+2</span>
                        </div>
                    </div>

                    <div class="hero-phone">
                        <div class="phone-frame gradient-border">
                            <div class="phone-screen">
                                <div class="phone-notch"></div>
                                <div class="phone-notif">
                                    <span class="phone-notif-icon">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                    </span>
                                    <span>
                                        <b data-i18n="hero.notif">إشعار جديد</b>
                                        <small data-i18n="hero.notifSub">ردّ المشرف على طلبك</small>
                                    </span>
                                </div>
                                <div class="ph-line w80"></div>
                                <div class="ph-line w60"></div>
                                <div class="ph-block"></div>
                                <div class="ph-line w66"></div>
                            </div>
                        </div>
                    </div>

                    <div class="ground-shadow"></div>
                </div>
            </div>
        </div>
    </section>

    <main>
        {{-- ======= Stats (أرقام حقيقية من قاعدة البيانات، مع كاش ساعة) ======= --}}
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
        <section class="section" style="padding-top: 20px; padding-bottom: 40px;">
            <div class="container">
                <div class="stats-card glass-strong gradient-border reveal">
                    <div class="stat">
                        <span class="stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </span>
                        <div class="stat-num text-gradient" data-count="{{ $siteStats['specializes'] }}" data-suffix="">0</div>
                        <div class="stat-label" data-i18n="stats.1">تخصصاً أكاديمياً</div>
                    </div>
                    <div class="stat">
                        <span class="stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <div class="stat-num text-gradient" data-count="{{ $siteStats['supervisors'] }}" data-suffix="">0</div>
                        <div class="stat-label" data-i18n="stats.2">مشرفاً أكاديمياً</div>
                    </div>
                    <div class="stat">
                        <span class="stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/></svg>
                        </span>
                        <div class="stat-num text-gradient" data-count="{{ $siteStats['projects'] }}" data-suffix="">0</div>
                        <div class="stat-label" data-i18n="stats.3">مشروع تخرج مسجل</div>
                    </div>
                    <div class="stat">
                        <span class="stat-icon">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                        </span>
                        <div class="stat-num text-gradient" data-count="{{ $siteStats['students'] }}" data-suffix="">0</div>
                        <div class="stat-label" data-i18n="stats.4">طالباً مسجلاً</div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= About ======= --}}
        <section id="about" class="section">
            <div class="container about-grid">
                <div class="about-illustration glass-strong gradient-border reveal" data-tilt="6">
                    <div class="glow-a"></div>
                    <div class="glow-b"></div>
                    <div class="about-scene">
                        <div class="about-building">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01"/></svg>
                        </div>
                        <div class="about-line"></div>
                        <div class="about-programs">
                            <div class="about-chip glass">
                                <span class="about-chip-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 2v2M15 2v2M9 20v2M15 20v2M2 9h2M2 15h2M20 9h2M20 15h2"/></svg>
                                </span>
                                <span data-i18n="about.p1">إدارة الفرق الطلابية والمشرفين</span>
                            </div>
                            <div class="about-chip glass">
                                <span class="about-chip-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                                </span>
                                <span data-i18n="about.p2">متابعة مراحل المشروع ونِسب الإنجاز</span>
                            </div>
                            <div class="about-chip glass">
                                <span class="about-chip-icon">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0M1.42 9a16 16 0 0 1 21.16 0M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01"/></svg>
                                </span>
                                <span data-i18n="about.p3">المناقشة والتقييم ورصد الدرجات</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-copy">
                    <span class="badge reveal"><span class="dot"></span><span data-i18n="about.kicker">عن المنصة</span></span>
                    <h2 class="reveal d1" data-i18n="about.title">منصة تخرُّج</h2>
                    <p class="reveal d2" data-i18n="about.text">
                        تخرُّج منصة مستقلة لإدارة مشاريع التخرج من أول تكوين الفريق واختيار المشرف، مروراً باعتماد
                        الفكرة ومتابعة المراحل، وصولاً إلى المناقشة والتقييم النهائي — كل ذلك في مكان واحد
                        وبسير عمل واضح لكل طرف.
                    </p>
                    <div class="reveal d3">
                        <div class="programs-title" data-i18n="about.pillarsTitle">ركائز المنصة</div>
                        <div class="program-item glass">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                            <span data-i18n="about.p1">إدارة الفرق الطلابية والمشرفين</span>
                        </div>
                        <div class="program-item glass">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                            <span data-i18n="about.p2">متابعة مراحل المشروع ونِسب الإنجاز</span>
                        </div>
                        <div class="program-item glass">
                            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                            <span data-i18n="about.p3">المناقشة والتقييم ورصد الدرجات</span>
                        </div>
                        <a href="#services" class="link-more">
                            <span data-i18n="about.more">المزيد</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= Services ======= --}}
        <section id="services" class="section">
            <div class="section-divider" aria-hidden="true"></div>
            <div class="container">
                <div class="section-head">
                    <span class="badge reveal"><span class="dot"></span><span data-i18n="services.kicker">الخدمات</span></span>
                    <h2 class="reveal d1" data-i18n="services.title">كل ما يحتاجه مشروعك في مكان واحد</h2>
                    <p class="reveal d2" data-i18n="services.text">
                        نظام متكامل يدير كافة العمليات من تسجيل الدخول واختيار الفريق والمشرف، وصولاً إلى مناقشة المشروع وتقييمه.
                    </p>
                </div>

                <div class="cards-grid">
                    <article class="p-card glass gradient-border reveal" data-tilt="6">
                        <div class="card-glow g-blue"></div>
                        <span class="card-icon i-blue">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <h3 data-i18n="services.1.title">تكوين الفريق واختيار المشرف</h3>
                        <p data-i18n="services.1.text">اختر زملاءك من قائمة الطلاب المتاحين في تخصصك، وشاهد المقاعد المتبقية لكل مشرف قبل تقديم طلبك.</p>
                    </article>

                    <article class="p-card glass gradient-border reveal d1" data-tilt="6">
                        <div class="card-glow g-violet"></div>
                        <span class="card-icon i-violet">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/></svg>
                        </span>
                        <h3 data-i18n="services.2.title">تتبع مراحل بنسبة إنجاز</h3>
                        <p data-i18n="services.2.text">مراحل يحددها مشرفك مع نسبة إنجاز مباشرة وموعد نهائي بعدّاد أيام — تعرف أين تقف في كل لحظة.</p>
                    </article>

                    <article class="p-card glass gradient-border reveal d2" data-tilt="6">
                        <div class="card-glow g-cyan"></div>
                        <span class="card-icon i-cyan">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/></svg>
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
                <div class="section-head">
                    <span class="badge reveal"><span class="dot"></span><span data-i18n="features.kicker">المميزات</span></span>
                    <h2 class="reveal d1" data-i18n="features.title">منصة صُممت لنجاح مشروعك</h2>
                    <p class="reveal d2" data-i18n="features.text">
                        يهدف النظام إلى إدارة كافة العمليات من مرحلة تسجيل الدخول واختيار الفريق والمشرف
                        وصولاً إلى مرحلة مناقشة المشروع وتقييمه.
                    </p>
                </div>

                <div class="features-grid">
                    <div class="feature-list">
                        <article class="feature-item glass reveal">
                            <span class="feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <div>
                                <h3 data-i18n="features.1.title">مراحل مشروع بنسبة إنجاز مباشرة</h3>
                                <p data-i18n="features.1.text">مشرفك يحدد مراحل مشروعك بتواريخ استحقاق، وأنت تتابع نسبة الإنجاز على خط زمني مرئي يميز المنجز والمتأخر تلقائياً.</p>
                            </div>
                        </article>
                        <article class="feature-item glass reveal d1">
                            <span class="feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6M10 22h4M15.09 14c.26-1.09 1.32-1.85 2.16-2.72a6 6 0 1 0-10.5 0c.84.87 1.9 1.63 2.16 2.72"/></svg>
                            </span>
                            <div>
                                <h3 data-i18n="features.2.title">مستكشف المشاريع السابقة</h3>
                                <p data-i18n="features.2.text">تصفّح مشاريع الدفعات السابقة بأنواعها ومشرفيها — استلهم فكرتك وتأكد أنها غير منفّذة قبل التقديم.</p>
                            </div>
                        </article>
                        <article class="feature-item glass reveal d2">
                            <span class="feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                            </span>
                            <div>
                                <h3 data-i18n="features.3.title">نقاش مدمج وإشعارات فورية</h3>
                                <p data-i18n="features.3.text">اسأل مشرفك وناقش فريقك داخل صفحة المشروع نفسها، واستلم إشعاراً لكل رد أو مرحلة تُنجز أو ملف يُرفع.</p>
                            </div>
                        </article>
                        <article class="feature-item glass reveal d3">
                            <span class="feature-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
                            </span>
                            <div>
                                <h3 data-i18n="features.4.title">تقييم إلكتروني بدرجة وتقدير</h3>
                                <p data-i18n="features.4.text">بعد اكتمال مشروعك يرصد المشرف درجتك النهائية مع التقدير وملاحظاته الختامية — وتصلك النتيجة بإشعار فوري.</p>
                            </div>
                        </article>
                    </div>

                    <div class="features-phone-wrap reveal d2" aria-hidden="true">
                        <div class="features-phone">
                            <div class="phone-frame gradient-border">
                                <div class="phone-screen">
                                    <div class="phone-notch"></div>
                                    <div class="app-head">
                                        <span class="app-brand">
                                            <i>
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                            </i>
                                            <span data-i18n="brand.name">تخرُّج</span>
                                        </span>
                                        <span class="app-bell">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                        </span>
                                    </div>
                                    <div class="app-card">
                                        <div class="app-progress-head">
                                            <span data-i18n="hero.progress">تقدم المشروع</span>
                                            <b>78%</b>
                                        </div>
                                        <div class="app-track"><div class="app-fill"></div></div>
                                    </div>
                                    <div class="app-task">
                                        <svg class="done" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                                        <span class="app-task-lines"><i class="tl-75"></i><i></i></span>
                                    </div>
                                    <div class="app-task">
                                        <svg class="done" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                                        <span class="app-task-lines"><i class="tl-66"></i><i></i></span>
                                    </div>
                                    <div class="app-task">
                                        <svg class="todo" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/></svg>
                                        <span class="app-task-lines"><i class="tl-80"></i><i></i></span>
                                    </div>
                                    <div class="app-graphic"></div>
                                </div>
                            </div>
                            <div class="float-badge glass">
                                <div class="float-row">
                                    <span class="float-icon green">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
                                    </span>
                                    <span class="float-title" data-i18n="hero.approved">تمت الموافقة على المشروع</span>
                                </div>
                            </div>
                            <div class="ground-shadow"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= How it works ======= --}}
        <section id="how" class="section">
            <div class="section-divider" aria-hidden="true"></div>
            <div class="container">
                <div class="section-head">
                    <span class="badge reveal"><span class="dot"></span><span data-i18n="how.kicker">كيف يعمل النظام؟</span></span>
                    <h2 class="reveal d1" data-i18n="how.title">أربع خطوات من الفكرة إلى الدرجة</h2>
                    <p class="reveal d2" data-i18n="how.text">
                        حسابك يُنشأ من إدارة المنصة — لا حاجة للتسجيل، فقط سجّل دخولك وابدأ.
                    </p>
                </div>

                <div class="how-grid">
                    <div class="how-step glass reveal">
                        <span class="how-num">1</span>
                        <h3 data-i18n="how.1.title">سجّل دخولك</h3>
                        <p data-i18n="how.1.text">ببريدك أو رقمك الجامعي — الحسابات جاهزة مسبقاً من إدارة المنصة.</p>
                    </div>
                    <div class="how-step glass reveal d1">
                        <span class="how-num">2</span>
                        <h3 data-i18n="how.2.title">كوّن فريقك وقدّم فكرتك</h3>
                        <p data-i18n="how.2.text">اختر زملاءك من قائمة المتاحين، واختر مشرفاً لديه مقاعد، واكتب فكرة مشروعك.</p>
                    </div>
                    <div class="how-step glass reveal d2">
                        <span class="how-num">3</span>
                        <h3 data-i18n="how.3.title">تابع وناقش وارفع</h3>
                        <p data-i18n="how.3.text">بعد موافقة المشرف: مراحل بنسبة إنجاز، نقاش مباشر، ملفات، وإشعار لكل جديد.</p>
                    </div>
                    <div class="how-step glass reveal d3">
                        <span class="how-num">4</span>
                        <h3 data-i18n="how.4.title">ناقش واستلم تقييمك</h3>
                        <p data-i18n="how.4.text">بعد المناقشة يرصد مشرفك درجتك النهائية بالتقدير وملاحظاته — وتصلك فوراً.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= Roles ======= --}}
        <section id="roles" class="section">
            <div class="section-divider" aria-hidden="true"></div>
            <div class="container">
                <div class="section-head">
                    <span class="badge reveal"><span class="dot"></span><span data-i18n="roles.kicker">أدوار المنصة</span></span>
                    <h2 class="reveal d1" data-i18n="roles.title">لكل دور مساحته الخاصة</h2>
                    <p class="reveal d2" data-i18n="roles.text">
                        تخرُّج مبنية حول ثلاثة أدوار متكاملة، لكل منها لوحة تحكم وصلاحيات تناسب مهامه،
                        بحيث يعرف كل طرف ما عليه بالضبط في كل مرحلة.
                    </p>
                </div>

                <div class="roles-grid">
                    <article class="role-card glass reveal d0" data-tilt="7">
                        <div class="role-icon-wrap">
                            <div class="role-icon-glow" aria-hidden="true"></div>
                            <span class="role-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></span>
                        </div>
                        <div>
                            <h3 data-i18n="roles.student.name">الطالب</h3>
                            <p class="role-tag" data-i18n="roles.student.role">تكوين الفريق وتقديم الفكرة</p>
                        </div>
                        <ul class="role-points">
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.student.p1">اختيار زملاء الفريق من قائمة المتاحين</span>
                                </li>
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.student.p2">اختيار مشرف لديه مقاعد شاغرة</span>
                                </li>
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.student.p3">متابعة المراحل والتعليقات ورفع الملفات</span>
                                </li>
                        </ul>
                    </article>
                    <article class="role-card glass reveal d1" data-tilt="7">
                        <div class="role-icon-wrap">
                            <div class="role-icon-glow" aria-hidden="true"></div>
                            <span class="role-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg></span>
                        </div>
                        <div>
                            <h3 data-i18n="roles.supervisor.name">المشرف</h3>
                            <p class="role-tag" data-i18n="roles.supervisor.role">المتابعة والاعتماد والتقييم</p>
                        </div>
                        <ul class="role-points">
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.supervisor.p1">استعراض طلبات الفرق واعتماد الأفكار</span>
                                </li>
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.supervisor.p2">تحديث نِسب الإنجاز لكل مرحلة</span>
                                </li>
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.supervisor.p3">رصد التقييم النهائي بعد المناقشة</span>
                                </li>
                        </ul>
                    </article>
                    <article class="role-card glass reveal d2" data-tilt="7">
                        <div class="role-icon-wrap">
                            <div class="role-icon-glow" aria-hidden="true"></div>
                            <span class="role-icon" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="4" x2="14" y2="4"/><line x1="10" y1="4" x2="3" y2="4"/><line x1="21" y1="12" x2="12" y2="12"/><line x1="8" y1="12" x2="3" y2="12"/><line x1="21" y1="20" x2="16" y2="20"/><line x1="12" y1="20" x2="3" y2="20"/><line x1="14" y1="2" x2="14" y2="6"/><line x1="8" y1="10" x2="8" y2="14"/><line x1="16" y1="18" x2="16" y2="22"/></svg></span>
                        </div>
                        <div>
                            <h3 data-i18n="roles.admin.name">الإدارة</h3>
                            <p class="role-tag" data-i18n="roles.admin.role">ضبط النظام وتنظيم الفصل</p>
                        </div>
                        <ul class="role-points">
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.admin.p1">إدارة التخصصات وأنواع المشاريع والفصول</span>
                                </li>
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.admin.p2">إضافة المشرفين وتوزيع المجموعات</span>
                                </li>
                                <li>
                                    <span class="role-check" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                                    <span data-i18n="roles.admin.p3">ضبط الحد الأقصى لأعضاء الفريق</span>
                                </li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        {{-- ======= Contact ======= --}}
        <section id="contact" class="section">
            <div class="section-divider" aria-hidden="true"></div>
            <div class="container">
                <div class="section-head">
                    <span class="badge reveal"><span class="dot"></span><span data-i18n="contact.kicker">اتصل بنا</span></span>
                    <h2 class="reveal d1" data-i18n="contact.title">تواصل معنا</h2>
                    <p class="reveal d2" data-i18n="contact.text">عندك سؤال أو اقتراح حول منصة تخرُّج؟ اكتب لنا وسنرد في أقرب وقت.</p>
                </div>

                <div class="contact-grid">
                    @php
                        $name = auth('admin')->check() ? auth('admin')->user()->name : (auth('supervisor')->check() ? auth('supervisor')->user()->name : (auth('student')->check() ? auth('student')->user()->name : ''));
                        $email = auth('admin')->check() ? auth('admin')->user()->email : (auth('supervisor')->check() ? auth('supervisor')->user()->email : (auth('student')->check() ? auth('student')->user()->email : ''));
                    @endphp
                    <form action="{{ route('site.send') }}" method="post" class="contact-form glass gradient-border reveal d1">
                        @csrf

                        @if (session('success'))
                            <p class="form-alert ok" role="status">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                                {{ session('success') }}
                            </p>
                        @endif
                        @if (session('fail'))
                            <p class="form-alert err" role="alert">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                {{ session('fail') }}
                            </p>
                        @endif
                        @if ($errors->any())
                            <p class="form-alert err" role="alert">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
                                {{ $errors->first() }}
                            </p>
                        @endif

                        <div class="form-row">
                            <input type="text" name="name" required placeholder="الاسم" data-i18n-placeholder="form.name"
                                class="@error('name') is-invalid @enderror" value="{{ old('name', $name) }}"
                                @if ($name) readonly @endif aria-label="Name" />
                            <input type="email" name="email" required placeholder="الايميل الخاص بك" data-i18n-placeholder="form.email"
                                class="@error('email') is-invalid @enderror" value="{{ old('email', $email) }}"
                                @if ($email) readonly @endif aria-label="Email" />
                        </div>
                        <input type="text" name="subject" required placeholder="الموضوع" data-i18n-placeholder="form.subject"
                            class="@error('subject') is-invalid @enderror" value="{{ old('subject') }}" aria-label="Subject" />
                        <textarea name="message" rows="5" required placeholder="الرسالة .." data-i18n-placeholder="form.message"
                            class="@error('message') is-invalid @enderror" aria-label="Message">{{ old('message') }}</textarea>

                        <button type="submit" class="btn btn-primary shine" data-magnetic>
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                            <span data-i18n="form.send">إرسال</span>
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    {{-- ======= Footer ======= --}}
    <footer class="site-footer">
        <div class="footer-card glass-strong">
            <div class="footer-brand">
                <span class="brand-logo" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                </span>
                <span>
                    <b data-i18n="footer.made">منصة متابعة مشاريع التخرج</b>
                    <small data-i18n="footer.rights">جميع الحقوق محفوظة — تخرُّج ©</small>
                </span>
            </div>
            <div class="socials">
                <a href="https://www.facebook.com/jamal.taroush" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="https://www.facebook.com/jamal.taroush" target="_blank" rel="noopener noreferrer" aria-label="Twitter">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"/></svg>
                </a>
                <a href="https://www.facebook.com/jamal.taroush" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <a href="https://www.facebook.com/jamal.taroush" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
            </div>
        </div>
    </footer>

    <a href="#hero" class="back-top" aria-label="Back to top">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </a>

    <script src="{{ asset('assets/js/premium.js') }}"></script>
</body>

</html>
