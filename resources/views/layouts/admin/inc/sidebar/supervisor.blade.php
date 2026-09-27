<li class="nav-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.dashboard') }}">
        <span class="nav-link-icon"><i class="ti ti-home"></i></span>
        <span class="nav-link-title">الصفحة الرئيسية</span>
    </a>
</li>

{{-- خطة المراحل: المرحلة وقالبها تُعرَّف مرّة فتصل إلى كل المجموعات --}}
<li class="nav-item {{ request()->routeIs('supervisor.plan') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.plan') }}">
        <span class="nav-link-icon"><i class="ti ti-route"></i></span>
        <span class="nav-link-title">خطة المراحل</span>
    </a>
</li>

{{-- النقاش تبويب لا قسم في ذيل صفحة كل مشروع --}}
<li class="nav-item {{ request()->routeIs('supervisor.discussion') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.discussion') }}">
        <span class="nav-link-icon"><i class="ti ti-messages"></i></span>
        <span class="nav-link-title">النقاش</span>
        @if (($layoutShared['discussionUnread'] ?? 0) > 0)
            <span class="sidebar-count">{{ $layoutShared['discussionUnread'] }}</span>
        @endif
    </a>
</li>

<li class="nav-item {{ request()->routeIs('supervisor.showNotification') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.showNotification') }}">
        <span class="nav-link-icon"><i class="ti ti-inbox"></i></span>
        <span class="nav-link-title">طلبات الإشراف</span>
        {{-- الطلبات المعلّقة وحدها: كان يجمع كل الإشعارات فلا تعرف أهي طلبات أم تحديثات.
             الجرس في الهيدر يبقى للإشعارات --}}
        @if (($layoutShared['pendingRequests'] ?? 0) > 0)
            <span class="sidebar-count">{{ $layoutShared['pendingRequests'] }}</span>
        @endif
    </a>
</li>

<li class="nav-item {{ request()->routeIs('supervisor.projects.archive') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.projects.archive') }}">
        <span class="nav-link-icon"><i class="ti ti-archive"></i></span>
        <span class="nav-link-title">أرشيف مشاريعي</span>
    </a>
</li>

{{-- «الملف الشخصي» في قائمة الأفاتار وحدها — انظر سايدبار الأدمن --}}
