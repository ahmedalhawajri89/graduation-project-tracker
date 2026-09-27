<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * استرجاع كلمة المرور — جدول لكل دور.
 *
 * \u200Econfig/auth.php\u200E كان يوجّه الحُرّاس الثلاثة إلى جدول \u200Epassword_resets\u200E
 * واحد مفهرس بالبريد. و\u200EDatabaseTokenRepository\u200E يحذف رموز البريد قبل
 * إنشاء رمز جديد — فطلب استرجاع من حساب يُبطل رمز حساب آخر يحمل نفس
 * البريد في جدول مختلف، بصمت.
 *
 * الجدول القديم فارغ: لم يكن في النظام مسار استرجاع إطلاقاً.
 */
return new class extends Migration
{
    private array $tables = [
        'password_reset_students',
        'password_reset_supervisors',
        'password_reset_admins',
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->string('email')->index();
                $t->string('token');
                $t->timestamp('created_at')->nullable();
            });
        }

        Schema::dropIfExists('password_resets');
    }

    public function down()
    {
        Schema::create('password_resets', function (Blueprint $t) {
            $t->string('email')->index();
            $t->string('token');
            $t->timestamp('created_at')->nullable();
        });

        foreach ($this->tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
