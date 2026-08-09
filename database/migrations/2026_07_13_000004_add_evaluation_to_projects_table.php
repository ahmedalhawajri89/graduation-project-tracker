<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('grade', 5, 2)->nullable()->after('date_line');
            $table->text('evaluation_note')->nullable()->after('grade');
            $table->timestamp('evaluated_at')->nullable()->after('evaluation_note');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['grade', 'evaluation_note', 'evaluated_at']);
        });
    }
};
