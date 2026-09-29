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
      "bento.aria": "لمحة حيّة من المنصة",
      "bento.progress": "نسبة الإنجاز",
      "bento.project": "كشف الرسائل الاحتيالية",
      "bento.of": "من 5 مراحل",
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
      "svc.1.title": "فريقك ومشرفك في دقائق",
      "svc.1.text": "اختر زملاءك من المتاحين في تخصصك، وشاهد المقاعد المتبقية لكل مشرف — وتنبّهك المنصة إن كانت فكرتك نُفّذت من قبل.",
      "svc.1.seats": "مقاعد متبقية لدى المشرف",
      "svc.1.similar": "فكرة مشابهة نُفّذت في 2023 — راجعها قبل التقديم",
      "svc.2.title": "سلّم مرحلتك، واستلم ملاحظة لا رفضاً",
      "svc.2.text": "تسلّم كل مرحلة بملف وملاحظة، ويعتمدها مشرفك أو يطلب تعديلاً بسببه الواضح — وكل جولة محفوظة.",
      "svc.2.s1": "سُلّمت",
      "svc.2.s2": "مطلوب تعديل",
      "svc.2.s3": "اعتُمدت",
      "svc.2.noteT": "ملاحظة المشرف",
      "svc.2.note": "ينقص مخطط الكيانات والعلاقات في الفصل الثالث.",
      "svc.3.title": "خطة مراحل بقوالبها",
      "svc.3.text": "يضعها المشرف مرّة بمواعيدها وقوالبها، فتصل كل مجموعاته.",
      "svc.4.title": "نقاش خاص بالفريق",
      "svc.4.text": "قناة لا يراها المشرف، و@ لتنبيه زميل بعينه.",
      "svc.5.title": "ملاحظات على الملفات",
      "svc.5.text": "«صفحة ٣ ينقصها المرجع» — على الملف نفسه، حتى تُعالَج.",
      "svc.6.title": "توزيع الأدوار",
      "svc.6.text": "مَن على الواجهات ومَن على الخادم — يراه الفريق والمشرف.",
      "svc.7.title": "مستكشف المشاريع السابقة",
      "svc.7.text": "تصفّح مشاريع الدفعات السابقة بأنواعها ومشرفيها، واستلهم فكرتك.",
      "svc.8.title": "درجة معتمدة لا تتغيّر",
      "svc.8.text": "يرصد المشرف درجتك بالتقدير وملاحظاته، وتُقفل بعد اعتمادها.",
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
      "how.4.text": "بعد المناقشة يرصد مشرفك درجتك بالتقدير، وتُعتمد فلا تتغيّر.",
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
      "dept.5.title": "سجلّ تدقيق لكل قرار",
      "dept.5.text": "كل اعتماد وفتح درجة وتغيير مهم يُحفظ باسم صاحبه ووقته.",
      "dept.6.title": "فصول دراسية تُفتح وتُغلق",
      "dept.6.text": "تفتح فصلاً جديداً للتقديم وتغلق السابق، فتبقى مشاريع كل دفعة في فصلها.",
      "faq.kicker": "الأسئلة الشائعة",
      "faq.title": "كل ما يسأله الطلاب قبل البدء",
      "faq.text": "إجابات مباشرة من واقع النظام — ولأي سؤال آخر تواصل معنا من قسم الاتصال بالأسفل.",
      "faq.1.title": "كم عضواً يتكون منه الفريق؟",
      "faq.1.text": "حسب نوع المشروع الذي يحدده قسمك — كل نوع له حد أدنى وأقصى يظهران أمامك في نموذج التقديم، والنظام لا يقبل فريقاً خارج الحدود.",
      "faq.2.title": "كيف أقدم طلب مشروع؟",
      "faq.2.text": "سجّل دخولك ← اختر نوع المشروع ومشرفاً لديه مقاعد متاحة ← اختر أعضاء فريقك من القائمة ← اكتب العنوان والوصف وأرسل. سيصل طلبك للمشرف فوراً.",
      "faq.3.title": "كيف أعرف ردّ المشرف، وماذا لو رُفض طلبي؟",
      "faq.3.text": "يصلك إشعار فور القبول أو الرفض. وإن رُفض فمعه السبب الذي كتبه المشرف، ويُفتح لك نموذج تقديم جديد مباشرة — عدّل فكرتك أو اختر مشرفاً آخر.",
      "faq.4.title": "ماذا يعني «مطلوب تعديل» على مرحلة سلّمتها؟",
      "faq.4.text": "أن مشرفك راجعها وكتب ما ينقصها — لا أنها رُفضت. عدّل ملفك وأعد التسليم من الصفحة نفسها، وتبقى كل جولة وملاحظتها محفوظة.",
      "faq.5.title": "من يوزّع الأدوار في الفريق؟",
      "faq.5.text": "قائد الفريق: يسند لكل عضو دوره ومسؤولياته، فيراها الفريق كله ويراها المشرف — واضح من على ماذا قبل المناقشة.",
      "faq.6.title": "هل يرى المشرف نقاش الفريق؟",
      "faq.6.text": "لا. للفريق قناة خاصة لا يراها المشرف ولا الإدارة، وقناة ثانية مشتركة مع المشرف للأسئلة والملاحظات.",
      "faq.7.title": "كيف يُقيَّم مشروعي النهائي؟",
      "faq.7.text": "بعد المناقشة يرصد مشرفك الدرجة من 100 مع التقدير وملاحظاته، ويصل الإشعار للفريق كله. وبعد اعتمادها تُقفل — لا يفتحها إلا الإدارة، ويُسجَّل ذلك.",
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
      "lc.title": "ما يتغيّر حين يجتمع مشروعك في مكان واحد",
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
      "contact.pointMail": "الرد خلال يوم عمل واحد",
      "contact.pointHours": "من الأحد إلى الخميس",
      "contact.faqTitle": "ربما الجواب جاهز",
      "contact.faqText": "سبعة أسئلة يسألها كل فريق قبل البدء",
      "contact.topics": "مواضيع شائعة",
      "contact.topic1": "مشكلة في الدخول",
      "contact.topic2": "سؤال عن المواعيد",
      "contact.topic3": "اقتراح للمنصة",
      "contact.hint": "نردّ على بريدك مباشرة",
      "contact.doneTitle": "وصلت رسالتك",
      "contact.doneText": "سنردّ عليك خلال يوم عمل على",
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
      "bento.aria": "A live glimpse of the platform",
      "bento.progress": "Progress",
      "bento.project": "Fraud message detection",
      "bento.of": "of 5 stages",
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
      "svc.1.title": "Your team and supervisor in minutes",
      "svc.1.text": "Pick teammates from your major, see each supervisor's open seats — and get warned if your idea has been done before.",
      "svc.1.seats": "seats left with this supervisor",
      "svc.1.similar": "A similar idea was done in 2023 — review it first",
      "svc.2.title": "Submit a stage, get feedback — not a rejection",
      "svc.2.text": "Submit each stage with a file and a note; your supervisor approves it or asks for changes with a clear reason. Every round is kept.",
      "svc.2.s1": "Submitted",
      "svc.2.s2": "Changes requested",
      "svc.2.s3": "Approved",
      "svc.2.noteT": "Supervisor's note",
      "svc.2.note": "The ER diagram is missing from chapter 3.",
      "svc.3.title": "A stage plan with templates",
      "svc.3.text": "Your supervisor sets it once, with dates and templates, for all their groups.",
      "svc.4.title": "A private team chat",
      "svc.4.text": "A channel your supervisor can't see, with @ to ping a teammate.",
      "svc.5.title": "Notes on files",
      "svc.5.text": "“Page 3 is missing a reference” — right on the file, until it's fixed.",
      "svc.6.title": "Team roles",
      "svc.6.text": "Who owns the frontend, who owns the backend — visible to the team and supervisor.",
      "svc.7.title": "Past projects explorer",
      "svc.7.text": "Browse previous cohorts' projects by type and supervisor, and find your idea.",
      "svc.8.title": "A grade that stays final",
      "svc.8.text": "Your supervisor records the grade with a rating and notes, and it locks once approved.",
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
      "dept.5.title": "An Audit Log for Every Decision",
      "dept.5.text": "Every approval, grade unlock and key change is kept with who did it and when.",
      "dept.6.title": "Semesters You Open and Close",
      "dept.6.text": "Open a new semester for submissions and close the last, so each cohort stays in its own term.",
      "faq.kicker": "FAQ",
      "faq.title": "Everything Students Ask Before Starting",
      "faq.text": "Straight answers from how the system actually works — for anything else, reach us in the contact section below.",
      "faq.1.title": "How Many Members per Team?",
      "faq.1.text": "It depends on the project type set by your department — each type has a min and max shown in the submission form, and the system enforces them.",
      "faq.2.title": "How Do I Submit a Project Request?",
      "faq.2.text": "Sign in → pick a project type and a supervisor with open seats → select your teammates from the list → write the title and description and send. Your supervisor is notified instantly.",
      "faq.3.title": "How Do I Hear Back — and What If I'm Rejected?",
      "faq.3.text": "You're notified the moment your request is accepted or rejected. A rejection comes with the supervisor's reason, and a new submission form opens right away — refine your idea or pick another supervisor.",
      "faq.4.title": "What Does “Changes Requested” Mean?",
      "faq.4.text": "That your supervisor reviewed the stage and wrote what's missing — not that it was rejected. Fix your file and resubmit from the same page; every round and its note are kept.",
      "faq.5.title": "Who Assigns Roles in the Team?",
      "faq.5.text": "The team leader: they give each member a role and responsibilities, visible to the whole team and the supervisor — clear who owns what before the defense.",
      "faq.6.title": "Can the Supervisor See the Team Chat?",
      "faq.6.text": "No. The team has a private channel hidden from supervisors and admins, plus a second channel shared with the supervisor for questions and feedback.",
      "faq.7.title": "How Is My Final Project Graded?",
      "faq.7.text": "After the defense your supervisor records a grade out of 100 with a rating and notes, and the whole team is notified. Once approved it locks — only admins can reopen it, and that is logged.",
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
      "lc.title": "What Changes When Your Project Lives in One Place",
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
      "contact.pointMail": "A reply within one business day",
      "contact.pointHours": "Sunday to Thursday",
      "contact.faqTitle": "The answer may be ready",
      "contact.faqText": "Seven questions every team asks before starting",
      "contact.topics": "Common topics",
      "contact.topic1": "Sign-in problem",
      "contact.topic2": "Question about deadlines",
      "contact.topic3": "Suggestion",
      "contact.hint": "We reply straight to your email",
      "contact.doneTitle": "Message received",
      "contact.doneText": "We'll reply within one business day at",
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

    initBento();
    initContact();
    initRoles();
    initLangProof();
  });

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
     الأسهم تتنقّل بين التبويبات، ولا دوران تلقائي: القارئ يختار. بلا هذا
     السكربت تظهر اللوحات الثلاث متتالية. */
  function initRoles() {
    var root = document.querySelector("[data-role-explorer]");
    if (!root) return;
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    var panels = root.querySelectorAll("[data-panel]");
    var nodes = root.querySelectorAll("[data-node]");
    root.classList.add("is-enhanced");

    function show(role, focus) {
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
    }

    tabs.forEach(function (t, i) {
      t.addEventListener("click", function () { show(t.dataset.role); });
      t.addEventListener("keydown", function (e) {
        // الاتجاه البصري يتبع لغة الصفحة: في RTL «اليسار» هو التالي
        var rtl = document.documentElement.dir === "rtl";
        var next = { ArrowLeft: rtl ? 1 : -1, ArrowRight: rtl ? -1 : 1, Home: -i, End: tabs.length - 1 - i }[e.key];
        if (next === undefined) return;
        e.preventDefault();
        show(tabs[(i + next + tabs.length) % tabs.length].dataset.role, true);
      });
    });

    // النقر على عقدة في الخطّ يفتح دورها أيضاً
    nodes.forEach(function (n) {
      n.addEventListener("click", function () { show(n.dataset.node); });
    });

    show(tabs[0].dataset.role);
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
      grade.textContent = "0";
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
