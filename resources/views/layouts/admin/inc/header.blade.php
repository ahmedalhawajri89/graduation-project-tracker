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

{{-- كان d-none d-lg-flex فيختفي الشريط كله تحت ٩٩٢ بكسل، ويفقد الموبايل
     البحث والإشعارات وقائمة المستخدم معاً. صار ظاهراً في كل المقاسات. --}}
<header class="navbar navbar-expand-md topbar d-print-none">
    <div class="container-xl">

        {{-- صدر الشريط: فتح السايدبار (موبايل) + مسار التنقّل --}}
        <button type="button" class="topbar-icon-btn topbar-burger" data-bs-toggle="collapse"
            data-bs-target="#sidebar-menu" aria-label="فتح القائمة" aria-expanded="false">
            <i class="ti ti-menu-2" aria-hidden="true"></i>
        </button>

        <x-breadcrumb />

        {{-- الوسط: البحث --}}
        <button type="button" class="cmdk-trigger" data-bs-toggle="modal" data-bs-target="#cmdk-modal"
            aria-label="فتح البحث السريع">
            <i class="ti ti-search" aria-hidden="true"></i>
            <span class="cmdk-trigger-label">بحث سريع أو انتقال..</span>
            <span class="cmdk-kbd">Ctrl K</span>
        </button>

        {{-- الذيل: إشعارات + مستخدم --}}
        <div class="navbar-nav flex-row align-items-center gap-1">

            {{-- الإشعارات: قائمة منسدلة بمعاينة --}}
            <div class="nav-item dropdown">
                <button type="button" class="topbar-icon-btn" data-bs-toggle="dropdown"
                    aria-label="الإشعارات ({{ $unreadCount }} غير مقروء)"
                    aria-haspopup="true" aria-expanded="false">
                    <i class="ti ti-bell" aria-hidden="true"></i>
                    @if ($unreadCount > 0)
                        <span class="topbar-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                    @endif
                </button>
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

            {{-- المستخدم: الأفاتار وحده. الاسم والدور داخل القائمة المنسدلة
                 (وهما فيها أصلاً) فلا يُكرَّران في الشريط. --}}
            <div class="nav-item dropdown">
                <button type="button" class="user-trigger" data-bs-toggle="dropdown"
                    aria-label="فتح قائمة المستخدم" aria-haspopup="true" aria-expanded="false">
                    <x-avatar :user="auth()->user()" class="avatar avatar-sm avatar-gradient" />
                    <i class="ti ti-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow user-menu">
                    <div class="user-menu-head">
                        <x-avatar :user="auth()->user()" class="avatar avatar-gradient" />
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate">{{ auth()->user()->name }}</div>
                            <div class="small text-secondary text-truncate">{{ auth()->user()->email }}</div>
                            {{-- كانت bg-purple-lt — بنفسجي بقي من قبل ترحيل الألوان --}}
                            <span class="badge role-badge mt-1">{{ $roleLabel }}</span>
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
