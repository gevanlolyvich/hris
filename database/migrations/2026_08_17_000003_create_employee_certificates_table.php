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
        Schema::create('employee_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('name');
            $table->string('issuer')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('description')->nullable();
            $table->text('file');
            $table->foreignId('created_by')->constrained('users');
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
        Schema::dropIfExists('employee_certificates');
    }
};