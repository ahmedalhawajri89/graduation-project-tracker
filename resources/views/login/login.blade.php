<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>تسجيل الدخول — نظام متابعة مشاريع التخرج</title>
    <link rel="icon" href="{{ asset('assets/img/1.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('vendor/fonts/cairo.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/tabler/css/tabler.rtl.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/tabler-icons/tabler-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
</head>

<body class="auth-body">
    <div class="login-split">

        {{-- لوحة العلامة --}}
        <aside class="login-brand">
            <div class="brand-inner">
                <div class="brand-logo">
                    <img src="{{ asset('assets/img/1.png') }}" alt="شعار النظام">
                </div>
                <h1>نظام متابعة مشاريع التخرج</h1>
                <p class="brand-sub">جامعة الأقصى</p>
                <p class="brand-tag">
                    منصّة موحّدة لإدارة ومتابعة مشاريع تخرّج الطلاب — من تسجيل المشروع حتى تقييمه النهائي.
                </p>
                <ul class="brand-features">
                    <li><span class="fchip"><i class="ti ti-check"></i></span> متابعة حالة المشروع لحظة بلحظة</li>
                    <li><span class="fchip"><i class="ti ti-check"></i></span> تواصل مباشر بين الطالب والمشرف</li>
                    <li><span class="fchip"><i class="ti ti-check"></i></span> إدارة المجموعات والتخصّصات بسهولة</li>
                </ul>
            </div>
            <div class="brand-foot">© {{ date('Y') }} جامعة الأقصى — جميع الحقوق محفوظة</div>
        </aside>

        {{-- النموذج --}}
        <main class="login-form-side">
            <div class="login-card">

                <div class="mobile-logo d-lg-none text-center mb-4">
                    <img src="{{ asset('assets/img/1.png') }}" alt="شعار النظام">
                </div>

                <h2>أهلاً بعودتك 👋</h2>
                <p class="login-lead">سجّل الدخول للمتابعة إلى لوحتك.</p>

                @if (Session::get('fail'))
                    <div class="alert alert-danger" role="alert">
                        <div class="d-flex">
                            <i class="ti ti-alert-circle fs-2 me-2"></i>
                            <div>{{ Session::get('fail') }}</div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('login.check') }}" method="POST" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="identify">
                            <i class="ti ti-user me-1"></i>
                            البريد الإلكتروني أو الرقم الجامعي
                        </label>
                        <input id="identify" type="text"
                            class="form-control @error('identify') is-invalid @enderror" name="identify"
                            value="{{ old('identify') }}" placeholder="example@alaqsa.edu.ps" required
                            autocomplete="username" autofocus>
                        @error('identify')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="password">
                            <i class="ti ti-lock me-1"></i>
                            كلمة السر
                        </label>
                        <div class="password-wrapper">
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password"
                                placeholder="••••••••" required autocomplete="current-password">
                            <button type="button" class="toggle-password" aria-label="إظهار كلمة السر"
                                data-target="password">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="btn btn-login w-100">
                            <i class="ti ti-login-2 me-1"></i>
                            تسجيل الدخول
                        </button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <a href="{{ route('site.home') }}" class="text-secondary text-decoration-none">
                        <i class="ti ti-arrow-right me-1"></i>
                        العودة إلى الموقع الرئيسي
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="{{ asset('vendor/tabler/js/tabler.min.js') }}"></script>
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
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>جارٍ الدخول...';
            }
        });
    </script>
</body>

</html>
