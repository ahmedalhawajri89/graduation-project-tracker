<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // حارس: لا اختبار على قاعدة لا ينتهي اسمها بـ _testing. كانت الاختبارات
        // تعمل على قاعدة التطوير، وعلى خادم إعداده للإنتاج كانت ستعدّل الإنتاج
        $database = DB::connection()->getDatabaseName();

        if (! str_ends_with((string) $database, '_testing')) {
            throw new \RuntimeException(
                "الاختبارات ترفض العمل على «{$database}» — قاعدة الاختبار يجب أن ينتهي اسمها بـ _testing. "
                . 'راجع DB_DATABASE في phpunit.xml ثم شغّل: php artisan test:prepare'
            );
        }

        try {
            DB::connection()->getPdo();
        } catch (\PDOException $e) {
            throw new \RuntimeException("قاعدة الاختبار «{$database}» غير موجودة — أنشئها بـ: php artisan test:prepare", 0, $e);
        }
    }
}
