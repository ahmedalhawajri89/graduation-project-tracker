<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * أول أدمن في تثبيت جديد — بكلمة سر يختارها من يثبّت.
 *
 * كان الطريق الوحيد ‎migrate --seed‎، وهو يزرع admin@admin.com بكلمة سر
 * معروفة ‎(adminadmin)‎ ومعها ٨٠٠ حساب تجريبي بالكلمة نفسها.
 */
class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {--name= : الاسم}
        {--email= : البريد}
        {--password= : كلمة السر (يُسأل عنها إن غابت — الأفضل ألّا تُكتب في سطر الأوامر)}';

    protected $description = 'ينشئ حساب أدمن';

    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?: $this->ask('الاسم'),
            'email' => $this->option('email') ?: $this->ask('البريد'),
            'password' => $this->option('password') ?: $this->secret('كلمة السر (٨ أحرف على الأقل)'),
        ];

        $validator = Validator::make($data, [
            'name' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:admins,email',
            'password' => 'required|string|min:8|max:60',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->info("أُنشئ حساب الأدمن: {$data['email']}");

        return self::SUCCESS;
    }
}
