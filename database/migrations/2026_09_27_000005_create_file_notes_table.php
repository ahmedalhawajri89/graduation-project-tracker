<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ملاحظات على ملفات المشروع: «صفحة ٣ ينقصها المرجع — @آية».
 *
 * كان الفريق يكتب اسم الملف نصّاً في النقاش (والمشرف يقرأ كل شيء)، أو ينتقل
 * إلى واتساب فتضيع المتابعة. الملاحظة مربوطة بملفها، وتنبّه عضواً بعينه،
 * وتُغلق حين تُعالَج.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('file_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_file_id')->constrained('project_files')->cascadeOnDelete();

            // الكاتب طالب أو مشرف
            $table->string('author_type');
            $table->unsignedBigInteger('author_id');

            // العضو المنبَّه — اختياري
            $table->foreignId('mentioned_id')->nullable()->constrained('students')->nullOnDelete();

            $table->text('body');

            // المعالجة: متى ومن — والملاحظة المفتوحة ما resolved_at فيها فارغ
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by_type')->nullable();
            $table->unsignedBigInteger('resolved_by_id')->nullable();

            $table->timestamps();

            $table->index(['author_type', 'author_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('file_notes');
    }
};
