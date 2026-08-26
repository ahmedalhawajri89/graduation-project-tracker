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

<aside class="navbar navbar-vertical navbar-expand-lg sidebar" data-bs-theme="dark">
    <div class="container-fluid d-flex flex-column h-100">

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu"
            aria-controls="sidebar-menu" aria-expanded="false" aria-label="إظهار القائمة">
            <span class="navbar-toggler-icon"></span>
        </button>

        {{-- الشعار --}}
        <h1 class="navbar-brand navbar-brand-autodark sidebar-brand">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <span class="sidebar-brand-logo">
                    <img src="{{ asset('assets/img/takharruj-logo.svg') }}" alt="شعار تخرُّج" height="26">
                </span>
                <span class="sidebar-brand-text">
                    <span class="d-block">تخرُّج</span>
                    <small>متابعة مشاريع التخرج</small>
                </span>
            </a>
        </h1>

        {{-- إشعارات للشاشات الصغيرة (الهيدر مخفي عليها) --}}
        <div class="navbar-nav flex-row d-lg-none">
            <div class="nav-item position-relative">
                <a href="{{ $sbNotifyRoute }}" class="nav-link px-2" title="الإشعارات">
                    <i class="ti ti-bell fs-2"></i>
                    @if ($sbUnread > 0)
                        <span class="badge bg-red text-red-fg badge-notify">{{ $sbUnread }}</span>
                    @endif
                </a>
            </div>
        </div>

        <div class="collapse navbar-collapse d-lg-flex flex-column flex-grow-1" id="sidebar-menu">

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
