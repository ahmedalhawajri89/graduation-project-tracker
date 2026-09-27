@php
    $newMessagesCount = \App\Models\Contact::where('is_read', 0)->count();
@endphp

{{-- ===== المتابعة ===== --}}
<li class="sidebar-label">المتابعة</li>

<li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.dashboard') }}">
        <span class="nav-link-icon"><i class="ti ti-home"></i></span>
        <span class="nav-link-title">الصفحة الرئيسية</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.groups.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.groups.index') }}">
        <span class="nav-link-icon"><i class="ti ti-users-group"></i></span>
        <span class="nav-link-title">بيانات المجموعات</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.contact.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.contact.index') }}">
        <span class="nav-link-icon"><i class="ti ti-mail-question"></i></span>
        <span class="nav-link-title">رسائل الاستفسار</span>
        @if ($newMessagesCount > 0)
            <span class="sidebar-count">{{ $newMessagesCount }}</span>
        @endif
    </a>
</li>

{{-- ===== المستخدمون ===== --}}
<li class="sidebar-label">المستخدمون</li>

<li class="nav-item {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.students.index') }}">
        <span class="nav-link-icon"><i class="ti ti-school"></i></span>
        <span class="nav-link-title">بيانات الطلاب</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.supervisors.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.supervisors.index') }}">
        <span class="nav-link-icon"><i class="ti ti-user-star"></i></span>
        <span class="nav-link-title">بيانات المشرفين</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.administrators.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.administrators.index') }}">
        <span class="nav-link-icon"><i class="ti ti-shield-lock"></i></span>
        <span class="nav-link-title">مسؤولو النظام</span>
    </a>
</li>

{{-- ===== الإعدادات ===== --}}
<li class="sidebar-label">الإعدادات</li>

<li class="nav-item {{ request()->routeIs('admin.specialize.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.specialize.index') }}">
        <span class="nav-link-icon"><i class="ti ti-category"></i></span>
        <span class="nav-link-title">إدارة التخصصات</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.semesters.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.semesters.index') }}">
        <span class="nav-link-icon"><i class="ti ti-calendar"></i></span>
        <span class="nav-link-title">الفصول الدراسية</span>
    </a>
</li>

<li class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.audit.index') }}">
        <span class="nav-link-icon"><i class="ti ti-history"></i></span>
        <span class="nav-link-title">سجلّ التدقيق</span>
    </a>
</li>

{{-- «الملف الشخصي» انتقل إلى قائمة الأفاتار وحدها.
     السايدبار يعدّد ما تُديره — الطلاب والمجموعات والفصول — والملف
     الشخصي ليس شيئاً تُديره، هو أنت. وكان في الموضعين معاً: مدخلان
     لصفحة واحدة بلا فرق بينهما، وموضع دائم في التنقّل الأساسي لفعل
     يُستعمل مرّتين في السنة. ولا يضيع وصول: الهيدر ظاهر في كل
     المقاسات. --}}
