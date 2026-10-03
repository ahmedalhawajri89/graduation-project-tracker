<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    @include('layouts.admin.inc.head')
</head>

<body>
    <div class="page">

        @include('layouts.admin.inc.sidebar')

        @include('layouts.admin.inc.header')

        <div class="page-wrapper">

            {{-- النسخة التجريبية: تنبيه رفيع ومبدّل أدوار — القصة من الجهتين دون خروج --}}
            @if (\App\Support\Demo::enabled())
                @php
                    $demoNow = auth('admin')->check() ? 'admin' : (auth('supervisor')->check() ? 'supervisor' : 'student');
                @endphp
                <div class="demo-bar" role="region" aria-label="{{ __('النسخة التجريبية') }}">
                    <span class="demo-bar-note"><i class="ti ti-flask" aria-hidden="true"></i> {{ __('نسخة تجريبية — جرّب بحرّية، البيانات تُعاد كل ليلة') }}</span>
                    <span class="demo-bar-roles">
                        <small>{{ __('شاهد كـ') }}</small>
                        @foreach (['student' => __('طالب'), 'supervisor' => __('مشرف'), 'admin' => __('مسؤول')] as $role => $label)
                            <form method="POST" action="{{ route('demo.enter', $role) }}">
                                @csrf
                                <button type="submit" class="demo-chip {{ $demoNow === $role ? 'is-on' : '' }}" @if ($demoNow === $role) aria-current="true" disabled @endif>{{ $label }}</button>
                            </form>
                        @endforeach
                    </span>
                </div>
            @endif

            <div class="page-body">
                <div class="container-xl">
                    @include('layouts.admin.inc.alert')

                    @yield('content')
                </div>
            </div>

            @include('layouts.admin.inc.footer')
        </div>
    </div>

    {{-- ===== البحث السريع (Command Palette) ===== --}}
    @php
        $cmdkActions = [];
        // keys: كلمات بحث باللغتين معاً — لا تُترجم (الاسم المترجَم يُضاف إليها في data-keys)
        if (auth()->guard('student')->check()) {
            $cmdkActions = [
                ['icon' => 'ti-home', 'label' => __('الصفحة الرئيسية'), 'url' => route('student.dashboard'), 'keys' => 'home dashboard رئيسية'],
                ['icon' => 'ti-list-check', 'label' => __('مراحل المشروع'), 'url' => route('student.dashboard') . '#milestones', 'keys' => 'milestones مراحل تقدم'],
                ['icon' => 'ti-files', 'label' => __('ملفات المشروع'), 'url' => route('student.dashboard') . '#files', 'keys' => 'files ملفات رفع'],
                ['icon' => 'ti-messages', 'label' => __('النقاش مع المشرف'), 'url' => route('student.discussion'), 'keys' => 'comments chat نقاش تعليق رسائل مشرف'],
                ['icon' => 'ti-users-group', 'label' => __('نقاش الفريق'), 'url' => route('student.discussion', ['tab' => 'team']), 'keys' => 'team chat فريق زملاء نقاش'],
                ['icon' => 'ti-users-group', 'label' => __('فريق المشروع'), 'url' => route('student.dashboard') . '#team', 'keys' => 'team فريق أعضاء'],
                ['icon' => 'ti-telescope', 'label' => __('مشاريع منجزة'), 'url' => route('student.projects.explore'), 'keys' => 'explore استكشاف مشاريع سابقة منجزة مكتملة أفكار'],
                ['icon' => 'ti-bell', 'label' => __('الإشعارات'), 'url' => route('student.showNotification'), 'keys' => 'notifications إشعارات ردود'],
                ['icon' => 'ti-user-cog', 'label' => __('الملف الشخصي'), 'url' => route('student.profile.edit'), 'keys' => 'profile ملف شخصي كلمة سر جوال'],
            ];
        } elseif (auth()->guard('supervisor')->check()) {
            $cmdkActions = [
                ['icon' => 'ti-home', 'label' => __('الصفحة الرئيسية'), 'url' => route('supervisor.dashboard'), 'keys' => 'home dashboard رئيسية مجموعات'],
                ['icon' => 'ti-route', 'label' => __('خطة المراحل'), 'url' => route('supervisor.plan'), 'keys' => 'plan stages milestones template خطة مراحل قالب قوالب'],
                ['icon' => 'ti-messages', 'label' => __('النقاش'), 'url' => route('supervisor.discussion'), 'keys' => 'comments chat نقاش تعليق رسائل مجموعات'],
                ['icon' => 'ti-briefcase', 'label' => __('طلبات الإشراف'), 'url' => route('supervisor.showNotification'), 'keys' => 'requests طلبات إشراف قبول رفض إشعارات'],
                ['icon' => 'ti-archive', 'label' => __('أرشيف مشاريعي'), 'url' => route('supervisor.projects.archive'), 'keys' => 'archive أرشيف مشاريع سابقة درجات'],
                ['icon' => 'ti-user-cog', 'label' => __('الملف الشخصي'), 'url' => route('supervisor.profile.edit'), 'keys' => 'profile ملف شخصي كلمة سر جوال'],
            ];
        } elseif (auth()->guard('admin')->check()) {
            $cmdkActions = [
                ['icon' => 'ti-home', 'label' => __('لوحة التحكم'), 'url' => route('admin.dashboard'), 'keys' => 'home dashboard رئيسية'],
                ['icon' => 'ti-school', 'label' => __('الطلاب'), 'url' => route('admin.students.index'), 'keys' => 'students طلاب'],
                ['icon' => 'ti-user-star', 'label' => __('المشرفون'), 'url' => route('admin.supervisors.index'), 'keys' => 'supervisors مشرفين'],
                ['icon' => 'ti-users', 'label' => __('الإداريون'), 'url' => route('admin.administrators.index'), 'keys' => 'admins إداريين'],
                ['icon' => 'ti-category', 'label' => __('التخصصات'), 'url' => route('admin.specialize.index'), 'keys' => 'specialize تخصصات'],
                ['icon' => 'ti-calendar', 'label' => __('الفصول الدراسية'), 'url' => route('admin.semesters.index'), 'keys' => 'semesters فصول'],
                ['icon' => 'ti-users-group', 'label' => __('المجموعات'), 'url' => route('admin.groups.index'), 'keys' => 'groups مجموعات فرق'],
                ['icon' => 'ti-mail', 'label' => __('رسائل التواصل'), 'url' => route('admin.contact.index'), 'keys' => 'contacts رسائل استفسارات'],
                ['icon' => 'ti-history', 'label' => __('سجلّ التدقيق'), 'url' => route('admin.audit.index'), 'keys' => 'audit سجل تدقيق درجات تغييرات'],
                ['icon' => 'ti-user-cog', 'label' => __('الملف الشخصي'), 'url' => route('admin.profile.edit'), 'keys' => 'profile ملف شخصي كلمة سر جوال'],
            ];
        }
    @endphp
    <div class="modal fade" id="cmdk-modal" tabindex="-1" aria-hidden="true" aria-label="{{ __('البحث السريع') }}">
        <div class="modal-dialog modal-dialog-centered cmdk-dialog">
            <div class="modal-content cmdk-content">
                <div class="cmdk-search">
                    <i class="ti ti-search"></i>
                    <input type="text" id="cmdk-input" class="cmdk-input" placeholder="{{ __('اكتب للبحث أو الانتقال..') }}"
                        autocomplete="off" aria-label="{{ __('بحث سريع') }}">
                    <span class="cmdk-kbd">Esc</span>
                </div>
                <div class="cmdk-list" id="cmdk-list" role="listbox">
                    @foreach ($cmdkActions as $action)
                        <a href="{{ $action['url'] }}" class="cmdk-item" role="option"
                            data-keys="{{ $action['label'] }} {{ $action['keys'] }}">
                            <span class="cmdk-item-icon"><i class="ti {{ $action['icon'] }}"></i></span>
                            <span>{{ $action['label'] }}</span>
                            <i class="ti ti-arrow-left cmdk-item-go"></i>
                        </a>
                    @endforeach
                    <div class="cmdk-no-results d-none" id="cmdk-empty">
                        <i class="ti ti-search-off"></i>
                        {{ __('لا توجد نتائج مطابقة') }}
                    </div>
                </div>
                <div class="cmdk-foot">
                    <span><span class="cmdk-kbd">↑↓</span> {{ __('تنقّل') }}</span>
                    <span><span class="cmdk-kbd">Enter</span> {{ __('فتح') }}</span>
                    <span><span class="cmdk-kbd">Ctrl K</span> {{ __('فتح/إغلاق') }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- نصوص ملفات JS المستقلة: t('نصّ عربي') يعيد ترجمته، والعربية تبقى مفتاحها --}}
    <script>
        window.I18N = @json(app()->getLocale() === 'ar' ? (object) [] : (json_decode((string) @file_get_contents(resource_path('lang/json/js/' . app()->getLocale() . '.json')), true) ?: (object) []));
        window.t = function (s, r) {
            var v = (window.I18N && window.I18N[s]) || s;
            if (r) Object.keys(r).forEach(function (k) { v = v.split(':' + k).join(r[k]); });
            return v;
        };
    </script>
    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/tabler/js/tabler.min.js') }}"></script>
    <script src="{{ asset('js/topbar.js') }}?v={{ filemtime(public_path('js/topbar.js')) }}"></script>
    <script src="{{ asset('js/dashboard-mobile.js') }}?v={{ filemtime(public_path('js/dashboard-mobile.js')) }}"></script>
    {{-- رفع الملفات بشريط تقدّم — قبل سكربت «حالة التحميل» أدناه ليسبقه --}}
    <script src="{{ asset('js/upload.js') }}?v={{ filemtime(public_path('js/upload.js')) }}"></script>
    {{-- الجرس وشارات الشريط الجانبي تتحدّث دون إعادة تحميل (LiveController) --}}
    <div hidden data-live-endpoint="{{ route('live') }}" data-live-now="{{ now()->toIso8601String() }}"></div>
    <script src="{{ asset('js/live.js') }}?v={{ filemtime(public_path('js/live.js')) }}"></script>

    <script>
        // إظهار/إخفاء كلمة السر (يعمل مع الحقول المضافة ديناميكياً)
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.toggle-password');
            if (!btn) return;
            var input = document.getElementById(btn.dataset.target);
            if (!input) return;
            var icon = btn.querySelector('i');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            if (icon) {
                icon.classList.toggle('ti-eye', !show);
                icon.classList.toggle('ti-eye-off', show);
            }
            btn.setAttribute('aria-label', show ? @json(__('إخفاء كلمة السر')) : @json(__('إظهار كلمة السر')));
        });

        // حالة تحميل لأزرار الإرسال لمنع النقر المزدوج
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form.tagName !== 'FORM') return;
            if (e.defaultPrevented) return; // أُلغي الإرسال (مثلاً برسالة تأكيد) — لا تعطّل الزر
            var btn = form.querySelector('button[type="submit"]');
            if (!btn || btn.dataset.loading === '1') return;
            btn.dataset.loading = '1';
            btn.dataset.original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>' +
                (btn.dataset.loadingText || @json(__('جارٍ المعالجة...')));
        });
    </script>

    @stack('js')
</body>

</html>
