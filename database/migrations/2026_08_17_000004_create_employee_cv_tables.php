<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_cvs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete()->unique();
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_cv_experiences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('company');
            $table->string('position');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('employee_cv_educations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('institution');
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->integer('start_year')->nullable();
            $table->integer('end_year')->nullable();
            $table->string('gpa')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_cv_skills', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('skill');
            $table->string('proficiency')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_cv_languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('language');
            $table->string('proficiency')->nullable();
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
        Schema::dropIfExists('employee_cv_languages');
        Schema::dropIfExists('employee_cv_skills');
        Schema::dropIfExists('employee_cv_educations');
        Schema::dropIfExists('employee_cv_experiences');
        Schema::dropIfExists('employee_cvs');
    }
};