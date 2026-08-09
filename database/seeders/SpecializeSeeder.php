<?php

namespace Database\Seeders;

use App\Models\Specialize;
use App\Models\SpecializeProject;
use Illuminate\Database\Seeder;

class SpecializeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Specialize::create([
            'name' => 'علوم الحاسوب',
        ]);
        Specialize::create([
            'name' => 'تكنولوجيا الشبكات والهواتف النقالة',
        ]);
        Specialize::create([
            'name' => 'تكنولوجيا المعلومات التطبيقية',
        ]);

        SpecializeProject::create([
            'name' => 'برمجة ويب',
            'specialize_id' => random_int(1, 3),
            'min' => random_int(1, 2),
            'max' => random_int(3, 5),
        ]);
        SpecializeProject::create([
            'name' => 'برمجة تطبيقات جوال',
            'specialize_id' => random_int(1, 3),
            'min' => random_int(1, 2),
            'max' => random_int(3, 5),
        ]);
        SpecializeProject::create([
            'name' => 'برمجة ذكاء صناعي',
            'specialize_id' => random_int(1, 3),
            'min' => random_int(1, 2),
            'max' => random_int(3, 5),
        ]);
        SpecializeProject::create([
            'name' => 'ابحاث',
            'specialize_id' => random_int(1, 3),
            'min' => random_int(1, 2),
            'max' => random_int(3, 5),
        ]);
        SpecializeProject::create([
            'name' => 'تدريب عملي',
            'specialize_id' => random_int(1, 3),
            'min' => random_int(1, 2),
            'max' => random_int(3, 5),
        ]);

    }
}
