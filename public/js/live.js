/* =========================================================
   التحديث الحيّ للّوحة — الجرس وشارات الشريط الجانبي
   ---------------------------------------------------------
   يسأل /live كل 20 ثانية والصفحة ظاهرة، وفوراً حين يعود إليها المستخدم.
   الجديد يظهر دون إعادة تحميل: الرقم على الجرس وقائمته، وشارات
   «النقاش» و«الطلبات» و«المناقشات» و«رسائل الاستفسار»، وعنوان التبويب
   «(3) …»، وتنبيه منبثق لكل إشعار وصل للتوّ.

   لا WebSocket: الخادم الحالي PHP عادي بلا عملية دائمة. السؤال الدوري
   رخيص (عدّادات واستعلام إشعارات)، ويتباطأ عند الخطأ، ويتوقّف عند
   انتهاء الجلسة (401) — لا يُبقي جلسة منتهية حيّة.
   ========================================================= */
(function () {
    'use strict';

    var root = document.querySelector('[data-live-endpoint]');
    if (!root || !window.fetch) return;

    var endpoint = root.getAttribute('data-live-endpoint');
    var INTERVAL = 20000;
    var MAX_BACKOFF = 5 * 60000;
    var since = root.getAttribute('data-live-now');
    var delay = INTERVAL;
    var timer = null;
    var busy = false;
    var stopped = false;
    var baseTitle = document.title.replace(/^\(\d+\+?\)\s*/, '');
    var tr = window.t || function (s) { return s; };

    function schedule(ms) {
        clearTimeout(timer);
        if (!stopped) timer = setTimeout(poll, ms);
    }

    function poll() {
        if (busy || stopped) return;
        if (document.hidden) return schedule(INTERVAL); // لا سؤال وتبويب مخفي
        busy = true;
        var url = endpoint + (since ? '?since=' + encodeURIComponent(since) : '');
        fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin', cache: 'no-store' })
            .then(function (r) {
                if (r.status === 401 || r.status === 419) { stopped = true; throw new Error('session'); }
                if (!r.ok) throw new Error('http ' + r.status);
                return r.json();
            })
            .then(function (data) {
                apply(data);
                since = data.now || since;
                delay = INTERVAL;
            })
            .catch(function () {
                // تباطؤ متضاعف عند الخطأ (الخادم مشغول أو الشبكة مقطوعة)
                delay = Math.min(delay * 2, MAX_BACKOFF);
            })
            .finally(function () {
                busy = false;
                schedule(delay);
            });
    }

    function fmt(n) { return n > 99 ? '99+' : String(n); }

    function apply(data) {
        var c = data.counts || {};
        var unread = c.notifications || 0;

        // الجرس: الرقم، والحلقة، ووصف قارئ الشاشة
        var bell = document.querySelector('[data-live-bell]');
        var badge = document.querySelector('[data-live-badge]');
        if (bell) {
            bell.classList.toggle('has-unread', unread > 0);
            if (data.labels && data.labels.bell) bell.setAttribute('aria-label', data.labels.bell);
            // اهتزاز خفيف حين يزيد العدد
            var before = parseInt(bell.getAttribute('data-live-n') || '0', 10);
            if (unread > before) { bell.classList.remove('is-ringing'); void bell.offsetWidth; bell.classList.add('is-ringing'); }
            bell.setAttribute('data-live-n', unread);
        }
        if (badge) {
            badge.hidden = !unread;
            badge.textContent = unread > 9 ? '9+' : String(unread);
        }

        // القائمة: لا تُستبدل وهي مفتوحة تحت يد المستخدم
        var menu = document.querySelector('[data-live-menu]');
        if (menu && data.menu && !menu.classList.contains('show')) menu.innerHTML = data.menu;

        // شارات الشريط الجانبي
        document.querySelectorAll('[data-live-count]').forEach(function (el) {
            var n = c[el.getAttribute('data-live-count')];
            if (n === null || n === undefined) return;
            el.hidden = !n;
            el.textContent = fmt(n);
        });

        // عنوان التبويب: «(3) الصفحة» يلفت النظر من تبويب آخر
        document.title = unread ? '(' + (unread > 9 ? '9+' : unread) + ') ' + baseTitle : baseTitle;

        (data.fresh || []).slice().reverse().forEach(toast);
    }

    // تنبيه منبثق بأسلوب تنبيهات اللوحة (alert.blade.php)
    function toast(item) {
        var stack = document.querySelector('.toast-stack');
        if (!stack) return;
        var el = document.createElement('div');
        el.className = 'app-toast is-notify';
        el.setAttribute('role', 'status');
        el.setAttribute('aria-live', 'polite');
        el.innerHTML =
            '<span class="app-toast-bar" aria-hidden="true"></span>' +
            '<i class="ti ti-bell-ringing app-toast-icon" aria-hidden="true"></i>' +
            '<a class="app-toast-text app-toast-link"></a>' +
            '<button type="button" class="app-toast-close"><i class="ti ti-x" aria-hidden="true"></i></button>';
        var link = el.querySelector('a');
        link.href = item.href || '#';
        var b = document.createElement('b');
        b.textContent = item.title || tr('إشعار جديد');
        var s = document.createElement('span');
        s.textContent = item.text || '';
        link.appendChild(b);
        link.appendChild(s);
        el.querySelector('.app-toast-close').setAttribute('aria-label', tr('إغلاق'));
        stack.appendChild(el);

        var t = null;
        function dismiss() {
            el.classList.add('is-leaving');
            el.addEventListener('animationend', function () { el.remove(); }, { once: true });
            // تقليل الحركة: لا حركة خروج فلا animationend
            setTimeout(function () { el.remove(); }, 600);
        }
        function start() { t = setTimeout(dismiss, 8000); }
        function stop() { clearTimeout(t); }
        el.addEventListener('mouseenter', stop);
        el.addEventListener('focusin', stop);
        el.addEventListener('mouseleave', start);
        el.addEventListener('focusout', start);
        el.querySelector('.app-toast-close').addEventListener('click', function () { stop(); dismiss(); });
        start();
    }

    // العودة إلى التبويب أو استعادة الاتصال: سؤال فوري
    document.addEventListener('visibilitychange', function () { if (!document.hidden) { delay = INTERVAL; schedule(300); } });
    window.addEventListener('online', function () { delay = INTERVAL; schedule(300); });

    var bell0 = document.querySelector('[data-live-bell]');
    var badge0 = document.querySelector('[data-live-badge]');
    if (bell0) bell0.setAttribute('data-live-n', badge0 && !badge0.hidden ? (parseInt(badge0.textContent, 10) || 0) : 0);

    schedule(INTERVAL);
})();
