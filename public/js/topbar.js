/* ============================================================
   الشريط العلوي: البحث السريع (Command Palette)
   Ctrl+K للفتح، أسهم للتنقل، Enter للفتح، Esc للإغلاق.
   ============================================================ */
(function () {
    "use strict";

    var modalEl = document.getElementById("cmdk-modal");
    if (!modalEl || typeof bootstrap === "undefined") return;

    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    var input = document.getElementById("cmdk-input");
    var list = document.getElementById("cmdk-list");
    var empty = document.getElementById("cmdk-empty");
    var items = Array.prototype.slice.call(list.querySelectorAll(".cmdk-item"));
    var activeIndex = -1;

    function visibleItems() {
        return items.filter(function (el) { return !el.classList.contains("d-none"); });
    }

    function setActive(idx) {
        var vis = visibleItems();
        items.forEach(function (el) { el.classList.remove("active"); });
        if (vis.length === 0) { activeIndex = -1; return; }
        activeIndex = ((idx % vis.length) + vis.length) % vis.length;
        var el = vis[activeIndex];
        el.classList.add("active");
        el.scrollIntoView({ block: "nearest" });
    }

    function filter() {
        var q = input.value.trim().toLowerCase();
        var found = 0;
        items.forEach(function (el) {
            var match = (el.getAttribute("data-keys") || "").toLowerCase().indexOf(q) !== -1;
            el.classList.toggle("d-none", q !== "" && !match);
            if (q === "" || match) found++;
        });
        empty.classList.toggle("d-none", found > 0);
        setActive(0);
    }

    /* فتح/إغلاق بـ Ctrl+K أو Cmd+K */
    document.addEventListener("keydown", function (e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === "k" || e.key === "K")) {
            e.preventDefault();
            modal.toggle();
        }
    });

    modalEl.addEventListener("shown.bs.modal", function () {
        input.value = "";
        filter();
        input.focus();
    });

    input.addEventListener("input", filter);

    input.addEventListener("keydown", function (e) {
        if (e.key === "ArrowDown") {
            e.preventDefault();
            setActive(activeIndex + 1);
        } else if (e.key === "ArrowUp") {
            e.preventDefault();
            setActive(activeIndex - 1);
        } else if (e.key === "Enter") {
            e.preventDefault();
            var vis = visibleItems();
            var target = vis[activeIndex >= 0 ? activeIndex : 0];
            if (target) window.location.href = target.href;
        }
    });

    /* تمييز العنصر عند مرور الفأرة */
    items.forEach(function (el, i) {
        el.addEventListener("mouseenter", function () {
            items.forEach(function (x) { x.classList.remove("active"); });
            el.classList.add("active");
            activeIndex = visibleItems().indexOf(el);
        });
    });

    /* تمرير سلس عند الانتقال لمرساة داخل نفس الصفحة */
    document.addEventListener("click", function (e) {
        var a = e.target.closest('a[href*="#"]');
        if (!a) return;
        var url = new URL(a.href, window.location.origin);
        if (url.pathname === window.location.pathname && url.hash) {
            var target = document.querySelector(url.hash);
            if (target) {
                e.preventDefault();
                modal.hide();
                target.scrollIntoView({ behavior: "smooth", block: "start" });
                target.classList.add("section-flash");
                setTimeout(function () { target.classList.remove("section-flash"); }, 1600);
            }
        }
    });
})();
