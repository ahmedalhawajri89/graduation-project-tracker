<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * رئيس لجنة المناقشة: عضو واحد يدير الجلسة ويوقّع المحضر أولاً.
 *
 * علامة على صفّ العضو لا دور ثالث: الرئيس يبقى مشرفاً أو ممتحناً بدرجته.
 * لجنة بلا رئيس محدّد (ما قبل هذا الترحيل) رئيسها مشرف المشروع.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('defense_members', function (Blueprint $table) {
            $table->boolean('is_chair')->default(false)->after('role');
        });
    }

    public function down()
    {
        Schema::table('defense_members', function (Blueprint $table) {
            $table->dropColumn('is_chair');
        });
    }
};
