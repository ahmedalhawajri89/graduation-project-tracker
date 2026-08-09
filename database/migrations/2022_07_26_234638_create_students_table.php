<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            $table->string('university_id', 10)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female'])->default('male');

            $table->foreignId('specialize_id')
                ->nullable()
                ->constrained('specializes', 'id')->nullOnDelete();
            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('admins', 'id')->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();

            // $table->primary('university_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('students');
    }
}
