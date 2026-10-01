{{--
    تنبيهات منبثقة.

    كانت \u200Etop-0 start-0\u200E — أي أعلى اليمين في RTL، فوق السايدبار وشريط
    البحث تماماً: تحجب التنقّل وقت ما يحتاجه المستخدم. صارت أسفل
    الجهة الأخرى، بعيداً عن السايدبار وعن الترويسة معاً.

    وقائمة أخطاء التحقّق كانت تُسرد كاملة بلا إخفاء تلقائي — وهي
    مكرّرة أصلاً تحت كل حقل. التنبيه يقول «راجع الحقول المعلَّمة»
    والتفصيل حيث يُصلَح.
--}}

@php
    $hasErrors = $errors->any();
    $errorCount = $errors->count();
@endphp

<div class="toast-stack" role="region" aria-label="{{ __('تنبيهات') }}">

    @if (Session::get('success'))
        <div class="app-toast is-success" role="status" aria-live="polite" data-delay="4000">
            <span class="app-toast-bar" aria-hidden="true"></span>
            <i class="ti ti-circle-check app-toast-icon" aria-hidden="true"></i>
            <p class="app-toast-text">{{ Session::get('success') }}</p>
            <button type="button" class="app-toast-close" aria-label="{{ __('إغلاق') }}">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
        </div>
    @endif

    @if (Session::get('fail'))
        <div class="app-toast is-danger" role="alert" aria-live="assertive" data-delay="7000">
            <span class="app-toast-bar" aria-hidden="true"></span>
            <i class="ti ti-alert-circle app-toast-icon" aria-hidden="true"></i>
            <p class="app-toast-text">{{ Session::get('fail') }}</p>
            <button type="button" class="app-toast-close" aria-label="{{ __('إغلاق') }}">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
        </div>
    @endif

    @if ($hasErrors)
        <div class="app-toast is-warning" role="alert" aria-live="assertive" data-delay="7000">
            <span class="app-toast-bar" aria-hidden="true"></span>
            <i class="ti ti-alert-triangle app-toast-icon" aria-hidden="true"></i>
            <p class="app-toast-text">
                @if ($errorCount === 1)
                    {{ $errors->first() }}
                @else
                    {{ __('تعذّر الحفظ — :n حقول تحتاج مراجعة، وهي معلَّمة بالأحمر.', ['n' => $errorCount]) }}
                @endif
            </p>
            <button type="button" class="app-toast-close" aria-label="{{ __('إغلاق') }}">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
        </div>
    @endif

</div>

@if (Session::get('success') || Session::get('fail') || $hasErrors)
    @push('js')
        <script>
            (function () {
                // بلا Bootstrap Toast: سلوكه يخفي العنصر بـ \u200Edisplay:none\u200E
                // فيقفز ما تحته، وتوقيته لا يتوقّف عند مرور الفأرة.
                document.querySelectorAll('.app-toast').forEach(function (toast, i) {
                    var delay = parseInt(toast.dataset.delay || '5000', 10);
                    var timer = null;

                    // تتابع بسيط في الظهور حين يكون أكثر من واحد
                    toast.style.animationDelay = (i * 90) + 'ms';

                    function dismiss() {
                        toast.classList.add('is-leaving');
                        toast.addEventListener('animationend', function () {
                            toast.remove();
                        }, { once: true });
                    }

                    function start() { timer = window.setTimeout(dismiss, delay); }
                    function stop() { window.clearTimeout(timer); }

                    // القراءة لا تُقاطَع: المؤقّت يتوقّف عند المرور أو
                    // التركيز بلوحة المفاتيح
                    toast.addEventListener('mouseenter', stop);
                    toast.addEventListener('focusin', stop);
                    toast.addEventListener('mouseleave', start);
                    toast.addEventListener('focusout', start);

                    toast.querySelector('.app-toast-close').addEventListener('click', function () {
                        stop();
                        dismiss();
                    });

                    start();
                });
            })();
        </script>
    @endpush
@endif
