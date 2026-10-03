/* =========================================================
   رفع الملفات بشريط تقدّم
   ---------------------------------------------------------
   يلتقط إرسال أي نموذج multipart فيه ملف مختار، ويرسله بطلب خفي ليُظهر
   النسبة والحجم والوقت المتبقي، مع «إلغاء». الخادم يردّ بعنوان الصفحة
   التالية (UploadResponse) فينتقل إليها السكربت — والنتيجة (نجاح أو
   أخطاء تحت الحقول) كما في الإرسال العادي تماماً.

   قبل الرفع: حجم أكبر من data-max-mb على الحقل يُرفض فوراً.
   يُحمَّل قبل سكربت «حالة التحميل» في التخطيط فيسبقه، وبعد تأكيد النموذج
   نفسه (onsubmit) فيحترمه. بلا JavaScript: إرسال عادي.
   ========================================================= */
(function () {
    'use strict';

    if (!window.XMLHttpRequest || !window.FormData) return;
    var tr = window.t || function (s) { return s; };

    function mb(bytes) { return (bytes / 1048576).toFixed(bytes < 10485760 ? 1 : 0); }
    function humanSize(bytes) {
        return bytes >= 1048576 ? mb(bytes) + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }
    function eta(sec) {
        if (!isFinite(sec) || sec <= 0) return '';
        if (sec < 60) return tr('نحو :n ثانية', { n: Math.max(1, Math.round(sec)) });
        return tr('نحو :n دقيقة', { n: Math.round(sec / 60) });
    }

    function chosenFiles(form) {
        var files = [];
        form.querySelectorAll('input[type="file"]').forEach(function (input) {
            Array.prototype.forEach.call(input.files || [], function (f) { files.push({ file: f, input: input }); });
        });
        return files;
    }

    // اللوحة داخل النموذج نفسه: شريط، ونسبة، وحجم، ووقت باقٍ، و«إلغاء»
    function panel(form) {
        // data-progress-into: نموذج صغير (زرّ وحده) يضع اللوحة في حاويته الأوسع
        var host = (form.dataset.progressInto && document.querySelector(form.dataset.progressInto)) || form;
        var el = host.querySelector('.up-progress');
        if (el) return el;
        el = document.createElement('div');
        el.className = 'up-progress';
        el.setAttribute('role', 'status');
        el.setAttribute('aria-live', 'polite');
        el.innerHTML =
            '<div class="up-top"><b class="up-pct">0%</b><span class="up-meta"></span>' +
            '<button type="button" class="up-cancel"></button></div>' +
            '<div class="up-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span></span></div>' +
            '<p class="up-msg" hidden></p>';
        el.querySelector('.up-cancel').textContent = tr('إلغاء');
        host.appendChild(el);
        return el;
    }

    function message(form, text) {
        var p = panel(form);
        p.hidden = false;
        p.classList.add('is-error');
        p.querySelector('.up-top').hidden = true;
        p.querySelector('.up-bar').hidden = true;
        var m = p.querySelector('.up-msg');
        m.hidden = false;
        m.textContent = text;
    }

    function setBusy(form, on) {
        form.classList.toggle('is-uploading', on);
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (b) { b.disabled = on; });
    }

    var uploading = 0;
    window.addEventListener('beforeunload', function (e) {
        if (uploading > 0) { e.preventDefault(); e.returnValue = ''; }
    });

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (e.defaultPrevented || form.tagName !== 'FORM') return;
        if ((form.getAttribute('enctype') || '').toLowerCase() !== 'multipart/form-data') return;
        if (form.hasAttribute('data-no-progress')) return;

        var files = chosenFiles(form);
        if (!files.length) return; // بلا ملف: الإرسال العادي

        // الحجم قبل الرفع — لا انتظار دقيقة لرفض معروف
        for (var i = 0; i < files.length; i++) {
            var max = parseFloat(files[i].input.getAttribute('data-max-mb') || '0');
            if (max && files[i].file.size > max * 1048576) {
                e.preventDefault();
                message(form, tr('الملف «:name» حجمه :size — الحدّ :max ميغابايت.', {
                    name: files[i].file.name, size: humanSize(files[i].file.size), max: max,
                }));
                return;
            }
        }

        e.preventDefault();
        var total = files.reduce(function (s, f) { return s + f.file.size; }, 0);
        var p = panel(form);
        p.hidden = false;
        p.classList.remove('is-error', 'is-saving');
        p.querySelector('.up-top').hidden = false;
        p.querySelector('.up-bar').hidden = false;
        p.querySelector('.up-msg').hidden = true;
        var pct = p.querySelector('.up-pct'), meta = p.querySelector('.up-meta'), bar = p.querySelector('.up-bar');
        var cancel = p.querySelector('.up-cancel');
        cancel.hidden = false;

        var xhr = new XMLHttpRequest();
        var started = Date.now();
        xhr.open((form.getAttribute('method') || 'POST').toUpperCase(), form.action);
        // صفحة لا JSON: أخطاء التحقّق تعود توجيهاً (لا 422) فيحوّله UploadResponse
        xhr.setRequestHeader('Accept', 'text/html');
        xhr.setRequestHeader('X-Upload', '1');

        xhr.upload.onprogress = function (ev) {
            var loaded = ev.loaded, size = ev.lengthComputable ? ev.total : total;
            var ratio = size ? Math.min(1, loaded / size) : 0;
            var n = Math.round(ratio * 100);
            pct.textContent = n + '%';
            bar.firstChild.style.width = n + '%';
            bar.setAttribute('aria-valuenow', n);
            var speed = loaded / Math.max(0.25, (Date.now() - started) / 1000);
            meta.textContent = tr(':done من :total', { done: humanSize(loaded), total: humanSize(size) })
                + (ratio < 1 ? ' · ' + eta((size - loaded) / speed) : '');
        };
        // وصل الملف كاملاً: الخادم يحفظه الآن
        xhr.upload.onload = function () {
            p.classList.add('is-saving');
            pct.textContent = '100%';
            bar.firstChild.style.width = '100%';
            meta.textContent = tr('جارٍ الحفظ…');
            cancel.hidden = true;
        };

        function done() { uploading = Math.max(0, uploading - 1); setBusy(form, false); }

        xhr.onload = function () {
            done();
            var data = null;
            try { data = JSON.parse(xhr.responseText); } catch (err) { /* صفحة لا JSON */ }
            if (xhr.status >= 200 && xhr.status < 300 && data && data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            if (xhr.status === 419) { window.location.reload(); return; }
            if (xhr.status === 413) { message(form, tr('الملف أكبر مما يقبله الخادم.')); return; }
            // ردّ غير متوقّع (صفحة كاملة): نعرضه كما لو أُرسل النموذج عادياً
            if (xhr.status >= 200 && xhr.status < 300 && xhr.responseURL) { window.location.href = xhr.responseURL; return; }
            message(form, tr('تعذّر الرفع — حاول مرة أخرى.'));
        };
        xhr.onerror = function () { done(); message(form, tr('انقطع الاتصال أثناء الرفع — الملف ما زال مختاراً، أعد المحاولة.')); };
        xhr.onabort = function () { done(); p.hidden = true; };
        cancel.onclick = function () { xhr.abort(); };

        uploading++;
        setBusy(form, true);
        xhr.send(new FormData(form));
    });
})();
