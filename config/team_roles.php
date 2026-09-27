<?php

/*
 * أدوار الفريق الجاهزة.
 *
 * «فرونت / باك / API» تناسب البرمجة وحدها، والمنصة فيها أبحاث وتدريب
 * عملي — فالمقترح يتبع نوع المشروع، والمشتركة للجميع، والدور الحرّ متاح.
 *
 * hue: لون ثابت للوسم (0–360)، هادئ التشبّع في العرض.
 * sets: كلمة في اسم نوع المشروع ← مفاتيح أدواره.
 */

return [
    'roles' => [
        // برمجة
        'frontend' => ['label' => 'واجهات أمامية', 'icon' => 'ti-layout', 'hue' => 217],
        'backend' => ['label' => 'الخادم', 'icon' => 'ti-server', 'hue' => 262],
        'api' => ['label' => 'API', 'icon' => 'ti-api', 'hue' => 190],
        'database' => ['label' => 'قاعدة البيانات', 'icon' => 'ti-database', 'hue' => 28],
        'mobile' => ['label' => 'تطبيق الجوال', 'icon' => 'ti-device-mobile', 'hue' => 200],
        'design' => ['label' => 'تصميم UI/UX', 'icon' => 'ti-palette', 'hue' => 330],
        'testing' => ['label' => 'الاختبار', 'icon' => 'ti-bug', 'hue' => 0],

        // ذكاء صناعي
        'data' => ['label' => 'البيانات وتجهيزها', 'icon' => 'ti-table', 'hue' => 40],
        'model' => ['label' => 'بناء النموذج', 'icon' => 'ti-brain', 'hue' => 280],
        'evaluation' => ['label' => 'تقييم النموذج', 'icon' => 'ti-chart-line', 'hue' => 160],
        'integration' => ['label' => 'التكامل والواجهة', 'icon' => 'ti-plug', 'hue' => 210],

        // أبحاث
        'literature' => ['label' => 'الدراسات السابقة', 'icon' => 'ti-books', 'hue' => 35],
        'methodology' => ['label' => 'المنهجية', 'icon' => 'ti-flask', 'hue' => 170],
        'analysis' => ['label' => 'تحليل النتائج', 'icon' => 'ti-chart-bar', 'hue' => 230],

        // تدريب عملي
        'fieldwork' => ['label' => 'التنفيذ الميداني', 'icon' => 'ti-tools', 'hue' => 20],
        'reports' => ['label' => 'التقارير', 'icon' => 'ti-report', 'hue' => 250],

        // مشتركة
        'docs' => ['label' => 'التوثيق والكتابة', 'icon' => 'ti-file-text', 'hue' => 220],
        'presentation' => ['label' => 'العرض التقديمي', 'icon' => 'ti-presentation', 'hue' => 300],
        'coordination' => ['label' => 'التنسيق والمتابعة', 'icon' => 'ti-users', 'hue' => 145],
    ],

    'sets' => [
        'ويب' => ['frontend', 'backend', 'api', 'database', 'design', 'testing'],
        'جوال' => ['mobile', 'backend', 'api', 'design', 'testing'],
        'ذكاء' => ['data', 'model', 'evaluation', 'integration'],
        'ابحاث' => ['literature', 'methodology', 'analysis'],
        'أبحاث' => ['literature', 'methodology', 'analysis'],
        'تدريب' => ['fieldwork', 'reports'],
    ],

    'common' => ['docs', 'presentation', 'coordination'],

    // حدّ الأدوار للعضو: أكثر من أربعة يعني أنه «مسؤول عن كل شيء»
    'max_per_member' => 4,
];
