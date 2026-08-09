<li class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('student.dashboard') }}">
        <span class="nav-link-icon"><i class="ti ti-home"></i></span>
        <span class="nav-link-title">الصفحة الرئيسية</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('student.showNotification') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('student.showNotification') }}">
        <span class="nav-link-icon"><i class="ti ti-bell"></i></span>
        <span class="nav-link-title">الإشعارات</span>
        @if (($layoutShared['unreadCount'] ?? 0) > 0)
            <span class="sidebar-count">{{ $layoutShared['unreadCount'] }}</span>
        @endif
    </a>
</li>

<li class="nav-item {{ request()->routeIs('student.projects.explore') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('student.projects.explore') }}">
        <span class="nav-link-icon"><i class="ti ti-telescope"></i></span>
        <span class="nav-link-title">مستكشف المشاريع</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('student.profile.edit') }}">
        <span class="nav-link-icon"><i class="ti ti-user-cog"></i></span>
        <span class="nav-link-title">الملف الشخصي</span>
    </a>
</li>
