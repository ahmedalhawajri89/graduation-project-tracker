<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الطالب مرّة واحدة في المشروع الواحد — في القاعدة لا في PHP وحده.
 *
 * كان يمكن لطلبين متزامنين أو لتعديل أدمن أن يُدرجا الصفّ نفسه مرّتين.
 * «مشروع نشط واحد لكل طالب» يبقى في التطبيق (يعتمد على حالة المشروع،
 * ولا يعبَّر عنه بقيد في MySQL) ويحرسه قفل الصفوف في المعاملة.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->unique(['student_id', 'project_id'], 'groups_student_project_unique');
        });
    }

    public function down()
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropUnique('groups_student_project_unique');
        });
    }
};
