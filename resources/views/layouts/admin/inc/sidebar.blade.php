@php
    if (auth()->guard('admin')->check()) {
        $sbRole = 'مسؤول النظام';
        $sbNotifyRoute = route('admin.contact.index');
    } elseif (auth()->guard('supervisor')->check()) {
        $sbRole = 'مشرف أكاديمي';
        $sbNotifyRoute = route('supervisor.showNotification');
    } else {
        $sbRole = 'طالب';
        $sbNotifyRoute = route('student.showNotification');
    }
    // من الـ View Composer — محسوبة مرة واحدة للطلب
    $sbUnread = $layoutShared['unreadCount'] ?? 0;
    $sbProject = $layoutShared['studentProject'] ?? null;
@endphp

{{-- حُذف data-bs-theme="dark": كان يفرض ألوان نصّ الوضع الداكن على
     خلفية صارت فاتحة، فتبقى النصوص بيضاء غير مقروءة. --}}
<aside class="navbar navbar-vertical navbar-expand-lg sidebar">
    <div class="container-fluid d-flex flex-column h-100">

        {{-- الشعار --}}
        <h1 class="navbar-brand sidebar-brand">
            <a href="{{ url('/') }}" class="brand-link text-decoration-none" aria-label="تخرُّج — الرئيسية">
                {{-- علامة الموقع نفسها — عقد على مسار، آخرها التخرّج. مضمّنة لا <img>:
                     المسار يُرسم عند التحميل والعقد تضيء تباعاً، والأخيرة تمتلئ عند المرور --}}
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 40 40">
                        <defs>
                            <linearGradient id="bm-tile" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0" stop-color="#3b82f6" />
                                <stop offset="1" stop-color="#1d4ed8" />
                            </linearGradient>
                        </defs>
                        <rect width="40" height="40" rx="11" fill="url(#bm-tile)" />
                        <path class="bm-rail" d="M10.5 20h19" stroke="#fff" stroke-opacity=".55" stroke-width="2" stroke-linecap="round" pathLength="1" />
                        <circle class="bm-node bm-n1" cx="10.5" cy="20" r="3.4" fill="#fff" />
                        <circle class="bm-node bm-n2" cx="20" cy="20" r="3.4" fill="#fff" />
                        <circle class="bm-node bm-n3" cx="29.5" cy="20" r="3.4" fill="#1d4ed8" stroke="#fff" stroke-width="2" />
                    </svg>
                    <span class="brand-shine"></span>
                </span>
                <span class="sidebar-brand-text">
                    <b>تخرُّج<i class="brand-dot"></i></b>
                    <small>متابعة مشاريع التخرج</small>
                </span>
            </a>
        </h1>

        {{-- شريط إشعارات الموبايل حُذف: الهيدر صار ظاهراً على كل
             المقاسات ويحمل الجرس وقائمة المستخدم. --}}

        <div class="collapse navbar-collapse d-lg-flex flex-column flex-grow-1" id="sidebar-menu">

            {{-- مساحة العمل: الفصل النشط. داخل القائمة المطويّة لا فوقها: كانت
                 تسبق شريط التنقّل على الجوال فتأخذ ~١٤٥ بكسل قبل أي محتوى --}}
            @isset($viewSemester)
                {{-- الفصل والسنة منفصلان: كان الاسم كاملاً يلتفّ على سطرين --}}
                @php $sbSem = $viewSemester->parts(); @endphp
                <div class="sidebar-context sem-card" title="{{ $viewSemester->label }}">
                    <span class="sem-icon" aria-hidden="true"><i class="ti ti-calendar-event"></i></span>
                    <span class="sidebar-context-text">
                        {{-- السنة في سطر التسمية لا بجانب الاسم: كانت تأخذ عرضه فيُقصّ
                             «الفصل الأوّل» إلى «الفصل الأو…» حين يضيق الشريط --}}
                        <small>
                            <span class="sem-live" aria-hidden="true"></span>الفصل الحالي
                            @if ($sbSem['year'])
                                <span class="sem-year" dir="ltr">{{ $sbSem['year'] }}</span>
                            @endif
                        </small>
                        <b>{{ $sbSem['term'] }}</b>
                    </span>
                </div>
            @endisset

            {{-- الأدمن لديه تسميات أقسام داخلية خاصة به --}}
            @unless (auth()->guard('admin')->check())
                <div class="sidebar-label">القائمة الرئيسية</div>
            @endunless

            <ul class="navbar-nav">
                @if (auth()->guard('admin')->check())
                    @include('layouts.admin.inc.sidebar.admin')
                @endif
                @if (auth()->guard('supervisor')->check())
                    @include('layouts.admin.inc.sidebar.supervisor')
                @endif
                @if (auth()->guard('student')->check())
                    @include('layouts.admin.inc.sidebar.student')
                @endif
            </ul>

            <div class="mt-auto w-100">

                {{-- بطاقة حالة المشروع (طالب فقط) --}}
                @if ($sbProject)
                    <a href="{{ route('student.dashboard') }}" class="sidebar-project" title="مشروعك">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-briefcase"></i>
                            <span class="text-truncate fw-bold">{{ $sbProject->title }}</span>
                        </div>
                        @if (!is_null($sbProject->progress))
                            <div class="sidebar-project-track">
                                <div class="sidebar-project-fill" style="width: {{ $sbProject->progress }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1 sidebar-project-meta">
                                <span>الإنجاز</span>
                                <span>{{ $sbProject->progress }}%</span>
                            </div>
                        @else
                            <div class="sidebar-project-meta">
                                <x-status-badge :status="$sbProject->status" />
                            </div>
                        @endif
                    </a>
                @endif

                {{-- تسجيل الخروج (موبايل فقط — الهيدر مخفي هناك) --}}
                <div class="sidebar-foot d-lg-none">
                    <a href="{{ route('logout') }}" class="sidebar-logout"
                        onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                        <i class="ti ti-logout"></i>
                        <span>تسجيل خروج</span>
                    </a>
                    <form action="{{ route('logout') }}" method="post" id="logout-form-sidebar">@csrf</form>
                </div>
            </div>
        </div>
    </div>
</aside>
