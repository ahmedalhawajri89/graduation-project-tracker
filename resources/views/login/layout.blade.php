{{--
    قشرة صفحات الدخول والاسترجاع.

    كانت صفحة الدخول تحمل القشرة كاملة داخلها. ومع إضافة صفحتَي
    «نسيت كلمة السر» و«تعيين كلمة سر جديدة» كانت ستُنسخ ثلاث مرات —
    والخطوط ومسار المنصّة والعلامة والتذييل تنحرف بينها مع أول تعديل.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title') — {{ __('تخرُّج') }}</title>
    <link rel="icon" href="{{ asset('assets/img/takharruj-logo.svg') }}">
    {{-- خطوط مستضافة محلياً بدل fonts.googleapis.com --}}
    <link rel="preload" href="{{ asset('assets/fonts/IBMPlexSansArabic-400-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link href="{{ asset('assets/fonts/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset(app()->getLocale() === 'ar' ? 'vendor/tabler/css/tabler.rtl.min.css' : 'vendor/tabler/css/tabler.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/tabler-icons/tabler-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}" rel="stylesheet">
</head>

<body class="auth-body">
    @include('login._path')

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
                <span class="auth-brand-name">{{ __('تخرُّج') }}</span>
            </div>

            @yield('card')

            <div class="text-center">
                @hasSection('back')
                    @yield('back')
                @else
                    <a href="{{ route('site.home') }}" class="auth-back">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M12 5l-7 7 7 7"/>
                        </svg>
                        {{ __('العودة إلى الموقع') }}
                    </a>
                @endif
            </div>
        </div>

        <p class="auth-foot">© {{ date('Y') }} {{ __('تخرُّج — جميع الحقوق محفوظة') }}</p>
    </main>

    <script>
        var authText = {
            show: @json(__('إظهار كلمة السر')),
            hide: @json(__('إخفاء كلمة السر')),
            busy: @json(__('جارٍ المعالجة…'))
        };

        // إظهار/إخفاء كلمة السر
        document.querySelectorAll('.toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                if (!input) return;
                var icon = btn.querySelector('i');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.classList.toggle('ti-eye', !show);
                icon.classList.toggle('ti-eye-off', show);
                btn.setAttribute('aria-label', show ? authText.hide : authText.show);
            });
        });

        // حالة تحميل الزرّ لمنع الإرسال المزدوج — وهو وارد هنا
        // خصوصاً: المستخدم قلق فيضغط مرّتين
        var form = document.querySelector('.login-card form');
        if (form) {
            form.addEventListener('submit', function (e) {
                var btn = e.target.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>'
                        + (btn.dataset.loading || authText.busy);
                }
            });
        }
    </script>
</body>

</html>
