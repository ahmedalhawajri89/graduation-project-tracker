<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * لجنة المناقشة: مشرف المشروع وممتحن من المشرفين.
 *
 * درجة كل عضو وملاحظاته هنا (تُرصد في المرحلة الثانية)، والنهائية
 * متوسطها. الأعمدة موجودة من الآن فلا يلزم ترحيل آخر حين تُبنى.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('defense_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defense_id')->constrained('defenses')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->constrained('supervisors')->cascadeOnDelete();
            $table->string('role', 12); // supervisor | examiner
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('comments')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['defense_id', 'supervisor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('defense_members');
    }
};
