{{-- استيراد المشرفين — انظر \u200Ex-import-modal\u200E. الأعمدة وشروطها من \u200ESupervisorsImport::rules()\u200E --}}
<x-import-modal noun="المشرفين" :action="route('admin.supervisors.import')" :template="route('admin.supervisors.template')"
    :specializes="$specializes" :columns="[
        ['name', true, 'اسم المشرف، حتى 70 حرفاً'],
        ['university_id', true, '9 أرقام، ولا يتكرّر'],
        ['email', true, 'بريد صحيح لا يتكرّر في المنصة ولا في الملف'],
        ['phone', false, '10 أرقام — والصفر الذي يُسقطه Excel يُعاد تلقائياً'],
        ['specialization', true, 'اسم التخصص كما هو في المنصة (القائمة أدناه)'],
        ['gender', false, 'male أو female — والفارغ يُعدّ male'],
        ['max_group', false, 'عدد المجموعات التي يقبلها (1 أو أكثر)'],
        ['password', false, '8 أحرف على الأقل — والفارغة تصير كلمة سر عشوائية'],
    ]" />
