{{-- استيراد الطلاب — انظر \u200Ex-import-modal\u200E. الأعمدة وشروطها من \u200EStudentsImport::rules()\u200E --}}
<x-import-modal noun="الطلاب" :action="route('admin.students.import')" :template="route('admin.students.template')"
    :specializes="$specializes" :columns="[
        ['name', true, 'اسم الطالب، حتى 70 حرفاً'],
        ['university_id', true, '10 أرقام تبدأ بـ 130 أو 230، ولا يتكرّر'],
        ['email', true, 'بريد صحيح لا يتكرّر في المنصة ولا في الملف'],
        ['phone', false, '10 أرقام — والصفر الذي يُسقطه Excel يُعاد تلقائياً'],
        ['specialization', true, 'اسم التخصص كما هو في المنصة (القائمة أدناه)'],
        ['gender', false, 'male أو female — والفارغ يُعدّ male'],
        ['password', false, '8 أحرف على الأقل — والفارغة تصير كلمة سر عشوائية'],
    ]" />
