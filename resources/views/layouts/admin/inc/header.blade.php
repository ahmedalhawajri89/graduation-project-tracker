@php
    if (auth()->guard('admin')->check()) {
        $notifyRoute = route('admin.contact.index');
        $roleLabel = 'مسؤول النظام';
    } elseif (auth()->guard('supervisor')->check()) {
        $notifyRoute = route('supervisor.showNotification');
        $roleLabel = 'مشرف أكاديمي';
    } else {
        $notifyRoute = route('student.showNotification');
        $roleLabel = 'طالب';
    }
    // من الـ View Composer — محسوبة مرة واحدة للطلب
    $unreadCount = $layoutShared['unreadCount'] ?? 0;
    $latestNotifications = $layoutShared['latestNotifications'] ?? collect();
@endphp

<header class="navbar navbar-expand-md topbar d-none d-lg-flex d-print-none">
    <div class="container-xl">

        {{-- يمين الشريط: بحث سريع + الفصل الحالي --}}
        <div class="navbar-nav flex-row align-items-center gap-2">

            <button type="button" class="cmdk-trigger" data-bs-toggle="modal" data-bs-target="#cmdk-modal"
                aria-label="فتح البحث السريع">
                <i class="ti ti-search"></i>
                <span class="cmdk-trigger-label">بحث سريع أو انتقال..</span>
                <span class="cmdk-kbd">Ctrl K</span>
            </button>

            @isset($viewSemester)
                <span class="topbar-chip d-none d-xl-inline-flex">
                    <i class="ti ti-calendar"></i>
                    الفصل: <strong>{{ $viewSemester->name }}</strong>
                </span>
            @endisset
        </div>

        {{-- يسار الشريط: إشعارات + مستخدم --}}
        <div class="navbar-nav flex-row order-md-last align-items-center gap-1">

            {{-- الإشعارات: قائمة منسدلة بمعاينة --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link px-2 position-relative topbar-icon-btn" data-bs-toggle="dropdown"
                    aria-label="الإشعارات ({{ $unreadCount }} غير مقروء)" aria-expanded="false">
                    <i class="ti ti-bell fs-2"></i>
                    @if ($unreadCount > 0)
                        <span class="topbar-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notif-menu">
                    <div class="notif-menu-head">
                        <span class="fw-bold">الإشعارات</span>
                        @if ($unreadCount > 0)
                            <span class="badge bg-red-lt text-red">{{ $unreadCount }} جديد</span>
                        @endif
                    </div>

                    @if ($latestNotifications->count() === 0)
                        <div class="notif-empty">
                            <i class="ti ti-bell-off"></i>
                            <span>لا توجد إشعارات بعد</span>
                        </div>
                    @else
                        @foreach ($latestNotifications as $notification)
                            <a href="{{ $notifyRoute }}" class="notif-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
                                <span class="notif-dot" aria-hidden="true"></span>
                                <span class="avatar avatar-sm rounded-circle bg-primary-lt text-primary">
                                    <i class="ti ti-message"></i>
                                </span>
                                <span class="notif-body">
                                    <span class="notif-title">
                                        {{ $notification->data['project'] ?? ($notification->data['title'] ?? 'إشعار') }}
                                    </span>
                                    <span class="notif-text">
                                        {{ \Illuminate\Support\Str::limit($notification->data['msg'] ?? ($notification->data['type'] ?? ''), 70) }}
                                    </span>
                                    <span class="notif-time">{{ $notification->created_at->diffForHumans() }}</span>
                                </span>
                            </a>
                        @endforeach
                    @endif

                    <a href="{{ $notifyRoute }}" class="notif-menu-foot">
                        عرض كل الإشعارات
                        <i class="ti ti-arrow-left"></i>
                    </a>
                </div>
            </div>

            {{-- المستخدم --}}
            <div class="nav-item dropdown">
                <a href="#" class="nav-link d-flex align-items-center lh-1 p-1 user-trigger" data-bs-toggle="dropdown"
                    aria-label="فتح قائمة المستخدم" aria-expanded="false">
                    <span class="avatar avatar-sm avatar-gradient">
                        {{ mb_substr(str_replace('د.', '', auth()->user()->name), 0, 2) }}
                    </span>
                    <div class="d-none d-xl-block ps-2 me-2 text-start">
                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                        <div class="mt-1 small text-secondary">{{ $roleLabel }}</div>
                    </div>
                    <i class="ti ti-chevron-down text-secondary d-none d-xl-block"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow user-menu">
                    <div class="user-menu-head">
                        <span class="avatar avatar-gradient">
                            {{ mb_substr(str_replace('د.', '', auth()->user()->name), 0, 2) }}
                        </span>
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate">{{ auth()->user()->name }}</div>
                            <div class="small text-secondary text-truncate">{{ auth()->user()->email }}</div>
                            <span class="badge bg-purple-lt text-purple mt-1">{{ $roleLabel }}</span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>

                    @php
                        $profileRoute = auth()->guard('admin')->check()
                            ? route('admin.profile.edit')
                            : (auth()->guard('supervisor')->check()
                                ? route('supervisor.profile.edit')
                                : route('student.profile.edit'));
                    @endphp
                    <a href="{{ $profileRoute }}" class="dropdown-item">
                        <i class="ti ti-user-cog me-2"></i>
                        الملف الشخصي
                    </a>
                    <a href="{{ $notifyRoute }}" class="dropdown-item">
                        <i class="ti ti-bell me-2"></i>
                        الإشعارات
                        @if ($unreadCount > 0)
                            <span class="badge bg-red-lt text-red ms-auto">{{ $unreadCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('site.home') }}" class="dropdown-item" target="_blank">
                        <i class="ti ti-world me-2"></i>
                        الموقع العام
                    </a>

                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item text-danger"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="ti ti-logout me-2"></i>
                        تسجيل خروج
                    </a>
                    <form action="{{ route('logout') }}" method="post" id="logout-form">@csrf</form>
                </div>
            </div>
        </div>

    </div>
</header>
