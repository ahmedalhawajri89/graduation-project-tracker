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

        // كل تخصص يحصل على مواضيعه صراحةً — الإسناد العشوائي كان يترك تخصصاً
        // بلا أي موضوع، فتظهر قائمة نوع المشروع فارغة عند الطالب
        $specializes = [
            'علوم الحاسوب' => ['برمجة ويب', 'برمجة ذكاء صناعي', 'أبحاث'],
            'تكنولوجيا الشبكات والهواتف النقالة' => ['برمجة تطبيقات جوال', 'تدريب عملي', 'أبحاث'],
            'تكنولوجيا المعلومات التطبيقية' => ['برمجة ويب', 'تدريب عملي', 'أبحاث'],
        ];

        foreach ($specializes as $name => $projects) {
            $specialize = Specialize::create(['name' => $name]);

            foreach ($projects as $project) {
                SpecializeProject::create([
                    'name' => $project,
                    'specialize_id' => $specialize->id,
                    'min' => 2,
                    'max' => 4,
                ]);
            }
        }

    }
}
