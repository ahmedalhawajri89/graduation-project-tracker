<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * يوم آخر تذكير بموعد المرحلة — يمنع تذكيرين في يوم واحد إن شُغّل
 * الأمر مرّتين. انظر \App\Console\Commands\RemindMilestoneDeadlines
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->date('reminded_on')->nullable()->after('done_at');
        });
    }

    public function down(): void
    {
        Schema::table('project_milestones', function (Blueprint $table) {
            $table->dropColumn('reminded_on');
        });
    }
};
