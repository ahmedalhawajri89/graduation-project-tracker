/**
 * شخصيات صفحات الدخول على منحنى المسار (login/_path.blade.php).
 *
 * المنحنى مرسوم بنظام 1440×900 ممطوطاً على الشاشة (preserveAspectRatio=none)،
 * فلا تُوضع الشخصيات داخله وإلا تمطّطت معه: يُحسب لكل واحدة موضعها على
 * المنحنى (getPointAtLength) ويُحوَّل إلى بكسلات، وتُرسم HTML فوقه.
 * في LTR يُعكس المنحنى، فتُعكس النقاط معه.
 *
 * data-near="x,y"  أقرب نقطة على المنحنى (المحطّة)، و data-dt  إزاحة على طوله
 * (نسبة من 0 إلى 1). الفريق يمشي من حافة الشاشة حتى موضعه، والأرجل تتحرّك
 * بالصنف is-walking على .auth-path (حيث الـ defs).
 */
(function () {
    'use strict';

    var cast = document.querySelector('[data-auth-cast]');
    if (!cast) return;
    var stage = cast.closest('.auth-path');
    var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', cast.dataset.route);
    var total = path.getTotalLength();
    var rtl = document.documentElement.dir === 'rtl';
    var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var VW = 1440, VH = 900;

    // أقرب نقطة على المنحنى — بحث خشن ثم دقيق
    function nearest(x, y) {
        var best = 0, bestD = Infinity, i, p, d;
        for (i = 0; i <= 400; i++) {
            p = path.getPointAtLength(total * i / 400);
            d = (p.x - x) * (p.x - x) + (p.y - y) * (p.y - y);
            if (d < bestD) { bestD = d; best = i / 400; }
        }
        for (i = -10; i <= 10; i++) {
            var t = Math.min(1, Math.max(0, best + i / 4000));
            p = path.getPointAtLength(total * t);
            d = (p.x - x) * (p.x - x) + (p.y - y) * (p.y - y);
            if (d < bestD) { bestD = d; best = t; }
        }
        return best;
    }

    function tOf(el) {
        var xy = el.dataset.near.split(',').map(Number);
        return nearest(xy[0], xy[1]) + parseFloat(el.dataset.dt || 0);
    }

    // القدمان عند أسفل منتصف العنصر — تُوضعان على نقطة المنحنى
    function place(el, t) {
        var p = path.getPointAtLength(total * Math.min(1, Math.max(0, t)));
        var x = p.x / VW * window.innerWidth;
        if (!rtl) x = window.innerWidth - x;
        var y = p.y / VH * window.innerHeight;
        var vb = el.viewBox.baseVal;
        var h = el.getBoundingClientRect().height || el.clientHeight;
        var w = h * vb.width / vb.height;
        // أسفل الـ viewBox تحت القدمين بقليل (الظلّ)
        var feet = h * (vb.y + vb.height) / vb.height;
        el.style.transform = 'translate(' + (x - w / 2) + 'px,' + (y - h + feet) + 'px)';
    }

    var actors = Array.prototype.slice.call(cast.querySelectorAll('.cast-actor'));
    var team = cast.querySelector('.cast-team');
    var walkers = Array.prototype.slice.call(team.querySelectorAll('.cast-walker'));
    var GAP = .024; // المسافة بين الطلاب على المنحنى
    var stopAt = tOf(team);
    function placeTeam(t) { walkers.forEach(function (el, i) { place(el, t - i * GAP); }); }
    // يبدأ حيث يدخل المنحنى الشاشة
    var startAt = nearest(VW, 170);
    var arrived = still;

    function layout() {
        actors.forEach(function (el) { place(el, tOf(el)); });
        if (arrived) placeTeam(stopAt);
    }

    layout();
    cast.classList.add('is-ready');
    window.addEventListener('resize', layout);

    if (still) { cast.classList.add('is-arrived'); return; }

    // مشي على طول المنحنى بتباطؤ عند الوصول، بعد أن يُرسم الجزء الذي مضى
    var DUR = 3400, DELAY = 900, t0 = null;
    placeTeam(startAt);
    setTimeout(function () {
        stage.classList.add('is-walking');
        requestAnimationFrame(function step(now) {
            if (t0 === null) t0 = now;
            var k = Math.min(1, (now - t0) / DUR);
            var e = 1 - Math.pow(1 - k, 2.2);
            placeTeam(startAt + (stopAt - startAt) * e);
            if (k < 1) return requestAnimationFrame(step);
            arrived = true;
            stage.classList.remove('is-walking');
            cast.classList.add('is-arrived');
        });
    }, DELAY);
})();
