<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('name');
        });

        // ترحيل سلس: آخر فصل موجود يصبح هو النشط تلقائياً (نفس سلوك النظام السابق)
        $latest = DB::table('semesters')->orderByDesc('id')->first();
        if ($latest) {
            DB::table('semesters')->where('id', $latest->id)->update(['is_active' => 1]);
        }
    }

    public function down()
    {
        Schema::table('semesters', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
