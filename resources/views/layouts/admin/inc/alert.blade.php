{{--
    تنبيهات منبثقة.

    كانت \u200Etop-0 start-0\u200E — أي أعلى اليمين في RTL، فوق السايدبار وشريط
    البحث تماماً: تحجب التنقّل وقت ما يحتاجه المستخدم. صارت أسفل
    الجهة الأخرى، بعيداً عن السايدبار وعن الترويسة معاً.

    وقائمة أخطاء التحقّق كانت تُسرد كاملة بلا إخفاء تلقائي — وهي
    مكرّرة أصلاً تحت كل حقل. التنبيه يقول «راجع الحقول المعلَّمة»
    والتفصيل حيث يُصلَح.

    السلوك (شريط الوقت، الإيقاف عند المرور، الإغلاق) في \u200Epublic/js/dialog.js\u200E،
    وهو نفسه ما ترسم به \u200EappToast()\u200E التنبيهات الحيّة.
--}}

@php
    $hasErrors = $errors->any();
    $errorCount = $errors->count();
    $toasts = [];
    if (Session::get('success')) {
        $toasts[] = ['success', 'ti-check', __('تمّ بنجاح'), Session::get('success'), 4500];
    }
    if (Session::get('fail')) {
        $toasts[] = ['danger', 'ti-x', __('تعذّر الإتمام'), Session::get('fail'), 7000];
    }
    if ($hasErrors) {
        $toasts[] = ['warning', 'ti-alert-triangle', __('راجع البيانات'), $errorCount === 1
            ? $errors->first()
            : __('تعذّر الحفظ — :n حقول تحتاج مراجعة، وهي معلَّمة بالأحمر.', ['n' => $errorCount]), 7000];
    }
@endphp

<div class="toast-stack" role="region" aria-label="{{ __('تنبيهات') }}">
    @foreach ($toasts as [$type, $icon, $title, $text, $delay])
        <div class="app-toast is-{{ $type }}" role="{{ $type === 'success' ? 'status' : 'alert' }}" data-delay="{{ $delay }}">
            <span class="app-toast-icon" aria-hidden="true"><i class="ti {{ $icon }}"></i></span>
            <div class="app-toast-body">
                <b class="app-toast-title">{{ $title }}</b>
                <p class="app-toast-text">{{ $text }}</p>
            </div>
            <button type="button" class="app-toast-close" aria-label="{{ __('إغلاق') }}">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
            <span class="app-toast-timer" aria-hidden="true"></span>
        </div>
    @endforeach
</div>
