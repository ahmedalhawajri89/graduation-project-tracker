<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * اعتماد الدرجة.
 *
 * كان المشرف يضع الدرجة ويغيّرها وحده، بلا حدّ، إلى الأبد — ولا أثر
 * لمن غيّر ومتى. ومشاريع التخرّج تُناقَش وتُعتمد نتائجها، فالنظام
 * كان يسجّل الدرجة ولا يحميها.
 *
 * بعد الاعتماد يُقفل التعديل على المشرف، ولا يفكّه إلا مسؤول النظام
 * بسبب مكتوب يُحفظ في سجلّ التدقيق.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('grade_locked_at')->nullable()->after('evaluated_at');
            // مَن وضع الدرجة: المشرف قد يتغيّر بعد التقييم
            $table->foreignId('graded_by')
                ->nullable()
                ->after('grade_locked_at')
                ->constrained('supervisors', 'id')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('graded_by');
            $table->dropColumn('grade_locked_at');
        });
    }
};
