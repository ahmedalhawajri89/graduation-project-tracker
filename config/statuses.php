<?php

/*
|--------------------------------------------------------------------------
| مصدر واحد لحالات المشروع (Design System — single source of truth)
|--------------------------------------------------------------------------
| كل ما يخص حالة المشروع (لون الشارة، الأيقونة، اللون السداسي للرسوم)
| يُعرّف هنا مرّة واحدة، وتستهلكه المكوّنات والرسوم البيانية.
| المسمّى المعروض (label) يأتي من ملفات الترجمة: __('site.'.$status)
*/

return [

    // ترتيب العرض في الرسوم البيانية
    'order' => ['request', 'accept', 'complete', 'reject'],

    'map' => [
        // الألوان تتبع توكنز نظام التصميم الموحّد (css/dashboard.css)
        // hex للأسطح الفاتحة وهو المستعمل في كل الواجهة حالياً.
        // hex_dark محفوظ لأي سطح داكن مستقبلاً (لوح، وضع ليلي) فلا تُعاد
        // كتابة الألوان في مكان آخر ويبقى هذا الملف المصدر الواحد.
        'request'  => ['badge' => 'bg-yellow-lt', 'icon' => 'ti-clock',                  'hex' => '#b45309', 'hex_dark' => '#fbbf24'],
        'accept'   => ['badge' => 'bg-green-lt',  'icon' => 'ti-circle-check',           'hex' => '#047857', 'hex_dark' => '#34d399'],
        'complete' => ['badge' => 'bg-blue-lt',   'icon' => 'ti-rosette-discount-check', 'hex' => '#2563eb', 'hex_dark' => '#60a5fa'],
        'reject'   => ['badge' => 'bg-red-lt',    'icon' => 'ti-circle-x',               'hex' => '#be123c', 'hex_dark' => '#fb7185'],
    ],

    // القيمة الافتراضية لأي حالة غير معروفة
    'fallback' => ['badge' => 'bg-secondary-lt', 'icon' => 'ti-help-circle', 'hex' => '#868e96', 'hex_dark' => '#a1a1aa'],
];
