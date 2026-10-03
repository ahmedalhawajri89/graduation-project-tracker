<?php

/*
| وضع العرض التجريبي — للنسخة الحيّة في البورتفوليو وحدها.
|
| مفعّلاً: أزرار «جرّب كطالب / مشرف / مسؤول» في صفحة الدخول، وشريط تبديل
| الأدوار في اللوحة، وحماية من التخريب (DemoGuard)، وبريد إلى السجلّ،
| وإعادة البيانات من لقطة كل ليلة (demo:reset). مطفأً: لا شيء من هذا.
*/

return [
    'enabled' => (bool) env('DEMO_MODE', false),

    // أقصى حجم لملف مرفوع في النسخة التجريبية (ميغابايت) — لا يمتلئ الخادم
    'upload_mb' => (int) env('DEMO_UPLOAD_MB', 2),

    // حسابات الأزرار: فارغ = تُختار من البيانات (App\Support\Demo::account)
    'accounts' => [
        'student' => env('DEMO_STUDENT'),
        'supervisor' => env('DEMO_SUPERVISOR'),
        'admin' => env('DEMO_ADMIN'),
    ],

    // الإعادة الليلية من اللقطة
    'reset_at' => env('DEMO_RESET_AT', '03:00'),
];
