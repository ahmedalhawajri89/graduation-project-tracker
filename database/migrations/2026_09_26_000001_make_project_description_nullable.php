<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الوصف اختياري كما يقول النموذج.
 *
 * ترحيل الإنشاء كتب \u200E->null()\u200E — وليس مُعدِّلاً في Blueprint — فخرج العمود
 * \u200ENOT NULL\u200E. ومع \u200Estrict\u200E كان كل مقترح بلا وصف يفشل برسالة «راجع
 * بيانات الفريق»، والنموذج يقول «وصف مختصر إن وُجد».
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }

    public function down()
    {
        // يُعاد \u200ENOT NULL\u200E بعد تحويل الفارغ إلى نصّ فارغ، وإلا فشل التغيير
        \Illuminate\Support\Facades\DB::table('projects')->whereNull('description')->update(['description' => '']);

        Schema::table('projects', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
        });
    }
};
