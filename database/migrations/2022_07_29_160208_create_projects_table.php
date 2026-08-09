<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->null();

            $table->foreignId('semester_id')
                ->nullable()
                ->constrained('semesters', 'id')
                ->nullOnDelete();

            $table->foreignId('supervisor_id')
                ->nullable()
                ->constrained('supervisors', 'id')
                ->nullOnDelete();

            $table->foreignId('specialize_project_id')
                ->nullable()
                ->constrained('specialize_projects', 'id')
                ->nullOnDelete();

            $table->enum('status', ['request', 'accept', 'reject', 'complete'])->default('request');

            $table->date('date_line')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
