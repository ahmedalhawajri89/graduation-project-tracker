{{--
    الجولة التعريفية — أول دخول لكل مستخدم (وتُعاد من قائمة المستخدم).
    شخصية الدور (من partials/cast) تعرّف باللوحة في خطوات قصيرة: تُضاء
    العناصر واحداً واحداً وتعتم الصفحة حولها. السلوك في public/js/tour.js؛
    الخطوة التي لا يظهر عنصرها (كالشريط الجانبي على الجوال) تُتخطّى.
--}}
@php
    $tourGuard = collect(['admin', 'supervisor', 'student'])->first(fn ($g) => auth()->guard($g)->check());
    $tourUser = auth()->guard($tourGuard)->user();
    $firstName = \Illuminate\Support\Str::of($tourUser->name)->replaceMatches('/^\s*(أ\.د\.|د\.|أ\.)\s*/u', '')->explode(' ')->first();
    $tourSteps = array_values(array_filter([
        [
            'title' => __('أهلاً :name 👋', ['name' => $firstName]),
            'text' => match ($tourGuard) {
                'student' => __('هنا تتابع مشروعك من الفكرة حتى الدرجة: فريقك ومراحلك وملفاتك ونقاشك مع المشرف.'),
                'supervisor' => __('هنا تتابع مجموعاتك: الطلبات، وخطة المراحل، ومراجعة التسليمات، والدرجات.'),
                default => __('هنا تضبط الفصل كلّه: الحسابات والتخصصات والمجموعات والمناقشات وسجلّ القرارات.'),
            },
        ],
        $tourGuard === 'student' ? ['target' => '.stu-hero', 'title' => __('مشروعك ومساره'), 'text' => __('فريقك يقف عند مرحلتك الحالية، والحلقة تقول كم أنجزتم.')] : null,
        ['target' => '.sidebar .navbar-nav', 'title' => __('كل صفحاتك هنا'), 'text' => __('القائمة الجانبية فيها كل ما تحتاجه، والأرقام بجانبها تتحدّث وحدها.')],
        ['target' => '.topbar-burger', 'title' => __('القائمة'), 'text' => __('كل صفحاتك في هذه القائمة.')],
        ['target' => '.cmdk-trigger', 'title' => __('بحث سريع'), 'text' => __('اكتب اسم أي صفحة وانتقل إليها — أو اضغط Ctrl K من أي مكان.')],
        ['target' => '.topbar-bell', 'title' => __('الإشعارات'), 'text' => __('تصلك لحظياً دون إعادة تحميل: ردّ المشرف، تسليم جديد، موعد قريب.')],
        ['target' => '.user-trigger', 'title' => __('حسابك'), 'text' => __('ملفك الشخصي واللغة، وهذه الجولة إن أردت رؤيتها مجدداً.')],
    ]));
    $tourWho = ['student' => 'hj-girl', 'supervisor' => 'hj-supervisor', 'admin' => 'hj-admin'][$tourGuard];
    $tourText = ['next' => __('التالي'), 'prev' => __('السابق'), 'done' => __('ابدأ الآن'), 'skip' => __('تخطَّ'), 'of' => __(':n من :total')];
@endphp
<div hidden data-tour
    data-tour-key="tour:v1:{{ $tourGuard }}:{{ $tourUser->id }}"
    data-tour-who="{{ $tourWho }}"
    data-tour-steps="{{ json_encode($tourSteps, JSON_UNESCAPED_UNICODE) }}"
    data-tour-text="{{ json_encode($tourText, JSON_UNESCAPED_UNICODE) }}"></div>
<script src="{{ asset('js/tour.js') }}?v={{ filemtime(public_path('js/tour.js')) }}" defer></script>
