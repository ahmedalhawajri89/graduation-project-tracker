/**
 * نافذة التأكيد والتنبيهات المنبثقة — للوحات الثلاث.
 *
 * appConfirm({title, text, ok, tone}) ← Promise<boolean>
 *   بدل confirm() الأصلي: نافذة <dialog> بأيقونة ولون حسب الخطورة، تحبس
 *   التركيز وتُغلق بـ Esc أو النقر خارجها، وتعلو الأدراج (الطبقة العليا).
 *
 * data-confirm="النص" على نموذج أو زرّ إرسال أو رابط — بلا سطر JS:
 *   data-confirm-title  data-confirm-ok  data-confirm-tone="danger|primary|warning"
 *   الإرسال يُعاد بـ requestSubmit(submitter) فيبقى اسم الزرّ وقيمته
 *   (قبول/رفض)، ويمرّ بشريط الرفع (upload.js) وحالة التحميل كالعادة.
 *
 * appToast({type, title, text, href, delay})
 *   type: success | danger | warning | info | notify. شريط الوقت أسفلها
 *   يتوقّف عند المرور أو التركيز، وانتهاؤه هو ما يغلقها — مؤقّت واحد.
 *   التنبيهات التي يرسمها الخادم (alert.blade.php) تُربط بالسلوك نفسه.
 */
