<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ما قرأه كل شخص من نقاش كل مشروع.
 *
 * صفّ لكل (مشروع، قارئ) يحمل آخر تعليق رآه — لا صفّ لكل تعليق. غير
 * المقروء = تعليقات غيره بمعرّف أكبر. الإشعارات لا تصلح لهذا: لا تحمل
 * رقم المشروع، وفتح صفحة الإشعارات يُعلّمها كلّها مقروءة.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('discussion_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->morphs('reader');
            $table->unsignedBigInteger('last_read_comment_id')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'reader_type', 'reader_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('discussion_reads');
    }
};
