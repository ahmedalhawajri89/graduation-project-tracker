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
        'request'  => ['badge' => 'bg-yellow-lt',   'icon' => 'ti-clock',                   'hex' => '#f59e0b'],
        'accept'   => ['badge' => 'bg-green-lt',     'icon' => 'ti-circle-check',            'hex' => '#10b981'],
        'complete' => ['badge' => 'bg-blue-lt',      'icon' => 'ti-rosette-discount-check',  'hex' => '#0ea5e9'],
        'reject'   => ['badge' => 'bg-red-lt',       'icon' => 'ti-circle-x',                'hex' => '#e11d48'],
    ],

    // القيمة الافتراضية لأي حالة غير معروفة
    'fallback' => ['badge' => 'bg-secondary-lt', 'icon' => 'ti-help-circle', 'hex' => '#868e96'],
];
