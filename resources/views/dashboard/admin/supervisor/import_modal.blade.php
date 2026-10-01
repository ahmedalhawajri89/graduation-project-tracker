{{-- استيراد المشرفين — انظر \u200Ex-import-modal\u200E. الأعمدة وشروطها من \u200ESupervisorsImport::rules()\u200E --}}
<x-import-modal :noun="__('المشرفين')" :action="route('admin.supervisors.import')" :template="route('admin.supervisors.template')"
    :specializes="$specializes" :columns="[
        ['name', true, __('اسم المشرف، حتى 70 حرفاً')],
        ['university_id', true, __('9 أرقام، ولا يتكرّر')],
        ['email', true, __('بريد صحيح لا يتكرّر في المنصة ولا في الملف')],
        ['phone', false, __('10 أرقام — والصفر الذي يُسقطه Excel يُعاد تلقائياً')],
        ['specialization', true, __('اسم التخصص كما هو في المنصة (القائمة أدناه)')],
        ['gender', false, __('male أو female — والفارغ يُعدّ male')],
        ['max_group', false, __('عدد المجموعات التي يقبلها (1 أو أكثر)')],
        ['password', false, __('8 أحرف على الأقل — والفارغة تصير كلمة سر عشوائية')],
    ]" />
