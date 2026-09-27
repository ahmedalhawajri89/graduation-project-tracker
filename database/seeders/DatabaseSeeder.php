<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * البذور حسب البيئة.
     *
     * كانت كلّها تُزرع دائماً: admin@admin.com و٨٠٠ حساب تجريبي بكلمة سر
     * معروفة (adminadmin)، و\u200EDemoSeeder\u200E الذي يعدّل بيانات قائمة (ينقل مشاريع
     * إلى فصل وهمي، ويضع مواعيد فائتة). في الإنتاج: ما يلزم النظام ليعمل وحده،
     * والأدمن الأول بـ \u200Ephp artisan admin:create\u200E.
     */
    public function run()
    {
        $this->call(self::seedersFor(app()->environment()));

        if (app()->environment('production')) {
            $this->command?->warn('لم يُزرع أي حساب — أنشئ الأدمن الأول بـ: php artisan admin:create');
        }
    }

    /** @return array<int, class-string<Seeder>> */
    public static function seedersFor(string $environment): array
    {
        $essential = [
            SemesterSeeder::class,
            SpecializeSeeder::class,
        ];

        if ($environment === 'production') {
            return $essential;
        }

        return [
            AdminSeeder::class,
            ...$essential,
            SupervisorSeeder::class,
            StudentSeeder::class,
            DemoSeeder::class,
        ];
    }
}
