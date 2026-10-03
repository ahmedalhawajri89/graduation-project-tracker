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
      "nav.contact": "تواصل معنا",
      "nav.faq": "الأسئلة الشائعة",
      "a11y.skip": "تخطَّ إلى المحتوى",
      "nav.login": "تسجيل الدخول",
      "nav.dashboard": "لوحة التحكم",
      "nav.logout": "تسجيل خروج",
      "brand.name": "تخرُّج",
      "brand.sub": "منصة متابعة مشاريع التخرج",
      "hero.badge": "منصة إدارة مشاريع التخرج للجامعات",
      "bento.aria": "لمحة حيّة من المنصة",
      "bento.progress": "نسبة الإنجاز",
      "bento.project": "كشف الرسائل الاحتيالية",
      "bento.of": "من 5 مراحل",
      "svx.1.t": "فريقك ومشرفك في دقائق",
      "svx.1.l": "زملاء من تخصصك، ومشرف بمقاعد متاحة، وتنبيه إن نُفّذت فكرتك.",
      "svx.2.t": "سلّم، واستلم ملاحظة",
      "svx.2.l": "كل مرحلة بملف، والمشرف يعتمدها أو يطلب تعديلاً بسببه.",
      "svx.3.t": "خطة مراحل جاهزة",
      "svx.3.l": "يضعها المشرف مرّة بمواعيدها وقوالبها، فتصل كل مجموعاته.",
      "svx.4.t": "نقاش حيّ",
      "svx.4.l": "قناة خاصة بالفريق، وأخرى مع المشرف — والرسائل تصل فوراً.",
      "svx.5.t": "مناقشة ودرجة معتمدة",
      "svx.5.l": "لجنة وموعد وقاعة، ودرجة تُرصد ثم تُقفل.",
      "svx.6.t": "استلهم من مشاريع سابقة",
      "svx.6.l": "تصفّح مشاريع الدفعات السابقة بأنواعها ومشرفيها.",
      "svx.s1.sup": "د. هبة الشوا",
      "svx.s1.supl": "مشرفة · برمجة ذكاء صناعي",
      "svx.s1.seats": "مقاعد",
      "svx.s1.r1": "واجهات",
      "svx.s1.r2": "الخادم",
      "svx.s1.r3": "التوثيق",
      "svx.s1.alert": "فكرة مشابهة نُفّذت في 2023 — راجعها قبل التقديم",
      "svx.s2.file": "الفصل الثالث",
      "svx.s2.warn": "مطلوب تعديل",
      "svx.s2.ok": "اعتُمدت ✓",
      "svx.s2.prog": "نسبة الإنجاز",
      "svx.s3.title": "خطة مراحل الفصل",
      "svx.s3.m1": "تحليل المتطلبات",
      "svx.s3.m1d": "30 يونيو",
      "svx.s3.m2": "تصميم قاعدة البيانات",
      "svx.s3.m2d": "14 يوليو",
      "svx.s3.m3": "الواجهات",
      "svx.s3.m3d": "28 يوليو",
      "svx.s3.m4": "التطوير والبرمجة",
      "svx.s3.m4d": "11 أغسطس",
      "svx.s3.m5": "الاختبار والتوثيق",
      "svx.s3.m5d": "25 أغسطس",
      "svx.s3.sent": "وصلت إلى 4 مجموعات ✓",
      "svx.s4.head": "نقاش الفريق",
      "svx.s4.lock": "🔒 خاص",
      "svx.s4.m1": "رفعت الفصل الثالث ✅",
      "svx.s4.at": "@آية",
      "svx.s4.m2": "ممتاز، أراجعه الليلة",
      "svx.s4.m3": "وأنا أجهّز العرض التقديمي 🎤",
      "svx.s5.mon": "أكتوبر",
      "svx.s5.when": "الأحد 09:00 · قاعة 204",
      "svx.s5.who": "المشرف + ممتحنان · رئيس اللجنة",
      "svx.s5.tag": "ممتاز · معتمدة 🔒",
      "svx.s6.q": "ذكاء",
      "svx.s6.p1": "كشف الأخبار الزائفة",
      "svx.s6.p1t": "ذكاء صناعي · 2023",
      "svx.s6.p2": "متجر إلكتروني",
      "svx.s6.p2t": "برمجة ويب · 2022",
      "svx.s6.p3": "تحليل صور الأشعة",
      "svx.s6.p3t": "ذكاء صناعي · 2024",
      "svx.s6.p4": "تطبيق حجز عيادات",
      "svx.s6.p4t": "تطبيقات جوال · 2023",
      "fh.1": "نقاش داخلي لا يراه المشرف",
      "fh.2": "كل قرار محفوظ ومقفل",
      "fh.3": "للفريق ومشرفه والإدارة فقط",
      "fh.4": "ما يخصّك فقط، لا كل رسالة",
      "fh.5": "والإنجليزية بنقرة",
      "fh.6": "كل صفحة لشاشتك الصغيرة",
      "st.1": "ببريدك أو رقمك الجامعي — حسابك جاهز.",
      "st.2": "زملاء ومشرف بمقاعد، وفكرة لم تُنفَّذ.",
      "st.3": "تسليم، فاعتماد أو تعديل بملاحظة.",
      "st.4": "لجنة ودرجة معتمدة لا تتغيّر.",
      "cmp.m1": "مين رفع الملف الأخير؟",
      "cmp.m2": "الموعد بكرة ولا الأسبوع الجاي؟؟",
      "cmp.m3": "مرفوض",
      "cmp.m4": "Re: Re: Fwd: التعديلات",
      "cmp.m5": "مين عليه الواجهات؟",
      "cmp.m6": "كشف الدرجات (ورقي)",
      "cmp.aria": "قارن قبل ومع تخرُّج",
      "contact.bubble": "أهلاً! نقرأ كل رسالة 👋",
      "journey.s1": "الفكرة",
      "journey.s2": "الفريق",
      "journey.s3": "موافقة المشرف",
      "journey.s4": "المراحل",
      "journey.s5": "المناقشة",
      "journey.s6": "التخرّج",
      "journey.c1": "فكرة جديدة",
      "journey.c1s": "كشف الأخبار الزائفة بالذكاء الاصطناعي",
      "journey.c2": "اكتمل الفريق",
      "journey.c2s": "3 أعضاء · قائدة الفريق آية",
      "journey.c3": "وافق المشرف",
      "journey.c3s": "د. هبة قبلت الطلب",
      "journey.c4": "اعتُمدت المرحلة",
      "journey.c4s": "الفصل الثالث · 4 من 5",
      "journey.c5": "جُدولت المناقشة",
      "journey.c5s": "الأحد 09:00 · قاعة 204",
      "journey.c6": "تخرّجنا! 🎓",
      "journey.c6s": "الدرجة 96 من 100 · ممتاز",
      "bento.stage": "المرحلة الحالية",
      "bento.stageTitle": "الفصل الثاني — الدراسات السابقة",
      "bento.s1": "مفتوحة",
      "bento.s2": "سُلّمت",
      "bento.s3": "اعتُمدت",
      "bento.chat": "نقاش الفريق",
      "bento.private": "خاص",
      "bento.msg": "@آية راجعي الفصل الثاني قبل الخميس",
      "bento.at": "@آية",
      "bento.roles": "أدوار الفريق",
      "bento.r1": "واجهات",
      "bento.r2": "الخادم",
      "bento.r3": "التوثيق",
      "bento.grade": "التقييم النهائي",
      "bento.excellent": "ممتاز",
      "bento.pending": "لم تُرصد بعد",
      "meta.title": "تخرُّج | منصة متابعة مشاريع التخرج",
      "hero.title": "<span class=\"ink-line\"><span>تتبّع مشروع تخرجك</span></span><span class=\"ink-line\"><span>من الفكرة <span class=\"text-gradient\">إلى <span class=\"rot\" data-rot=\"المناقشة|الدرجة|التخرّج\">المناقشة</span></span></span></span>",
      "hero.stageLabel": "مسار المشروع في المنصة",
      "hero.stageProject": "من التقديم إلى الدرجة النهائية",
      "hero.step1": "تقديم الطلب",
      "hero.step1m": "الطالب وفريقه",
      "hero.step2": "موافقة المشرف",
      "hero.step2m": "قبول أو رفض مسبّب",
      "hero.step3": "متابعة التنفيذ",
      "hero.step3m": "مراحل وملفات ونقاش",
      "hero.step4": "المناقشة والتقييم",
      "hero.step4m": "درجة نهائية موثّقة",
      "hero.fact1": "مشروع يُتابَع على المنصة",
      "hero.figuresLabel": "المنصة اليوم",
      "unit.students": "طالباً",
      "unit.members": "أعضاء",
      "state.progress": "قيد التنفيذ",
      "state.done": "مكتمل",
      "hero.fact2": "مشرف أكاديمي",
      "hero.fact3": "طالب وطالبة",
      "hero.sub": "فريقك ومشرفك ومراحل مشروعك ودرجتك — تتابعها كلها من لوحة واحدة، ويتابعها مشرفك معك.",
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
      "about.title": "من تكوين الفريق حتى الدرجة",
      "about.text": "تخرُّج منصة مستقلة لإدارة مشاريع التخرج من أول تكوين الفريق واختيار المشرف، مروراً باعتماد الفكرة ومتابعة المراحل، وصولاً إلى المناقشة والتقييم النهائي — بمسار واضح لكل طرف.",
      "about.pillarsTitle": "ركائز المنصة",
      "about.panelTitle": "المنصة الآن",
      "about.panelEmpty": "لم تُسجَّل تخصصات بعد",
      "about.live": "مباشر",
      "about.statStudents": "طالب",
      "about.statSupervisors": "مشرف",
      "about.statDone": "مشروع مكتمل",
      "about.split": "الطلاب حسب التخصص",
      "about.other": "تخصصات أخرى",
      "about.p1": "إدارة الفرق الطلابية والمشرفين",
      "about.p2": "خطط المراحل والتسليم والمراجعة",
      "about.p3": "المناقشة والتقييم ورصد الدرجات",
      "about.more": "المزيد",
      "services.kicker": "الخدمات",
      "services.title": "كل ما يحتاجه مشروعك في مكان واحد",
      "services.text": "من تكوين الفريق إلى الدرجة المعتمدة — أدوات تغنيك عن مجموعات واتساب والبريد والملفات المبعثرة.",
      "features.5.title": "عربية أولاً",
      "features.5.text": "واجهة عربية كاملة من اليمين إلى اليسار، بخطوط مصمّمة للقراءة، والإنجليزية بنقرة.",
      "features.6.title": "على الجوال كما الحاسوب",
      "features.6.text": "سلّم مرحلة أو ردّ على مشرفك من هاتفك — كل صفحة مصمّمة للشاشة الصغيرة.",
      "features.kicker": "المميزات",
      "features.title": "مصمّمة للواقع الأكاديمي",
      "features.text": "تفاصيل لا تُرى في العرض الأول، لكنها ما يجعل الفصل الدراسي يمرّ بلا مفاجآت.",
      "features.trust.label": "ضمانات الثقة",
      "features.trust.text": "ما يُعتمد عليه عند الاعتراض والتدقيق",
      "features.daily.label": "راحة كل يوم",
      "features.daily.text": "ما يجعل العمل اليومي أخفّ",
      "features.p1.msg": "ننهي فصل التحليل الليلة؟",
      "features.p1.seen": "مرئي للفريق فقط",
      "features.p1.sup": "المشرف",
      "features.p1.adm": "الإدارة",
      "features.p3.l1": "اعتماد درجة",
      "features.p3.l2": "فكّ اعتماد — بسبب مكتوب",
      "features.p3.lock": "لا تعديل ولا حذف",
      "features.p4.who": "تنزيل للفريق ومشرفه والإدارة فقط",
      "features.p2.a": "مطلوب تعديل في «الفصل الثالث»",
      "features.p2.b": "ذكرك زميل في نقاش الفريق",
      "features.p2.c": "رسالة عادية في النقاش",
      "features.p2.muted": "بلا تنبيه",
      "features.p5.btn": "تبديل اللغة في المثال",
      "features.p6.stage": "الفصل الثالث",
      "features.p6.btn": "تسليم المرحلة",
      "features.1.title": "خصوصية الفريق",
      "features.1.text": "نقاش الفريق الداخلي لا يراه المشرف ولا الإدارة — يكتب الطلاب بحرّية بدل الهروب إلى واتساب.",
      "features.2.title": "إشعارات بلا ضجيج",
      "features.2.text": "يصلك التنبيه حين يخصّك الأمر: طلب تعديل، أو ذكرك زميل، أو اعتُمدت مرحلة — لا مع كل رسالة.",
      "features.3.title": "سجلّ لا يُعدَّل",
      "features.3.text": "الدرجة تُقفل بعد اعتمادها، وكل قرار مهم يُحفظ في سجلّ تدقيق — مرجع واضح عند أيّ اعتراض.",
      "features.4.title": "ملفات محمية",
      "features.4.text": "ملفات المشروع والتسليمات على قرص خاص، لا يُنزلها إلا الفريق ومشرفه والإدارة.",
      "how.kicker": "كيف يعمل النظام؟",
      "how.title": "أربع خطوات من الفكرة إلى الدرجة",
      "how.text": "حسابك يُنشأ من إدارة القسم — لا تسجيل ولا انتظار، سجّل دخولك وابدأ.",
      "how.1.title": "ادخل إلى المنصة",
      "how.1.text": "ببريدك أو رقمك الجامعي — حسابك جاهز من إدارة القسم.",
      "how.2.title": "كوّن فريقك وقدّم فكرتك",
      "how.2.text": "اختر زملاءك ومشرفاً لديه مقاعد، واكتب فكرتك بعد أن تتأكد أنها لم تُنفَّذ.",
      "how.3.title": "سلّم مراحلك",
      "how.3.text": "مرحلة بعد مرحلة: تسليم، فاعتماد أو تعديل بملاحظة، ونقاش مع فريقك ومشرفك.",
      "how.4.title": "ناقش واستلم درجتك",
      "how.4.text": "بعد المناقشة يرصد مشرفك درجتك مع ملاحظاته، وتُعتمد فلا تتغيّر.",
      "show.kicker": "من المنصة",
      "show.title": "مشاريع أُنجزت على تخرُّج",
      "show.text": "ليست أمثلة مصنوعة — مشاريع أكملتها فرق فعلاً على المنصة، مرحلةً بعد مرحلة.",
      "show.stat.done": "مشروعاً مكتملاً",
      "show.stat.avg": "متوسط الدرجات",
      "show.stat.specs": "تخصصات",
      "show.stat.stages": "مراحل معتمدة لكل مشروع",
      "show.stages": "مراحل معتمدة",
      "show.weeks": "أسابيع",
      "show.more": "ادخل لتتصفّح أرشيف المشاريع كاملاً",
      "dept.kicker": "للأقسام والكليات",
      "dept.title": "الفصل الدراسي كله أمام القسم",
      "dept.text": "بدل جداول متفرقة ومجموعات محادثة، تعطي تخرُّج القسمَ صورةً واحدة: من قدّم، ومن وافق، وأين وصل كل فريق، ومن لم يلتحق بمجموعة بعد.",
      "dept.1.title": "توزيع المشرفين بحدّ أقصى لكل واحد",
      "dept.1.text": "تحدد للمشرف عدد المجموعات التي يقبلها، والنظام يرفض ما زاد تلقائياً.",
      "dept.2.title": "استيراد الطلاب والمشرفين من ملف Excel",
      "dept.2.text": "ترفع كشف الدفعة مرة واحدة فتُنشأ الحسابات كلها بلا إدخال يدوي.",
      "dept.3.title": "أنواع مشاريع بحدود فريق لكل تخصص",
      "dept.3.text": "تضبط لكل تخصص أنواع مشاريعه والحد الأدنى والأقصى لأعضاء الفريق.",
      "dept.4.title": "تصدير كشف المجموعات إلى Excel",
      "dept.4.text": "تُخرج كشفاً بالمجموعات ومشرفيها ودرجاتها في أي لحظة من الفصل.",
      "dept.5.title": "سجلّ تدقيق لكل قرار",
      "dept.5.text": "كل اعتماد وفتح درجة وتغيير مهم يُحفظ باسم صاحبه ووقته.",
      "dept.6.title": "فصول دراسية تُفتح وتُغلق",
      "dept.6.text": "تفتح فصلاً جديداً للتقديم وتغلق السابق، فتبقى مشاريع كل دفعة في فصلها.",
      "dept.file.name": "ملف الفصل",
      "dept.file.term": "الفصل الأول",
      "dept.file.q1": "من قدّم؟",
      "dept.file.q2": "من وافق؟",
      "dept.file.q3": "أين وصل كل فريق؟",
      "dept.file.q4": "من لم يلتحق؟",
      "dept.file.teams": "فريقاً",
      "dept.file.students": "طالباً",
      "dept.file.s1": "الفكرة",
      "dept.file.s2": "التنفيذ",
      "dept.file.s3": "التسليم",
      "dept.file.s4": "المناقشة",
      "dept.p1.when": "قبل الفصل",
      "dept.p1.name": "التجهيز",
      "dept.p2.when": "بداية الفصل",
      "dept.p2.name": "التوزيع",
      "dept.p3.when": "حتى نهايته",
      "dept.p3.name": "المتابعة والإغلاق",
      "dept.pf.term2": "الفصل الثاني",
      "dept.pf.accounts": "حساباً",
      "dept.pf.type": "مشروع برمجي",
      "dept.pf.members": "أعضاء",
      "dept.pf.groups": "مجموعات",
      "dept.pf.sixth": "السادسة مرفوضة",
      "dept.pf.audit": "اعتماد فكرة مشروع",
      "dept.pf.export": "تنزيل الكشف",
      "faq.kicker": "الأسئلة الشائعة",
      "faq.title": "كل ما يسأله الطلاب قبل البدء",
      "faq.text": "إجابات مباشرة من واقع النظام — ولأي سؤال آخر تواصل معنا من قسم الاتصال بالأسفل.",
      "faq.1.title": "كم عضواً يتكون منه الفريق؟",
      "faq.1.text": "حسب نوع المشروع الذي يحدده قسمك — كل نوع له حد أدنى وأقصى يظهران أمامك في نموذج التقديم، والنظام لا يقبل فريقاً خارج الحدود.",
      "faq.2.title": "كيف أقدم طلب مشروع؟",
      "faq.2.text": "أربع خطوات من لوحتك، ويصل طلبك للمشرف فوراً:",
      "faq.3.title": "كيف أعرف ردّ المشرف، وماذا لو رُفض طلبي؟",
      "faq.3.text": "يصلك إشعار فور القبول أو الرفض، ومعه ملاحظة المشرف إن كتبها. وإن رُفض طلبك يُفتح لك نموذج تقديم جديد مباشرة — عدّل فكرتك أو اختر مشرفاً آخر.",
      "faq.4.title": "ماذا يعني «مطلوب تعديل» على مرحلة سلّمتها؟",
      "faq.4.text": "أن مشرفك راجعها وكتب ما ينقصها — لا أنها رُفضت. عدّل ملفك وأعد التسليم من الصفحة نفسها، وتبقى كل جولة وملاحظتها محفوظة.",
      "faq.5.title": "من يوزّع الأدوار في الفريق؟",
      "faq.5.text": "قائد الفريق: يسند لكل عضو دوره ومسؤولياته، فيراها الفريق كله ويراها المشرف — واضح من على ماذا قبل المناقشة.",
      "faq.6.title": "هل يرى المشرف نقاش الفريق؟",
      "faq.6.text": "لا. للفريق قناة خاصة لا يراها المشرف ولا الإدارة، وقناة ثانية مشتركة مع المشرف للأسئلة والملاحظات.",
      "faq.7.title": "كيف يُقيَّم مشروعي النهائي؟",
      "faq.7.text": "بعد اكتمال المشروع يرصد مشرفك الدرجة من 100 مع ملاحظاته، ويُحسب التقدير منها تلقائياً، ويصل الإشعار للفريق كله. وبعد اعتمادها تُقفل — لا يفتحها إلا الإدارة بسبب مكتوب، ويُسجَّل ذلك.",
      "faq.filters": "تصفية الأسئلة",
      "faq.cat.all": "الكل",
      "faq.cat.apply": "التقديم",
      "faq.cat.team": "الفريق",
      "faq.cat.track": "المتابعة والتقييم",
      "faq.2.s1": "سجّل دخولك",
      "faq.2.s2": "اختر النوع ومشرفاً لديه مقاعد",
      "faq.2.s3": "اختر أعضاء فريقك",
      "faq.2.s4": "اكتب العنوان والوصف وأرسل",
      "faq.ask.title": "ما وجدت جوابك؟",
      "faq.ask.text": "اكتب لنا سؤالك ونردّ عليك على بريدك في أقرب وقت.",
      "faq.ask.button": "راسلنا",
      "roles.kicker": "أدوار المنصة",
      "roles.title": "لكل دور مساحته الخاصة",
      "roles.text": "ثلاثة أدوار، لكلٍّ لوحته وصلاحياته — ويتسلّم كلٌّ من الآخر في الوقت المناسب. اختر دوراً لترى مساحته.",
      "roles.student.name": "الطالب", "roles.student.role": "الفريق والتسليم والنقاش",
      "roles.supervisor.name": "المشرف", "roles.supervisor.role": "التخطيط والمراجعة والتقييم",
      "roles.admin.name": "الإدارة", "roles.admin.role": "ضبط النظام وتنظيم الفصل",
      "roles.student.c1.t": "تكوين الفريق",
      "roles.student.c1.d": "زملاؤك من تخصصك، ومشرف لديه مقاعد متاحة",
      "roles.student.c2.t": "تسليم المراحل",
      "roles.student.c2.d": "ملف وملاحظة لكل مرحلة، وإعادة بعد التعديل",
      "roles.student.c3.t": "نقاش الفريق",
      "roles.student.c3.d": "قناة خاصة بالفريق، و@ لتنبيه زميل بعينه",
      "roles.student.c4.t": "الأدوار والتذكير",
      "roles.student.c4.d": "يوزّع القائد المسؤوليات، ويصل تذكير قبل كل موعد",
      "roles.student.private": "نقاش الفريق الخاص لا يصل المشرف ولا الإدارة",
      "roles.supervisor.c1.t": "قبول الطلبات",
      "roles.supervisor.c1.d": "حسب مقاعده، مع تنبيه للفكرة المشابهة",
      "roles.supervisor.c2.t": "خطة المراحل",
      "roles.supervisor.c2.d": "مواعيد وقوالب تصل كل مجموعاته مرّة واحدة",
      "roles.supervisor.c3.t": "المراجعة",
      "roles.supervisor.c3.d": "اعتماد، أو «مطلوب تعديل» بسبب مكتوب",
      "roles.supervisor.c4.t": "الدرجة",
      "roles.supervisor.c4.d": "رصد بالتقدير والملاحظات، ثم اعتماد يقفلها",
      "roles.supervisor.private": "يرى مجموعاته وحدها — ونقاش الفريق الخاص يبقى للفريق",
      "roles.admin.c1.t": "إعداد الفصل",
      "roles.admin.c1.d": "التخصصات وأنواع المشاريع وحدود الفرق والفصول",
      "roles.admin.c2.t": "الحسابات",
      "roles.admin.c2.d": "استيراد الطلاب والمشرفين من Excel دفعة واحدة",
      "roles.admin.c3.t": "متابعة الفرق",
      "roles.admin.c3.d": "المتأخّر والمتوقّف وما ينتظر المشرف، بنقرة",
      "roles.admin.c4.t": "سجلّ التدقيق",
      "roles.admin.c4.d": "كل قرار مسجّل، وفتح الدرجة المعتمدة بسبب مكتوب",
      "roles.admin.private": "ترى كل شيء إلا نقاش الفرق الخاص — وكل قرار لها في السجلّ",
      "roles.flow.label": "كيف تتصل الأدوار",
      "roles.flow.1": "يسلّم المرحلة ويعدّل",
      "roles.flow.2": "يعتمد ويرصد الدرجة",
      "roles.flow.3": "تتابع الفصل كلّه",
      "roles.tour.pause": "إيقاف الجولة",
      "roles.tour.play": "تشغيل الجولة",
      "roles.scene.s.now": "ماذا عليّ الآن",
      "roles.scene.s.stage": "الفصل الثالث — التحليل",
      "roles.scene.s.due": "آخر موعد بعد يومين",
      "roles.scene.s.submit": "تسليم المرحلة",
      "roles.scene.s.progress": "إنجاز المشروع",
      "roles.scene.v.review": "بانتظار مراجعتك",
      "roles.scene.v.team": "فريق «التنبؤ بالتسرب»",
      "roles.scene.v.round": "الفصل الثالث · الجولة 2",
      "roles.scene.v.approve": "اعتماد",
      "roles.scene.v.revise": "مطلوب تعديل",
      "roles.scene.a.health": "متابعة الفرق",
      "roles.scene.a.late": "مرحلة فات موعدها",
      "roles.scene.a.review": "تسليم ينتظر المشرف",
      "roles.scene.a.idle": "فريق متوقّف",
      "roles.scene.a.roles": "فريق بلا أدوار",
      "lc.kicker": "لماذا تخرُّج",
      "lc.title": "ما يتغيّر حين يصير مشروعك على تخرُّج",
      "lc.text": "ستّ مشكلات يعرفها كل فريق تخرّج، وما تفعله المنصة بكلٍّ منها.",
      "lc.1.title": "التنسيق",
      "lc.2.title": "الملفات",
      "lc.3.title": "المواعيد",
      "lc.4.title": "الملاحظات",
      "lc.before": "قبل",
      "lc.after": "مع تخرُّج",
      "lc.1.before": "مجموعة واتساب يقرؤها الجميع وتضيع فيها المهام",
      "lc.1.after": "نقاش فريق خاص، و@ لتنبيه زميل بعينه",
      "lc.2.before": "نسخ متضاربة بين البريد والرسائل",
      "lc.2.after": "ملف واحد، وملاحظاته عليه حتى تُعالَج",
      "lc.3.before": "جدول يُرسل مرّة ثم يضيع بين الرسائل",
      "lc.3.after": "خطة مراحل بمواعيدها وقوالبها تصل كل مجموعة",
      "lc.4.before": "«مرفوض» بلا سبب، وتخمين ما المطلوب",
      "lc.4.after": "«مطلوب تعديل» بسبب واضح، وكل جولة محفوظة",
      "lc.5.title": "المسؤوليات",
      "lc.5.before": "لا أحد يعرف مَن فعل ماذا حتى المناقشة",
      "lc.5.after": "أدوار معلنة يراها الفريق والمشرف",
      "lc.6.title": "الدرجة",
      "lc.6.before": "كشف ورقي يتغيّر ولا أثر لمن غيّره",
      "lc.6.after": "درجة معتمدة مقفلة، وسجلّ لكل قرار",
      "contact.kicker": "اتصل بنا",
      "contact.title": "تواصل معنا",
      "contact.text": "عندك سؤال أو اقتراح حول منصة تخرُّج؟ اكتب لنا وسنرد في أقرب وقت.",
      "contact.asideTitle": "قبل أن تكتب",
      "contact.asideText": "حسابك يُنشأ من إدارة قسمك، فإن لم تستطع الدخول برقمك الجامعي راجعها أولاً. وللأسئلة حول المواعيد وأنواع المشاريع، اكتب لنا هنا.",
      "contact.pointMail": "الرد على بريدك الإلكتروني",
      "contact.pointHours": "من الأحد إلى الخميس",
      "contact.faqTitle": "ربما الجواب جاهز",
      "contact.faqText": "سبعة أسئلة يسألها كل فريق قبل البدء",
      "contact.topics": "مواضيع شائعة",
      "contact.topic1": "مشكلة في الدخول",
      "contact.topic2": "سؤال عن المواعيد",
      "contact.topic3": "اقتراح للمنصة",
      "contact.hint": "نردّ على بريدك مباشرة",
      "contact.doneTitle": "وصلت رسالتك",
      "contact.doneText": "سنردّ عليك في أقرب وقت على",
      "contact.again": "إرسال رسالة أخرى",
      "contact.errNetwork": "تعذّر الإرسال — تحقّق من اتصالك وحاول مرة أخرى.",
      "contact.errThrottle": "أرسلت عدّة رسائل متتالية — انتظر دقيقة ثم حاول مجدداً.",
      "contact.errServer": "حدث خطأ من جهتنا — حاول بعد قليل.",
      "contact.errRequired": "هذا الحقل مطلوب.",
      "contact.errEmail": "اكتب بريداً إلكترونياً صحيحاً.",
      "form.name": "الاسم",
      "form.email": "البريد الإلكتروني",
      "form.subject": "الموضوع",
      "form.message": "الرسالة",
      "form.send": "إرسال",
      "footer.made": "منصة متابعة مشاريع التخرج",
      "cta.title": "جاهز تبدأ مشروع تخرجك؟",
      "cta.text": "حسابك جاهز مسبقاً من إدارة المنصة — سجّل دخولك برقمك الجامعي، كوّن فريقك، واختر مشرفك. بقية الطريق تتابعها من لوحتك.",
      "cta.button": "تسجيل الدخول",
      "cta.secondary": "عندي سؤال",
      "cta.step1": "سجّل برقمك الجامعي",
      "cta.step2": "كوّن فريقك",
      "cta.step3": "اختر مشرفك",
      "footer.current": "الفصل الحالي",
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
      "hero.badge": "Graduation project management for universities",
      "bento.aria": "A live glimpse of the platform",
      "bento.progress": "Progress",
      "bento.project": "Fraud message detection",
      "bento.of": "of 5 stages",
      "svx.1.t": "Team and supervisor in minutes",
      "svx.1.l": "Classmates from your major, a supervisor with open seats, and a heads-up if your idea was done before.",
      "svx.2.t": "Submit, get feedback",
      "svx.2.l": "Each stage with a file — your supervisor approves it or asks for changes, with the reason.",
      "svx.3.t": "A ready milestone plan",
      "svx.3.l": "Set once by the supervisor, with dates and templates, and sent to all their groups.",
      "svx.4.t": "Live discussion",
      "svx.4.l": "A private team channel and one with your supervisor — messages arrive instantly.",
      "svx.5.t": "Defense and a locked grade",
      "svx.5.l": "A committee, a time and a room, and a grade that is recorded and then locked.",
      "svx.6.t": "Learn from past projects",
      "svx.6.l": "Browse earlier cohorts’ projects by type and supervisor.",
      "svx.s1.sup": "Dr. Heba Alshawa",
      "svx.s1.supl": "Supervisor · AI programming",
      "svx.s1.seats": "seats",
      "svx.s1.r1": "Front end",
      "svx.s1.r2": "Back end",
      "svx.s1.r3": "Docs",
      "svx.s1.alert": "A similar idea was done in 2023 — review it first",
      "svx.s2.file": "Chapter 3",
      "svx.s2.warn": "Changes requested",
      "svx.s2.ok": "Approved ✓",
      "svx.s2.prog": "Progress",
      "svx.s3.title": "This term’s milestone plan",
      "svx.s3.m1": "Requirements",
      "svx.s3.m1d": "Jun 30",
      "svx.s3.m2": "Database design",
      "svx.s3.m2d": "Jul 14",
      "svx.s3.m3": "Interfaces",
      "svx.s3.m3d": "Jul 28",
      "svx.s3.m4": "Development",
      "svx.s3.m4d": "Aug 11",
      "svx.s3.m5": "Testing & docs",
      "svx.s3.m5d": "Aug 25",
      "svx.s3.sent": "Sent to 4 groups ✓",
      "svx.s4.head": "Team chat",
      "svx.s4.lock": "🔒 Private",
      "svx.s4.m1": "Chapter three is uploaded ✅",
      "svx.s4.at": "@Aya",
      "svx.s4.m2": "great, I’ll review it tonight",
      "svx.s4.m3": "I’m on the slides 🎤",
      "svx.s5.mon": "October",
      "svx.s5.when": "Sunday 09:00 · Room 204",
      "svx.s5.who": "Supervisor + 2 examiners · a chair",
      "svx.s5.tag": "Excellent · Locked 🔒",
      "svx.s6.q": "AI",
      "svx.s6.p1": "Fake-news detection",
      "svx.s6.p1t": "AI · 2023",
      "svx.s6.p2": "Online store",
      "svx.s6.p2t": "Web · 2022",
      "svx.s6.p3": "X-ray image analysis",
      "svx.s6.p3t": "AI · 2024",
      "svx.s6.p4": "Clinic booking app",
      "svx.s6.p4t": "Mobile · 2023",
      "fh.1": "A team chat your supervisor can’t see",
      "fh.2": "Every decision saved and locked",
      "fh.3": "Only the team, supervisor and admins",
      "fh.4": "Only what concerns you",
      "fh.5": "And English in one click",
      "fh.6": "Every page fits your phone",
      "st.1": "Email or university ID — your account is ready.",
      "st.2": "Teammates, a supervisor with seats, a fresh idea.",
      "st.3": "Submit, then approval or changes with a note.",
      "st.4": "A committee and a grade that won’t change.",
      "cmp.m1": "Who uploaded the latest file?",
      "cmp.m2": "Is it due tomorrow or next week??",
      "cmp.m3": "Rejected",
      "cmp.m4": "Re: Re: Fwd: changes",
      "cmp.m5": "Who’s on the front end?",
      "cmp.m6": "Grade sheet (paper)",
      "cmp.aria": "Compare before and with Takharruj",
      "contact.bubble": "Hi! We read every message 👋",
      "journey.s1": "Idea",
      "journey.s2": "Team",
      "journey.s3": "Supervisor approval",
      "journey.s4": "Milestones",
      "journey.s5": "Defense",
      "journey.s6": "Graduation",
      "journey.c1": "A new idea",
      "journey.c1s": "Fake-news detection with AI",
      "journey.c2": "Team complete",
      "journey.c2s": "3 members · led by Aya",
      "journey.c3": "Supervisor approved",
      "journey.c3s": "Dr. Heba accepted the request",
      "journey.c4": "Milestone approved",
      "journey.c4s": "Chapter three · 4 of 5",
      "journey.c5": "Defense scheduled",
      "journey.c5s": "Sunday 09:00 · Room 204",
      "journey.c6": "We graduated! 🎓",
      "journey.c6s": "Grade 96 / 100 · Excellent",
      "bento.stage": "Current stage",
      "bento.stageTitle": "Chapter 2 — Literature review",
      "bento.s1": "Open",
      "bento.s2": "Submitted",
      "bento.s3": "Approved",
      "bento.chat": "Team chat",
      "bento.private": "Private",
      "bento.msg": "@Aya please review chapter 2 by Thursday",
      "bento.at": "@Aya",
      "bento.roles": "Team roles",
      "bento.r1": "Frontend",
      "bento.r2": "Backend",
      "bento.r3": "Docs",
      "bento.grade": "Final grade",
      "bento.excellent": "Excellent",
      "bento.pending": "Not graded yet",
      "meta.title": "Takharruj | Graduation Project Tracking",
      "hero.title": "<span class=\"ink-line\"><span>Track Your Graduation Project</span></span><span class=\"ink-line\"><span>From Idea <span class=\"text-gradient\">to <span class=\"rot\" data-rot=\"Defense|Grading|Graduation\">Defense</span></span></span></span>",
      "hero.stageLabel": "The project path",
      "hero.stageProject": "From submission to final grade",
      "hero.step1": "Submit a proposal",
      "hero.step1m": "Student and team",
      "hero.step2": "Supervisor approval",
      "hero.step2m": "Accept or reject with a reason",
      "hero.step3": "Track the work",
      "hero.step3m": "Milestones, files, discussion",
      "hero.step4": "Defense & grading",
      "hero.step4m": "A recorded final grade",
      "hero.fact1": "projects tracked",
      "hero.figuresLabel": "The platform today",
      "unit.students": "students",
      "unit.members": "members",
      "state.progress": "In progress",
      "state.done": "Completed",
      "hero.fact2": "academic supervisors",
      "hero.fact3": "students",
      "hero.sub": "Your team, your supervisor, your stages and your grade — all followed from one dashboard, with your supervisor alongside you.",
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
      "about.title": "From Team to Final Grade",
      "about.text": "Takharruj is a standalone platform for running graduation projects — from forming the team and picking a supervisor, through idea approval and phase tracking, all the way to the defense and final evaluation.",
      "about.pillarsTitle": "Platform pillars",
      "about.panelTitle": "The platform now",
      "about.panelEmpty": "No specializations recorded yet",
      "about.live": "Live",
      "about.statStudents": "students",
      "about.statSupervisors": "supervisors",
      "about.statDone": "completed projects",
      "about.split": "Students by major",
      "about.other": "Other majors",
      "about.p1": "Student teams & supervisor management",
      "about.p2": "Stage plans, submissions and review",
      "about.p3": "Defense, evaluation & grade recording",
      "about.more": "Learn more",
      "services.kicker": "Services",
      "services.title": "Everything Your Project Needs, in One Place",
      "services.text": "From forming your team to an approved grade — tools that replace scattered WhatsApp groups, emails and files.",
      "features.5.title": "Arabic first",
      "features.5.text": "A fully right-to-left Arabic interface with fonts made for reading — and English one click away.",
      "features.6.title": "Mobile as well as desktop",
      "features.6.text": "Submit a stage or reply to your supervisor from your phone — every page is designed for small screens.",
      "features.kicker": "Features",
      "features.title": "Built for Academic Reality",
      "features.text": "Details you don't see at first glance — but they're what makes the term run without surprises.",
      "features.trust.label": "Trust guarantees",
      "features.trust.text": "What you can rely on in an appeal or an audit",
      "features.daily.label": "Everyday comfort",
      "features.daily.text": "What makes daily work lighter",
      "features.p1.msg": "Finish the analysis chapter tonight?",
      "features.p1.seen": "Visible to the team only",
      "features.p1.sup": "Supervisor",
      "features.p1.adm": "Admins",
      "features.p3.l1": "Grade approved",
      "features.p3.l2": "Approval lifted — with a reason",
      "features.p3.lock": "No edits, no deletes",
      "features.p4.who": "Download for the team, supervisor and admins only",
      "features.p2.a": "Changes requested on \"Chapter 3\"",
      "features.p2.b": "A teammate mentioned you",
      "features.p2.c": "An ordinary chat message",
      "features.p2.muted": "No alert",
      "features.p5.btn": "Toggle the example language",
      "features.p6.stage": "Chapter 3",
      "features.p6.btn": "Submit stage",
      "features.1.title": "Team privacy",
      "features.1.text": "The internal team chat is hidden from supervisors and admins — students speak freely instead of escaping to WhatsApp.",
      "features.2.title": "Notifications without noise",
      "features.2.text": "You're alerted when it's about you: changes requested, a mention, or an approved stage — not for every message.",
      "features.3.title": "A record that can't be edited",
      "features.3.text": "Grades lock once approved, and every key decision is kept in an audit log — a clear reference for any appeal.",
      "features.4.title": "Protected files",
      "features.4.text": "Project files and submissions live on private storage, downloadable only by the team, their supervisor and admins.",
      "how.kicker": "How it works",
      "how.title": "Four Steps from Idea to Grade",
      "how.text": "Your account is created by your department — no sign-up, no waiting. Just sign in.",
      "how.1.title": "Sign in",
      "how.1.text": "With your email or university ID — your account is ready.",
      "how.2.title": "Form your team, propose your idea",
      "how.2.text": "Choose teammates and a supervisor with open seats, after checking your idea hasn't been done.",
      "how.3.title": "Submit your stages",
      "how.3.text": "Stage by stage: submit, get approved or revise with feedback, and talk with your team and supervisor.",
      "how.4.title": "Defend and get your grade",
      "how.4.text": "After the defense your supervisor records your grade, and once approved it's final.",
      "show.kicker": "From the platform",
      "show.title": "Projects Completed on Takharruj",
      "show.text": "Not invented examples — projects teams actually completed on the platform, stage by stage.",
      "show.stat.done": "completed projects",
      "show.stat.avg": "average grade",
      "show.stat.specs": "majors",
      "show.stat.stages": "approved stages per project",
      "show.stages": "approved stages",
      "show.weeks": "weeks",
      "show.more": "Sign in to browse the full project archive",
      "dept.kicker": "For departments",
      "dept.title": "The Whole Semester, at a Glance",
      "dept.text": "Instead of scattered spreadsheets and chat groups, Takharruj gives the department a single picture: who applied, who approved, where each team stands, and who has not joined a group yet.",
      "dept.1.title": "Supervisor Capacity, Set per Supervisor",
      "dept.1.text": "You set how many groups each supervisor accepts, and the system rejects the surplus automatically.",
      "dept.2.title": "Import Students and Supervisors from Excel",
      "dept.2.text": "Upload the cohort sheet once and every account is created without manual entry.",
      "dept.3.title": "Project Types with Team Limits per Specialization",
      "dept.3.text": "For each specialization you define its project types and the minimum and maximum team size.",
      "dept.4.title": "Export the Groups Sheet to Excel",
      "dept.4.text": "Produce a sheet of groups, their supervisors and their grades at any point in the semester.",
      "dept.5.title": "An Audit Log for Every Decision",
      "dept.5.text": "Every approval, grade unlock and key change is kept with who did it and when.",
      "dept.6.title": "Semesters You Open and Close",
      "dept.6.text": "Open a new semester for submissions and close the last, so each cohort stays in its own term.",
      "dept.file.name": "Semester file",
      "dept.file.term": "First semester",
      "dept.file.q1": "Who applied?",
      "dept.file.q2": "Who approved?",
      "dept.file.q3": "Where is each team?",
      "dept.file.q4": "Who hasn’t joined?",
      "dept.file.teams": "teams",
      "dept.file.students": "students",
      "dept.file.s1": "Idea",
      "dept.file.s2": "Build",
      "dept.file.s3": "Delivery",
      "dept.file.s4": "Defense",
      "dept.p1.when": "Before the semester",
      "dept.p1.name": "Setup",
      "dept.p2.when": "As it starts",
      "dept.p2.name": "Assignment",
      "dept.p3.when": "Through to the end",
      "dept.p3.name": "Tracking & closing",
      "dept.pf.term2": "Second semester",
      "dept.pf.accounts": "accounts",
      "dept.pf.type": "Software project",
      "dept.pf.members": "members",
      "dept.pf.groups": "groups",
      "dept.pf.sixth": "Sixth rejected",
      "dept.pf.audit": "Project idea approved",
      "dept.pf.export": "Download sheet",
      "faq.kicker": "FAQ",
      "faq.title": "Everything Students Ask Before Starting",
      "faq.text": "Straight answers from how the system actually works — for anything else, reach us in the contact section below.",
      "faq.1.title": "How Many Members per Team?",
      "faq.1.text": "It depends on the project type set by your department — each type has a min and max shown in the submission form, and the system enforces them.",
      "faq.2.title": "How Do I Submit a Project Request?",
      "faq.2.text": "Four steps from your dashboard, and your supervisor is notified instantly:",
      "faq.3.title": "How Do I Hear Back — and What If I'm Rejected?",
      "faq.3.text": "You're notified the moment your request is accepted or rejected, with the supervisor's note if they wrote one. If it's rejected, a new submission form opens right away — refine your idea or pick another supervisor.",
      "faq.4.title": "What Does “Changes Requested” Mean?",
      "faq.4.text": "That your supervisor reviewed the stage and wrote what's missing — not that it was rejected. Fix your file and resubmit from the same page; every round and its note are kept.",
      "faq.5.title": "Who Assigns Roles in the Team?",
      "faq.5.text": "The team leader: they give each member a role and responsibilities, visible to the whole team and the supervisor — clear who owns what before the defense.",
      "faq.6.title": "Can the Supervisor See the Team Chat?",
      "faq.6.text": "No. The team has a private channel hidden from supervisors and admins, plus a second channel shared with the supervisor for questions and feedback.",
      "faq.7.title": "How Is My Final Project Graded?",
      "faq.7.text": "Once the project is complete your supervisor records a grade out of 100 with notes, the rating is derived from it automatically, and the whole team is notified. Once approved it locks — only admins can reopen it, with a written reason, and that is logged.",
      "faq.filters": "Filter questions",
      "faq.cat.all": "All",
      "faq.cat.apply": "Applying",
      "faq.cat.team": "Team",
      "faq.cat.track": "Tracking & grading",
      "faq.2.s1": "Sign in",
      "faq.2.s2": "Pick a type and a supervisor with seats",
      "faq.2.s3": "Choose your teammates",
      "faq.2.s4": "Write the title and description, send",
      "faq.ask.title": "Didn't find your answer?",
      "faq.ask.text": "Write to us and we will reply to your email as soon as we can.",
      "faq.ask.button": "Message us",
      "roles.kicker": "Platform roles",
      "roles.title": "A Dedicated Space for Every Role",
      "roles.text": "Three roles, each with its own dashboard and permissions — handing work to one another at the right moment. Pick a role to see its space.",
      "roles.student.name": "Student", "roles.student.role": "Team, submissions, discussion",
      "roles.supervisor.name": "Supervisor", "roles.supervisor.role": "Plan, review, evaluate",
      "roles.admin.name": "Administration", "roles.admin.role": "Configure and organize the term",
      "roles.student.c1.t": "Form your team",
      "roles.student.c1.d": "Teammates from your major, and a supervisor with open seats",
      "roles.student.c2.t": "Submit stages",
      "roles.student.c2.d": "A file and a note per stage, resubmitted after changes",
      "roles.student.c3.t": "Team chat",
      "roles.student.c3.d": "A private team channel, with @ to ping a teammate",
      "roles.student.c4.t": "Roles & reminders",
      "roles.student.c4.d": "The leader assigns responsibilities; a reminder arrives before each deadline",
      "roles.student.private": "The team's private chat never reaches the supervisor or admins",
      "roles.supervisor.c1.t": "Accept requests",
      "roles.supervisor.c1.d": "Within their seats, with a warning for similar ideas",
      "roles.supervisor.c2.t": "Stage plan",
      "roles.supervisor.c2.d": "Dates and templates sent to all their groups at once",
      "roles.supervisor.c3.t": "Review",
      "roles.supervisor.c3.d": "Approve, or \"changes requested\" with a written reason",
      "roles.supervisor.c4.t": "Grade",
      "roles.supervisor.c4.d": "Recorded with a rating and notes, then locked on approval",
      "roles.supervisor.private": "Sees only their own groups — the team's private chat stays with the team",
      "roles.admin.c1.t": "Set up the term",
      "roles.admin.c1.d": "Majors, project types, team limits and semesters",
      "roles.admin.c2.t": "Accounts",
      "roles.admin.c2.d": "Import students and supervisors from Excel in one go",
      "roles.admin.c3.t": "Team health",
      "roles.admin.c3.d": "What's late, idle or waiting on a supervisor — one click away",
      "roles.admin.c4.t": "Audit log",
      "roles.admin.c4.d": "Every decision recorded; approved grades unlocked with a reason",
      "roles.admin.private": "Sees everything except teams' private chats — and every action is logged",
      "roles.flow.label": "How the roles connect",
      "roles.flow.1": "Submits and revises",
      "roles.flow.2": "Approves and grades",
      "roles.flow.3": "Oversees the whole term",
      "roles.tour.pause": "Pause tour",
      "roles.tour.play": "Play tour",
      "roles.scene.s.now": "What's on me now",
      "roles.scene.s.stage": "Chapter 3 — Analysis",
      "roles.scene.s.due": "Due in two days",
      "roles.scene.s.submit": "Submit stage",
      "roles.scene.s.progress": "Project progress",
      "roles.scene.v.review": "Waiting for your review",
      "roles.scene.v.team": "Team \"Dropout prediction\"",
      "roles.scene.v.round": "Chapter 3 · Round 2",
      "roles.scene.v.approve": "Approve",
      "roles.scene.v.revise": "Request changes",
      "roles.scene.a.health": "Team health",
      "roles.scene.a.late": "Overdue stages",
      "roles.scene.a.review": "Waiting on supervisor",
      "roles.scene.a.idle": "Idle teams",
      "roles.scene.a.roles": "Teams without roles",
      "lc.kicker": "Why Takharruj",
      "lc.title": "What Changes When Your Project Runs on Takharruj",
      "lc.text": "Six problems every graduation team knows — and what the platform does about each.",
      "lc.1.title": "Coordination",
      "lc.2.title": "Files",
      "lc.3.title": "Deadlines",
      "lc.4.title": "Feedback",
      "lc.before": "Before",
      "lc.after": "With Takharruj",
      "lc.1.before": "A WhatsApp group everyone reads, where tasks get lost",
      "lc.1.after": "A private team chat, with @ to ping a teammate",
      "lc.2.before": "Conflicting copies across email and chats",
      "lc.2.after": "One file, with its notes attached until resolved",
      "lc.3.before": "A schedule sent once, then buried in messages",
      "lc.3.after": "A stage plan with dates and templates for every group",
      "lc.4.before": "“Rejected” with no reason, and guessing what’s wanted",
      "lc.4.after": "“Changes requested” with a clear reason, every round kept",
      "lc.5.title": "Ownership",
      "lc.5.before": "Nobody knows who did what until the defense",
      "lc.5.after": "Declared roles, visible to the team and supervisor",
      "lc.6.title": "Grades",
      "lc.6.before": "A paper sheet that changes with no trace of who changed it",
      "lc.6.after": "An approved, locked grade — and a log of every decision",
      "contact.kicker": "Contact",
      "contact.title": "Get in Touch",
      "contact.text": "Have a question or a suggestion about Takharruj? Write to us and we'll get back to you shortly.",
      "contact.asideTitle": "Before you write",
      "contact.asideText": "Your account is created by your department. If you cannot sign in with your university ID, check with them first. For questions about deadlines and project types, write to us here.",
      "contact.pointMail": "A reply to your email",
      "contact.pointHours": "Sunday to Thursday",
      "contact.faqTitle": "The answer may be ready",
      "contact.faqText": "Seven questions every team asks before starting",
      "contact.topics": "Common topics",
      "contact.topic1": "Sign-in problem",
      "contact.topic2": "Question about deadlines",
      "contact.topic3": "Suggestion",
      "contact.hint": "We reply straight to your email",
      "contact.doneTitle": "Message received",
      "contact.doneText": "We'll reply as soon as we can at",
      "contact.again": "Send another message",
      "contact.errNetwork": "Couldn't send — check your connection and try again.",
      "contact.errThrottle": "Too many messages in a row — wait a minute and try again.",
      "contact.errServer": "Something went wrong on our side — try again shortly.",
      "contact.errRequired": "This field is required.",
      "contact.errEmail": "Enter a valid email address.",
      "form.name": "Your name",
      "form.email": "Your email",
      "form.subject": "Subject",
      "form.message": "Message",
      "form.send": "Send message",
      "footer.made": "Graduation Project Tracking Platform",
      "cta.title": "Ready to Start Your Graduation Project?",
      "cta.text": "Your account is already created by the platform administration — sign in with your university ID, build your team and pick your supervisor. The rest you follow from your dashboard.",
      "cta.button": "Sign in",
      "cta.secondary": "I have a question",
      "cta.step1": "Sign in with your university ID",
      "cta.step2": "Build your team",
      "cta.step3": "Pick your supervisor",
      "footer.current": "Current semester",
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

  // اختيار من اللوحة (كوكي) يتبعه الموقع العام أيضاً
  var cookieLocale = (document.cookie.match(/(?:^|;\s*)locale=(ar|en)/) || [])[1];
  var locale = cookieLocale || localStorage.getItem("locale") || "ar";

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
    // الكوكي نفسه تقرؤه اللوحات (SetLocale): اختيار واحد للموقع كله
    document.cookie = "locale=" + loc + ";path=/;max-age=31536000;samesite=lax";
    document.documentElement.lang = loc;
    document.documentElement.dir = d.dir;
    if (d["meta.title"]) document.title = d["meta.title"];

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
    // وصف الصور لقارئات الشاشة يتبع اللغة أيضاً (لقطة المنتج في الهيرو)
    document.querySelectorAll("[data-i18n-alt]").forEach(function (el) {
      var key = el.getAttribute("data-i18n-alt");
      if (d[key] !== undefined) el.setAttribute("alt", d[key]);
    });
    document.querySelectorAll("[data-i18n-aria]").forEach(function (el) {
      var key = el.getAttribute("data-i18n-aria");
      if (d[key] !== undefined) el.setAttribute("aria-label", d[key]);
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
    var narrow = window.matchMedia("(max-width: 640px)");
    var lastY = window.scrollY;
    function onScroll() {
      var y = window.scrollY;
      if (siteHeader) siteHeader.classList.toggle("scrolled", y > 24);
      if (backTop) {
        // على الجوال يغطّي النص أثناء القراءة: يظهر فقط حين يصعد القارئ
        var up = y < lastY;
        backTop.classList.toggle("show", y > 500 && (!narrow.matches || up));
        lastY = y;
        var max = document.documentElement.scrollHeight - window.innerHeight;
        backTop.style.setProperty("--p", max > 0 ? Math.min(100, (y / max) * 100).toFixed(1) + "%" : "0%");
      }
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

    initFaq();

    /* حُذفت خمس سلوكيات زخرفية: بارالاكس الهيرو، إمالة البطاقات،
       الأزرار المغناطيسية، توهّج المؤشر بحلقة requestAnimationFrame
       الدائمة، والجسيمات الطافية. الحركة الباقية في CSS وحدها. */

    initBento();
    initRotator();
    initJourney();
    initServices();
    initFeatureHub();
    initStairs();
    initCompare();
    initSeason();
    initContact();
    initRoles();
    initLangProof();
  });

  /* ---------- الهيرو: رحلة المشروع ----------
     الفريق يمشي على المسار من محطة إلى محطة، وعند كل وصول تُضاء المحطة
     وتطفو بطاقتها، والأثر خلفه يمتلئ. في المحطة الأخيرة تُرمى القبعات
     وتتناثر القصاصات، ثم تبدأ الرحلة من جديد. العالم يُرسم يساراً→يميناً
     ويُعكس في العربية. على الشاشات الضيقة «كاميرا» تتبع الفريق (viewBox).
     تتوقّف حين يخرج المشهد من الشاشة أو يُخفى التبويب. */
  function initJourney() {
    var scene = document.querySelector("[data-journey]");
    if (!scene) return;
    var svg = scene.querySelector(".hj-svg");
    var world = svg.querySelector(".hj-world");
    var path = svg.querySelector(".hj-path");
    var trail = svg.querySelector(".hj-trail");
    var team = svg.querySelector(".hj-team");
    var stations = Array.prototype.slice.call(svg.querySelectorAll(".hj-station"));
    var labels = scene.querySelectorAll("[data-label]");
    var cards = scene.querySelectorAll("[data-card]");
    var confetti = scene.querySelector(".hj-confetti");
    var L = path.getTotalLength();
    var STOPS = [0.02, 0.2, 0.39, 0.58, 0.78, 0.985];
    var W = 1200;

    trail.style.strokeDasharray = L;
    trail.style.strokeDashoffset = L;

    function rtl() { return document.documentElement.dir === "rtl"; }
    function at(f) { return path.getPointAtLength(L * f); }

    // العالم معكوس في العربية: الفريق يبدأ من اليمين كما يُقرأ السطر
    function orient() {
      world.setAttribute("transform", rtl() ? "translate(" + W + " 0) scale(-1 1)" : "");
      svg.classList.toggle("is-rtl", rtl());
    }

    function placeStatics() {
      stations.forEach(function (g, k) {
        var p = at(STOPS[k]);
        g.setAttribute("transform", "translate(" + p.x + " " + p.y + ")");
        // الأيقونة تبقى مقروءة في الاتجاهين
        g.querySelector(".hj-ico").setAttribute("transform", (rtl() ? "scale(-1 1) " : "") + "translate(-9 -9) scale(.75)");
      });
      svg.querySelectorAll(".hj-actor").forEach(function (u) {
        var p = at(STOPS[+u.dataset.at]);
        u.setAttribute("transform", "translate(" + (p.x + +u.dataset.dx) + " " + (p.y + +u.dataset.dy) + ")");
      });
    }

    var f = STOPS[0];
    function placeTeam() {
      var p = at(f);
      team.setAttribute("transform", "translate(" + p.x + " " + (p.y - 2) + ")");
      trail.style.strokeDashoffset = L * (1 - f);
      camera(p.x);
    }

    // كاميرا الشاشات الضيقة: نافذة بعرض 460 تتبع الفريق
    var narrow = false;
    function camera(x) {
      if (!narrow) return;
      var vx = rtl() ? W - x : x;
      var left = Math.max(0, Math.min(W - 460, vx - 230));
      svg.setAttribute("viewBox", left + " 40 460 260");
      overlay();
    }
    function measure() {
      narrow = scene.clientWidth < 760;
      if (!narrow) svg.setAttribute("viewBox", "0 0 " + W + " 300");
      placeTeam();
      overlay();
    }

    // الأسماء والبطاقات (HTML) فوق المحطات
    function overlay() {
      var box = scene.getBoundingClientRect();
      stations.forEach(function (g, k) {
        var r = g.querySelector(".hj-node").getBoundingClientRect();
        var x = r.left + r.width / 2 - box.left, y = r.top - box.top;
        var off = r.right < box.left || r.left > box.right;
        if (labels[k]) { labels[k].style.transform = "translate(" + x + "px," + (y + r.height + 8) + "px) translate(-50%, 0)"; labels[k].classList.toggle("is-out", off); }
        if (cards[k]) {
          var cw = cards[k].offsetWidth || 220;
          var cx = Math.max(8, Math.min(box.width - cw - 8, x - cw / 2));
          cards[k].style.transform = "translate(" + cx + "px," + (y - (narrow ? 96 : 112)) + "px) translate(0, -100%)";
        }
      });
    }

    function station(k) {
      stations.forEach(function (g, i) { g.classList.toggle("is-done", i < k); g.classList.toggle("is-now", i === k); });
      labels.forEach(function (l, i) { l.classList.toggle("is-now", i === k); l.classList.toggle("is-done", i < k); });
      cards.forEach(function (c, i) { c.classList.toggle("is-show", i === k); });
      overlay();
    }

    function burst() {
      if (!confetti) return;
      var colors = ["#2563eb", "#7c3aed", "#f59e0b", "#10b981", "#ec4899"];
      var r = team.getBoundingClientRect(), box = scene.getBoundingClientRect();
      for (var i = 0; i < 26; i++) {
        var s = document.createElement("i");
        s.style.left = (r.left + r.width / 2 - box.left) + "px";
        s.style.top = (r.top - box.top + 10) + "px";
        s.style.background = colors[i % colors.length];
        s.style.setProperty("--dx", (Math.random() * 260 - 130) + "px");
        s.style.setProperty("--dy", (-80 - Math.random() * 140) + "px");
        s.style.setProperty("--r", (Math.random() * 720 - 360) + "deg");
        s.style.animationDelay = (Math.random() * 120) + "ms";
        confetti.appendChild(s);
      }
      setTimeout(function () { confetti.innerHTML = ""; }, 1900);
    }

    orient();
    placeStatics();
    measure();
    window.addEventListener("resize", function () { measure(); });
    // تبديل اللغة يقلب الاتجاه
    new MutationObserver(function () { orient(); placeStatics(); measure(); })
      .observe(document.documentElement, { attributes: true, attributeFilter: ["dir"] });

    if (reduceMotion) {
      f = STOPS[STOPS.length - 1];
      placeTeam();
      station(STOPS.length - 1);
      scene.classList.add("is-graduated");
      return;
    }

    // ===== الحلقة =====
    var k = 0, running = false, visible = false, raf = 0, wait = 0, phase = "hold", t0 = 0, from = 0, to = 0;
    var MOVE = 1700, HOLD = 1500, GRAD_HOLD = 3200;

    function startMove() {
      if (k >= STOPS.length - 1) {
        // نهاية الرحلة: من جديد
        scene.classList.add("is-fading");
        wait = setTimeout(function () {
          k = 0; f = STOPS[0]; placeTeam(); station(0);
          scene.classList.remove("is-graduated", "is-fading");
          wait = setTimeout(step, HOLD);
        }, 600);
        return;
      }
      from = STOPS[k]; to = STOPS[k + 1]; t0 = performance.now(); phase = "move";
      cards.forEach(function (c) { c.classList.remove("is-show"); });
      scene.classList.add("is-walking");
      raf = requestAnimationFrame(frame);
    }
    function frame(now) {
      if (!running) return;
      var p = Math.min(1, (now - t0) / MOVE);
      var e = p < .5 ? 2 * p * p : 1 - Math.pow(-2 * p + 2, 2) / 2;
      f = from + (to - from) * e;
      placeTeam();
      if (p < 1) { raf = requestAnimationFrame(frame); return; }
      k++;
      phase = "hold";
      scene.classList.remove("is-walking");
      station(k);
      if (k === STOPS.length - 1) {
        scene.classList.add("is-graduated");
        burst();
        wait = setTimeout(step, GRAD_HOLD);
      } else {
        wait = setTimeout(step, HOLD);
      }
    }
    function step() { if (running) startMove(); else phase = "paused"; }

    function sync() {
      var should = visible && !document.hidden;
      if (should === running) return;
      running = should;
      if (running) {
        if (phase === "move") { t0 = performance.now() - (f - from) / ((to - from) || 1) * MOVE; raf = requestAnimationFrame(frame); }
        else if (phase === "paused") { startMove(); }
      } else {
        cancelAnimationFrame(raf);
        if (phase === "hold") { clearTimeout(wait); phase = "paused"; }
      }
    }

    station(0);
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (entries) { visible = entries[0].isIntersecting; sync(); }, { threshold: 0.2 }).observe(scene);
    } else { visible = true; }
    document.addEventListener("visibilitychange", sync);
    phase = "paused";
    setTimeout(sync, 900);
  }

  /* ---------- العنوان: «من الفكرة إلى المناقشة · الدرجة · التخرّج» ----------
     الكلمات كلها في خانة واحدة (inline-grid) فعرضها عرض أطولها: التبديل لا
     يزيح السطر. القاموس يعيد كتابة العنوان عند تبديل اللغة، فيُقرأ العنصر في
     كل دورة لا مرّة واحدة. لمن أوقف الحركة: الكلمة الأولى ثابتة. */
  function initRotator() {
    if (reduceMotion) return;
    var i = 0;
    function build(el) {
      if (el.dataset.built === el.dataset.rot) return;
      el.innerHTML = "";
      el.dataset.rot.split("|").forEach(function (w, k) {
        var s = document.createElement("span");
        s.className = "rot-word" + (k === 0 ? " is-on" : "");
        s.textContent = w;
        el.appendChild(s);
      });
      el.dataset.built = el.dataset.rot;
      i = 0;
    }
    setInterval(function () {
      var el = document.querySelector("[data-rot]");
      if (!el || document.hidden) return;
      build(el);
      var words = el.querySelectorAll(".rot-word");
      var cur = words[i % words.length];
      i = (i + 1) % words.length;
      var nxt = words[i];
      cur.classList.remove("is-on"); cur.classList.add("is-off");
      nxt.classList.remove("is-off"); nxt.classList.add("is-on");
      setTimeout(function () { cur.classList.remove("is-off"); }, 600);
    }, 2600);
    var first = document.querySelector("[data-rot]");
    if (first) build(first);
  }

  /* ---------- الخدمات (القسم 02): قصّة متحركة ----------
     القائمة تتقدّم مع التمرير: الخدمة التي تعبر منتصف الشاشة تصير نشطة،
     ومسرحها يتبدّل وتبدأ حركته من أولها (الحركات في CSS تنتظر is-active).
     هنا ما لا يقدر عليه CSS: الأرقام تعدّ (data-tween) والبحث يُكتب (data-type).
     النقر على خدمة ينقل إليها. لمن أوقف الحركة: المشاهد بحالتها الأخيرة. */
  function initServices() {
    var root = document.querySelector("[data-svx]");
    if (!root) return;
    var items = Array.prototype.slice.call(root.querySelectorAll("[data-svx-item]"));
    var scenes = root.querySelectorAll("[data-scene]");
    var dots = root.querySelectorAll(".svx-dots i");
    var cur = -1, timers = [];
    root.classList.add("is-enhanced");

    function clear() { timers.forEach(clearTimeout); timers = []; }

    function finalOf(scene) {
      scene.querySelectorAll("[data-tween]").forEach(function (el) { el.textContent = el.dataset.to; });
      scene.querySelectorAll("[data-type]").forEach(function (el) { el.textContent = typeText(el); });
    }

    function typeText(el) {
      var d = dict[locale] || {};
      return d[el.getAttribute("data-i18n-type")] || el.textContent;
    }

    function play(scene) {
      if (reduceMotion) { finalOf(scene); return; }
      scene.querySelectorAll("[data-tween]").forEach(function (el) {
        var from = +el.dataset.from, to = +el.dataset.to;
        el.textContent = from;
        timers.push(setTimeout(function () {
          var t0 = performance.now();
          (function tick(t) {
            var k = Math.min(1, (t - t0) / 900), e = 1 - Math.pow(1 - k, 3);
            el.textContent = Math.round(from + (to - from) * e);
            if (k < 1 && scene.classList.contains("is-active")) requestAnimationFrame(tick);
          })(t0);
        }, +el.dataset.delay || 0));
      });
      scene.querySelectorAll("[data-type]").forEach(function (el) {
        var text = typeText(el), i = 0;
        el.textContent = "";
        timers.push(setTimeout(function step() {
          el.textContent = text.slice(0, ++i);
          if (i < text.length) timers.push(setTimeout(step, 140));
          else scene.classList.add("is-typed");
        }, +el.dataset.delay || 0));
      });
    }

    function activate(i) {
      if (i === cur) return;
      cur = i;
      clear();
      items.forEach(function (it, k) {
        it.classList.toggle("is-active", k === i);
        it.classList.toggle("is-past", k < i);
        it.querySelector(".svx-btn").setAttribute("aria-current", k === i ? "true" : "false");
      });
      dots.forEach(function (d, k) { d.classList.toggle("is-on", k === i); });
      scenes.forEach(function (s) { s.classList.remove("is-active", "is-typed"); });
      var scene = scenes[i];
      void scene.offsetWidth; // تعاد الحركة من أولها
      scene.classList.add("is-active");
      play(scene);
    }

    // النقر: إلى الخدمة نفسها (والتمرير يُكمل التفعيل)
    items.forEach(function (it, k) {
      it.querySelector(".svx-btn").addEventListener("click", function () {
        activate(k);
        it.scrollIntoView({ behavior: reduceMotion ? "auto" : "smooth", block: "center" });
      });
    });

    if (!("IntersectionObserver" in window)) { activate(0); return; }

    // الخدمة التي تعبر شريط منتصف الشاشة
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) activate(items.indexOf(e.target));
      });
    }, { rootMargin: window.matchMedia("(max-width: 900px)").matches ? "-64% 0px -30% 0px" : "-48% 0px -48% 0px" });
    items.forEach(function (it) { io.observe(it); });

    // أول ظهور للقسم: المشهد الأول يُلعب حين يُرى لا قبل
    new IntersectionObserver(function (entries, obs) {
      if (!entries[0].isIntersecting) return;
      obs.disconnect();
      if (cur <= 0) { cur = -1; activate(0); }
    }, { threshold: 0.35 }).observe(root.querySelector(".svx-stage"));
  }

  /* ---------- المميزات (03): محور حول جهاز ----------
     المميزة النشطة تتبدّل وحدها كل بضع ثوانٍ وتظهر حيّة على الشاشة؛ المرور
     أو النقر يختار ويوقف التبديل حتى يبتعد المؤشّر. في «عربية أولاً» الجملة
     تنقلب بين اللغتين أمامك. تتوقّف حين يخرج القسم من الشاشة. */
  function initFeatureHub() {
    var root = document.querySelector("[data-fhub]");
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll("[data-fh]"));
    var views = root.querySelectorAll("[data-fh-view]");
    var lang = root.querySelector("[data-fx-lang] .fx-lang-toggle");
    var cur = 0, timer = 0, flip = 0, held = false, visible = false;

    function show(i) {
      cur = i;
      tabs.forEach(function (t, k) {
        t.setAttribute("aria-selected", k === i ? "true" : "false");
        t.parentNode.classList.toggle("is-on", k === i);
      });
      views.forEach(function (v, k) { v.classList.toggle("is-on", k === i); });
      // الجوال: المميزات شريط أفقي — النشطة تنزلق إلى منتصفه (أفقياً فقط)
      var list = root.querySelector(".fh-pills"), pill = tabs[i].parentNode;
      if (visible && list.scrollWidth > list.clientWidth + 4) {
        var lr = list.getBoundingClientRect(), pr = pill.getBoundingClientRect();
        list.scrollBy({ left: pr.left - lr.left - (lr.width - pr.width) / 2, behavior: reduceMotion ? "auto" : "smooth" });
      }
      clearInterval(flip);
      if (i === 4 && lang && !reduceMotion) flip = setInterval(function () { lang.click(); }, 1600);
    }
    function tick() { if (visible && !held && !document.hidden) show((cur + 1) % tabs.length); }

    tabs.forEach(function (t, k) {
      t.addEventListener("click", function () { show(k); });
      t.addEventListener("mouseenter", function () { held = true; show(k); });
      t.addEventListener("focus", function () { held = true; show(k); });
      t.addEventListener("keydown", function (e) {
        var d = { ArrowDown: 1, ArrowUp: -1, ArrowLeft: 1, ArrowRight: -1 }[e.key];
        if (d === undefined) return;
        e.preventDefault();
        var n = (k + d + tabs.length) % tabs.length;
        tabs[n].focus();
      });
    });
    root.addEventListener("mouseleave", function () { held = false; });
    root.addEventListener("focusout", function () { held = false; });

    if (!reduceMotion) timer = setInterval(tick, 3400);
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (e) { visible = e[0].isIntersecting; }, { threshold: 0.3 }).observe(root);
    } else { visible = true; }
  }

  /* ---------- كيف يعمل (04): طالب يصعد الدرج ----------
     حين يظهر القسم يصعد الطالب درجةً درجة، وتُضاء كل درجة وشرحها عند وصوله،
     وفي القمّة تظهر قبعة التخرّج. يُعاد حين يعود القسم إلى الشاشة. */
  function initStairs() {
    var root = document.querySelector("[data-stairs]");
    if (!root) return;
    var steps = Array.prototype.slice.call(root.querySelectorAll("[data-step]"));
    var climber = root.querySelector(".st-climber");
    var timers = [];

    function place(k) {
      var block = steps[k].querySelector(".st-block");
      var box = root.getBoundingClientRect(), r = block.getBoundingClientRect();
      var x = r.left + r.width / 2 - box.left - climber.offsetWidth / 2;
      var y = r.top - box.top - climber.offsetHeight + 6;
      climber.style.transform = "translate(" + x + "px," + y + "px)";
    }
    function light(k) {
      steps.forEach(function (s, i) { s.classList.toggle("is-on", i <= k); s.classList.toggle("is-now", i === k); });
      root.classList.toggle("is-top", k === steps.length - 1);
    }
    function climb() {
      timers.forEach(clearTimeout); timers = [];
      root.classList.remove("is-top");
      if (reduceMotion) { place(steps.length - 1); light(steps.length - 1); return; }
      root.classList.add("no-move"); place(0); light(-1);
      void climber.offsetWidth;
      root.classList.remove("no-move");
      steps.forEach(function (s, k) {
        timers.push(setTimeout(function () {
          root.classList.add("is-hopping");
          place(k);
          timers.push(setTimeout(function () { root.classList.remove("is-hopping"); light(k); }, 520));
        }, 400 + k * 1100));
      });
    }
    window.addEventListener("resize", function () {
      var on = steps.filter(function (s) { return s.classList.contains("is-now"); })[0];
      place(on ? steps.indexOf(on) : 0);
    });
    place(0);
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (e) { if (e[0].isIntersecting) climb(); }, { threshold: 0.45 }).observe(root);
    } else { climb(); }
  }

  /* ---------- لماذا تخرُّج (07): قبل/بعد بشريط سحب ----------
     المقبض يُسحب أو يُحرَّك بالأسهم (input range فيعمل بلوحة المفاتيح وقارئ
     الشاشة)، وحين يظهر القسم أوّل مرة ينزلق وحده من «قبل» إلى «مع تخرُّج». */
  function initCompare() {
    var root = document.querySelector("[data-compare]");
    if (!root) return;
    var range = root.querySelector(".cmp-range");
    function set(v) { root.style.setProperty("--pos", v + "%"); range.value = v; }
    range.addEventListener("input", function () { root.classList.remove("is-auto"); set(+range.value); });
    set(50);
    if (reduceMotion || !("IntersectionObserver" in window)) { set(12); return; }
    var io = new IntersectionObserver(function (e) {
      if (!e[0].isIntersecting) return;
      io.disconnect();
      set(94);
      root.classList.add("is-auto");
      setTimeout(function () { set(12); }, 1200);
      setTimeout(function () { root.classList.remove("is-auto"); }, 3000);
    }, { threshold: 0.5 });
    io.observe(root);
  }

  /* ---------- الأقسام والكليات (09): المسؤول يمشي دورة الفصل ----------
     حين يظهر القسم يمشي مسؤول القسم من محطة إلى محطة، والخطّ خلفه يمتلئ،
     وتُضاء كل محطة وميزتاها عند وصوله. يُعاد حين يعود القسم إلى الشاشة. */
  function initSeason() {
    var root = document.querySelector("[data-season]");
    if (!root) return;
    var sts = Array.prototype.slice.call(root.querySelectorAll("[data-st]"));
    var walker = root.querySelector(".ds-walker");
    var fill = root.querySelector(".ds-fill");
    var timers = [];

    function place(k) {
      var node = sts[k].querySelector(".ds-node");
      var box = root.getBoundingClientRect(), r = node.getBoundingClientRect();
      var x = r.left + r.width / 2 - box.left - walker.offsetWidth / 2;
      var y = r.top - box.top - walker.offsetHeight + 4;
      walker.style.transform = "translate(" + x + "px," + y + "px)";
      var first = sts[0].querySelector(".ds-node").getBoundingClientRect();
      fill.style.width = Math.abs(r.left - first.left) + "px";
    }
    function light(k) { sts.forEach(function (s, i) { s.classList.toggle("is-on", i <= k); s.classList.toggle("is-now", i === k); }); }
    function walk() {
      timers.forEach(clearTimeout); timers = [];
      if (reduceMotion) { place(sts.length - 1); light(sts.length - 1); return; }
      root.classList.add("no-move"); place(0); light(-1);
      void walker.offsetWidth;
      root.classList.remove("no-move");
      sts.forEach(function (s, k) {
        timers.push(setTimeout(function () {
          root.classList.add("is-walking");
          place(k);
          timers.push(setTimeout(function () { root.classList.remove("is-walking"); light(k); }, k ? 1100 : 300));
        }, 300 + k * 1700));
      });
    }
    window.addEventListener("resize", function () {
      var now = sts.filter(function (s) { return s.classList.contains("is-now"); })[0];
      place(now ? sts.indexOf(now) : 0);
    });
    place(0);
    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (e) { if (e[0].isIntersecting) walk(); }, { threshold: 0.45 }).observe(root);
    } else { walk(); }
  }

  /* ---------- دليل «عربية أولاً» (القسم 03) ----------
     المفتاح يقلب جملة المثال ولغتها واتجاهها — ما يفعله مبدّل اللغة في
     المنصة نفسها، مصغّراً. لا يمسّ لغة الصفحة. */
  function initLangProof() {
    var box = document.querySelector("[data-fx-lang]");
    if (!box) return;
    var btn = box.querySelector(".fx-lang-toggle");
    var line = box.querySelector(".fx-lang-line");
    btn.addEventListener("click", function () {
      var en = btn.getAttribute("aria-pressed") !== "true";
      btn.setAttribute("aria-pressed", en ? "true" : "false");
      line.classList.remove("is-flip"); void line.offsetWidth; line.classList.add("is-flip");
      line.textContent = en ? line.dataset.en : line.dataset.ar;
      line.setAttribute("dir", en ? "ltr" : "rtl");
    });
  }

  /* ---------- مستكشف الأدوار (القسم 05) ----------
     تبويب لكل دور يبدّل لوحته، ويُضيء عقدته على خطّ «كيف تتصل الأدوار».

     جولة تلقائية: حين يُرى القسم تمرّ الأدوار الثلاثة كقصة — خطّ التبويب
     النشط شريط تقدّم، ووصلة التسليم إلى الدور التالي تتحرّك. تتجمّد عند
     المرور أو التركيز داخل القسم، وتتوقّف نهائياً حين يأخذ الزائر القيادة
     (نقر، أسهم، روابط الفوتر). زرّ إيقاف/تشغيل دائماً، ولا تشغيل تلقائي مع
     «تقليل الحركة». المدة في CSS (--rx-dur)، والانتقال على animationend
     فيبقى الإيقاف والشريط متزامنين. بلا هذا السكربت تظهر اللوحات متتالية. */
  function initRoles() {
    var root = document.querySelector("[data-role-explorer]");
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    var panels = root.querySelectorAll("[data-panel]");
    var nodes = root.querySelectorAll("[data-node]");
    var edges = root.querySelectorAll("[data-edge]");
    var toggle = root.querySelector("[data-rx-tour]");
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    var current = tabs[0].dataset.role;
    // stopped: أوقفها الزائر — لا تعود إلا بزرّ التشغيل
    var tour = { on: !reduce, stopped: reduce, visible: false, hover: false };
    root.classList.add("is-enhanced");

    function show(role, focus) {
      current = role;
      tabs.forEach(function (t) {
        var on = t.dataset.role === role;
        t.setAttribute("aria-selected", on ? "true" : "false");
        t.tabIndex = on ? 0 : -1;
        if (on && focus) t.focus();
      });
      panels.forEach(function (p) {
        var on = p.dataset.panel === role;
        p.hidden = !on;
        // إعادة تشغيل حركة الدخول عند كل تبديل
        if (on) { p.classList.remove("is-in"); void p.offsetWidth; p.classList.add("is-in"); }
      });
      nodes.forEach(function (n) { n.classList.toggle("is-active", n.dataset.node === role); });
      edges.forEach(function (e) { e.classList.toggle("is-flowing", e.dataset.edge === role); });
      restartProgress();
    }

    // شريط التقدّم حركة CSS على ::after — تُعاد من الصفر عند كل دور
    function restartProgress() {
      root.classList.remove("is-playing");
      void root.offsetWidth;
      sync();
    }

    function sync() {
      var running = tour.on && !tour.stopped;
      root.classList.toggle("is-playing", running);
      root.classList.toggle("is-paused", running && (!tour.visible || tour.hover || document.hidden));
      if (toggle) {
        var label = running ? t("roles.tour.pause", "إيقاف الجولة") : t("roles.tour.play", "تشغيل الجولة");
        toggle.querySelector("[data-rx-tour-label]").textContent = label;
        toggle.setAttribute("aria-pressed", running ? "true" : "false");
        toggle.classList.toggle("is-off", !running);
      }
    }

    function t(key, fallback) {
      var d = dict[locale];
      return (d && d[key]) || fallback;
    }

    function next() {
      var i = tabs.findIndex(function (x) { return x.dataset.role === current; });
      show(tabs[(i + 1) % tabs.length].dataset.role);
    }

    // الزائر أخذ القيادة: تتوقّف الجولة في هذه الزيارة
    function takeOver() {
      tour.stopped = true;
      sync();
    }

    tabs.forEach(function (tab, i) {
      // الشريط اكتمل: الدور التالي
      tab.addEventListener("animationend", function (e) {
        if (e.animationName === "rx-progress" && tab.dataset.role === current && tour.on && !tour.stopped) next();
      });
      tab.addEventListener("click", function () { takeOver(); show(tab.dataset.role); });
      tab.addEventListener("keydown", function (e) {
        // الاتجاه البصري يتبع لغة الصفحة: في RTL «اليسار» هو التالي
        var rtl = document.documentElement.dir === "rtl";
        var step = { ArrowLeft: rtl ? 1 : -1, ArrowRight: rtl ? -1 : 1, Home: -i, End: tabs.length - 1 - i }[e.key];
        if (step === undefined) return;
        e.preventDefault();
        takeOver();
        show(tabs[(i + step + tabs.length) % tabs.length].dataset.role, true);
      });
    });

    // النقر على عقدة في الخطّ يفتح دورها أيضاً
    nodes.forEach(function (n) {
      n.addEventListener("click", function () { takeOver(); show(n.dataset.node); });
    });

    // روابط الفوتر «الطالب / المشرف / الإدارة» تفتح تبويب دورها قبل النزول
    document.querySelectorAll("[data-role-link]").forEach(function (a) {
      a.addEventListener("click", function () { takeOver(); show(a.dataset.roleLink); });
    });

    if (toggle) {
      toggle.hidden = false;
      toggle.addEventListener("click", function () {
        var running = tour.on && !tour.stopped;
        tour.on = true;
        tour.stopped = running;
        // تشغيل يدوي: من الدور الحالي من أوله
        if (!running) restartProgress(); else sync();
      });
    }

    // تتجمّد عند المرور أو التركيز داخل القسم (لا عند زرّ الجولة نفسه)
    // الفأرة وحدها: لمسة على الجوال تُطلق mouseenter وتبقى «فوقه» فتتجمّد الجولة بلا سبب
    root.addEventListener("pointerenter", function (e) { if (e.pointerType === "mouse") { tour.hover = true; sync(); } });
    root.addEventListener("pointerleave", function (e) { if (e.pointerType === "mouse") { tour.hover = false; sync(); } });
    root.addEventListener("focusin", function (e) { if (e.target !== toggle) { tour.hover = true; sync(); } });
    root.addEventListener("focusout", function () { tour.hover = false; sync(); });
    document.addEventListener("visibilitychange", sync);

    // تبدأ حين يُرى القسم، وتنتظر حين يخرج من الشاشة
    if ("IntersectionObserver" in window) {
      // «مرئي»: 40% منه، أو نصف الشاشة حين يكون أطول منها (الجوال)
      new IntersectionObserver(function (entries) {
        var e = entries[0];
        tour.visible = e.isIntersecting && (e.intersectionRatio >= 0.4 || e.intersectionRect.height >= window.innerHeight * 0.5);
        sync();
      }, { threshold: [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6] }).observe(root);
    } else {
      tour.visible = true;
    }

    // اللوحات بارتفاع أطولها: التبديل التلقائي لا يُزيح ما تحت القسم
    // أثناء القراءة (على الجوال تختلف أطوالها كثيراً)
    function equalize() {
      var tallest = 0;
      panels.forEach(function (p) {
        var wasHidden = p.hidden;
        p.style.minHeight = "";
        p.hidden = false;
        tallest = Math.max(tallest, p.offsetHeight);
        p.hidden = wasHidden;
      });
      panels.forEach(function (p) { p.style.minHeight = tallest + "px"; });
    }
    var resizeTimer;
    window.addEventListener("resize", function () { clearTimeout(resizeTimer); resizeTimer = setTimeout(equalize, 150); });
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(equalize);

    show(tabs[0].dataset.role);
    equalize();
  }

  /* ---------- الأسئلة الشائعة (القسم 08) ----------
     الأسئلة <details> أصلية تعمل بلا سكربت. هنا: حركة فتح وإغلاق ناعمة،
     سؤال واحد مفتوح في كل مرة، فلاتر الفئات، والرابط المباشر #faq-N. */
  function initFaq() {
    var items = Array.prototype.slice.call(document.querySelectorAll(".fq-item"));
    if (!items.length) return;
    var reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    // السكربت يتولّى «واحد مفتوح» بنفسه كي يتحرّك الإغلاق أيضاً
    items.forEach(function (d) { d.removeAttribute("name"); });

    function toggle(d, open) {
      var body = d.querySelector(".fq-body");
      if (d._anim) { d._anim.cancel(); d._anim = null; }
      if (reduce || !body.animate) { d.open = open; return; }
      if (open) {
        d.open = true;
        d._anim = body.animate(
          [{ height: "0px", opacity: 0 }, { height: body.scrollHeight + "px", opacity: 1 }],
          { duration: 340, easing: "cubic-bezier(.16, 1, .3, 1)" }
        );
      } else {
        d.classList.add("is-closing");
        d._anim = body.animate(
          [{ height: body.scrollHeight + "px", opacity: 1 }, { height: "0px", opacity: 0 }],
          { duration: 240, easing: "ease" }
        );
        d._anim.onfinish = function () { d.open = false; d.classList.remove("is-closing"); d._anim = null; };
      }
    }

    function openOnly(d) {
      items.forEach(function (o) { if (o !== d && o.open) toggle(o, false); });
      if (!d.open) toggle(d, true);
    }

    items.forEach(function (d) {
      d.querySelector("summary").addEventListener("click", function (e) {
        e.preventDefault();
        if (d.open && !d.classList.contains("is-closing")) toggle(d, false);
        else openOnly(d);
      });
    });

    // الفلاتر: تظهر فقط حين يعمل السكربت
    var bar = document.querySelector("[data-fq-filters]");
    if (bar) {
      bar.hidden = false;
      var buttons = bar.querySelectorAll("[data-fq-filter]");
      buttons.forEach(function (btn) {
        btn.addEventListener("click", function () {
          var cat = btn.dataset.fqFilter;
          buttons.forEach(function (b) { b.setAttribute("aria-pressed", b === btn ? "true" : "false"); });
          var shown = items.filter(function (d) {
            var on = cat === "all" || d.dataset.cat === cat;
            d.hidden = !on;
            return on;
          });
          // يبقى جواب ظاهر مفتوحاً: إن اختفى المفتوح يُفتح أول الظاهرين
          if (!shown.some(function (d) { return d.open; }) && shown[0]) openOnly(shown[0]);
        });
      });
    }

    // رابط مباشر لسؤال: #faq-3 يفتحه
    function fromHash() {
      var m = /^#faq-(\d+)$/.exec(location.hash);
      var d = m && document.getElementById("faq-" + m[1]);
      if (!d) return;
      d.hidden = false;
      openOnly(d);
    }
    fromHash();
    window.addEventListener("hashchange", fromHash);
  }

  /* ---------- نموذج التواصل: إرسال في الخلفية ----------
     كان يُرسل فتُعاد الصفحة من رأسها — إلى الهيرو — فلا يرى المرسل أن
     رسالته وصلت. الآن يبقى في قسمه: الأخطاء تحت حقولها، والتأكيد مكان
     النموذج. وبلا JavaScript يعمل النموذج كما كان ويعود الخادم إلى #contact. */
  function initContact() {
    var form = document.querySelector("[data-contact-form]");
    var done = document.querySelector("[data-contact-done]");
    if (!form || !done || !window.fetch || !window.FormData) return;

    var t = function (key) { return (dict[locale] && dict[locale][key]) || dict.ar[key] || ""; };
    var submit = form.querySelector("[data-submit]");
    var formError = form.querySelector("[data-form-error]");
    var message = form.querySelector('[name="message"]');
    var subject = form.querySelector('[name="subject"]');
    var counter = form.querySelector('[data-count-for="message"]');

    function count() { if (counter) counter.textContent = message.value.length; }
    message.addEventListener("input", count);
    count();

    // مواضيع شائعة: النقر يملأ الموضوع بنصّ الزرّ بلغة الصفحة الحالية
    form.querySelectorAll(".subject-chips button").forEach(function (chip) {
      chip.addEventListener("click", function () {
        subject.value = chip.textContent.trim();
        form.querySelectorAll(".subject-chips button").forEach(function (c) {
          c.setAttribute("aria-pressed", c === chip ? "true" : "false");
        });
        clearError("subject");
        message.focus();
      });
    });

    function fieldError(name, text) {
      var input = form.querySelector('[name="' + name + '"]');
      var slot = form.querySelector('[data-error-for="' + name + '"]');
      if (input) { input.classList.add("is-invalid"); input.setAttribute("aria-invalid", "true"); }
      if (slot) slot.textContent = text;
    }
    function clearError(name) {
      var input = form.querySelector('[name="' + name + '"]');
      var slot = form.querySelector('[data-error-for="' + name + '"]');
      if (input) { input.classList.remove("is-invalid"); input.removeAttribute("aria-invalid"); }
      if (slot) slot.textContent = "";
    }
    ["name", "email", "subject", "message"].forEach(function (n) {
      var input = form.querySelector('[name="' + n + '"]');
      if (input) input.addEventListener("input", function () { clearError(n); });
    });

    // تحقّق أوّلي في المتصفّح بنصوص المنصّة لا فقاعات المتصفّح — والخادم يبقى الحَكَم
    function validate() {
      var ok = true, first = null;
      ["name", "email", "subject", "message"].forEach(function (n) {
        var input = form.querySelector('[name="' + n + '"]');
        var v = input.value.trim();
        var err = !v ? t("contact.errRequired")
          : (n === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) ? t("contact.errEmail") : "";
        if (err) { fieldError(n, err); ok = false; first = first || input; }
      });
      if (first) first.focus();
      return ok;
    }

    function busy(on) {
      form.classList.toggle("is-sending", on);
      submit.disabled = on;
      submit.setAttribute("aria-busy", on ? "true" : "false");
    }
    function showFormError(text) {
      formError.querySelector("span").textContent = text;
      formError.hidden = false;
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      formError.hidden = true;
      form.querySelectorAll(".form-alert:not([data-form-error])").forEach(function (el) { el.remove(); });
      if (!validate()) return;

      busy(true);
      fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
        credentials: "same-origin"
      }).then(function (res) {
        if (res.ok) return success();
        if (res.status === 422) {
          return res.json().then(function (body) {
            var first = null;
            Object.keys(body.errors || {}).forEach(function (n) {
              fieldError(n, body.errors[n][0]);
              first = first || form.querySelector('[name="' + n + '"]');
            });
            if (first) first.focus();
          });
        }
        showFormError(res.status === 429 ? t("contact.errThrottle") : t("contact.errServer"));
      }).catch(function () {
        showFormError(t("contact.errNetwork"));
      }).then(function () { busy(false); });
    });

    function success() {
      done.querySelector("[data-done-email]").textContent = form.querySelector('[name="email"]').value;
      // ارتفاع ثابت أثناء التبديل: لا قفزة في الصفحة حين يصغر المحتوى
      var box = form.parentElement;
      box.style.minHeight = box.offsetHeight + "px";
      form.hidden = true;
      done.hidden = false;
      done.focus({ preventScroll: true });
    }

    done.querySelector("[data-contact-again]").addEventListener("click", function () {
      subject.value = "";
      message.value = "";
      count();
      form.querySelectorAll(".subject-chips button").forEach(function (c) { c.setAttribute("aria-pressed", "false"); });
      done.hidden = true;
      form.hidden = false;
      form.parentElement.style.minHeight = "";
      subject.focus({ preventScroll: true });
    });
  }

  /* ---------- بنتو الهيرو: قصّة واحدة في حلقة ----------
     الفريق يكتب في نقاشه ← يُسلّم المرحلة ← المشرف يعتمدها فيرتفع الإنجاز ←
     الأدوار تتوزّع ← الدرجة تُرصد. خطوات بمؤقّتات لا حلقة إطارات دائمة:
     تعمل حين تكون الشبكة ظاهرة وحدها، وتتوقّف بالمرور وبإخفاء التبويب.
     ولمن أوقف الحركة: الحالة الأخيرة ثابتة بلا مؤقّت واحد. */
  function initBento() {
    var root = document.querySelector("[data-bento]");
    if (!root) return;

    var ring = root.querySelector("[data-ring]");
    var ringNum = root.querySelector("[data-ring-num]");
    var ringDone = root.querySelector("[data-ring-done]");
    var steps = root.querySelectorAll("[data-steps] li");
    var typed = root.querySelector("[data-typed]");
    var roles = root.querySelectorAll("[data-roles] li");
    var grade = root.querySelector("[data-grade]");
    var chat = root.querySelector(".is-chat");

    function src() { return (root.querySelector("[data-type-src]") || {}).textContent || ""; }
    function at() { return (root.querySelector("[data-type-at]") || {}).textContent || ""; }

    // الذكر يُبرز حين تكتمل الرسالة — النصّ يُهرَّب، والوسم وحده HTML
    function withMention(text) {
      var tag = at(), esc = function (t) { var d = document.createElement("div"); d.textContent = t; return d.innerHTML; };
      var i = tag ? text.indexOf(tag) : -1;
      return i === -1 ? esc(text) : esc(text.slice(0, i)) + '<span class="mention">' + esc(tag) + "</span>" + esc(text.slice(i + tag.length));
    }

    function setStep(n) { steps.forEach(function (li, i) { li.classList.toggle("is-on", i <= n); li.classList.toggle("is-now", i === n); }); }
    function setRing(p, done) {
      ring.style.setProperty("--p", p);
      ringNum.textContent = p;
      ringDone.textContent = done;
    }
    function tween(el, from, to, ms, fmt) {
      var t0 = performance.now();
      (function tick(t) {
        var k = Math.min(1, (t - t0) / ms), e = 1 - Math.pow(1 - k, 3);
        el.textContent = fmt(Math.round(from + (to - from) * e));
        if (k < 1) requestAnimationFrame(tick);
      })(t0);
    }

    function finalState() {
      setStep(2); setRing(60, 3);
      typed.innerHTML = withMention(src());
      chat.classList.add("is-sent");
      roles.forEach(function (li) { li.classList.add("is-in"); });
      grade.textContent = "96";
      root.classList.add("is-graded");
    }

    if (reduceMotion) { finalState(); return; }

    function reset() {
      setStep(0); setRing(40, 2);
      typed.textContent = "";
      chat.classList.remove("is-sent");
      roles.forEach(function (li) { li.classList.remove("is-in"); });
      grade.textContent = "—";
      root.classList.remove("is-graded");
    }

    // الخطوات: [تأخير قبلها بالملّي ثانية، الفعل]
    var script = [
      [500, function () { chat.classList.add("is-typing"); }],
      [0, function (next) {
        var text = src(), i = 0;
        (function type() {
          if (!running) return (resume = type);
          typed.textContent = text.slice(0, ++i);
          if (i < text.length) setTimeout(type, 42); else next();
        })();
        return true; // الخطوة تستدعي التالية بنفسها
      }],
      [250, function () { typed.innerHTML = withMention(src()); chat.classList.remove("is-typing"); chat.classList.add("is-sent"); }],
      [900, function () { setStep(1); }],
      [1300, function () { setStep(2); setRing(60, 3); tween(ringNum, 40, 60, 900, String); }],
      [900, function () { roles[0] && roles[0].classList.add("is-in"); }],
      [220, function () { roles[1] && roles[1].classList.add("is-in"); }],
      [220, function () { roles[2] && roles[2].classList.add("is-in"); }],
      [700, function () { root.classList.add("is-graded"); tween(grade, 0, 96, 1000, String); }],
      [3400, function () { root.classList.add("is-resetting"); }],
      [450, function () { reset(); root.classList.remove("is-resetting"); }],
    ];

    var i = 0, timer = 0, running = false, visible = false, hovered = false, resume = null;

    function next() {
      if (!running) return;
      var step = script[i];
      timer = setTimeout(function () {
        timer = 0;
        // توقّف قبل الخطوة: العدّاد لم يتقدّم بعد، فالاستئناف يعيدها هي نفسها
        if (!running) { resume = next; return; }
        var self = step[1](function () { i = (i + 1) % script.length; next(); });
        if (self !== true) { i = (i + 1) % script.length; next(); }
      }, step[0]);
    }

    function sync() {
      var should = visible && !hovered && !document.hidden;
      if (should === running) return;
      running = should;
      if (running) {
        if (resume) { var r = resume; resume = null; r(); }
        else if (!timer) next();
      }
    }

    reset();

    // تبديل اللغة يغيّر نصّ المصدر: الرسالة المكتملة تُعاد بلغتها فوراً لا في الدورة التالية
    var srcEl = root.querySelector("[data-type-src]");
    if (srcEl && "MutationObserver" in window) {
      new MutationObserver(function () {
        if (chat.classList.contains("is-sent")) typed.innerHTML = withMention(src());
      }).observe(srcEl, { childList: true, characterData: true, subtree: true });
    }

    root.addEventListener("mouseenter", function () { hovered = true; root.classList.add("is-paused"); sync(); });
    root.addEventListener("mouseleave", function () { hovered = false; root.classList.remove("is-paused"); sync(); });
    document.addEventListener("visibilitychange", sync);

    if ("IntersectionObserver" in window) {
      new IntersectionObserver(function (entries) {
        visible = entries[0].isIntersecting;
        sync();
      }, { threshold: 0.25 }).observe(root);
    } else {
      visible = true;
      sync();
    }
  }
})();
