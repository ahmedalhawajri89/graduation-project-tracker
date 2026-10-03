/**
 * الجولة التعريفية (layouts/admin/inc/_tour.blade.php).
 *
 * تظهر مرة لكل مستخدم في هذا المتصفّح (data-tour-key)، وتُعاد بأي عنصر
 * يحمل data-tour-start. «بقعة» بظلّ واسع تضيء العنصر وتعتم ما حوله،
 * وبطاقة بشخصية الدور تشرحه. Esc يتخطّى، والأسهم تتنقّل.
 */
(function () {
    'use strict';

    var cfg = document.querySelector('[data-tour]');
    if (!cfg) return;
    var all = JSON.parse(cfg.dataset.tourSteps || '[]');
    var L = JSON.parse(cfg.dataset.tourText || '{}');
    var key = cfg.dataset.tourKey;
    var rtl = document.documentElement.dir === 'rtl';
    var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var steps, i, root, spot, pop, back;

    function visible(sel) {
        var el = sel && document.querySelector(sel);
        if (!el) return null;
        var r = el.getBoundingClientRect();
        return r.width && r.height && getComputedStyle(el).visibility !== 'hidden' ? el : null;
    }

    function build() {
        root = document.createElement('div');
        root.className = 'tour';
        root.innerHTML =
            '<div class="tour-spot is-center"></div>' +
            '<div class="tour-pop" role="dialog" aria-modal="true" aria-labelledby="tour-title" aria-describedby="tour-text">' +
            '<svg class="tour-who" viewBox="-34 -100 68 106" aria-hidden="true"><use href="#' + cfg.dataset.tourWho + '"/></svg>' +
            '<div class="tour-body"><small class="tour-count"></small><h2 class="tour-title" id="tour-title"></h2><p class="tour-text" id="tour-text"></p></div>' +
            '<div class="tour-foot"><span class="tour-dots" aria-hidden="true"></span>' +
            '<button type="button" class="tour-skip"></button>' +
            '<button type="button" class="tour-prev btn btn-outline-secondary"></button>' +
            '<button type="button" class="tour-next btn btn-primary"></button></div></div>';
        document.body.appendChild(root);
        spot = root.querySelector('.tour-spot');
        pop = root.querySelector('.tour-pop');
        root.querySelector('.tour-skip').textContent = L.skip;
        root.querySelector('.tour-prev').textContent = L.prev;
        root.querySelector('.tour-skip').addEventListener('click', end);
        root.querySelector('.tour-prev').addEventListener('click', function () { go(i - 1); });
        root.querySelector('.tour-next').addEventListener('click', function () { i < steps.length - 1 ? go(i + 1) : end(); });
        // النقر على العتمة لا يغلق الجولة صدفةً — التخطّي زرّ صريح
        root.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { e.preventDefault(); end(); }
            else if (e.key === (rtl ? 'ArrowLeft' : 'ArrowRight')) { e.preventDefault(); if (i < steps.length - 1) go(i + 1); }
            else if (e.key === (rtl ? 'ArrowRight' : 'ArrowLeft')) { e.preventDefault(); if (i > 0) go(i - 1); }
            else if (e.key === 'Tab') {
                // التركيز يبقى داخل البطاقة
                var f = Array.prototype.filter.call(pop.querySelectorAll('button'), function (b) { return !b.hidden; });
                var first = f[0], last = f[f.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });
        window.addEventListener('resize', place);
        window.addEventListener('scroll', place, { passive: true });
    }

    function place() {
        if (!root || !steps) return;
        var s = steps[i], el = s.target && visible(s.target);
        var vw = document.documentElement.clientWidth, vh = document.documentElement.clientHeight, pad = 8, gap = 14;
        var pw = pop.offsetWidth, ph = pop.offsetHeight;
        if (!el) {
            spot.classList.add('is-center');
            spot.style.cssText = 'left:' + vw / 2 + 'px;top:' + vh / 2 + 'px;width:0;height:0;';
            pop.style.left = Math.max(12, (vw - pw) / 2) + 'px';
            pop.style.top = Math.max(12, (vh - ph) / 2) + 'px';
            return;
        }
        spot.classList.remove('is-center');
        var r = el.getBoundingClientRect();
        var top = Math.max(4, r.top - pad), left = Math.max(4, r.left - pad);
        var h = Math.min(r.height + pad * 2, vh - top - 4), w = Math.min(r.width + pad * 2, vw - left - 4);
        spot.style.cssText = 'left:' + left + 'px;top:' + top + 'px;width:' + w + 'px;height:' + h + 'px;';
        // البطاقة تحت العنصر إن اتّسع المكان، وإلا فوقه، وإلا بجانبه
        var x, y;
        if (top + h + gap + ph < vh) { y = top + h + gap; x = left + w / 2 - pw / 2; }
        else if (top - gap - ph > 0) { y = top - gap - ph; x = left + w / 2 - pw / 2; }
        else {
            y = Math.min(vh - ph - 12, Math.max(12, top));
            var after = rtl ? left - gap - pw : left + w + gap;
            var before = rtl ? left + w + gap : left - gap - pw;
            x = after >= 12 && after + pw <= vw - 12 ? after : before;
        }
        // هامش أوسع أفقياً: شريط تمرير اللوحة داخلي فلا يُطرح من clientWidth
        pop.style.left = Math.min(vw - pw - 26, Math.max(26, x)) + 'px';
        pop.style.top = Math.min(vh - ph - 12, Math.max(12, y)) + 'px';
    }

    function go(n) {
        i = Math.max(0, Math.min(steps.length - 1, n));
        var s = steps[i], el = s.target && visible(s.target);
        if (el) {
            var r = el.getBoundingClientRect();
            if (r.top < 70 || r.bottom > window.innerHeight - 40) el.scrollIntoView({ block: 'center', behavior: 'instant' });
        }
        pop.querySelector('.tour-title').textContent = s.title;
        pop.querySelector('.tour-text').textContent = s.text;
        pop.querySelector('.tour-count').textContent = L.of.replace(':n', i + 1).replace(':total', steps.length);
        var dots = pop.querySelector('.tour-dots');
        dots.innerHTML = '';
        steps.forEach(function (_, k) { var d = document.createElement('i'); if (k === i) d.className = 'is-on'; dots.appendChild(d); });
        pop.querySelector('.tour-prev').hidden = i === 0;
        pop.querySelector('.tour-skip').hidden = i === steps.length - 1;
        pop.querySelector('.tour-next').textContent = i === steps.length - 1 ? L.done : L.next;
        pop.classList.remove('is-in'); void pop.offsetWidth; pop.classList.add('is-in');
        place();
        pop.querySelector('.tour-next').focus({ preventScroll: true });
    }

    function start() {
        // الخطوات التي تظهر عناصرها الآن (الجوال يخفي الشريط الجانبي ويُظهر زرّ القائمة)
        steps = all.filter(function (s) { return !s.target || visible(s.target); });
        if (!steps.length) return;
        if (!root) build();
        back = document.activeElement;
        document.documentElement.classList.add('is-touring');
        root.hidden = false;
        go(0);
    }

    function end() {
        try { localStorage.setItem(key, '1'); } catch (e) {}
        document.documentElement.classList.remove('is-touring');
        root.classList.add('is-leaving');
        setTimeout(function () { root.hidden = true; root.classList.remove('is-leaving'); }, still ? 0 : 250);
        if (back && back.focus) back.focus({ preventScroll: true });
    }

    document.addEventListener('click', function (e) {
        var t = e.target.closest('[data-tour-start]');
        if (!t) return;
        e.preventDefault();
        start();
    });

    var seen = true;
    try { seen = !!localStorage.getItem(key); } catch (e) {}
    // بعد أن تستقرّ الصفحة، ولا فوق نافذة احتفال أو تأكيد مفتوحة
    if (!seen) setTimeout(function () {
        if (document.querySelector('dialog[open]')) {
            document.querySelector('dialog[open]').addEventListener('close', function () { setTimeout(start, 400); }, { once: true });
        } else start();
    }, 900);
})();
