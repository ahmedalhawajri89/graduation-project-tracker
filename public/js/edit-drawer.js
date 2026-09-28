/**
 * درج التعديل — للطالب والمشرف والمسؤول (\u200Ex-edit-drawer\u200E).
 *
 * زرّ التعديل في الجدول يحمل السجلّ في \u200Edata-record\u200E (JSON واحد). عند الفتح:
 * تُملأ الحقول ورأس الهوية، وتُحفظ لقطة القيم الأصلية، فيُعلَّم كل حقل تغيّر
 * ويعدّ التذييل التغييرات، ولا يُفعَّل «حفظ» بلا تغيير. والإغلاق بتغييرات
 * غير محفوظة يسأل. وبعد فشل التحقّق يُعاد فتح الدرج للسجلّ نفسه بقيمه.
 *
 * كان هذا jQuery منسوخاً في ثلاث صفحات يقرأ عشر خصائص \u200Edata-*\u200E.
 */
(function () {
    'use strict';

    var drawer = document.querySelector('[data-edit-drawer]');
    if (!drawer || typeof bootstrap === 'undefined') return;

    var form = drawer.querySelector('[data-ed-form]');
    var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(drawer);
    var saveBtn = drawer.querySelector('[data-ed-save]');
    var changesEl = drawer.querySelector('[data-ed-changes]');

    // الحقول التي تُقارَن بالأصل — ما في السجلّ وله حقل في النموذج
    var TRACKED = ['name', 'university_id', 'specialize_id', 'email', 'phone', 'gender', 'max_group'];

    var original = {};
    var submitting = false;

    function fieldsNamed(name) {
        return Array.prototype.slice.call(form.querySelectorAll('[name="' + name + '"]'));
    }

    function getValue(name) {
        var els = fieldsNamed(name);
        if (!els.length) return undefined;
        if (els[0].type === 'radio') {
            var checked = els.filter(function (el) { return el.checked; })[0];
            return checked ? checked.value : '';
        }
        return els[0].value;
    }

    function setValue(name, value) {
        var els = fieldsNamed(name);
        if (!els.length) return;
        var v = value === null || value === undefined ? '' : String(value);
        if (els[0].type === 'radio') {
            els.forEach(function (el) { el.checked = el.value === v; });
        } else {
            els[0].value = v;
        }
    }

    /* ---------- الرأس ---------- */
    function fillHead(record) {
        var avatar = drawer.querySelector('[data-ed-avatar]');
        avatar.textContent = '';
        avatar.classList.toggle('has-photo', !!record.avatar_url);
        if (record.avatar_url) {
            var img = document.createElement('img');
            img.src = record.avatar_url;
            img.alt = '';
            avatar.appendChild(img);
        } else {
            avatar.textContent = record.initials || '؟';
        }

        drawer.querySelector('[data-ed-name]').textContent = record.name || '';
        drawer.querySelector('[data-ed-sub]').textContent = record.sub || '';

        var status = drawer.querySelector('[data-ed-status]');
        if (record.status) {
            status.textContent = record.status.label;
            status.dataset.tone = record.status.tone || '';
            status.hidden = false;
        } else {
            status.hidden = true;
        }

        var actions = drawer.querySelector('[data-ed-avatar-actions]');
        if (actions) {
            actions.hidden = !record.avatar_url;
            setRemoveAvatar(false);
        }
    }

    /* ---------- إزالة الصورة ---------- */
    function setRemoveAvatar(on) {
        var input = drawer.querySelector('[data-ed-remove]');
        if (!input) return;
        input.value = on ? '1' : '0';
        drawer.querySelector('[data-ed-remove-toggle]').hidden = on;
        drawer.querySelector('[data-ed-remove-note]').hidden = !on;
        drawer.querySelector('[data-ed-avatar]').classList.toggle('is-removing', on);
        refresh();
    }
    var removeToggle = drawer.querySelector('[data-ed-remove-toggle]');
    if (removeToggle) {
        removeToggle.addEventListener('click', function () { setRemoveAvatar(true); });
        drawer.querySelector('[data-ed-remove-undo]').addEventListener('click', function () { setRemoveAvatar(false); });
    }

    /* ---------- كلمة السر ---------- */
    var pw = {
        wrap: drawer.querySelector('[data-ed-password]'),
        input: drawer.querySelector('#ed-password'),
        confirm: drawer.querySelector('[data-ed-password-confirm]'),
        open: drawer.querySelector('[data-ed-password-open]'),
        body: drawer.querySelector('[data-ed-password-body]'),
        strength: drawer.querySelector('[data-ed-strength]'),
        label: drawer.querySelector('[data-ed-strength-label]')
    };

    function setPasswordOpen(on) {
        if (!pw.wrap) return;
        pw.wrap.classList.toggle('is-open', on);
        pw.open.hidden = on;
        pw.body.hidden = !on;
        // مغلقة = معطّلة فلا تُرسل: «فارغة» لا تعني شيئاً للخادم ولا للقارئ
        pw.input.disabled = !on;
        if (pw.confirm) pw.confirm.disabled = !on;
        if (!on) {
            pw.input.value = '';
            pw.input.type = 'password';
            if (pw.confirm) pw.confirm.value = '';
        }
        strength();
        refresh();
        if (on) pw.input.focus();
    }

    function strength() {
        if (!pw.input) return;
        var v = pw.input.value;
        var level = 0;
        if (v.length >= 8) level++;
        if (v.length >= 12) level++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) level++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) level++;
        if (v.length < 8) level = v.length ? 1 : 0;
        pw.strength.dataset.level = level;
        pw.label.textContent = !v.length ? '8 أحرف على الأقل'
            : v.length < 8 ? 'قصيرة — 8 أحرف على الأقل'
            : ['', 'ضعيفة', 'مقبولة', 'جيدة', 'قوية'][level];
        if (pw.confirm) pw.confirm.value = v;
    }

    function generate() {
        // بلا الأحرف الملتبسة (0/O، 1/l/I) — تُملى على الهاتف أحياناً
        var sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%*?-'];
        var all = sets.join('');
        var bytes = new Uint32Array(14);
        window.crypto.getRandomValues(bytes);
        var chars = sets.map(function (s, i) { return s[bytes[i] % s.length]; });
        for (var i = sets.length; i < 14; i++) chars.push(all[bytes[i] % all.length]);
        // خلط فيشر-ييتس بعشوائية آمنة
        var order = new Uint32Array(chars.length);
        window.crypto.getRandomValues(order);
        for (var j = chars.length - 1; j > 0; j--) {
            var k = order[j] % (j + 1);
            var t = chars[j]; chars[j] = chars[k]; chars[k] = t;
        }
        return chars.join('');
    }

    if (pw.wrap) {
        pw.open.addEventListener('click', function () { setPasswordOpen(true); });
        drawer.querySelector('[data-ed-password-close]').addEventListener('click', function () { setPasswordOpen(false); });
        drawer.querySelector('[data-ed-password-show]').addEventListener('click', function () {
            var shown = pw.input.type === 'text';
            pw.input.type = shown ? 'password' : 'text';
            this.querySelector('.ti').className = 'ti ' + (shown ? 'ti-eye' : 'ti-eye-off');
        });
        drawer.querySelector('[data-ed-password-generate]').addEventListener('click', function () {
            pw.input.value = generate();
            // تظهر المولَّدة ليُنسخ ما سيُبلَّغ به صاحب الحساب
            pw.input.type = 'text';
            drawer.querySelector('[data-ed-password-show] .ti').className = 'ti ti-eye-off';
            strength();
            refresh();
            pw.input.select();
        });
        pw.input.addEventListener('input', strength);
    }

    /* ---------- العدّاد (الحدّ الأقصى للمجموعات) ---------- */
    drawer.querySelectorAll('[data-ed-step]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.parentElement.querySelector('input');
            var next = (parseInt(input.value, 10) || 0) + parseInt(btn.dataset.edStep, 10);
            input.value = Math.max(parseInt(input.min, 10) || 0, Math.min(parseInt(input.max, 10) || 99, next));
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
    });

    /* ---------- تتبّع التغييرات ---------- */
    function refresh() {
        var count = 0;
        TRACKED.forEach(function (name) {
            var now = getValue(name);
            if (now === undefined) return;
            var changed = String(now) !== String(original[name] === undefined || original[name] === null ? '' : original[name]);
            var wrap = form.querySelector('[data-ed-wrap="' + name + '"]');
            if (wrap) wrap.classList.toggle('is-changed', changed);
            if (changed) count++;
        });
        if (pw.input && !pw.input.disabled && pw.input.value) count++;
        var remove = drawer.querySelector('[data-ed-remove]');
        if (remove && remove.value === '1') count++;

        saveBtn.disabled = count === 0;
        changesEl.textContent = count === 0 ? 'لا تغييرات'
            : count === 1 ? 'تغيير واحد'
            : count === 2 ? 'تغييران'
            : count + ' تغييرات';
        changesEl.classList.toggle('is-dirty', count > 0);
        return count;
    }
    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);

    /* ---------- الفتح والإغلاق ---------- */
    function load(record, old) {
        original = record;
        form.querySelector('[name="id"]').value = record.id;
        TRACKED.forEach(function (name) { setValue(name, record[name]); });
        if (old) {
            TRACKED.forEach(function (name) {
                if (old[name] !== undefined) setValue(name, old[name]);
            });
        }
        fillHead(record);
        if (!old || !pw.wrap || !pw.wrap.classList.contains('is-open')) setPasswordOpen(false);
        else { pw.input.disabled = false; if (pw.confirm) pw.confirm.disabled = false; }
        submitting = false;
        saveBtn.classList.remove('is-saving');
        refresh();
    }

    function clearServerErrors() {
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('.invalid-feedback').forEach(function (el) { el.remove(); });
    }

    drawer.addEventListener('show.bs.offcanvas', function (e) {
        var trigger = e.relatedTarget && e.relatedTarget.closest('[data-record]');
        if (!trigger) return; // فتح برمجي بعد فشل التحقّق — مُحمَّل مسبقاً
        clearServerErrors();
        try {
            load(JSON.parse(trigger.getAttribute('data-record')));
        } catch (err) {
            e.preventDefault();
        }
    });

    drawer.addEventListener('shown.bs.offcanvas', function () {
        var first = form.querySelector('.is-invalid') || form.querySelector('[name="name"]');
        if (first) first.focus({ preventScroll: true });
    });

    drawer.addEventListener('hide.bs.offcanvas', function (e) {
        if (submitting || refresh() === 0) return;
        if (!window.confirm('لديك تغييرات لم تُحفظ. إغلاق الدرج وتجاهلها؟')) e.preventDefault();
    });

    form.addEventListener('submit', function () {
        submitting = true;
        saveBtn.classList.add('is-saving');
    });

    /* ---------- إعادة الفتح بعد فشل التحقّق ---------- */
    if (drawer.dataset.reopen) {
        try {
            var payload = JSON.parse(drawer.dataset.reopen);
            load(payload.record, payload.old || {});
            offcanvas.show();
        } catch (err) { /* بيانات تالفة: تبقى الصفحة كما هي */ }
    }
})();
