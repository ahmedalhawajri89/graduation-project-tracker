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
                <button type="button" class="topbar-icon-btn topbar-bell {{ $unreadCount ? 'has-unread' : '' }}" data-bs-toggle="dropdown"
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
                            {{-- الطالب: أيقونة الفئة (قبول، تعديل، إدارة…) كصفحة إشعاراته --}}
                            @php
                                $nv = auth()->guard('student')->check()
                                    ? \App\Support\NotificationView::present($notification)
                                    : ['icon' => 'ti-message', 'tone' => 'is-brand'];
                            @endphp
                            <a href="{{ $notifyRoute }}" class="notif-item {{ is_null($notification->read_at) ? 'unread' : '' }}">
                                <span class="notif-dot" aria-hidden="true"></span>
                                <span class="notif-card-icon is-sm {{ $nv['tone'] }}" aria-hidden="true">
                                    <i class="ti {{ $nv['icon'] }}"></i>
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

            <span class="topbar-sep" aria-hidden="true"></span>

            {{-- المستخدم: شريحة بالأفاتار، والاسم المختصر والدور على الشاشات
                 الواسعة وحدها — تحتها يحتاج مسار التنقّل العرض --}}
            @php
                $shortName = \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->take(2)->implode(' ');
            @endphp
            <div class="nav-item dropdown">
                <button type="button" class="user-trigger" data-bs-toggle="dropdown"
                    aria-label="فتح قائمة المستخدم" aria-haspopup="true" aria-expanded="false">
                    <span class="user-trigger-avatar">
                        <x-avatar :user="auth()->user()" class="avatar avatar-sm avatar-gradient" />
                        <span class="user-online" aria-hidden="true"></span>
                    </span>
                    <span class="user-trigger-text">
                        <b>{{ $shortName }}</b>
                        <small>{{ $roleLabel }}</small>
                    </span>
                    <i class="ti ti-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow user-menu">
                    <div class="user-menu-head">
                        <span class="user-trigger-avatar is-lg">
                            <x-avatar :user="auth()->user()" class="avatar avatar-gradient" />
                            <span class="user-online" aria-hidden="true"></span>
                        </span>
                        <div class="min-w-0">
                            <div class="fw-bold text-truncate">{{ auth()->user()->name }}</div>
                            <div class="small text-secondary text-truncate">{{ auth()->user()->email }}</div>
                            {{-- كانت bg-purple-lt — بنفسجي بقي من قبل ترحيل الألوان --}}
                            <span class="badge role-badge mt-1">{{ $roleLabel }}</span>
                        </div>
                    </div>

                    @php
                        $profileRoute = auth()->guard('admin')->check()
                            ? route('admin.profile.edit')
                            : (auth()->guard('supervisor')->check()
                                ? route('supervisor.profile.edit')
                                : route('student.profile.edit'));
                    @endphp
                    <div class="user-menu-list">
                        <a href="{{ $profileRoute }}" class="dropdown-item">
                            <span class="user-menu-icon"><i class="ti ti-user-cog"></i></span>
                            الملف الشخصي
                        </a>
                        <a href="{{ $notifyRoute }}" class="dropdown-item">
                            <span class="user-menu-icon"><i class="ti ti-bell"></i></span>
                            الإشعارات
                            @if ($unreadCount > 0)
                                <span class="user-menu-count">{{ $unreadCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('site.home') }}" class="dropdown-item" target="_blank">
                            <span class="user-menu-icon"><i class="ti ti-world"></i></span>
                            الموقع العام
                            <i class="ti ti-external-link user-menu-ext" aria-hidden="true"></i>
                        </a>
                    </div>

                    <div class="user-menu-list is-foot">
                        <a href="{{ route('logout') }}" class="dropdown-item is-danger"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <span class="user-menu-icon"><i class="ti ti-logout"></i></span>
                            تسجيل خروج
                        </a>
                    </div>
                    <form action="{{ route('logout') }}" method="post" id="logout-form">@csrf</form>
                </div>
            </div>
        </div>

    </div>
</header>
