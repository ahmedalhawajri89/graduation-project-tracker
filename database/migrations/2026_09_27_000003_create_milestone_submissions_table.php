<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * تسليم المراحل: الطالب يسلّم، والمشرف يعتمد أو يطلب تعديلاً بملاحظة.
 *
 * كانت المرحلة زرّ تبديل عند المشرف وحده: لا يقول الطالب «سلّمت»، ولا
 * يقول المشرف «عدّلوا هذا» إلا في النقاش، فيضيع السبب بين الرسائل.
 *
 * \u200Eproject_milestones.status\u200E: open ← submitted ← approved | revision.
 * و\u200Eis_done\u200E يبقى كما هو (approved ⇔ is_done): كل ما يحسب الإنجاز يقرؤه.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->string('status', 12)->default('open')->after('is_done');
        });

        DB::table('project_milestones')->where('is_done', true)->update(['status' => 'approved']);

        Schema::create('milestone_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('milestone_id')->constrained('project_milestones')->cascadeOnDelete();
            // الطالب يُحذف ويبقى تسليمه: عمل الفريق لا عمل فرد
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->unsignedSmallInteger('round');
            $table->text('note')->nullable();

            // اختياري، على القرص الخاص — يُنزَّل عبر مسار محميّ
            $table->string('file_path')->nullable();
            $table->string('file_name', 150)->nullable();
            $table->unsignedInteger('file_size')->nullable();

            // ردّ المشرف: approved | revision، وملاحظته شرط للتعديل
            $table->string('decision', 10)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('supervisors')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['milestone_id', 'round']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('milestone_submissions');

        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
