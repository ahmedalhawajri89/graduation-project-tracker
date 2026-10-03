<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ __('تسجيل الدخول — تخرُّج') }}</title>
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

    @php($isEn = app()->getLocale() === 'en')
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
            {{-- التبديل يمرّ بالخادم (كوكي locale) ليتبعه الدخول واللوحة --}}
            {{-- اسم اللغة الأخرى بلغتها هي، فلا يُترجَم --}}
            <button type="button" class="auth-lang" data-auth-lang data-next="{{ $isEn ? 'ar' : 'en' }}"
                lang="{{ $isEn ? 'ar' : 'en' }}"
                aria-label="{{ $isEn ? 'التبديل إلى العربية' : 'Switch to English' }}">{{ $isEn ? 'ع' : 'EN' }}</button>
            </div>

            <h2>{{ __('تسجيل الدخول') }}</h2>
            {{-- السطر يشرح اكتشاف الدور التلقائي في AuthController@login --}}
            <p class="login-lead">
                {{ __('ادخل بالرقم الجامعي أو بريدك الإلكتروني — وسيوجّهك النظام إلى لوحتك حسب دورك.') }}
            </p>

            @if (Session::get('fail'))
                <div class="alert" role="alert">{{ Session::get('fail') }}</div>
            @endif

            <form action="{{ route('login.check') }}" method="POST" novalidate>
                @csrf

                <div class="mb-3 auth-field">
                    <label class="form-label" for="identify">{{ __('البريد الإلكتروني أو الرقم الجامعي') }}</label>
                    <input id="identify" type="text"
                        class="form-control @error('identify') is-invalid @enderror" name="identify"
                        value="{{ old('identify') }}" placeholder="{{ __('مثال: 2300000238') }}" required
                        autocomplete="username" autofocus>
                    @error('identify')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4 auth-field">
                    <label class="form-label" for="password">{{ __('كلمة السر') }}</label>
                    <div class="password-wrapper">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror" name="password"
                            required autocomplete="current-password">
                        <button type="button" class="toggle-password" aria-label="{{ __('إظهار كلمة السر') }}"
                            data-target="password">
                            <i class="ti ti-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="auth-actions">
                    <button type="submit" class="btn btn-login">{{ __('دخول') }}</button>
                </div>

                {{-- بلا مدخل ظاهر هنا لا قيمة لمسار الاسترجاع: من نسي
                     كلمته لا يبحث عن رابط في صفحة أخرى --}}
                <p class="auth-forgot">
                    <a href="{{ route('password.request') }}">{{ __('نسيت كلمة السر؟') }}</a>
                </p>
            </form>

            {{-- النسخة التجريبية وحدها: دخول بنقرة بلا تسجيل (DemoController) --}}
            @if (\App\Support\Demo::enabled())
                <div class="demo-try">
                    <p class="demo-try-title"><i class="ti ti-sparkles" aria-hidden="true"></i> {{ __('جرّب المنصّة بنقرة — بلا تسجيل') }}</p>
                    <div class="demo-try-roles">
                        @foreach ([
                            ['student', 'ti-school', __('جرّب كطالب'), __('مشروع ومراحل ونقاش ومناقشة')],
                            ['supervisor', 'ti-user-check', __('جرّب كمشرف'), __('مجموعات وطلبات ولجنة مناقشة')],
                            ['admin', 'ti-adjustments', __('جرّب كمسؤول'), __('مخطِّط المناقشات والإحصائيات')],
                        ] as [$role, $icon, $label, $hint])
                            <form method="POST" action="{{ route('demo.enter', $role) }}">
                                @csrf
                                <button type="submit" class="demo-role is-{{ $role }}">
                                    <span class="demo-role-icon" aria-hidden="true"><i class="ti {{ $icon }}"></i></span>
                                    <span class="demo-role-text"><b>{{ $label }}</b><small>{{ $hint }}</small></span>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- يجيب السؤال الأكثر وروداً في نموذج التواصل: أين أسجّل؟ --}}
            <p class="auth-note">
                {{ __('لا يوجد تسجيل ذاتي — الحسابات تُنشئها إدارة القسم. راجعهم إن تعذّر عليك الدخول.') }}
            </p>

            <div class="text-center">
                <a href="{{ route('site.home') }}" class="auth-back">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    <span>{{ __('العودة إلى الموقع') }}</span>
                </a>
            </div>
        </div>

        <p class="auth-foot">© {{ date('Y') }} <span>{{ __('تخرُّج — جميع الحقوق محفوظة') }}</span></p>
    </main>

    <script>
        var authText = {
            show: @json(__('إظهار كلمة السر')),
            hide: @json(__('إخفاء كلمة السر')),
            busy: @json(__('جارٍ الدخول…'))
        };

        // إظهار/إخفاء كلمة السر
        document.querySelectorAll('.toggle-password').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(btn.dataset.target);
                var icon = btn.querySelector('i');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.classList.toggle('ti-eye', !show);
                icon.classList.toggle('ti-eye-off', show);
                btn.setAttribute('aria-label', show ? authText.hide : authText.show);
            });
        });

        // اللغة من الخادم (كوكي locale)، والتبديل يمرّ به ليتبعه الدخول واللوحة.
        // تُحفظ أيضاً لصفحات الموقع العامة التي تقرأ localStorage
        var langBtn = document.querySelector('[data-auth-lang]');
        if (langBtn) langBtn.addEventListener('click', function () {
            var next = langBtn.dataset.next;
            try { localStorage.setItem('locale', next); } catch (e) {}
            window.location.href = @json(url('/locale')) + '/' + next;
        });

        // حالة تحميل زر الدخول لمنع الإرسال المزدوج
        document.querySelector('form').addEventListener('submit', function (e) {
            var btn = e.target.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>' + authText.busy;
            }
        });
    </script>
</body>

</html>
