<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * أدوار الفريق: مَن مسؤول عن ماذا في المشروع.
 *
 * كانت بطاقة الفريق أسماءً فقط، و«مَن عمل ماذا؟» — سؤال المشرف ولجنة
 * المناقشة — لا جواب له في المنصة. القائد يوزّع، والجميع يرى.
 *
 * الدور مربوط بصفّ العضوية (\u200Egroups\u200E) لا بالطالب: دورٌ في هذا المشروع،
 * وحذف العضوية يحذف أدواره.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('group_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            // مفتاح من الأدوار الجاهزة (\u200Econfig/team_roles.php\u200E) أو custom لدور حرّ
            $table->string('role_key', 30);
            $table->string('label', 40);
            $table->timestamps();

            $table->unique(['group_id', 'label']);
        });

        Schema::table('groups', function (Blueprint $table) {
            // سطر يصف مسؤولية العضو — «واجهات الطالب والمشرف»
            $table->string('responsibility', 160)->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('responsibility');
        });

        Schema::dropIfExists('group_roles');
    }
};
