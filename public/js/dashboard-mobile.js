/*
 * اللوحات على الجوال — سلوك مشترك لكل الصفحات.
 *
 * ١) الفلاتر: كانت تأخذ الشاشة الأولى كاملة قبل أي نتيجة. على الشاشات
 *    الضيقة يبقى البحث ظاهراً وتُطوى بقية القوائم خلف زر «تصفية» يحمل
 *    عدد المفعّل منها. على الكمبيوتر لا يتغيّر شيء (الزر مخفي بـ CSS).
 * ٢) الجداول: على الجوال يصير كل صف بطاقة، وكل خلية تحمل عنوان عمودها
 *    (data-label) من رأس الجدول — يُعاد بعد كل رسم لـ DataTables.
 */
(function () {
    'use strict';

    // ---------- ١) الفلاتر ----------
    document.querySelectorAll('.filter-form').forEach(function (form, i) {
        var extra = form.querySelectorAll('.filter-field:not(.filter-field--search), .filter-actions');
        if (!extra.length) return;

        // المفعّل: قائمة أولها «الكل» (قيمة فارغة) واختير غيره
        var active = Array.prototype.filter.call(form.querySelectorAll('select'), function (s) {
            return s.options.length && s.options[0].value === '' && s.value !== '';
        }).length;

        var id = 'filter-more-' + i;
        extra.forEach(function (el) { el.setAttribute('data-filter-more', id); });

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'filter-toggle';
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML =
            '<i class="ti ti-adjustments-horizontal" aria-hidden="true"></i><span>' + t('تصفية') + '</span>' +
            (active ? '<b class="filter-toggle-count">' + active + '</b>' : '') +
            '<i class="ti ti-chevron-down filter-toggle-caret" aria-hidden="true"></i>';

        var search = form.querySelector('.filter-field--search');
        if (search && search.parentNode === form) search.insertAdjacentElement('afterend', btn);
        else form.insertBefore(btn, form.firstChild);

        form.classList.add('is-collapsible');
        btn.addEventListener('click', function () {
            var open = form.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    // ---------- ٢) عناوين الأعمدة داخل الخلايا ----------
    function labelCells(table) {
        var heads = Array.prototype.map.call(table.querySelectorAll('thead th'), function (th) {
            return th.textContent.replace(/\s+/g, ' ').trim();
        });
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            Array.prototype.forEach.call(tr.children, function (td, i) {
                td.setAttribute('data-label', heads[i] || '');
            });
        });
    }
    window.dtLabelCells = labelCells;
    document.querySelectorAll('table.table').forEach(labelCells);
})();
