<li class="nav-item {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('student.dashboard') }}">
        <span class="nav-link-icon"><i class="ti ti-home"></i></span>
        <span class="nav-link-title">الصفحة الرئيسية</span>
    </a>
</li>

{{-- النقاش تبويب لا قسم في ذيل اللوحة: يطول بالرسائل ولا يدفع الصفحة --}}
<li class="nav-item {{ request()->routeIs('student.discussion') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('student.discussion') }}">
        <span class="nav-link-icon"><i class="ti ti-messages"></i></span>
        <span class="nav-link-title">النقاش</span>
        @if (($layoutShared['discussionUnread'] ?? 0) > 0)
            <span class="sidebar-count">{{ $layoutShared['discussionUnread'] }}</span>
        @endif
    </a>
</li>

{{-- الفريق والأدوار: لمن له مشروع — مَن مسؤول عن ماذا --}}
@if (! empty($layoutShared['studentProject']))
    <li class="nav-item {{ request()->routeIs('student.team') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('student.team') }}">
            <span class="nav-link-icon"><i class="ti ti-id-badge-2"></i></span>
            <span class="nav-link-title">الفريق والأدوار</span>
        </a>
    </li>
@endif

<li class="nav-item {{ request()->routeIs('student.showNotification') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('student.showNotification') }}">
        <span class="nav-link-icon"><i class="ti ti-bell"></i></span>
        <span class="nav-link-title">الإشعارات</span>
        @if (($layoutShared['unreadCount'] ?? 0) > 0)
            <span class="sidebar-count">{{ $layoutShared['unreadCount'] }}</span>
        @endif
    </a>
</li>

{{-- غايته تسبق المقترح: هل نُفّذت فكرتي؟ ومع من؟ — فيغيب بعد قبول
     المشروع، والمسار يبقى متاحاً عبر البحث السريع --}}
@if (empty($layoutShared['studentProject']) || request()->routeIs('student.projects.explore'))
    <li class="nav-item {{ request()->routeIs('student.projects.explore') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('student.projects.explore') }}">
            <span class="nav-link-icon"><i class="ti ti-telescope"></i></span>
            <span class="nav-link-title">مشاريع منجزة</span>
        </a>
    </li>
@endif

{{-- «الملف الشخصي» في قائمة الأفاتار وحدها — انظر سايدبار الأدمن --}}
