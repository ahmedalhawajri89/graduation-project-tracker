<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />

    <title>مشروع التخرج</title>
    <meta content="" name="description" />
    <meta content="" name="keywords" />
    <link rel="icon" href="{{ asset('/assets/img/logo.png') }}" />

    <!-- Favicons -->
    <link href="{{ asset('assets/img/favicon.png') }}" rel="icon" />
    <link href="{{ asset('assets/img/apple-touch-icon.png') }} " rel="apple-touch-icon" />

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet" />

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" />
</head>

<body>
    <!-- ======= Header ======= -->
    <header id="header" class="fixed-top">
        <div class="container d-flex align-items-center justify-content-between">
            <h1 class="logo"><a href="index.html">تتبع المشاريع</a></h1>

            <nav id="navbar" class="navbar">
                <ul>
                    <li>
                        <a class="nav-link scrollto active" href="#hero">الرئيسية</a>
                    </li>
                    <li><a class="nav-link scrollto" href="#about">عن الكلية</a></li>
                    <li><a class="nav-link scrollto" href="#services">الخدمات</a></li>
                    <li><a class="nav-link scrollto" href="#features">الميزات</a></li>

                    <li><a class="nav-link scrollto" href="#contact">اتصل بنا</a></li>

                    @if (auth()->guard('admin')->check() ||
                        auth()->guard('supervisor')->check() ||
                        auth()->guard('student')->check())
                        <li>
                            <a class="getstarted scrollto" href="{{ route('login') }}">
                                لوحة التحكم
                            </a>
                        </li>
                        <li>
                            <a class="nav-link scrollto" href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                                تسجيل خروج
                            </a>

                            <form action="{{ route('logout') }}" method="post" id="logout-form">@csrf</form>
                        </li>
                    @else
                        <li>
                            <a class="getstarted scrollto" href="{{ route('login') }}">
                                تسجيل دخول
                            </a>
                        </li>
                    @endif




                </ul>
                <i class="bi bi-list mobile-nav-toggle"></i>
            </nav>
            <!-- .navbar -->
        </div>
    </header>
    <!-- End Header -->

    <!-- ======= Hero Section ======= -->
    <section id="hero" class="d-flex align-items-center">
        <div class="container-fluid" data-aos="fade-up">
            <div class="row justify-content-center">
                <div
                    class="col-xl-5 col-lg-6 pt-3 pt-lg-0 order-2 order-lg-1 d-flex flex-column justify-content-center">
                    <h1>أهلا وسهلا بكم في موقع تتبع مشاريع التخرج ..</h1>
                    <h2>لكلية الحاسابات وتكنولوجيا المعلومات</h2>
                    <div>
                        <a href="#about" class="btn-get-started scrollto">عن الكلية </a>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-6 order-1 order-lg-2 hero-img" data-aos="zoom-in" data-aos-delay="150">
                    <img src="assets/img/a12.png" class="img-fluid animated" alt="" />
                </div>
            </div>
        </div>
    </section>
    <!-- End Hero -->

    <main id="main">
        <!-- ======= About Section ======= -->
        <section id="about" class="about">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 order-1 order-lg-2" data-aos="zoom-in" data-aos-delay="150">
                        <img src="assets/img/about.jpg" class="img-fluid" alt="" />
                    </div>
                    <div class="col-lg-6 pt-4 pt-lg-0 order-2 order-lg-1 content" data-aos="fade-right">
                        <h3>كلية الحاسبات وتكنولوجيا المعلومات</h3>
                        <p class="fst-italic">وتضم البرامج الأكاديمية التالية</p>
                        <ul>
                            <li>
                                <i class="bi bi-check-circle"></i> بكالوريوس علم الحاسوب
                            </li>
                            <li>
                                <i class="bi bi-check-circle"></i> بكالوريوس تكنولوجيا
                                المعلومات التطبيقية
                            </li>
                            <li>
                                <i class="bi bi-check-circle"></i> بكالوريوس الشبكات والهواتف
                                النقالة
                            </li>
                        </ul>
                        <a href="#services" class="read-more">المزيد <i class="bi bi-long-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </section>
        <!-- End About Section -->

        <!-- ======= Counts Section ======= -->
        <section id="counts" class="counts">
            <div class="container">
                <div class="row counters">
                    <div class="col-lg-3 col-6 text-center">

                    </div>
                    <div class="col-lg-3 col-6 text-center">
                    </div>
                </div>
        </section>
        <!-- End Counts Section -->

        <!-- ======= Services Section ======= -->
        <section id="services" class="services section-bg">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>الخدمات</h2>
                    <p>
                        أنشأت كلية الحاسبات وتكنولوجيا المعلومات في بداية العام الدراسي
                        2018-2019 كإحدى كليات جامعة الأقصى , وذلك تلبية لحاجة المجتمع
                        الفلسطيني الملحة للخريجين المؤهلين في مجال تكنولوجيا المعلومات و
                        التقنيات الحديثة.
                    </p>
                </div>

                <div class="row gy-4">
                    <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in"
                        data-aos-delay="100">
                        <div class="icon-box iconbox-blue">
                            <div class="icon">
                                <svg width="100" height="100" viewBox="0 0 600 600"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke="none" stroke-width="0" fill="#f5f5f5"
                                        d="M300,521.0016835830174C376.1290562159157,517.8887921683347,466.0731472004068,529.7835943286574,510.70327084640275,468.03025145048787C554.3714126377745,407.6079735673963,508.03601936045806,328.9844924480964,491.2728898941984,256.3432110539036C474.5976632858925,184.082847569629,479.9380746630129,96.60480741107993,416.23090153303,58.64404602377083C348.86323505073057,18.502131276798302,261.93793281208167,40.57373210992963,193.5410806939664,78.93577620505333C130.42746243093433,114.334589627462,98.30271207620316,179.96522072025542,76.75703585869454,249.04625023123273C51.97151888228291,328.5150500222984,13.704378332031375,421.85034740162234,66.52175969318436,486.19268352777647C119.04800174914682,550.1803526380478,217.28368757567262,524.383925680826,300,521.0016835830174">
                                    </path>
                                </svg>
                                <i class="bx bxl-dribbble"></i>
                            </div>
                            <h4><a href="">سهولة اختيار الفريق</a></h4>
                            <p>يساعدك الموقع على توفير الوقت والجهد اللزم لانجاز المشروع</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in"
                        data-aos-delay="200">
                        <div class="icon-box iconbox-orange">
                            <div class="icon">
                                <svg width="100" height="100" viewBox="0 0 600 600"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke="none" stroke-width="0" fill="#f5f5f5"
                                        d="M300,582.0697525312426C382.5290701553225,586.8405444964366,449.9789794690241,525.3245884688669,502.5850820975895,461.55621195738473C556.606425686781,396.0723002908107,615.8543463187945,314.28637112970534,586.6730223649479,234.56875336149918C558.9533121215079,158.8439757836574,454.9685369536778,164.00468322053177,381.49747125262974,130.76875717737553C312.15926192815925,99.40240125094834,248.97055460311594,18.661163978235184,179.8680185752513,50.54337015887873C110.5421016452524,82.52863877960104,119.82277516462835,180.83849132639028,109.12597500060166,256.43424936330496C100.08760227029461,320.3096726198365,92.17705696193138,384.0621239912766,124.79988738764834,439.7174275375508C164.83382741302287,508.01625554203684,220.96474134820875,577.5009287672846,300,582.0697525312426">
                                    </path>
                                </svg>
                                <i class="bx bx-file"></i>
                            </div>
                            <h4><a href="">تتبع المشروع</a></h4>
                            <p>يعمل على تتبع المشروع بين الطالب والمشرف بشكل مباشر</p>
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in"
                        data-aos-delay="300">
                        <div class="icon-box iconbox-pink">
                            <div class="icon">
                                <svg width="100" height="100" viewBox="0 0 600 600"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke="none" stroke-width="0" fill="#f5f5f5"
                                        d="M300,541.5067337569781C382.14930387511276,545.0595476570109,479.8736841581634,548.3450877840088,526.4010558755058,480.5488172755941C571.5218469581645,414.80211281144784,517.5187510058486,332.0715597781072,496.52539010469104,255.14436215662573C477.37192572678356,184.95920475031193,473.57363656557914,105.61284051026155,413.0603344069578,65.22779650032875C343.27470386102294,18.654635553484475,251.2091493199835,5.337323636656869,175.0934190732945,40.62881213300186C97.87086631185822,76.43348514350839,51.98124368387456,156.15599469081315,36.44837278890362,239.84606092416172C21.716077023791087,319.22268207091537,43.775223500013084,401.1760424656574,96.891909868211,461.97329694683043C147.22146801428983,519.5804099606455,223.5754009179313,538.201503339737,300,541.5067337569781">
                                    </path>
                                </svg>
                                <i class="bx bx-tachometer"></i>
                            </div>
                            <h4><a href="">ادارة الفرق والمشرفين</a></h4>
                            <p>
                                يساعد على التواصل بين اعضاء الفريق وكذلك مع المشرف بشكل مباشر
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Services Section -->

        <!-- ======= Features Section ======= -->
        <section id="features" class="features">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>المميزات</h2>
                    <p>
                        يهدف هذا النظام الى ادارة كافة العمليات من مرحلة تسجيل الدخول الى
                        النظام واختيار الفريق والمشرف وصولا الى مرحلة مناقشة المشروع
                        وتقيمه .
                    </p>
                </div>

                <div class="row">
                    <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column align-items-lg-center">
                        <div class="icon-box mt-5 mt-lg-0" data-aos="fade-up" data-aos-delay="100">
                            <i class="bx bx-receipt"></i>
                            <h4>اختيار فريق المشروع</h4>
                            <p>
                                يساعد تكوين الفريق في تشبييك وربط الطلاب مع بعضهم البعض وتعزيز
                                روح التعاون وتبادل الافكار والملاحظات بينهم التي تساعد في نجاح
                                المشروع .
                            </p>
                        </div>
                        <div class="icon-box mt-5 mt-lg-0" data-aos="fade-up" data-aos-delay="100">
                            <i class="bx bx-receipt"></i>
                            <h4>البحث عن فكرة نوعية</h4>
                            <p>
                                تساعد الطلاب في ايجاد فكرة مناسبة لهم بناءا على قدراتهم
                                وميولهم في تنفيذ هذه الفكرة وايضا مواكبة كل ما هو جديد من
                                الافكار التي تحل مشكلة معينة او تخدم فئة معينة في المجتمع .
                            </p>
                        </div>
                        <div class="icon-box mt-5 mt-lg-0" data-aos="fade-up" data-aos-delay="200">
                            <i class="bx bx-receipt"></i>
                            <h4>تواصل المشرفين مع الطلاب</h4>
                            <p>
                                تساعد في سهولة تواصل المشرف مع الطالب من خلال متابعة مسار
                                المشروع والية تنفيذه والعقبات التي تواجه الطالب .
                            </p>
                        </div>
                        <div class="icon-box mt-5 mt-lg-0" data-aos="fade-up" data-aos-delay="300">
                            <i class="bx bx-receipt"></i>
                            <h4>الموافقة وتقيم المشروع</h4>
                            <p>
                                تساهم في موافقة لجنة المشروع او المشرف على الفكرة التي طرحها
                                الطالب , وايضا معرفة تقييم المشروع في مرحلته النهائية بعد
                                المناقشة .
                            </p>
                        </div>
                    </div>
                    <div class="image col-lg-6 order-1 order-lg-2" data-aos="zoom-in" data-aos-delay="400">
                        <img src="assets/img/aa.png" alt="" class="img-fluid" />
                    </div>
                </div>
            </div>
        </section>
        <!-- End Features Section -->

        <!-- ======= Testimonials Section ======= -->
        <section id="testimonials" class="testimonials section-bg">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>طاقم الكلية</h2>
                    <p>
                        تضم الكلية بين جنباتها نخبة من المتخصصين في مجال تكنولوجيا
                        المعلومات من حملة شهادات الدكتوراه والماجستير من جامعات عالمية.
                        حيث يمتاز الطاقم بالتنوع و الحداثة في تخصصاتهم وذلك لمواكبة التطور
                        الهائل في هذا مجال. حيث أن الكلية تضم (14) عضواً برتبة استاذ
                        مساعد، و (4) عضواً برتبة ماجستير، وعدد من المعيدين من حملة
                        البكالوريوس.
                    </p>
                </div>

                <div class="testimonials-slider swiper" data-aos="fade-up" data-aos-delay="100">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="test">
                                    <img src="assets/img/dr/dr.mohammed.jpg" class="testimonial-img"
                                        alt="" />
                                    <h3>د.محمد عوض الله</h3>
                                    <h4>أستاذ مشارك</h4>
                                </div>
                            </div>
                        </div>
                        <!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="test">
                                    <img src="assets/img/dr/dr.mohammed_radi.jpg" class="testimonial-img"
                                        alt="" />
                                    <h3>د.محمد راضي</h3>
                                    <h4>أستاذ مساعد</h4>
                                </div>
                            </div>
                        </div>
                        <!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="test">
                                    <img src="assets/img/dr/dr.yousef.jpg" class="testimonial-img" alt="" />
                                    <h3>د.يوسف حمودة</h3>
                                    <h4>أستاذ مشارك</h4>
                                </div>
                            </div>
                        </div>
                        <!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="test">
                                    <img src="assets/img/dr/dr.yousef_yousef.jpg" class="testimonial-img"
                                        alt="" />
                                    <h3>د.يوسف يوسف</h3>
                                    <h4>أستاذ مساعد</h4>
                                </div>
                            </div>
                        </div>
                        <!-- End testimonial item -->

                        <div class="swiper-slide">
                            <div class="testimonial-item">
                                <div class="test">
                                    <img src="assets/img/dr/dr.abd.jpg" class="testimonial-img" alt="" />
                                    <h3>د.عبد الرافع الزاملي</h3>
                                    <h4>أستاذ مساعد</h4>
                                </div>
                            </div>
                        </div>
                        <!-- End testimonial item -->
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
        <!-- End Testimonials Section -->

        <!-- ======= Frequently Asked Questions Section ======= -->
        <section id="faq" class="faq">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>مراحل دورة حياة المشروع</h2>
                    <p>
                        هي مجموعة من الأنشطة التي يتم تطبيقها لتحقيق أهداف محددة في فترة
                        زمنية محددة، حيث ان اي مشروع بغض النظر عن طبيعته ومدته وحجم
                        نشاطاته، يمر بمراحل محددة وهي
                    </p>
                </div>

                <div class="faq-list">
                    <ul>
                        <li data-aos="fade-up" data-aos="fade-up" data-aos-delay="100">
                            <i class="bx bx-help-circle icon-help"></i>
                            <a data-bs-toggle="collapse" class="collapse" data-bs-target="#faq-list-1">التفكير في
                                المشروع:
                                <i class="bx bx-chevron-down icon-show"></i><i
                                    class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-1" class="collapse show" data-bs-parent=".faq-list">
                                <p>
                                    وهي المرحلة التي تقوم فيها بإبتكار فكرة للمشروع، وتبحث
                                    خلالها عن أولوية هذه الفكرة وجدواها.
                                </p>
                            </div>
                        </li>

                        <li data-aos="fade-up" data-aos-delay="200">
                            <i class="bx bx-help-circle icon-help"></i>
                            <a data-bs-toggle="collapse" data-bs-target="#faq-list-2" class="collapsed">التخطيط
                                للمشروع: <i class="bx bx-chevron-down icon-show"></i><i
                                    class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-2" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    وهي المرحلة التي ينتقل فيها المشروع من مجرد فكرة الى خطة
                                    توضح أهدافه ونشاطاته وخدماته والفئات الذين يخدمهم وكيف
                                    يستخدمهم.
                                </p>
                            </div>
                        </li>

                        <li data-aos="fade-up" data-aos-delay="300">
                            <i class="bx bx-help-circle icon-help"></i>
                            <a data-bs-toggle="collapse" data-bs-target="#faq-list-3" class="collapsed">
                                رصد الموارد:<i class="bx bx-chevron-down icon-show"></i><i
                                    class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-3" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    وهي المرحلة التي يتم فيها الموارد البشرية والمالية التي
                                    تحتاجها لتنفيذ المشروع، وتعيين الافراد وفرق العمل وتوزيع
                                    الأدوار والمسؤوليات عليهم.
                                </p>
                            </div>
                        </li>

                        <li data-aos="fade-up" data-aos-delay="400">
                            <i class="bx bx-help-circle icon-help"></i>
                            <a data-bs-toggle="collapse" data-bs-target="#faq-list-4" class="collapsed">
                                تطبيق المشروع: <i class="bx bx-chevron-down icon-show"></i><i
                                    class="bx bx-chevron-up icon-close"></i></a>
                            <div id="faq-list-4" class="collapse" data-bs-parent=".faq-list">
                                <p>
                                    وهي المرحلة التي يتم فيها بدء تنفيذ المشروع والعمل على إدارة
                                    أداء المشروع والتأكد من انه يجري وفق ما هو مخطط له وفي
                                    الاتجاه الصحيح.
                                </p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- End Frequently Asked Questions Section -->

        <!-- ======= Contact Section ======= -->
        <section id="contact" class="contact section-bg">
            <div class="container" data-aos="fade-up">
                <div class="section-title">
                    <h2>اتصل بنا</h2>
                    <p>للتواصل مع عمادة كلية الحاسبات وتكنولوجيا المعلومات</p>
                </div>

                <div class="row">
                    <div class="col-lg-6">
                        <div class="info-box mb-4">
                            <i class="bx bx-map"></i>
                            <h3>جامعة الأقصى – غزة</h3>
                            <p>غرفة GWH401 – مبني الوحدة</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="info-box mb-4">
                            <i class="bx bx-envelope"></i>
                            <h3>ايميل</h3>
                            <p>fcit@alaqsa.edu.ps</p>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="info-box mb-4">
                            <i class="bx bx-phone-call"></i>
                            <h3>تلفون</h3>
                            <p>97082641601+</p>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @include('layouts.admin.inc.alert')
                    <div class="col-lg-6">
                        <iframe class="mb-4 mb-lg-0"
                            src="https://www.google.com/maps/embed?pb=!1m19!1m8!1m3!1d1689.6688676903332!2d34.440154!3d31.510897000000003!3m2!1i1024!2i768!4f13.1!4m8!3e6!4m0!4m5!1s0x14fd7f418cfa8357%3A0x56d415183481113e!2z2KzYp9mF2LnYqSDYp9mE2KPZgti12YnYjCDYp9mE2LTYp9ix2Lkg2KfZhNi52YXZiNmF2Yog2YXYtdix2YEg2KfZhNiu2LXZiNi12Iwg2LrYstip!3m2!1d31.510886699999997!2d34.4407764!5e1!3m2!1sar!2s!4v1648203626448!5m2!1sar!2s"
                            frameborder="0" style="border: 0; width: 100%; height: 384px" allowfullscreen></iframe>
                    </div>

                    <div class="col-lg-6">
                        @php
                            $name = auth('admin')->check() ? auth('admin')->user()->name : (auth('supervisor')->check() ? auth('supervisor')->user()->name : (auth('student')->check() ? auth('student')->user()->name : ''));
                            $email = auth('admin')->check() ? auth('admin')->user()->email : (auth('supervisor')->check() ? auth('supervisor')->user()->email : (auth('student')->check() ? auth('student')->user()->email : ''));
                        @endphp
                        <form action="{{ route('site.send') }}" method="post" role="form"
                            class="php-email-form">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <input type="text" name="name"
                                        class="form-control @error('name') is-invalid @enderror" id="name"
                                        placeholder="الاسم" @if ($name) readonly @endif required
                                        value="{{ $name }}" />
                                </div>
                                <div class="col-md-6 form-group mt-3 mt-md-0">
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" id="email" placeholder="الايميل الخاص بك"
                                        @if ($email) readonly @endif required
                                        value="{{ $email }}" />
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                    name="subject" id="subject" placeholder="الموضوع" required />
                            </div>
                            <div class="form-group mt-3">
                                <textarea class="form-control @error('message') is-invalid @enderror" name="message" rows="5"
                                    placeholder="الرسالة .. " required></textarea>
                            </div>
                            <div class="my-3">
                                <div class="loading">تحميل ..</div>
                                <div class="error-message"></div>
                                <div class="sent-message">تم ارسال رسالتك. شكرا لك!</div>
                            </div>
                            <div class="text-center">
                                <button type="submit">ارسال</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Contact Section -->
    </main>
    <!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer">
        <div class="container">
            <div class="copyright-wrap d-md-flex py-4">
                <div class="me-md-auto text-center">
                    <div class="copyright">
                        جميع الحقوق محفوظة - كلية الحاسابات وتكنولوجيا المعلومات &copy;
                    </div>
                </div>
                <div class="social-links text-center text-md-right pt-3 pt-md-0">
                    <a href="https://www.facebook.com/jamal.taroush" class="twitter"><i
                            class="bx bxl-twitter"></i></a>
                    <a href="https://www.facebook.com/jamal.taroush" class="facebook"><i
                            class="bx bxl-facebook"></i></a>
                    <a href="https://www.facebook.com/jamal.taroush" class="instagram"><i
                            class="bx bxl-instagram"></i></a>
                    <a href="https://www.facebook.com/jamal.taroush" class="google-plus"><i
                            class="bx bxl-skype"></i></a>
                    <a href="https://www.facebook.com/jamal.taroush" class="linkedin"><i
                            class="bx bxl-linkedin"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets/vendor/purecounter/purecounter.js') }}"></script>
    <script src="{{ asset('assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/swiper/swiper-bundle.min.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>

</html>
