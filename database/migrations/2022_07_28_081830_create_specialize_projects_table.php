<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpecializeProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('specialize_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('specialize_id')
                ->constrained('specializes', 'id')
                ->cascadeOnDelete();
            $table->integer('min')->default(0);
            $table->integer('max')->default(0);
            $table->timestamps();

            $table->unique(['name', 'specialize_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('specialize_projects');
    }
}
