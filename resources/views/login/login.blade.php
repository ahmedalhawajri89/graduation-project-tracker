<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>تسجيل الدخول — تخرُّج</title>
    <link rel="icon" href="{{ asset('assets/img/takharruj-logo.svg') }}">
    {{-- خطوط مستضافة محلياً بدل fonts.googleapis.com --}}
    <link rel="preload" href="{{ asset('assets/fonts/IBMPlexSansArabic-400-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link href="{{ asset('assets/fonts/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/tabler/css/tabler.rtl.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/tabler-icons/tabler-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}" rel="stylesheet">
</head>

<body class="auth-body">
    {{-- مسار المنصة يمرّ خلف البطاقة: مرحلتان مضتا يميناً واثنتان قادمتان يساراً --}}
    <div class="auth-path" aria-hidden="true">
        <span class="line"></span>
        <span class="pulse-track"><span class="pulse"></span></span>
        <span class="node done n1"></span><span class="step n1">تقديم الطلب</span>
        <span class="node done n2"></span><span class="step n2">موافقة المشرف</span>
        <span class="node todo n3"></span><span class="step n3">متابعة التنفيذ</span>
        <span class="node todo n4"></span><span class="step n4">المناقشة والتقييم</span>
    </div>

    <main class="auth-col">
        <div class="login-card @if (Session::get('fail') || $errors->any()) has-error @endif">
            <div class="auth-brand">
            {{-- العلامة نفسها المستعملة في هيدر الموقع: عقد على مسار --}}
            <span class="auth-mark" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 32 32" fill="none">
                    <path d="M6 16h20" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="6" cy="16" r="3.2" fill="currentColor"/>
                    <circle cx="16" cy="16" r="3.2" fill="currentColor"/>
                    <circle cx="26" cy="16" r="3.2" fill="none" stroke="currentColor" stroke-width="2"/>
                </svg>
            </span>
            <span class="auth-brand-name">تخرُّج</span>
            </div>

            <h2>تسجيل الدخول</h2>
            {{-- السطر يشرح اكتشاف الدور التلقائي في AuthController@login --}}
            <p class="login-lead">
                ادخل بالرقم الجامعي أو بريدك الإلكتروني — وسيوجّهك النظام إلى لوحتك حسب دورك.
            </p>

            @if (Session::get('fail'))
                <div class="alert" role="alert">{{ Session::get('fail') }}</div>
            @endif

            <form action="{{ route('login.check') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3 auth-field">
                    <label class="form-label" for="identify">البريد الإلكتروني أو الرقم الجامعي</label>
                    <input id="identify" type="text"
                        class="form-control @error('identify') is-invalid @enderror" name="identify"
                        value="{{ old('identify') }}" placeholder="مثال: 2300000238" required
                        autocomplete="username" autofocus>
                    @error('identify')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 auth-field">
                    <label class="form-label" for="password">كلمة السر</label>
                    <div class="password-wrapper">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                            data-target="password">
                            <i class="ti ti-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-login">دخول</button>
                </div>

                {{-- بلا مدخل ظاهر هنا لا قيمة لمسار الاسترجاع: من نسي
                     كلمته لا يبحث عن رابط في صفحة أخرى --}}
                <p class="auth-forgot">
                    <a href="{{ route('password.request') }}">نسيت كلمة السر؟</a>
                </p>
            </form>

            {{-- يجيب السؤال الأكثر وروداً في نموذج التواصل: أين أسجّل؟ --}}
            <p class="auth-note">
                لا يوجد تسجيل ذاتي — الحسابات تُنشئها إدارة القسم.
                راجعهم إن تعذّر عليك الدخول.
            </p>

            <div class="text-center">
                <a href="{{ route('site.home') }}" class="auth-back">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    العودة إلى الموقع
                </a>
            </div>
        </div>

        <p class="auth-foot">© {{ date('Y') }} تخرُّج — جميع الحقوق محفوظة</p>
    </main>

    <script>
        // إظهار/إخفاء كلمة السر
        document.querySelectorAll('.toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                var icon = btn.querySelector('i');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.classList.toggle('ti-eye', !show);
                icon.classList.toggle('ti-eye-off', show);
                btn.setAttribute('aria-label', show ? 'إخفاء كلمة السر' : 'إظهار كلمة السر');
            });
        });

        // حالة تحميل زر الدخول لمنع الإرسال المزدوج
        document.querySelector('form').addEventListener('submit', function (e) {
            var btn = e.target.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>جارٍ الدخول…';
            }
        });
    </script>
</body>

</html>
