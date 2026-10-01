/**
 * درج التعديل والإضافة — للطالب والمشرف والمسؤول (\u200Ex-edit-drawer\u200E).
 *
 * التعديل: زرّ الجدول يحمل السجلّ في \u200Edata-record\u200E (JSON واحد). تُملأ الحقول
 * ورأس الهوية، وتُحفظ لقطة القيم الأصلية، فيُعلَّم كل حقل تغيّر ويعدّ التذييل
 * التغييرات، ولا يُفعَّل «حفظ» بلا تغيير.
 * الإضافة: نموذج فارغ، ورأس يتكوّن أثناء الكتابة (الأحرف الأولى والاسم)،
 * وكلمة السر مفتوحة مطلوبة، و«حفظ وإضافة آخر» يعيد الفتح فارغاً للتالي.
 * كلاهما: الإغلاق بتغييرات غير محفوظة يسأل، وفشل التحقّق يعيد الفتح بقيمه.
 *
 * كان هذا jQuery منسوخاً في ست نوافذ يقرأ عشر خصائص \u200Edata-*\u200E.
 */
(function () {
    'use strict';

    if (typeof bootstrap === 'undefined') return;

    // الحقول التي تُقارَن بالأصل — ما في السجلّ وله حقل في النموذج
    var TRACKED = ['name', 'university_id', 'specialize_id', 'email', 'phone', 'gender', 'max_group'];

    /* الأحرف الأولى كما في \u200EHasAvatar\u200E: «د.» و«أ.د.» بادئة لا تميّز أحداً */
    function initials(name) {
        var bare = String(name || '').replace(/^\s*(أ\.د\.|د\.|أ\.)\s*/, '').trim();
        return bare ? Array.from(bare).slice(0, 2).join('') : '';
    }

    function generatePassword() {
        // بلا الأحرف الملتبسة (0/O، 1/l/I) — تُملى على الهاتف أحياناً
        var sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%*?-'];
        var all = sets.join('');
        var bytes = new Uint32Array(14);
        window.crypto.getRandomValues(bytes);
        var chars = sets.map(function (s, i) { return s[bytes[i] % s.length]; });
        for (var i = sets.length; i < 14; i++) chars.push(all[bytes[i] % all.length]);
        var order = new Uint32Array(chars.length);
        window.crypto.getRandomValues(order);
        for (var j = chars.length - 1; j > 0; j--) {
            var k = order[j] % (j + 1);
            var t = chars[j]; chars[j] = chars[k]; chars[k] = t;
        }
        return chars.join('');
    }

    function init(drawer) {
        var create = drawer.dataset.mode === 'create';
        var form = drawer.querySelector('[data-ed-form]');
        var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(drawer);
        var saveBtn = drawer.querySelector('[data-ed-save]');
        var changesEl = drawer.querySelector('[data-ed-changes]');
        var nameEl = drawer.querySelector('[data-ed-name]');
        var avatarEl = drawer.querySelector('[data-ed-avatar]');
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
            } else if (els[0].tagName === 'SELECT' && v === '') {
                els[0].selectedIndex = 0;
            } else {
                els[0].value = v;
            }
        }

        /* ---------- الرأس ---------- */
        function paintAvatar(url, text) {
            avatarEl.textContent = '';
            avatarEl.classList.toggle('has-photo', !!url);
            avatarEl.classList.toggle('is-new', create && !text && !url);
            if (url) {
                var img = document.createElement('img');
                img.src = url;
                img.alt = '';
                avatarEl.appendChild(img);
            } else if (text) {
                avatarEl.textContent = text;
            } else {
                var icon = document.createElement('i');
                icon.className = 'ti ti-user-plus';
                avatarEl.appendChild(icon);
            }
        }

        function fillHead(record) {
            paintAvatar(record.avatar_url, record.initials || '؟');
            nameEl.textContent = record.name || '';
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

        // الإضافة: الرأس يتكوّن من الحقول نفسها أثناء الكتابة
        function liveHead() {
            if (!create) return;
            var name = (getValue('name') || '').trim();
            nameEl.textContent = name || nameEl.dataset.placeholder || '';
            nameEl.classList.toggle('is-placeholder', !name);
            paintAvatar(null, initials(name));
            drawer.querySelector('[data-ed-sub]').textContent =
                (getValue('university_id') || '').trim() || (getValue('email') || '').trim();
        }

        /* ---------- إزالة الصورة (التعديل) ---------- */
        function setRemoveAvatar(on) {
            var input = drawer.querySelector('[data-ed-remove]');
            if (!input) return;
            input.value = on ? '1' : '0';
            drawer.querySelector('[data-ed-remove-toggle]').hidden = on;
            drawer.querySelector('[data-ed-remove-note]').hidden = !on;
            avatarEl.classList.toggle('is-removing', on);
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
            input: drawer.querySelector('[data-ed-password-input]'),
            confirm: drawer.querySelector('[data-ed-password-confirm]'),
            open: drawer.querySelector('[data-ed-password-open]'),
            body: drawer.querySelector('[data-ed-password-body]'),
            strength: drawer.querySelector('[data-ed-strength]'),
            label: drawer.querySelector('[data-ed-strength-label]'),
            showBtn: drawer.querySelector('[data-ed-password-show]')
        };
        var alwaysOpen = pw.wrap && pw.wrap.hasAttribute('data-always-open');

        function setPasswordOpen(on) {
            if (!pw.wrap) return;
            if (alwaysOpen) on = true;
            pw.wrap.classList.toggle('is-open', on);
            if (pw.open) pw.open.hidden = on;
            pw.body.hidden = !on;
            // مغلقة = معطّلة فلا تُرسل: «فارغة» لا تعني شيئاً للخادم ولا للقارئ
            pw.input.disabled = !on;
            if (pw.confirm) pw.confirm.disabled = !on;
            strength();
            refresh();
        }
        function clearPassword() {
            if (!pw.input) return;
            pw.input.value = '';
            pw.input.type = 'password';
            if (pw.showBtn) pw.showBtn.querySelector('.ti').className = 'ti ti-eye';
            strength();
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
            pw.label.textContent = !v.length ? t('8 أحرف على الأقل')
                : v.length < 8 ? t('قصيرة — 8 أحرف على الأقل')
                : ['', t('ضعيفة'), t('مقبولة'), t('جيدة'), t('قوية')][level];
            if (pw.confirm) pw.confirm.value = v;
        }

        if (pw.wrap) {
            if (pw.open) {
                pw.open.addEventListener('click', function () { setPasswordOpen(true); pw.input.focus(); });
            }
            var closeBtn = drawer.querySelector('[data-ed-password-close]');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () { clearPassword(); setPasswordOpen(false); });
            }
            pw.showBtn.addEventListener('click', function () {
                var shown = pw.input.type === 'text';
                pw.input.type = shown ? 'password' : 'text';
                this.querySelector('.ti').className = 'ti ' + (shown ? 'ti-eye' : 'ti-eye-off');
            });
            drawer.querySelector('[data-ed-password-generate]').addEventListener('click', function () {
                pw.input.value = generatePassword();
                // تظهر المولَّدة ليُنسخ ما سيُبلَّغ به صاحب الحساب
                pw.input.type = 'text';
                pw.showBtn.querySelector('.ti').className = 'ti ti-eye-off';
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
                var base = original[name] === undefined || original[name] === null ? '' : String(original[name]);
                var changed = String(now) !== base;
                var wrap = form.querySelector('[data-ed-wrap="' + name + '"]');
                // في الإضافة لا «تغيّر» لكل حقل مملوء — النقطة للتعديل وحده
                if (wrap && !create) wrap.classList.toggle('is-changed', changed);
                if (changed) count++;
            });
            if (pw.input && !pw.input.disabled && pw.input.value) count++;
            var remove = drawer.querySelector('[data-ed-remove]');
            if (remove && remove.value === '1') count++;

            if (!create) {
                saveBtn.disabled = count === 0;
                changesEl.textContent = count === 0 ? t('لا تغييرات')
                    : count === 1 ? t('تغيير واحد')
                    : count === 2 ? t('تغييران')
                    : t(':n تغييرات', { n: count });
                changesEl.classList.toggle('is-dirty', count > 0);
            }
            liveHead();
            return count;
        }
        form.addEventListener('input', refresh);
        form.addEventListener('change', refresh);

        /* ---------- التحميل ---------- */
        function load(record, old) {
            original = create ? defaults() : record;
            var idInput = form.querySelector('[name="id"]');
            if (idInput) idInput.value = record.id || '';
            TRACKED.forEach(function (name) { setValue(name, original[name]); });
            if (old) {
                TRACKED.forEach(function (name) {
                    if (old[name] !== undefined && old[name] !== null) setValue(name, old[name]);
                });
            }
            if (create) {
                liveHead();
            } else {
                fillHead(record);
            }
            // بعد فشل التحقّق في كلمة السر يبقى حقلها مفتوحاً (يفتحه Blade)
            var keepOpen = !!old && pw.wrap && pw.wrap.classList.contains('is-open');
            clearPassword();
            setPasswordOpen(alwaysOpen || keepOpen);
            submitting = false;
            saveBtn.classList.remove('is-saving');
            refresh();
        }

        // القيم الابتدائية للإضافة — من \u200Edata-default\u200E على الحقل (مثل حدّ المجموعات)
        function defaults() {
            var d = {};
            form.querySelectorAll('[data-default]').forEach(function (el) { d[el.name] = el.dataset.default; });
            return d;
        }

        function clearServerErrors() {
            form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
            form.querySelectorAll('.invalid-feedback').forEach(function (el) { el.remove(); });
        }

        drawer.addEventListener('show.bs.offcanvas', function (e) {
            if (!e.relatedTarget) return; // فتح برمجي بعد فشل التحقّق — مُحمَّل مسبقاً
            clearServerErrors();
            if (create) {
                form.reset();
                load({}, null);
                return;
            }
            var trigger = e.relatedTarget.closest('[data-record]');
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
            if (!window.confirm(t('لديك بيانات لم تُحفظ. إغلاق الدرج وتجاهلها؟'))) e.preventDefault();
        });

        form.addEventListener('submit', function (e) {
            submitting = true;
            var btn = e.submitter || saveBtn;
            btn.classList.add('is-saving');
        });

        /* ---------- إعادة الفتح ---------- */
        if (drawer.dataset.reopen) {
            try {
                var payload = JSON.parse(drawer.dataset.reopen);
                load(payload.record || {}, payload.old || null);
                offcanvas.show();
            } catch (err) { /* بيانات تالفة: تبقى الصفحة كما هي */ }
        }
    }

    document.querySelectorAll('[data-edit-drawer]').forEach(init);
})();
