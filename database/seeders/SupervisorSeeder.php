<?php

namespace Database\Seeders;

use App\Models\Supervisor;
use Illuminate\Database\Seeder;

class SupervisorSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $maleNames = ['احمد', 'محمد', 'حسن', 'حسين', 'علاء', 'محمود', 'يوسف', 'يونس', 'عبد الله', 'منير', 'تيسير', 'شمس', 'حامد', 'عمر', 'عامر', 'اشرف', 'كرم', 'نور'];
        $femaleNames = ['علا', 'عبير', 'اسماء', 'سالي', 'ميساء', 'ايمان', 'هنادي', 'رانيا', 'اسلام'];

        $data = [];

        for ($i = 0; $i < 300; $i++) {
            $gender = \Arr::random(['male', 'female']);
            if ($gender == 'male') {
                $name = \Arr::random($maleNames) . ' ' . \Arr::random($maleNames);
            } else {
                $name = \Arr::random($femaleNames) . ' ' . \Arr::random($maleNames);
            }

            $data[] = [
                'university_id' => (700200300 + $i + 1),
                'name' => 'د.' . $name,
                'email' => "supervisor_{$i}@supervisor.com",
                'email_verified_at' => now(),
                'password' => bcrypt('adminadmin'),
                'phone' => '0599907811',
                'specialize_id' => random_int(1, 3),
                'admin_id' => 1,
                'gender' => $gender,
                'remember_token' => \Str::random(10),
                'max_group' => random_int(1, 3),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $chunks = array_chunk($data, 300);
        foreach ($chunks as $chunk) {
            Supervisor::insert($chunk);
        }

        /*
    SELECT supervisors.name, supervisors.university_id, supervisors.email, supervisors.phone, specializes.name, supervisors.password, supervisors.gender
    FROM `supervisors` INNER JOIN specializes
    ON supervisors.specialize_id = specializes.id
     */

    }
}