(function () {
    'use strict';

    var tr = window.t || function (s) { return s; };

    /* ===================== التأكيد ===================== */
    var TONES = {
        danger: { icon: 'ti-trash', title: 'تأكيد الحذف', ok: 'حذف' },
        warning: { icon: 'ti-alert-triangle', title: 'تأكيد', ok: 'متابعة' },
        primary: { icon: 'ti-help-circle', title: 'تأكيد', ok: 'تأكيد' },
    };
    var dlg = null;

    function build() {
        dlg = document.createElement('dialog');
        dlg.className = 'app-dialog';
        dlg.setAttribute('aria-labelledby', 'app-dialog-title');
        dlg.setAttribute('aria-describedby', 'app-dialog-text');
        dlg.innerHTML =
            '<div class="app-dialog-card">' +
            '<span class="app-dialog-icon" aria-hidden="true"><i class="ti"></i></span>' +
            '<h2 class="app-dialog-title" id="app-dialog-title"></h2>' +
            '<p class="app-dialog-text" id="app-dialog-text"></p>' +
            '<div class="app-dialog-actions">' +
            '<button type="button" class="btn app-dialog-ok"></button>' +
            '<button type="button" class="btn app-dialog-cancel"></button>' +
            '</div></div>';
        // النقر على الخلفية (خارج البطاقة) يلغي
        // أزرار عادية لا form method=dialog: مستمعو الإرسال العامّون (حالة التحميل) لا يرونها
        dlg.querySelector('.app-dialog-ok').addEventListener('click', function () { dlg.close('ok'); });
        dlg.querySelector('.app-dialog-cancel').addEventListener('click', function () { dlg.close('cancel'); });
        dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close('cancel'); });
        // Esc يغلق النافذة وحدها، لا الدرج أو النافذة التي تحتها
        dlg.addEventListener('keydown', function (e) { if (e.key === 'Escape') e.stopPropagation(); });
        document.body.appendChild(dlg);
    }

    window.appConfirm = function (opts) {
        opts = typeof opts === 'string' ? { text: opts } : (opts || {});
        if (!dlg) build();
        // درج أو نافذة Bootstrap مفتوحة تحبس التركيز داخلها وتسحبه من أي
        // عنصر خارجها: النافذة تُوضع داخلها (تُعرض في الطبقة العليا أينما كانت)
        var host = document.querySelector('.offcanvas.show, .modal.show') || document.body;
        if (dlg.parentNode !== host) host.appendChild(dlg);
        var tone = TONES[opts.tone] ? opts.tone : 'danger';
        var d = TONES[tone];
        dlg.dataset.tone = tone;
        dlg.querySelector('.app-dialog-icon i').className = 'ti ' + (opts.icon || d.icon);
        dlg.querySelector('.app-dialog-title').textContent = opts.title || tr(d.title);
        // \n في النص فقرةٌ ثانية — كانت تفصل الجملتين في confirm()
        var text = dlg.querySelector('.app-dialog-text');
        text.textContent = '';
        String(opts.text || '').split(/\n+/).forEach(function (line, i) {
            if (!line.trim()) return;
            var el = document.createElement(i ? 'small' : 'span');
            el.textContent = line;
            text.appendChild(el);
        });
        var ok = dlg.querySelector('.app-dialog-ok');
        ok.textContent = opts.ok || tr(d.ok);
        ok.className = 'btn app-dialog-ok ' + (tone === 'danger' ? 'btn-danger' : tone === 'warning' ? 'btn-warning' : 'btn-primary');
        dlg.querySelector('.app-dialog-cancel').textContent = opts.cancel || tr('إلغاء');

        var back = document.activeElement;
        dlg.returnValue = '';
        if (dlg.open) dlg.close();

        return new Promise(function (resolve) {
            // تُفتح في المهمّة التالية: إن طُلبت من ضغطة Esc (إغلاق درج بتغييرات)
            // فالضغطة نفسها كانت ستغلقها فور فتحها
            setTimeout(function () {
                dlg.showModal();
                // الإلغاء هو الافتراضي في الحذف: Enter الطائش لا يحذف
                (tone === 'danger' ? dlg.querySelector('.app-dialog-cancel') : ok).focus();
            }, 0);
            dlg.addEventListener('close', function () {
                if (back && back.focus) back.focus({ preventScroll: true });
                resolve(dlg.returnValue === 'ok');
            }, { once: true });
        });
    };

    function optsOf(el) {
        return {
            text: el.dataset.confirm,
            title: el.dataset.confirmTitle,
            ok: el.dataset.confirmOk,
            tone: el.dataset.confirmTone,
        };
    }

    // طور الالتقاط على المستند: يسبق كل مستمع آخر للإرسال (حالة التحميل،
    // الإرسال بلا تحميل في النقاش)، فلا يرى أحدها إرسالاً لم يُؤكَّد بعد
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form.tagName !== 'FORM') return;
        var sub = e.submitter;
        var src = sub && sub.dataset.confirm ? sub : (form.dataset.confirm ? form : null);
        if (!src) return;
        if (form.dataset.confirmed === '1') { delete form.dataset.confirmed; return; }
        e.preventDefault();
        e.stopImmediatePropagation();
        window.appConfirm(optsOf(src)).then(function (yes) {
            if (!yes) return;
            form.dataset.confirmed = '1';
            if (form.requestSubmit) form.requestSubmit(sub && sub.form === form ? sub : undefined);
            else form.submit();
        });
    }, true);

    document.addEventListener('click', function (e) {
        var a = e.target.closest('a[data-confirm]');
        if (!a || e.button !== 0 || e.ctrlKey || e.metaKey) return;
        e.preventDefault();
        window.appConfirm(optsOf(a)).then(function (yes) { if (yes) window.location.href = a.href; });
    }, true);

    /* ===================== التنبيهات ===================== */
    var ICONS = { success: 'ti-check', danger: 'ti-x', warning: 'ti-alert-triangle', info: 'ti-info-small', notify: 'ti-bell-ringing' };
    var TITLES = { success: 'تمّ بنجاح', danger: 'تعذّر الإتمام', warning: 'راجع البيانات', info: 'للعلم', notify: 'إشعار جديد' };
    var MAX = 4;

    function stack() {
        var s = document.querySelector('.toast-stack');
        if (!s) {
            s = document.createElement('div');
            s.className = 'toast-stack';
            s.setAttribute('role', 'region');
            s.setAttribute('aria-label', tr('تنبيهات'));
            document.body.appendChild(s);
        }
        return s;
    }

    function dismiss(el) {
        if (el.classList.contains('is-leaving')) return;
        // ارتفاعها يُطوى فتنزلق الباقية إلى مكانها بدل القفز
        el.style.height = el.offsetHeight + 'px';
        void el.offsetHeight;
        el.classList.add('is-leaving');
        setTimeout(function () { el.remove(); }, 420);
    }

    function wire(el) {
        if (el.dataset.wired) return;
        el.dataset.wired = '1';
        var delay = parseInt(el.dataset.delay || '5000', 10);
        el.style.setProperty('--toast-delay', delay + 'ms');
        var bar = el.querySelector('.app-toast-timer');
        if (bar) bar.addEventListener('animationend', function () { dismiss(el); });
        else setTimeout(function () { dismiss(el); }, delay);
        var close = el.querySelector('.app-toast-close');
        if (close) close.addEventListener('click', function () { dismiss(el); });
        // الأقدم يخرج حين يزدحم المكدّس
        var all = el.parentNode ? el.parentNode.querySelectorAll('.app-toast:not(.is-leaving)') : [];
        for (var i = 0; i < all.length - MAX; i++) dismiss(all[i]);
    }

    window.appToast = function (opts) {
        opts = typeof opts === 'string' ? { text: opts } : (opts || {});
        var type = ICONS[opts.type] ? opts.type : 'info';
        var el = document.createElement('div');
        el.className = 'app-toast is-' + type;
        el.setAttribute('role', type === 'danger' || type === 'warning' ? 'alert' : 'status');
        el.dataset.delay = opts.delay || (type === 'danger' || type === 'warning' ? 7000 : type === 'notify' ? 8000 : 4500);

        var icon = document.createElement('span');
        icon.className = 'app-toast-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<i class="ti ' + ICONS[type] + '"></i>';

        var body = document.createElement(opts.href ? 'a' : 'div');
        body.className = 'app-toast-body';
        if (opts.href) body.href = opts.href;
        var b = document.createElement('b');
        b.className = 'app-toast-title';
        b.textContent = opts.title || tr(TITLES[type]);
        body.appendChild(b);
        if (opts.text) {
            var p = document.createElement('p');
            p.className = 'app-toast-text';
            p.textContent = opts.text;
            body.appendChild(p);
        }

        var close = document.createElement('button');
        close.type = 'button';
        close.className = 'app-toast-close';
        close.setAttribute('aria-label', tr('إغلاق'));
        close.innerHTML = '<i class="ti ti-x" aria-hidden="true"></i>';

        var timer = document.createElement('span');
        timer.className = 'app-toast-timer';
        timer.setAttribute('aria-hidden', 'true');

        el.append(icon, body, close, timer);
        stack().appendChild(el);
        wire(el);
        return el;
    };

    function boot() {
        document.querySelectorAll('.toast-stack .app-toast').forEach(function (el, i) {
            el.style.animationDelay = (i * 90) + 'ms';
            wire(el);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
