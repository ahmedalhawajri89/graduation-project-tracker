<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * سجلّ التدقيق.
 *
 * من غيّر درجة؟ من نقل مجموعة إلى مشرف آخر؟ من حذف مشروعاً أو أوقف
 * تخصصاً؟ \u200Eevaluated_at\u200E وحده لا يقول شيئاً عن التاريخ — وهذا بالضبط
 * ما يُتنازَع عليه في نظام يحمل درجات.
 *
 * سجلّ إلحاق فقط: لا مسار تعديل ولا حذف في الواجهة. سجلّ يُعدَّل ليس
 * سجلّاً.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // الفاعل متعدّد الأشكال: أدمن أو مشرف أو طالب
            $table->nullableMorphs('actor');
            // لقطة نصّية: الفاعل قد يُحذف، وسجلّ يشير إلى صفّ محذوف
            // يصير سطوراً فارغة — وهو أوّل ما يُحتاج إليه عند التنازع
            $table->string('actor_name', 120)->nullable();
            $table->string('actor_role', 20)->nullable();

            // مفتاح ثابت لا جملة عربية: يُبحث به ويُرشَّح، والنصّ
            // المعروض يُترجم في العرض
            $table->string('action', 60)->index();

            $table->nullableMorphs('subject');
            $table->string('subject_label', 200)->nullable();

            // القديم والجديد
            $table->json('changes')->nullable();

            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
    }
};
