{{-- استيراد الطلاب — انظر \u200Ex-import-modal\u200E. الأعمدة وشروطها من \u200EStudentsImport::rules()\u200E --}}
<x-import-modal :noun="__('الطلاب')" :action="route('admin.students.import')" :template="route('admin.students.template')"
    :specializes="$specializes" :columns="[
        ['name', true, __('اسم الطالب، حتى 70 حرفاً')],
        ['university_id', true, __('10 أرقام تبدأ بـ 130 أو 230، ولا يتكرّر')],
        ['email', true, __('بريد صحيح لا يتكرّر في المنصة ولا في الملف')],
        ['phone', false, __('10 أرقام — والصفر الذي يُسقطه Excel يُعاد تلقائياً')],
        ['specialization', true, __('اسم التخصص كما هو في المنصة (القائمة أدناه)')],
        ['gender', false, __('male أو female — والفارغ يُعدّ male')],
        ['password', false, __('8 أحرف على الأقل — والفارغة تصير كلمة سر عشوائية')],
    ]" />
