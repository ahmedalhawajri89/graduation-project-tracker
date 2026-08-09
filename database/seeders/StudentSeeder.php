<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $maleNames = ['احمد', 'محمد', 'حسن', 'حسين', 'علاء', 'محمود', 'يوسف', 'يونس', 'عبد الله', 'منير', 'تيسير', 'شمس', 'حامد', 'عمر', 'عامر', 'اشرف', 'كرم', 'نور'];
        $femaleNames = ['علا', 'عبير', 'اسماء', 'سالي', 'ميساء', 'ايمان', 'هنادي', 'رانيا', 'اسلام'];

        $data = [];
        for ($i = 0; $i < 500; $i++) {

            $gender = \Arr::random(['male', 'female']);
            if ($gender == 'male') {
                $name = \Arr::random($maleNames) . ' ' . \Arr::random($maleNames);
                $uId = 1300000000;
            } else {
                $name = \Arr::random($femaleNames) . ' ' . \Arr::random($maleNames);
                $uId = 2300000000;
            }

            $data[] = [
                'university_id' => ($uId + $i + 1),
                'name' => $name,
                'email' => "student_{$i}@student.com",
                'email_verified_at' => now(),
                'password' => bcrypt('adminadmin'),
                'phone' => '0599907811',
                'specialize_id' => random_int(1, 3),
                'admin_id' => 1,
                'gender' => $gender,
                'remember_token' => \Str::random(10),
            ];
        }

        $chunks = array_chunk($data, 300);
        foreach ($chunks as $chunk) {
            Student::insert($chunk);
        }

        /*
    SELECT students.name, students.university_id, students.email, students.phone, specializes.name, students.password, students.gender
    FROM `students` INNER JOIN specializes
    ON students.specialize_id = specializes.id
     */

    }
}
