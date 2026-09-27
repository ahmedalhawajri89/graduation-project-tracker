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
        $data = [];

        for ($i = 0; $i < 500; $i++) {

            $gender = \Arr::random(['male', 'female']);
            [$name, $slug] = NameBook::person($gender);
            $uId = $gender === 'male' ? 1300000000 : 2300000000;

            $data[] = [
                'university_id' => ($uId + $i + 1),
                'name' => $name,
                // الرقم الجامعي يضمن عدم تكرار البريد مهما تكرّر الاسم
                'email' => "{$slug}." . ($i + 1) . "@student.com",
                'email_verified_at' => now(),
                'password' => bcrypt('adminadmin'),
                'phone' => NameBook::phone(),
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
    }
}
