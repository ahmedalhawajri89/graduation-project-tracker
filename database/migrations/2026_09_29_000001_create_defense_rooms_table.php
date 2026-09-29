<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * قاعات المناقشة.
 *
 * قائمة تُدار في المنصّة لا نصّ حرّ: بها يُعرف أن قاعتين لم تُحجزا في
 * الوقت نفسه. القاعة المعطّلة تبقى لمناقشاتها السابقة ولا تُعرض للجدولة.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('defense_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->unique();
            $table->string('location', 120)->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('defense_rooms');
    }
};
