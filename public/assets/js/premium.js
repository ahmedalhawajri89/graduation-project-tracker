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
      "nav.faq": "الأسئلة",
      "a11y.skip": "تخطَّ إلى المحتوى",
      "nav.login": "تسجيل دخول",
      "nav.dashboard": "لوحة التحكم",
      "nav.logout": "تسجيل خروج",
      "brand.name": "تخرُّج",
      "brand.sub": "منصة متابعة مشاريع التخرج",
      "hero.badge": "منصة ذكية لإدارة مشاريع التخرج",
      "hero.title": "<span class=\"ink-line\"><span>تتبّع مشروع تخرجك</span></span><span class=\"ink-line\"><span>من الفكرة <span class=\"text-gradient\">إلى المناقشة</span></span></span>",
      "hero.stageLabel": "مسار المشروع في المنصة",
      "hero.stageProject": "من التقديم إلى الدرجة النهائية",
      "hero.step1": "تقديم الطلب",
      "hero.step1m": "الطالب وفريقه",
      "hero.step2": "موافقة المشرف",
      "hero.step2m": "قبول أو رفض مسبّب",
      "hero.step3": "متابعة التنفيذ",
      "hero.step3m": "مراحل وملفات ونقاش",
      "hero.step4": "التقييم والمناقشة",
      "hero.step4m": "درجة نهائية موثّقة",
      "hero.fact1": "مشروع يُتابَع على المنصة",
      "hero.figuresLabel": "المنصة اليوم",
      "unit.students": "طالباً",
      "unit.members": "أعضاء",
      "state.progress": "قيد التنفيذ",
      "state.done": "مكتمل",
      "hero.fact2": "مشرف أكاديمي",
      "hero.fact3": "طالب وطالبة",
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
      "about.panelTitle": "التخصصات على المنصة",
      "about.panelEmpty": "لم تُسجَّل تخصصات بعد",
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
      "show.kicker": "من المنصة",
      "show.title": "مشاريع تُتابَع على تخرُّج الآن",
      "show.text": "ليست أمثلة مصنوعة — هذه مشاريع مسجّلة فعلاً على المنصة بأنواعها وفصولها وأحجام فرقها.",
      "dept.kicker": "للأقسام والكليات",
      "dept.title": "ملف الفصل الدراسي كاملاً في مكان واحد",
      "dept.text": "بدل جداول متفرقة ومجموعات محادثة، تعطي تخرُّج القسمَ صورةً واحدة: من قدّم، ومن وافق، وأين وصل كل فريق، ومن لم يلتحق بمجموعة بعد.",
      "dept.1.title": "توزيع المشرفين بحدّ أقصى لكل واحد",
      "dept.1.text": "تحدد للمشرف عدد المجموعات التي يقبلها، والنظام يرفض ما زاد تلقائياً.",
      "dept.2.title": "استيراد الطلاب والمشرفين من ملف Excel",
      "dept.2.text": "ترفع كشف الدفعة مرة واحدة فتُنشأ الحسابات كلها بلا إدخال يدوي.",
      "dept.3.title": "أنواع مشاريع بحدود فريق لكل تخصص",
      "dept.3.text": "تضبط لكل تخصص أنواع مشاريعه والحد الأدنى والأقصى لأعضاء الفريق.",
      "dept.4.title": "تصدير كشف المجموعات إلى Excel",
      "dept.4.text": "تُخرج كشفاً بالمجموعات ومشرفيها ودرجاتها في أي لحظة من الفصل.",
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
      "contact.asideTitle": "قبل أن تكتب",
      "contact.asideText": "حسابك يُنشأ من إدارة المنصة، فإن لم تستطع الدخول برقمك الجامعي راجع إدارة قسمك أولاً. وللأسئلة حول المواعيد وأنواع المشاريع، اكتب لنا هنا.",
      "contact.pointMail": "الرد خلال يوم عمل واحد",
      "contact.pointHours": "من الأحد إلى الخميس",
      "form.name": "الاسم",
      "form.email": "الايميل الخاص بك",
      "form.subject": "الموضوع",
      "form.message": "الرسالة ..",
      "form.send": "إرسال",
      "footer.made": "منصة متابعة مشاريع التخرج",
      "cta.title": "جاهز تبدأ مشروع تخرجك؟",
      "cta.text": "حسابك جاهز مسبقاً من إدارة المنصة — سجّل دخولك برقمك الجامعي، كوّن فريقك، واختر مشرفك. بقية الطريق تتابعها من لوحتك.",
      "cta.button": "تسجيل الدخول",
      "cta.secondary": "عندي سؤال",
      "footer.about": "منصة لإدارة مشاريع التخرج من تكوين الفريق واعتماد الفكرة، مروراً بمتابعة المراحل والملفات والنقاش، وصولاً إلى المناقشة والتقييم النهائي.",
      "footer.colPlatform": "المنصة",
      "footer.colRoles": "الأدوار",
      "footer.colHelp": "المساعدة",
      "footer.how": "كيف يعمل",
      "footer.noSignup": "الحسابات تُنشأ من الإدارة",
      "footer.top": "العودة للأعلى",
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
      "nav.faq": "FAQ",
      "a11y.skip": "Skip to content",
      "nav.login": "Sign in",
      "nav.dashboard": "Dashboard",
      "nav.logout": "Log out",
      // اسم العلامة لا يُترجم — يُنقل صوتياً كما يُكتب في الشعار
      "brand.name": "Takharruj",
      "brand.sub": "Graduation Project Tracking Platform",
      "hero.badge": "A smart platform for graduation projects",
      "hero.title": "<span class=\"ink-line\"><span>Track Your Graduation Project</span></span><span class=\"ink-line\"><span>From Idea <span class=\"text-gradient\">to Defense</span></span></span>",
      "hero.stageLabel": "The project path",
      "hero.stageProject": "From submission to final grade",
      "hero.step1": "Submit a proposal",
      "hero.step1m": "Student and team",
      "hero.step2": "Supervisor approval",
      "hero.step2m": "Accept or reject with a reason",
      "hero.step3": "Track the work",
      "hero.step3m": "Milestones, files, discussion",
      "hero.step4": "Evaluation and defense",
      "hero.step4m": "A recorded final grade",
      "hero.fact1": "projects tracked",
      "hero.figuresLabel": "The platform today",
      "unit.students": "students",
      "unit.members": "members",
      "state.progress": "In progress",
      "state.done": "Completed",
      "hero.fact2": "academic supervisors",
      "hero.fact3": "students",
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
      "about.panelTitle": "Specializations on the platform",
      "about.panelEmpty": "No specializations recorded yet",
      "about.p1": "Student teams & supervisor management",
      "about.p2": "Phase tracking with completion progress",
      "about.p3": "Defense, evaluation & grade recording",
      "about.more": "Learn more",
      "services.kicker": "Services",
      "services.title": "Everything Your Project Needs, in One Place",
      "services.text": "An integrated system managing everything from sign-in, team formation, and supervisor selection to project defense and evaluation.",
      "services.1.title": "Effortless Team Selection",
      "services.1.text": "Save the time and effort needed to complete your project and form your team smoothly.",
      "services.2.title": "Project Tracking",
      "services.2.text": "Direct, live follow-up of the project between student and supervisor at every stage.",
      "services.3.title": "Teams & Supervisors Management",
      "services.3.text": "Direct, effective communication between team members and their supervisor across the whole lifecycle.",
      "features.kicker": "Features",
      "features.title": "A Platform Built for Your Project's Success",
      "features.text": "The system manages every step — from sign-in, team formation and supervisor selection, to project defense and evaluation.",
      "features.1.title": "Milestones with Live Progress",
      "features.1.text": "Your supervisor sets milestones with due dates, and you track completion on a visual timeline that flags overdue items automatically.",
      "features.2.title": "Past Projects Explorer",
      "features.2.text": "Browse previous cohorts' projects by type and supervisor — get inspired and make sure your idea hasn't been done before.",
      "features.3.title": "Built-in Discussion & Instant Alerts",
      "features.3.text": "Ask your supervisor and discuss with your team right inside the project page, with a notification for every reply, milestone, or file.",
      "features.4.title": "Digital Evaluation with Grade",
      "features.4.text": "Once your project is complete, your supervisor records the final grade with a rating and closing notes — delivered instantly.",
      "how.kicker": "How it works",
      "how.title": "Four Steps from Idea to Grade",
      "how.text": "Your account is created by the platform administration — no sign-up needed, just log in and start.",
      "how.1.title": "Sign In",
      "how.1.text": "With your email or student ID — accounts are pre-created by the platform administration.",
      "how.2.title": "Form Your Team & Submit",
      "how.2.text": "Pick available teammates from a list, choose a supervisor with open seats, and describe your idea.",
      "how.3.title": "Track, Discuss, Upload",
      "how.3.text": "After approval: milestones with progress, direct discussion, files, and an alert for every update.",
      "how.4.title": "Defend & Get Your Grade",
      "how.4.text": "After the defense, your supervisor records your final grade with rating and notes — instantly delivered.",
      "show.kicker": "From the platform",
      "show.title": "Projects Being Tracked on Takharruj Right Now",
      "show.text": "Not invented examples — these are projects actually registered on the platform, with their types, semesters and team sizes.",
      "dept.kicker": "For departments",
      "dept.title": "The Whole Semester in One Place",
      "dept.text": "Instead of scattered spreadsheets and chat groups, Takharruj gives the department a single picture: who applied, who approved, where each team stands, and who has not joined a group yet.",
      "dept.1.title": "Supervisor Capacity, Set per Supervisor",
      "dept.1.text": "You set how many groups each supervisor accepts, and the system rejects the surplus automatically.",
      "dept.2.title": "Import Students and Supervisors from Excel",
      "dept.2.text": "Upload the cohort sheet once and every account is created without manual entry.",
      "dept.3.title": "Project Types with Team Limits per Specialization",
      "dept.3.text": "For each specialization you define its project types and the minimum and maximum team size.",
      "dept.4.title": "Export the Groups Sheet to Excel",
      "dept.4.text": "Produce a sheet of groups, their supervisors and their grades at any point in the semester.",
      "faq.kicker": "FAQ",
      "faq.title": "Everything Students Ask Before Starting",
      "faq.text": "Straight answers from how the system actually works — for anything else, reach us in the contact section below.",
      "faq.1.title": "How Many Members per Team?",
      "faq.1.text": "It depends on the project type set by your department — each type has a min and max shown in the submission form, and the system enforces them.",
      "faq.2.title": "How Do I Submit a Project Request?",
      "faq.2.text": "Sign in → pick a project type and a supervisor with open seats → select your teammates from the list → write the title and description and send. Your supervisor is notified instantly.",
      "faq.3.title": "How Do I Know the Supervisor's Reply?",
      "faq.3.text": "You get an in-system notification the moment your request is accepted or rejected — in the bell and on your dashboard, along with every later update.",
      "faq.4.title": "What If My Project Is Rejected?",
      "faq.4.text": "You receive the supervisor's rejection reason with the notification, and the system reopens the submission form — refine your idea or pick another supervisor and resubmit.",
      "faq.5.title": "How Is My Final Project Graded?",
      "faq.5.text": "During execution you track milestone progress live, and after the defense your supervisor records a final grade out of 100 with a rating and notes — shown on your dashboard with an alert to the whole team.",
      "roles.kicker": "Platform roles",
      "roles.title": "A Dedicated Space for Every Role",
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
      "lc.title": "Clear Phases, from Idea to Delivery",
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
      "contact.title": "Get in Touch",
      "contact.text": "Have a question or a suggestion about Takharruj? Write to us and we'll get back to you shortly.",
      "contact.asideTitle": "Before you write",
      "contact.asideText": "Your account is created by the platform administration. If you cannot sign in with your university ID, check with your department first. For questions about deadlines and project types, write to us here.",
      "contact.pointMail": "A reply within one business day",
      "contact.pointHours": "Sunday to Thursday",
      "form.name": "Your name",
      "form.email": "Your email",
      "form.subject": "Subject",
      "form.message": "Message ..",
      "form.send": "Send message",
      "footer.made": "Graduation Project Tracking Platform",
      "cta.title": "Ready to Start Your Graduation Project?",
      "cta.text": "Your account is already created by the platform administration — sign in with your university ID, build your team and pick your supervisor. The rest you follow from your dashboard.",
      "cta.button": "Sign in",
      "cta.secondary": "I have a question",
      "footer.about": "A platform for managing graduation projects: forming the team, approving the idea, tracking milestones, files and discussion, through to the defense and final grade.",
      "footer.colPlatform": "Platform",
      "footer.colRoles": "Roles",
      "footer.colHelp": "Help",
      "footer.how": "How it works",
      "footer.noSignup": "Accounts are created by the administration",
      "footer.top": "Back to top",
      "footer.rights": "All rights reserved — Takharruj ©"
    }
  };

  var locale = localStorage.getItem("locale") || "ar";

  /* لسان المبدّل ينزلق تحت الخيار النشط — يُقاس لا يُخمَّن */
  function moveLangThumb() {
    var sw = document.querySelector(".lang-switch");
    if (!sw) return;
    var thumb = sw.querySelector(".lang-thumb");
    var active = sw.querySelector('button[aria-pressed="true"]');
    if (!thumb || !active) return;
    // translateX موجبها يميناً دائماً مهما كان الاتجاه، و offsetLeft يُقاس
    // من الحافة اليسرى — فالحساب واحد في RTL و LTR
    thumb.style.width = active.offsetWidth + "px";
    thumb.style.transform = "translateX(" + active.offsetLeft + "px)";
  }

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
    // المبدّل المجزّأ: الحالة تُعلَن للقارئات بـ aria-pressed لا باللون وحده
    document.querySelectorAll(".lang-switch button").forEach(function (btn) {
      btn.setAttribute("aria-pressed", btn.dataset.locale === loc ? "true" : "false");
    });
    moveLangThumb();

  }

  /* ثلاثة عناصر تقيس نفسها بالبكسل، وعرض النص الإنجليزي يختلف عن العربي.
     تُحدَّث بعد انتهاء الانتقال لا أثناءه وإلا قِيست على حالة وسيطة. */
  function remeasure() {
    moveLangThumb();
    if (typeof window.__pillToCurrent === "function") window.__pillToCurrent();
    document.querySelectorAll(".faq-item.open .faq-body").forEach(function (body) {
      body.style.maxHeight = "none";
      body.style.maxHeight = body.scrollHeight + "px";
    });
  }

  var switching = false;

  /* غلاف يحوّل التبديل من قفزة إلى حركة واحدة مقصودة */
  function switchLocale(loc) {
    if (loc === locale || switching || !dict[loc]) return;

    // تقليل الحركة: فوري بلا مزج
    if (reduceMotion) { applyLocale(loc); remeasure(); return; }

    switching = true;
    var root = document.documentElement;
    root.classList.add("no-entrance");

    var done = function () {
      root.classList.remove("no-entrance", "locale-swapping");
      switching = false;
      remeasure();
    };

    // المسار المفضّل: المتصفح يمزج بين لقطتي ما قبل وما بعد،
    // فينعكس التخطيط ضمن المزج لا قبله
    if (typeof root.style.viewTransitionName !== "undefined" && document.startViewTransition) {
      var t = document.startViewTransition(function () { applyLocale(loc); });
      t.finished.then(done, done);
      return;
    }

    // البديل: يخفت المحتوى، يُستبدل النص وهو غير مرئي، ثم يعود
    root.classList.add("locale-swapping");
    window.setTimeout(function () {
      applyLocale(loc);
      window.requestAnimationFrame(function () {
        root.classList.remove("locale-swapping");
        window.setTimeout(done, 230);
      });
    }, 200);
  }

  /* ==================== boot ==================== */
  document.addEventListener("DOMContentLoaded", function () {
    if (locale !== "ar") applyLocale(locale);
    else applyLocale("ar"); // normalize lang labels

    document.querySelectorAll(".lang-switch button").forEach(function (btn) {
      btn.addEventListener("click", function () {
        switchLocale(btn.dataset.locale);
      });
    });

    // عرض الروابط يتغير حين يحلّ الخط الحقيقي محل البديل
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function () { pillToCurrent(); moveLangThumb(); });
    }

    /* ----- header scroll state ----- */
    // الخلفية صارت على الهيدر نفسه (بعرض الصفحة) لا على الشريط الداخلي
    var siteHeader = document.querySelector(".site-header");
    var backTop = document.querySelector(".back-top");
    function onScroll() {
      var y = window.scrollY;
      if (siteHeader) siteHeader.classList.toggle("scrolled", y > 24);
      if (backTop) backTop.classList.toggle("show", y > 500);
    }
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });

    /* ----- الحبّة المنزلقة بين روابط التنقل -----
       عنصر واحد ينتقل بـ transform بدل أن يومض إطار تحت كل رابط.
       يتبع المؤشر عند المرور، ويعود إلى القسم النشط عند مغادرته. */
    var navWrap = document.querySelector(".nav-wrap");
    var navPill = navWrap && navWrap.querySelector(".nav-pill");

    function movePill(target) {
      if (!navPill || !target) return;
      var wrapBox = navWrap.getBoundingClientRect();
      var box = target.getBoundingClientRect();
      // الإزاحة تُقاس من حافة البداية المنطقية ليصحّ في RTL و LTR معاً
      var rtl = getComputedStyle(document.documentElement).direction === "rtl";
      var offset = rtl ? wrapBox.right - box.right : box.left - wrapBox.left;
      navPill.style.width = box.width + "px";
      navPill.style.transform = "translateY(-50%) translateX(" + (rtl ? -offset : offset) + "px)";
      navWrap.classList.add("pill-ready");
    }

    function pillToCurrent() {
      var active = document.querySelector('.nav-links a[aria-current="true"]');
      if (active) movePill(active);
      else if (navWrap) navWrap.classList.remove("pill-ready");
    }
    // تحتاجها remeasure المعرّفة خارج هذا النطاق بعد انتهاء تبديل اللغة
    window.__pillToCurrent = pillToCurrent;

    if (navWrap && navPill) {
      document.querySelectorAll(".nav-links a").forEach(function (a) {
        a.addEventListener("mouseenter", function () { movePill(a); });
        a.addEventListener("focus", function () { movePill(a); });
      });
      navWrap.addEventListener("mouseleave", pillToCurrent);
      window.addEventListener("resize", function () { pillToCurrent(); moveLangThumb(); }, { passive: true });
    }

    /* ----- مؤشر القسم النشط -----
       الصفحة ٨٧٠٠ بكسل و١٢ قسماً؛ بدون هذا لا يعرف الزائر أين هو.
       IntersectionObserver لا حساب على كل تمرير. */
    var navMap = {};
    document.querySelectorAll('.nav-links a[href^="#"]').forEach(function (a) {
      var id = a.getAttribute("href").slice(1);
      if (id) navMap[id] = a;
    });
    var spyTargets = Object.keys(navMap)
      .map(function (id) { return document.getElementById(id); })
      .filter(Boolean);

    if (spyTargets.length && "IntersectionObserver" in window) {
      var visible = {};
      var spy = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) { visible[e.target.id] = e.isIntersecting ? e.intersectionRatio : 0; });
        var best = null, bestRatio = 0;
        Object.keys(visible).forEach(function (id) {
          if (visible[id] > bestRatio) { bestRatio = visible[id]; best = id; }
        });
        Object.keys(navMap).forEach(function (id) {
          if (id === best) navMap[id].setAttribute("aria-current", "true");
          else navMap[id].removeAttribute("aria-current");
        });
        // لا نسحب الحبّة من تحت مؤشر الفأرة
        if (!navWrap || !navWrap.matches(":hover")) pillToCurrent();
      }, { rootMargin: "-25% 0px -55% 0px", threshold: [0, .25, .5, 1] });
      spyTargets.forEach(function (t) { spy.observe(t); });
    }

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

    /* ----- accordion (الأسئلة الشائعة) -----
       عنصر ".step" القديم لم يعد موجوداً — الأكورديون صار في قسم FAQ */
    document.querySelectorAll(".faq-item").forEach(function (item) {
      var btn = item.querySelector(".faq-btn");
      var body = item.querySelector(".faq-body");
      if (!btn || !body) return;
      btn.addEventListener("click", function () {
        var isOpen = item.classList.contains("open");
        document.querySelectorAll(".faq-item.open").forEach(function (other) {
          other.classList.remove("open");
          other.querySelector(".faq-body").style.maxHeight = "0px";
          other.querySelector(".faq-btn").setAttribute("aria-expanded", "false");
        });
        if (!isOpen) {
          item.classList.add("open");
          body.style.maxHeight = body.scrollHeight + "px";
          btn.setAttribute("aria-expanded", "true");
        }
      });
    });
    // السؤال الأول مفتوح افتراضياً حتى لا يبدو القسم صفاً من الأزرار
    var firstFaq = document.querySelector(".faq-item");
    if (firstFaq) {
      firstFaq.classList.add("open");
      var fb = firstFaq.querySelector(".faq-body");
      if (fb) fb.style.maxHeight = fb.scrollHeight + "px";
      var fbtn = firstFaq.querySelector(".faq-btn");
      if (fbtn) fbtn.setAttribute("aria-expanded", "true");
    }

    // إعادة قياس الارتفاع بعد تبديل اللغة أو تغيّر العرض
    window.addEventListener("resize", function () {
      var open = document.querySelector(".faq-item.open .faq-body");
      if (open) open.style.maxHeight = open.scrollHeight + "px";
    }, { passive: true });

    /* حُذفت خمس سلوكيات زخرفية: بارالاكس الهيرو، إمالة البطاقات،
       الأزرار المغناطيسية، توهّج المؤشر بحلقة requestAnimationFrame
       الدائمة، والجسيمات الطافية. الحركة الباقية في CSS وحدها. */
  });
})();
