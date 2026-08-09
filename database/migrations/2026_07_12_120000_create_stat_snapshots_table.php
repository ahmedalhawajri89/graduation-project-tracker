<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stat_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();      // لقطة واحدة لكل يوم
            $table->unsignedInteger('students')->default(0);
            $table->unsignedInteger('supervisors')->default(0);
            $table->unsignedInteger('groups')->default(0);       // المجموعات/المشاريع النشطة
            $table->unsignedInteger('messages')->default(0);
            $table->unsignedInteger('has_group')->default(0);
            $table->unsignedInteger('not_has_group')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stat_snapshots');
    }
};
