/* ============================================================
   Premium redesign — interactions & i18n
   ============================================================ */
(function () {
  "use strict";

  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var finePointer = window.matchMedia("(pointer: fine)").matches;

  /* ==================== i18n ==================== */
  var dict = {
    ar: {
      dir: "rtl",
      "nav.home": "الرئيسية",
      "nav.about": "عن المنصة",
      "nav.services": "الخدمات",
      "nav.features": "الميزات",
      "nav.roles": "الأدوار",
      "nav.contact": "اتصل بنا",
      "nav.login": "تسجيل دخول",
      "nav.dashboard": "لوحة التحكم",
      "nav.logout": "تسجيل خروج",
      "brand.name": "تخرُّج",
      "brand.sub": "منصة متابعة مشاريع التخرج",
      "hero.badge": "منصة ذكية لإدارة مشاريع التخرج",
      "hero.title": "تتبّع مشروع تخرجك<br><span class=\"text-gradient\">من الفكرة إلى المناقشة</span>",
      "hero.sub": "منصة تخرُّج لإدارة الفرق، اختيار المشرفين، ومتابعة مراحل المشروع بتجربة عصرية وسلسة.",
      "hero.cta1": "ابدأ الآن",
      "hero.cta2": "اكتشف المزيد",
      "hero.dashTitle": "لوحة متابعة المشروع",
      "hero.dashProject": "نظام تتبع ذكي",
      "hero.dashPhase": "المرحلة: التنفيذ",
      "hero.progress": "تقدم المشروع",
      "hero.approved": "تمت الموافقة على المشروع",
      "hero.approvedSub": "المشرف الأكاديمي · قبل دقيقتين",
      "hero.team": "أعضاء الفريق",
      "hero.notif": "إشعار جديد",
      "hero.notifSub": "ردّ المشرف على طلبك",
      "stats.1": "تخصصاً أكاديمياً",
      "stats.2": "مشرفاً أكاديمياً",
      "stats.3": "مشروع تخرج مسجل",
      "stats.4": "طالباً مسجلاً",
      "about.kicker": "عن المنصة",
      "about.title": "منصة تخرُّج",
      "about.text": "تخرُّج منصة مستقلة لإدارة مشاريع التخرج من أول تكوين الفريق واختيار المشرف، مروراً باعتماد الفكرة ومتابعة المراحل، وصولاً إلى المناقشة والتقييم النهائي — كل ذلك في مكان واحد وبسير عمل واضح لكل طرف.",
      "about.pillarsTitle": "ركائز المنصة",
      "about.p1": "إدارة الفرق الطلابية والمشرفين",
      "about.p2": "متابعة مراحل المشروع ونِسب الإنجاز",
      "about.p3": "المناقشة والتقييم ورصد الدرجات",
      "about.more": "المزيد",
      "services.kicker": "الخدمات",
      "services.title": "كل ما يحتاجه مشروعك في مكان واحد",
      "services.text": "نظام متكامل يدير كافة العمليات من تسجيل الدخول واختيار الفريق والمشرف، وصولاً إلى مناقشة المشروع وتقييمه.",
      "services.1.title": "سهولة اختيار الفريق",
      "services.1.text": "يساعدك الموقع على توفير الوقت والجهد اللازم لإنجاز المشروع وتكوين فريقك بسلاسة.",
      "services.2.title": "تتبع المشروع",
      "services.2.text": "متابعة مباشرة لمسار المشروع بين الطالب والمشرف في كل مرحلة من مراحله.",
      "services.3.title": "إدارة الفرق والمشرفين",
      "services.3.text": "تواصل مباشر وفعّال بين أعضاء الفريق ومع المشرف طوال دورة حياة المشروع.",
      "features.kicker": "المميزات",
      "features.title": "منصة صُممت لنجاح مشروعك",
      "features.text": "يهدف النظام إلى إدارة كافة العمليات من مرحلة تسجيل الدخول واختيار الفريق والمشرف وصولاً إلى مرحلة مناقشة المشروع وتقييمه.",
      "features.1.title": "مراحل مشروع بنسبة إنجاز مباشرة",
      "features.1.text": "مشرفك يحدد مراحل مشروعك بتواريخ استحقاق، وأنت تتابع نسبة الإنجاز على خط زمني مرئي يميز المنجز والمتأخر تلقائياً.",
      "features.2.title": "مستكشف المشاريع السابقة",
      "features.2.text": "تصفّح مشاريع الدفعات السابقة بأنواعها ومشرفيها — استلهم فكرتك وتأكد أنها غير منفّذة قبل التقديم.",
      "features.3.title": "نقاش مدمج وإشعارات فورية",
      "features.3.text": "اسأل مشرفك وناقش فريقك داخل صفحة المشروع نفسها، واستلم إشعاراً لكل رد أو مرحلة تُنجز أو ملف يُرفع.",
      "features.4.title": "تقييم إلكتروني بدرجة وتقدير",
      "features.4.text": "بعد اكتمال مشروعك يرصد المشرف درجتك النهائية مع التقدير وملاحظاته الختامية — وتصلك النتيجة بإشعار فوري.",
      "how.kicker": "كيف يعمل النظام؟",
      "how.title": "أربع خطوات من الفكرة إلى الدرجة",
      "how.text": "حسابك يُنشأ من إدارة المنصة — لا حاجة للتسجيل، فقط سجّل دخولك وابدأ.",
      "how.1.title": "سجّل دخولك",
      "how.1.text": "ببريدك أو رقمك الجامعي — الحسابات جاهزة مسبقاً من إدارة المنصة.",
      "how.2.title": "كوّن فريقك وقدّم فكرتك",
      "how.2.text": "اختر زملاءك من قائمة المتاحين، واختر مشرفاً لديه مقاعد، واكتب فكرة مشروعك.",
      "how.3.title": "تابع وناقش وارفع",
      "how.3.text": "بعد موافقة المشرف: مراحل بنسبة إنجاز، نقاش مباشر، ملفات، وإشعار لكل جديد.",
      "how.4.title": "ناقش واستلم تقييمك",
      "how.4.text": "بعد المناقشة يرصد مشرفك درجتك النهائية بالتقدير وملاحظاته — وتصلك فوراً.",
      "faq.kicker": "الأسئلة الشائعة",
      "faq.title": "كل ما يسأله الطلاب قبل البدء",
      "faq.text": "إجابات مباشرة من واقع النظام — ولأي سؤال آخر تواصل معنا من قسم الاتصال بالأسفل.",
      "faq.1.title": "كم عضواً يتكون منه الفريق؟",
      "faq.1.text": "حسب نوع المشروع الذي يحدده قسمك — كل نوع له حد أدنى وأقصى يظهران أمامك في نموذج التقديم، والنظام لا يقبل فريقاً خارج الحدود.",
      "faq.2.title": "كيف أقدم طلب مشروع؟",
      "faq.2.text": "سجّل دخولك ← اختر نوع المشروع ومشرفاً لديه مقاعد متاحة ← اختر أعضاء فريقك من القائمة ← اكتب العنوان والوصف وأرسل. سيصل طلبك للمشرف فوراً.",
      "faq.3.title": "كيف أعرف رد المشرف على طلبي؟",
      "faq.3.text": "يصلك إشعار داخل النظام فور القبول أو الرفض — تجده في جرس الإشعارات وفي لوحتك الرئيسية مع كل تحديث لاحق على مشروعك.",
      "faq.4.title": "ماذا لو رُفض مشروعي؟",
      "faq.4.text": "يصلك سبب الرفض الذي كتبه المشرف مع الإشعار، ويفتح النظام لك نموذج تقديم جديد مباشرة — عدّل فكرتك أو اختر مشرفاً آخر وأعد الإرسال.",
      "faq.5.title": "كيف يُقيَّم مشروعي النهائي؟",
      "faq.5.text": "خلال التنفيذ تتابع نسبة إنجاز مراحلك أولاً بأول، وبعد المناقشة يرصد مشرفك الدرجة النهائية من 100 مع التقدير وملاحظاته — وتظهر في لوحتك مع إشعار لكل الفريق.",
      "roles.kicker": "أدوار المنصة",
      "roles.title": "لكل دور مساحته الخاصة",
      "roles.text": "تخرُّج مبنية حول ثلاثة أدوار متكاملة، لكل منها لوحة تحكم وصلاحيات تناسب مهامه، بحيث يعرف كل طرف ما عليه بالضبط في كل مرحلة.",
      "roles.student.name": "الطالب", "roles.student.role": "تكوين الفريق وتقديم الفكرة",
      "roles.student.p1": "اختيار زملاء الفريق من قائمة المتاحين",
      "roles.student.p2": "اختيار مشرف لديه مقاعد شاغرة",
      "roles.student.p3": "متابعة المراحل والتعليقات ورفع الملفات",
      "roles.supervisor.name": "المشرف", "roles.supervisor.role": "المتابعة والاعتماد والتقييم",
      "roles.supervisor.p1": "استعراض طلبات الفرق واعتماد الأفكار",
      "roles.supervisor.p2": "تحديث نِسب الإنجاز لكل مرحلة",
      "roles.supervisor.p3": "رصد التقييم النهائي بعد المناقشة",
      "roles.admin.name": "الإدارة", "roles.admin.role": "ضبط النظام وتنظيم الفصل",
      "roles.admin.p1": "إدارة التخصصات وأنواع المشاريع والفصول",
      "roles.admin.p2": "إضافة المشرفين وتوزيع المجموعات",
      "roles.admin.p3": "ضبط الحد الأقصى لأعضاء الفريق",
      "lc.kicker": "دورة حياة المشروع",
      "lc.title": "مراحل واضحة، من الفكرة إلى الإنجاز",
      "lc.text": "أي مشروع، بغض النظر عن طبيعته ومدته وحجم نشاطاته، يمر بمراحل محددة لتحقيق أهدافه في فترة زمنية محددة.",
      "lc.1.title": "التفكير في المشروع",
      "lc.1.text": "المرحلة التي تبتكر فيها فكرة المشروع، وتبحث خلالها عن أولوية هذه الفكرة وجدواها.",
      "lc.2.title": "التخطيط للمشروع",
      "lc.2.text": "ينتقل المشروع من مجرد فكرة إلى خطة توضح أهدافه ونشاطاته وخدماته والفئات المستفيدة منه.",
      "lc.3.title": "رصد الموارد",
      "lc.3.text": "تحديد الموارد البشرية والمالية اللازمة للتنفيذ، وتعيين الأفراد وفرق العمل وتوزيع الأدوار والمسؤوليات.",
      "lc.4.title": "تطبيق المشروع",
      "lc.4.text": "بدء التنفيذ وإدارة أداء المشروع والتأكد من سيره وفق ما هو مخطط له وفي الاتجاه الصحيح.",
      "contact.kicker": "اتصل بنا",
      "contact.title": "تواصل معنا",
      "contact.text": "عندك سؤال أو اقتراح حول منصة تخرُّج؟ اكتب لنا وسنرد في أقرب وقت.",
      "form.name": "الاسم",
      "form.email": "الايميل الخاص بك",
      "form.subject": "الموضوع",
      "form.message": "الرسالة ..",
      "form.send": "إرسال",
      "footer.made": "منصة متابعة مشاريع التخرج",
      "footer.rights": "جميع الحقوق محفوظة — تخرُّج ©"
    },
    en: {
      dir: "ltr",
      "nav.home": "Home",
      "nav.about": "About",
      "nav.services": "Services",
      "nav.features": "Features",
      "nav.roles": "Roles",
      "nav.contact": "Contact",
      "nav.login": "Sign in",
      "nav.dashboard": "Dashboard",
      "nav.logout": "Log out",
      "brand.name": "Project Tracker",
      "brand.sub": "Graduation Project Tracking Platform",
      "hero.badge": "A smart platform for graduation projects",
      "hero.title": "Track your graduation project<br><span class=\"text-gradient\">from idea to defense</span>",
      "hero.sub": "Takharruj helps you manage teams, choose supervisors, and follow every phase of your project — with a modern, seamless experience.",
      "hero.cta1": "Get started",
      "hero.cta2": "Learn more",
      "hero.dashTitle": "Project dashboard",
      "hero.dashProject": "Smart tracking system",
      "hero.dashPhase": "Phase: Implementation",
      "hero.progress": "Project progress",
      "hero.approved": "Project approved",
      "hero.approvedSub": "Academic supervisor · 2 min ago",
      "hero.team": "Team members",
      "hero.notif": "New notification",
      "hero.notifSub": "Your supervisor replied",
      "stats.1": "Academic programs",
      "stats.2": "Academic supervisors",
      "stats.3": "Registered projects",
      "stats.4": "Registered students",
      "about.kicker": "About the platform",
      "about.title": "Takharruj",
      "about.text": "Takharruj is a standalone platform for running graduation projects — from forming the team and picking a supervisor, through idea approval and phase tracking, all the way to the defense and final evaluation.",
      "about.pillarsTitle": "Platform pillars",
      "about.p1": "Student teams & supervisor management",
      "about.p2": "Phase tracking with completion progress",
      "about.p3": "Defense, evaluation & grade recording",
      "about.more": "Learn more",
      "services.kicker": "Services",
      "services.title": "Everything your project needs, in one place",
      "services.text": "An integrated system managing everything from sign-in, team formation, and supervisor selection to project defense and evaluation.",
      "services.1.title": "Effortless team selection",
      "services.1.text": "Save the time and effort needed to complete your project and form your team smoothly.",
      "services.2.title": "Project tracking",
      "services.2.text": "Direct, live follow-up of the project between student and supervisor at every stage.",
      "services.3.title": "Teams & supervisors management",
      "services.3.text": "Direct, effective communication between team members and their supervisor across the whole lifecycle.",
      "features.kicker": "Features",
      "features.title": "A platform built for your project's success",
      "features.text": "The system manages every step — from sign-in, team formation and supervisor selection, to project defense and evaluation.",
      "features.1.title": "Milestones with live progress",
      "features.1.text": "Your supervisor sets milestones with due dates, and you track completion on a visual timeline that flags overdue items automatically.",
      "features.2.title": "Past projects explorer",
      "features.2.text": "Browse previous cohorts' projects by type and supervisor — get inspired and make sure your idea hasn't been done before.",
      "features.3.title": "Built-in discussion & instant alerts",
      "features.3.text": "Ask your supervisor and discuss with your team right inside the project page, with a notification for every reply, milestone, or file.",
      "features.4.title": "Digital evaluation with grade",
      "features.4.text": "Once your project is complete, your supervisor records the final grade with a rating and closing notes — delivered instantly.",
      "how.kicker": "How it works",
      "how.title": "Four steps from idea to grade",
      "how.text": "Your account is created by the platform administration — no sign-up needed, just log in and start.",
      "how.1.title": "Sign in",
      "how.1.text": "With your email or student ID — accounts are pre-created by the platform administration.",
      "how.2.title": "Form your team & submit",
      "how.2.text": "Pick available teammates from a list, choose a supervisor with open seats, and describe your idea.",
      "how.3.title": "Track, discuss, upload",
      "how.3.text": "After approval: milestones with progress, direct discussion, files, and an alert for every update.",
      "how.4.title": "Defend & get your grade",
      "how.4.text": "After the defense, your supervisor records your final grade with rating and notes — instantly delivered.",
      "faq.kicker": "FAQ",
      "faq.title": "Everything students ask before starting",
      "faq.text": "Straight answers from how the system actually works — for anything else, reach us in the contact section below.",
      "faq.1.title": "How many members per team?",
      "faq.1.text": "It depends on the project type set by your department — each type has a min and max shown in the submission form, and the system enforces them.",
      "faq.2.title": "How do I submit a project request?",
      "faq.2.text": "Sign in → pick a project type and a supervisor with open seats → select your teammates from the list → write the title and description and send. Your supervisor is notified instantly.",
      "faq.3.title": "How do I know the supervisor's reply?",
      "faq.3.text": "You get an in-system notification the moment your request is accepted or rejected — in the bell and on your dashboard, along with every later update.",
      "faq.4.title": "What if my project is rejected?",
      "faq.4.text": "You receive the supervisor's rejection reason with the notification, and the system reopens the submission form — refine your idea or pick another supervisor and resubmit.",
      "faq.5.title": "How is my final project graded?",
      "faq.5.text": "During execution you track milestone progress live, and after the defense your supervisor records a final grade out of 100 with a rating and notes — shown on your dashboard with an alert to the whole team.",
      "roles.kicker": "Platform roles",
      "roles.title": "A dedicated space for every role",
      "roles.text": "Takharruj is built around three complementary roles, each with its own dashboard and permissions — so everyone knows exactly what's expected of them at every stage.",
      "roles.student.name": "Student", "roles.student.role": "Form a team, submit an idea",
      "roles.student.p1": "Pick teammates from the available list",
      "roles.student.p2": "Choose a supervisor with open seats",
      "roles.student.p3": "Follow phases, comments, and upload files",
      "roles.supervisor.name": "Supervisor", "roles.supervisor.role": "Guide, approve, evaluate",
      "roles.supervisor.p1": "Review team requests and approve ideas",
      "roles.supervisor.p2": "Update completion progress per phase",
      "roles.supervisor.p3": "Record the final grade after the defense",
      "roles.admin.name": "Administration", "roles.admin.role": "Configure and organize the term",
      "roles.admin.p1": "Manage specializations, project types, terms",
      "roles.admin.p2": "Add supervisors and distribute groups",
      "roles.admin.p3": "Set the maximum team size",
      "lc.kicker": "Project lifecycle",
      "lc.title": "Clear phases, from idea to delivery",
      "lc.text": "Every project — whatever its nature, duration, or scale — moves through defined phases to reach its goals.",
      "lc.1.title": "Ideation",
      "lc.1.text": "The phase where you invent the project idea and examine its priority and feasibility.",
      "lc.2.title": "Planning",
      "lc.2.text": "The project evolves from an idea into a plan defining its goals, activities, and audiences.",
      "lc.3.title": "Resourcing",
      "lc.3.text": "Identify the human and financial resources needed, assign teams, and distribute roles.",
      "lc.4.title": "Execution",
      "lc.4.text": "Start implementation, manage performance, and keep the project on track.",
      "contact.kicker": "Contact",
      "contact.title": "Get in touch",
      "contact.text": "Have a question or a suggestion about Takharruj? Write to us and we'll get back to you shortly.",
      "form.name": "Your name",
      "form.email": "Your email",
      "form.subject": "Subject",
      "form.message": "Message ..",
      "form.send": "Send message",
      "footer.made": "Graduation Project Tracking Platform",
      "footer.rights": "All rights reserved — Takharruj ©"
    }
  };

  var locale = localStorage.getItem("locale") || "ar";

  function applyLocale(loc) {
    var d = dict[loc];
    if (!d) return;
    locale = loc;
    localStorage.setItem("locale", loc);
    document.documentElement.lang = loc;
    document.documentElement.dir = d.dir;

    document.querySelectorAll("[data-i18n]").forEach(function (el) {
      var key = el.getAttribute("data-i18n");
      if (d[key] !== undefined) {
        if (el.hasAttribute("data-i18n-html")) el.innerHTML = d[key];
        else el.textContent = d[key];
      }
    });
    document.querySelectorAll("[data-i18n-placeholder]").forEach(function (el) {
      var key = el.getAttribute("data-i18n-placeholder");
      if (d[key] !== undefined) el.setAttribute("placeholder", d[key]);
    });
    document.querySelectorAll(".lang-label").forEach(function (el) {
      el.textContent = loc === "ar" ? "EN" : "عربي";
    });

    // Recalculate open accordion height after text length changes
    document.querySelectorAll(".step.open .step-body").forEach(function (body) {
      body.style.maxHeight = body.scrollHeight + "px";
    });
  }

  /* ==================== boot ==================== */
  document.addEventListener("DOMContentLoaded", function () {
    if (locale !== "ar") applyLocale(locale);
    else applyLocale("ar"); // normalize lang labels

    document.querySelectorAll(".lang-btn").forEach(function (btn) {
      btn.addEventListener("click", function () {
        applyLocale(locale === "ar" ? "en" : "ar");
      });
    });

    /* ----- header scroll state ----- */
    var navBar = document.querySelector(".nav-bar");
    var backTop = document.querySelector(".back-top");
    function onScroll() {
      var y = window.scrollY;
      if (navBar) navBar.classList.toggle("scrolled", y > 24);
      if (backTop) backTop.classList.toggle("show", y > 500);
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });

    /* ----- mobile menu ----- */
    var toggle = document.querySelector(".menu-toggle");
    var menu = document.querySelector(".mobile-menu");
    if (toggle && menu) {
      toggle.addEventListener("click", function () {
        var open = menu.classList.toggle("open");
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
      });
      menu.querySelectorAll("a").forEach(function (a) {
        a.addEventListener("click", function () {
          menu.classList.remove("open");
          toggle.setAttribute("aria-expanded", "false");
        });
      });
    }

    /* ----- scroll reveal ----- */
    var revealEls = document.querySelectorAll(".reveal");
    if ("IntersectionObserver" in window && !reduceMotion) {
      var io = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (e) {
            if (e.isIntersecting) {
              e.target.classList.add("visible");
              io.unobserve(e.target);
            }
          });
        },
        { rootMargin: "-60px" }
      );
      revealEls.forEach(function (el) { io.observe(el); });
    } else {
      revealEls.forEach(function (el) { el.classList.add("visible"); });
    }

    /* ----- counters ----- */
    var counters = document.querySelectorAll("[data-count]");
    function animateCounter(el) {
      var to = parseInt(el.getAttribute("data-count"), 10) || 0;
      var suffix = el.getAttribute("data-suffix") || "";
      if (reduceMotion) { el.textContent = to.toLocaleString() + suffix; return; }
      var start = performance.now();
      var dur = 1600;
      function tick(now) {
        var p = Math.min((now - start) / dur, 1);
        var eased = p === 1 ? 1 : 1 - Math.pow(2, -10 * p);
        el.textContent = Math.round(eased * to).toLocaleString() + suffix;
        if (p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    }
    if ("IntersectionObserver" in window) {
      var cio = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (e) {
            if (e.isIntersecting) { animateCounter(e.target); cio.unobserve(e.target); }
          });
        },
        { rootMargin: "-40px" }
      );
      counters.forEach(function (el) { cio.observe(el); });
    } else {
      counters.forEach(animateCounter);
    }

    /* ----- accordion (lifecycle) ----- */
    document.querySelectorAll(".step").forEach(function (step) {
      var btn = step.querySelector(".step-btn");
      var body = step.querySelector(".step-body");
      if (!btn || !body) return;
      btn.addEventListener("click", function () {
        var isOpen = step.classList.contains("open");
        document.querySelectorAll(".step.open").forEach(function (s) {
          s.classList.remove("open");
          s.querySelector(".step-body").style.maxHeight = "0px";
          s.querySelector(".step-btn").setAttribute("aria-expanded", "false");
        });
        if (!isOpen) {
          step.classList.add("open");
          body.style.maxHeight = body.scrollHeight + "px";
          btn.setAttribute("aria-expanded", "true");
        }
      });
    });
    // open first step by default
    var first = document.querySelector(".step");
    if (first) {
      first.classList.add("open");
      var fb = first.querySelector(".step-body");
      if (fb) fb.style.maxHeight = fb.scrollHeight + "px";
      var fbtn = first.querySelector(".step-btn");
      if (fbtn) fbtn.setAttribute("aria-expanded", "true");
    }

    /* ----- hero mouse parallax ----- */
    var hero = document.querySelector(".hero");
    var sceneInner = document.querySelector(".scene-inner");
    if (hero && sceneInner && finePointer && !reduceMotion) {
      hero.addEventListener("mousemove", function (e) {
        var r = hero.getBoundingClientRect();
        var nx = ((e.clientX - r.left) / r.width) * 2 - 1;
        var ny = ((e.clientY - r.top) / r.height) * 2 - 1;
        sceneInner.style.transform =
          "translate3d(" + nx * 14 + "px," + ny * 10 + "px,0) rotateX(" + ny * -4 + "deg) rotateY(" + nx * 6 + "deg)";
      });
      hero.addEventListener("mouseleave", function () {
        sceneInner.style.transform = "";
      });
    }

    /* ----- card tilt ----- */
    if (finePointer && !reduceMotion) {
      document.querySelectorAll("[data-tilt]").forEach(function (card) {
        var max = parseFloat(card.getAttribute("data-tilt")) || 7;
        card.addEventListener("mousemove", function (e) {
          var r = card.getBoundingClientRect();
          var px = (e.clientX - r.left) / r.width;
          var py = (e.clientY - r.top) / r.height;
          card.style.transform =
            "translateY(-6px) rotateX(" + (0.5 - py) * max + "deg) rotateY(" + (px - 0.5) * max + "deg)";
        });
        card.addEventListener("mouseleave", function () {
          card.style.transform = "";
        });
      });
    }

    /* ----- magnetic buttons ----- */
    if (finePointer && !reduceMotion) {
      document.querySelectorAll("[data-magnetic]").forEach(function (el) {
        el.addEventListener("mousemove", function (e) {
          var r = el.getBoundingClientRect();
          var dx = (e.clientX - r.left - r.width / 2) * 0.2;
          var dy = (e.clientY - r.top - r.height / 2) * 0.2;
          el.style.transform = "translate(" + dx + "px," + dy + "px)";
        });
        el.addEventListener("mouseleave", function () {
          el.style.transform = "";
        });
      });
    }

    /* ----- cursor glow ----- */
    var glow = document.querySelector(".cursor-glow");
    if (glow && finePointer && !reduceMotion) {
      var gx = -400, gy = -400, tx = -400, ty = -400;
      window.addEventListener("mousemove", function (e) {
        tx = e.clientX - 200;
        ty = e.clientY - 200;
      }, { passive: true });
      (function loop() {
        gx += (tx - gx) * 0.08;
        gy += (ty - gy) * 0.08;
        glow.style.transform = "translate(" + gx + "px," + gy + "px)";
        requestAnimationFrame(loop);
      })();
    } else if (glow) {
      glow.remove();
    }

    /* ----- particles ----- */
    var particles = document.querySelector(".particles");
    if (particles && !reduceMotion) {
      for (var i = 0; i < 14; i++) {
        var s = document.createElement("i");
        var seed = ((i * 9301 + 49297) % 233280) / 233280;
        s.style.left = (seed * 90 + 5) + "%";
        s.style.top = (((i * 37) % 80) + 10) + "%";
        var size = 3 + (i % 3) * 2;
        s.style.width = size + "px";
        s.style.height = size + "px";
        s.style.animationDelay = (i % 7) * 0.8 + "s";
        s.style.animationDuration = 7 + (i % 5) * 2 + "s";
        particles.appendChild(s);
      }
    }
  });
})();
