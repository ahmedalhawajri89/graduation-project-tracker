<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * المناقشة: الخطوة بين «اكتمل المشروع» و«رُصدت الدرجة».
 *
 * كان النظام يقفز من الاكتمال إلى درجة يرصدها المشرف وحده، والمناقشة
 * تجري خارج المنصّة بلا موعد ولا قاعة ولا لجنة. مناقشة واحدة لكل
 * مشروع: إعادة الجدولة تعدّل صفّها، والإلغاء يغيّر حالته.
 *
 * النوع حضوري أو عن بُعد أو مدمج: القاعة للأول والثالث، ورابط الاجتماع
 * (Google Meet أو غيره) للثاني والثالث.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('defenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->unsignedSmallInteger('duration_minutes')->default(45);
            $table->string('mode', 12)->default('in_person');
            $table->foreignId('room_id')->nullable()->constrained('defense_rooms')->nullOnDelete();
            $table->string('meeting_url', 500)->nullable();
            $table->string('status', 12)->default('scheduled');
            $table->text('notes')->nullable();
            // تذكير اليوم السابق ويوم المناقشة مرة واحدة لكل يوم (defenses:remind)
            $table->date('reminded_on')->nullable();
            $table->foreignId('scheduled_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('defenses');
    }
};
