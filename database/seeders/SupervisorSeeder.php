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
        $data = [];

        for ($i = 0; $i < 300; $i++) {
            $gender = \Arr::random(['male', 'female']);
            [$name, $slug] = NameBook::person($gender);

            $data[] = [
                'university_id' => (700200300 + $i + 1),
                'name' => 'د. ' . $name,
                'email' => "{$slug}." . ($i + 1) . "@supervisor.com",
                'email_verified_at' => now(),
                'password' => bcrypt('adminadmin'),
                'phone' => NameBook::phone(),
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
    }
}
