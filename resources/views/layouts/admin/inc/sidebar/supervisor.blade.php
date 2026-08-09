<li class="nav-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.dashboard') }}">
        <span class="nav-link-icon"><i class="ti ti-home"></i></span>
        <span class="nav-link-title">الصفحة الرئيسية</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('supervisor.showNotification') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.showNotification') }}">
        <span class="nav-link-icon"><i class="ti ti-inbox"></i></span>
        <span class="nav-link-title">الطلبات والإشعارات</span>
        @if (($layoutShared['unreadCount'] ?? 0) > 0)
            <span class="sidebar-count">{{ $layoutShared['unreadCount'] }}</span>
        @endif
    </a>
</li>

<li class="nav-item {{ request()->routeIs('supervisor.projects.archive') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.projects.archive') }}">
        <span class="nav-link-icon"><i class="ti ti-archive"></i></span>
        <span class="nav-link-title">أرشيف مشاريعي</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('supervisor.profile.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.profile.edit') }}">
        <span class="nav-link-icon"><i class="ti ti-user-cog"></i></span>
        <span class="nav-link-title">الملف الشخصي</span>
    </a>
</li>
