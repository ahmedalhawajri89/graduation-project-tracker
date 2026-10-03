<li class="nav-item {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.dashboard') }}">
        <span class="nav-link-icon"><i class="ti ti-home"></i></span>
        <span class="nav-link-title">{{ __('الصفحة الرئيسية') }}</span>
    </a>
</li>

{{-- خطة المراحل: المرحلة وقالبها تُعرَّف مرّة فتصل إلى كل المجموعات --}}
<li class="nav-item {{ request()->routeIs('supervisor.plan') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.plan') }}">
        <span class="nav-link-icon"><i class="ti ti-route"></i></span>
        <span class="nav-link-title">{{ __('خطة المراحل') }}</span>
    </a>
</li>

{{-- مناقشاتي: لجان هو عضو فيها، والعدد ما ينتظر درجته --}}
@if (\App\Support\DefenseScheduler::enabled())
    @php $defensesToGrade = (int) ($layoutShared['counts']['defenses'] ?? 0); @endphp
    {{-- ظاهر دائماً: بلا مناقشات تشرح الصفحة متى تظهر فيها --}}
    <li class="nav-item {{ request()->routeIs('supervisor.defenses.*') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('supervisor.defenses.index') }}">
                <span class="nav-link-icon"><i class="ti ti-presentation"></i></span>
                <span class="nav-link-title">{{ __('مناقشاتي') }}</span>
                <x-live-count key="defenses" :n="$defensesToGrade" />
            </a>
        </li>
@endif

{{-- النقاش تبويب لا قسم في ذيل صفحة كل مشروع --}}
<li class="nav-item {{ request()->routeIs('supervisor.discussion') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.discussion') }}">
        <span class="nav-link-icon"><i class="ti ti-messages"></i></span>
        <span class="nav-link-title">{{ __('النقاش') }}</span>
        <x-live-count key="discussion" :n="$layoutShared['discussionUnread'] ?? 0" />
    </a>
</li>

<li class="nav-item {{ request()->routeIs('supervisor.showNotification') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.showNotification') }}">
        <span class="nav-link-icon"><i class="ti ti-inbox"></i></span>
        <span class="nav-link-title">{{ __('طلبات الإشراف') }}</span>
        {{-- الطلبات المعلّقة وحدها: كان يجمع كل الإشعارات فلا تعرف أهي طلبات أم تحديثات.
             الجرس في الهيدر يبقى للإشعارات --}}
        <x-live-count key="requests" :n="$layoutShared['pendingRequests'] ?? 0" />
    </a>
</li>

<li class="nav-item {{ request()->routeIs('supervisor.projects.archive') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('supervisor.projects.archive') }}">
        <span class="nav-link-icon"><i class="ti ti-archive"></i></span>
        <span class="nav-link-title">{{ __('أرشيف مشاريعي') }}</span>
    </a>
</li>

{{-- «الملف الشخصي» في قائمة الأفاتار وحدها — انظر سايدبار الأدمن --}}
