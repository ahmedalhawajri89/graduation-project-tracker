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
        <span class="node done n1"></span><span class="step n1" data-l="s1">تقديم الطلب</span>
        <span class="node done n2"></span><span class="step n2" data-l="s2">موافقة المشرف</span>
        <span class="node todo n3"></span><span class="step n3" data-l="s3">متابعة التنفيذ</span>
        <span class="node todo n4"></span><span class="step n4" data-l="s4">المناقشة والتقييم</span>
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
            <span class="auth-brand-name" data-l="brand">تخرُّج</span>
            {{-- اللغة نفسها التي اختارها الزائر في الموقع (localStorage.locale) --}}
            <button type="button" class="auth-lang" data-auth-lang aria-label="Switch language">EN</button>
            </div>

            <h2 data-l="title">تسجيل الدخول</h2>
            {{-- السطر يشرح اكتشاف الدور التلقائي في AuthController@login --}}
            <p class="login-lead" data-l="lead">
                ادخل بالرقم الجامعي أو بريدك الإلكتروني — وسيوجّهك النظام إلى لوحتك حسب دورك.
            </p>

            @if (Session::get('fail'))
                <div class="alert" role="alert" data-l-msg>{{ Session::get('fail') }}</div>
            @endif

            <form action="{{ route('login.check') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3 auth-field">
                    <label class="form-label" for="identify" data-l="identify">البريد الإلكتروني أو الرقم الجامعي</label>
                    <input id="identify" type="text"
                        class="form-control @error('identify') is-invalid @enderror" name="identify"
                        value="{{ old('identify') }}" placeholder="مثال: 2300000238" data-l-ph="identifyPh" required
                        autocomplete="username" autofocus>
                    @error('identify')
                        <div class="invalid-feedback" data-l-msg>{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 auth-field">
                    <label class="form-label" for="password" data-l="password">كلمة السر</label>
                    <div class="password-wrapper">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            required autocomplete="current-password">
                        <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                            data-target="password">
                            <i class="ti ti-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block" data-l-msg>{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-login" data-l="submit">دخول</button>
                </div>

                {{-- بلا مدخل ظاهر هنا لا قيمة لمسار الاسترجاع: من نسي
                     كلمته لا يبحث عن رابط في صفحة أخرى --}}
                <p class="auth-forgot">
                    <a href="{{ route('password.request') }}" data-l="forgot">نسيت كلمة السر؟</a>
                </p>
            </form>

            {{-- يجيب السؤال الأكثر وروداً في نموذج التواصل: أين أسجّل؟ --}}
            <p class="auth-note" data-l="note">
                لا يوجد تسجيل ذاتي — الحسابات تُنشئها إدارة القسم.
                راجعهم إن تعذّر عليك الدخول.
            </p>

            <div class="text-center">
                <a href="{{ route('site.home') }}" class="auth-back">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    <span data-l="back">العودة إلى الموقع</span>
                </a>
            </div>
        </div>

        <p class="auth-foot">© {{ date('Y') }} <span data-l="rights">تخرُّج — جميع الحقوق محفوظة</span></p>
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
                btn.setAttribute('aria-label', show ? window.__authText.hide : window.__authText.show);
            });
        });

        // لغة الصفحة: نصوصها الثابتة ورسائل الخادم المعروفة. لوحات التحكم عربية فقط.
        (function () {
            var L = {
                en: {
                    brand: 'Takharruj', title: 'Sign in',
                    lead: 'Use your university ID or email — the system takes you to your dashboard based on your role.',
                    identify: 'Email or university ID', identifyPh: 'e.g. 2300000238', password: 'Password',
                    submit: 'Sign in', forgot: 'Forgot your password?',
                    note: 'There is no self sign-up — accounts are created by the department. Contact them if you cannot sign in.',
                    back: 'Back to the site', rights: 'Takharruj — All rights reserved',
                    s1: 'Request', s2: 'Supervisor approval', s3: 'Execution', s4: 'Defense & grading',
                    docTitle: 'Sign in — Takharruj', busy: 'Signing in…', show: 'Show password', hide: 'Hide password',
                    msgs: {
                        'أدخل بريدك الإلكتروني أو رقمك الجامعي.': 'Enter your email or university ID.',
                        'أدخل كلمة السر.': 'Enter your password.',
                        'بيانات الدخول غير صحيحة. تأكد من البريد/الرقم الجامعي وكلمة السر.': 'Those details did not match. Check your email/university ID and password.'
                    }
                }
            };
            var ar = { busy: 'جارٍ الدخول…', show: 'إظهار كلمة السر', hide: 'إخفاء كلمة السر', docTitle: document.title };
            var loc = 'ar';
            try { loc = localStorage.getItem('locale') === 'en' ? 'en' : 'ar'; } catch (e) {}
            // النص العربي الأصلي محفوظ للعودة إليه
            document.querySelectorAll('[data-l]').forEach(function (el) { el.dataset.ar = el.textContent; });
            document.querySelectorAll('[data-l-ph]').forEach(function (el) { el.dataset.ar = el.placeholder; });
            document.querySelectorAll('[data-l-msg]').forEach(function (el) { el.dataset.ar = el.textContent.trim(); });

            function apply(l) {
                var d = L[l], html = document.documentElement, btn = document.querySelector('[data-auth-lang]');
                html.lang = l; html.dir = l === 'en' ? 'ltr' : 'rtl';
                document.querySelectorAll('[data-l]').forEach(function (el) { el.textContent = d ? d[el.dataset.l] : el.dataset.ar; });
                document.querySelectorAll('[data-l-ph]').forEach(function (el) { el.placeholder = d ? d[el.dataset.lPh] : el.dataset.ar; });
                document.querySelectorAll('[data-l-msg]').forEach(function (el) { el.textContent = (d && d.msgs[el.dataset.ar]) || el.dataset.ar; });
                document.title = (d || ar).docTitle;
                window.__authText = d || ar;
                if (btn) { btn.textContent = l === 'en' ? 'ع' : 'EN'; btn.setAttribute('aria-label', l === 'en' ? 'التبديل إلى العربية' : 'Switch to English'); }
                loc = l;
            }
            apply(loc);
            var b = document.querySelector('[data-auth-lang]');
            if (b) b.addEventListener('click', function () {
                var next = loc === 'en' ? 'ar' : 'en';
                try { localStorage.setItem('locale', next); } catch (e) {}
                apply(next);
            });
        })();

        // حالة تحميل زر الدخول لمنع الإرسال المزدوج
        document.querySelector('form').addEventListener('submit', function (e) {
            var btn = e.target.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + window.__authText.busy;
            }
        });
    </script>
</body>

</html>
