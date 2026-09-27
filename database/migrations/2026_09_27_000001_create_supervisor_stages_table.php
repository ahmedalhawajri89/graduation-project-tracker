<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * خطة المراحل: المشرف يعرّف المرحلة مرّة — عنوانها وموعدها وتعليماتها
 * وقالبها — فتُنشأ في كل مجموعاته. كانت المراحل تُكتب لكل مجموعة على
 * حدة، والقالب يُرفع في كل مشروع ويختلط بملفات الطلاب.
 *
 * \u200Eproject_milestones.stage_id\u200E يربط مرحلة المجموعة بأصلها في الخطة: تعديل
 * الأصل يسري، وحذفه يحذف غير المنجز ويُبقي المنجز (nullOnDelete).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('supervisor_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('supervisors')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->cascadeOnDelete();
            $table->string('title', 150);
            $table->text('instructions')->nullable();
            $table->date('due_date')->nullable();

            // القالب اختياري: ملف على القرص الخاص، يُنزَّل عبر مسار محميّ
            $table->string('template_path')->nullable();
            $table->string('template_name', 150)->nullable();
            $table->unsignedInteger('template_size')->nullable();

            $table->timestamps();

            $table->index(['supervisor_id', 'semester_id']);
        });

        Schema::table('project_milestones', function (Blueprint $table) {
            $table->foreignId('stage_id')->nullable()->after('project_id')
                ->constrained('supervisor_stages')->nullOnDelete();

            // مرحلة الخطة مرّة واحدة في المشروع — يمنع إنشاءها مرّتين عند التزامن
            $table->unique(['project_id', 'stage_id']);
        });
    }

    public function down()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'stage_id']);
            $table->dropConstrainedForeignId('stage_id');
        });

        Schema::dropIfExists('supervisor_stages');
    }
};
