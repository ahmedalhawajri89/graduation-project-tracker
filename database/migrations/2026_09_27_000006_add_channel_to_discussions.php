<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * نقاش الفريق: قناة ثانية في المشروع، للأعضاء وحدهم.
 *
 * كان النقاش خيطاً واحداً يقرؤه المشرف كلّه، فينتقل الكلام الداخلي
 * («عدّلي الملف قبل الخميس») إلى واتساب. لكل رسالة قناتها الآن، والافتراضي
 * «supervisor» — فالرسائل القائمة وكل قارئ قديم يبقيان على خيط المشرف.
 *
 * مؤشّر القراءة لكل قناة: كان واحداً للمشروع، و\u200EmarkRead\u200E بأكبر رقم
 * كان سيعلّم القناة الأخرى مقروءة.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('project_comments', function (Blueprint $table) {
            $table->string('channel', 12)->default('supervisor')->after('project_id');
            // أرقام الطلاب المذكورين بـ@ — في نقاش الفريق
            $table->json('mentions')->nullable()->after('body');
            $table->index(['project_id', 'channel']);
        });

        Schema::table('discussion_reads', function (Blueprint $table) {
            $table->string('channel', 12)->default('supervisor')->after('reader_id');
        });

        // الفريد الجديد أولاً: المفتاح الأجنبي على project_id يحتاج فهرساً يبدأ به
        Schema::table('discussion_reads', function (Blueprint $table) {
            $table->unique(['project_id', 'reader_type', 'reader_id', 'channel'], 'discussion_reads_reader_channel_unique');
        });
        Schema::table('discussion_reads', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'reader_type', 'reader_id']);
        });
    }

    public function down()
    {
        // مؤشّرات قناة الفريق تُحذف قبل عودة الفريد القديم
        \Illuminate\Support\Facades\DB::table('discussion_reads')->where('channel', 'team')->delete();
        \Illuminate\Support\Facades\DB::table('project_comments')->where('channel', 'team')->delete();

        Schema::table('discussion_reads', function (Blueprint $table) {
            $table->unique(['project_id', 'reader_type', 'reader_id']);
        });
        Schema::table('discussion_reads', function (Blueprint $table) {
            $table->dropUnique('discussion_reads_reader_channel_unique');
            $table->dropColumn('channel');
        });

        Schema::table('project_comments', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'channel']);
            $table->dropColumn(['channel', 'mentions']);
        });
    }
};
