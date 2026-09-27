<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    @include('layouts.admin.inc.head')
</head>

<body>
    <div class="page">

        @include('layouts.admin.inc.sidebar')

        @include('layouts.admin.inc.header')

        <div class="page-wrapper">

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
        if (auth()->guard('student')->check()) {
            $cmdkActions = [
                ['icon' => 'ti-home', 'label' => 'الصفحة الرئيسية', 'url' => route('student.dashboard'), 'keys' => 'home dashboard رئيسية'],
                ['icon' => 'ti-list-check', 'label' => 'مراحل المشروع', 'url' => route('student.dashboard') . '#milestones', 'keys' => 'milestones مراحل تقدم'],
                ['icon' => 'ti-files', 'label' => 'ملفات المشروع', 'url' => route('student.dashboard') . '#files', 'keys' => 'files ملفات رفع'],
                ['icon' => 'ti-messages', 'label' => 'النقاش مع المشرف', 'url' => route('student.discussion'), 'keys' => 'comments chat نقاش تعليق رسائل مشرف'],
                ['icon' => 'ti-users-group', 'label' => 'نقاش الفريق', 'url' => route('student.discussion', ['tab' => 'team']), 'keys' => 'team chat فريق زملاء نقاش'],
                ['icon' => 'ti-users-group', 'label' => 'فريق المشروع', 'url' => route('student.dashboard') . '#team', 'keys' => 'team فريق أعضاء'],
                ['icon' => 'ti-telescope', 'label' => 'مشاريع منجزة', 'url' => route('student.projects.explore'), 'keys' => 'explore استكشاف مشاريع سابقة منجزة مكتملة أفكار'],
                ['icon' => 'ti-bell', 'label' => 'الإشعارات', 'url' => route('student.showNotification'), 'keys' => 'notifications إشعارات ردود'],
                ['icon' => 'ti-user-cog', 'label' => 'الملف الشخصي', 'url' => route('student.profile.edit'), 'keys' => 'profile ملف شخصي كلمة سر جوال'],
            ];
        } elseif (auth()->guard('supervisor')->check()) {
            $cmdkActions = [
                ['icon' => 'ti-home', 'label' => 'الصفحة الرئيسية', 'url' => route('supervisor.dashboard'), 'keys' => 'home dashboard رئيسية مجموعات'],
                ['icon' => 'ti-route', 'label' => 'خطة المراحل', 'url' => route('supervisor.plan'), 'keys' => 'plan stages milestones template خطة مراحل قالب قوالب'],
                ['icon' => 'ti-messages', 'label' => 'النقاش', 'url' => route('supervisor.discussion'), 'keys' => 'comments chat نقاش تعليق رسائل مجموعات'],
                ['icon' => 'ti-briefcase', 'label' => 'طلبات الإشراف', 'url' => route('supervisor.showNotification'), 'keys' => 'requests طلبات إشراف قبول رفض إشعارات'],
                ['icon' => 'ti-archive', 'label' => 'أرشيف مشاريعي', 'url' => route('supervisor.projects.archive'), 'keys' => 'archive أرشيف مشاريع سابقة درجات'],
                ['icon' => 'ti-user-cog', 'label' => 'الملف الشخصي', 'url' => route('supervisor.profile.edit'), 'keys' => 'profile ملف شخصي كلمة سر جوال'],
            ];
        } elseif (auth()->guard('admin')->check()) {
            $cmdkActions = [
                ['icon' => 'ti-home', 'label' => 'لوحة التحكم', 'url' => route('admin.dashboard'), 'keys' => 'home dashboard رئيسية'],
                ['icon' => 'ti-school', 'label' => 'الطلاب', 'url' => route('admin.students.index'), 'keys' => 'students طلاب'],
                ['icon' => 'ti-user-star', 'label' => 'المشرفون', 'url' => route('admin.supervisors.index'), 'keys' => 'supervisors مشرفين'],
                ['icon' => 'ti-users', 'label' => 'الإداريون', 'url' => route('admin.administrators.index'), 'keys' => 'admins إداريين'],
                ['icon' => 'ti-category', 'label' => 'التخصصات', 'url' => route('admin.specialize.index'), 'keys' => 'specialize تخصصات'],
                ['icon' => 'ti-calendar', 'label' => 'الفصول الدراسية', 'url' => route('admin.semesters.index'), 'keys' => 'semesters فصول'],
                ['icon' => 'ti-users-group', 'label' => 'المجموعات', 'url' => route('admin.groups.index'), 'keys' => 'groups مجموعات فرق'],
                ['icon' => 'ti-mail', 'label' => 'رسائل التواصل', 'url' => route('admin.contact.index'), 'keys' => 'contacts رسائل استفسارات'],
                ['icon' => 'ti-history', 'label' => 'سجلّ التدقيق', 'url' => route('admin.audit.index'), 'keys' => 'audit سجل تدقيق درجات تغييرات'],
                ['icon' => 'ti-user-cog', 'label' => 'الملف الشخصي', 'url' => route('admin.profile.edit'), 'keys' => 'profile ملف شخصي كلمة سر جوال'],
            ];
        }
    @endphp
    <div class="modal fade" id="cmdk-modal" tabindex="-1" aria-hidden="true" aria-label="البحث السريع">
        <div class="modal-dialog modal-dialog-centered cmdk-dialog">
            <div class="modal-content cmdk-content">
                <div class="cmdk-search">
                    <i class="ti ti-search"></i>
                    <input type="text" id="cmdk-input" class="cmdk-input" placeholder="اكتب للبحث أو الانتقال.."
                        autocomplete="off" aria-label="بحث سريع">
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
                        لا توجد نتائج مطابقة
                    </div>
                </div>
                <div class="cmdk-foot">
                    <span><span class="cmdk-kbd">↑↓</span> تنقّل</span>
                    <span><span class="cmdk-kbd">Enter</span> فتح</span>
                    <span><span class="cmdk-kbd">Ctrl K</span> فتح/إغلاق</span>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('vendor/tabler/js/tabler.min.js') }}"></script>
    <script src="{{ asset('js/topbar.js') }}"></script>

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
            btn.setAttribute('aria-label', show ? 'إخفاء كلمة السر' : 'إظهار كلمة السر');
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
                (btn.dataset.loadingText || 'جارٍ المعالجة...');
        });
    </script>

    @stack('js')
</body>

</html>
