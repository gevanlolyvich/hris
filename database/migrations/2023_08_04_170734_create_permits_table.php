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
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->foreignId('permit_type_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('total_permit_days');
            $table->text('reason');
            $table->string('docs')->nullable();
            $table->string('status');
            $table->boolean('is_approved')->nullable();
            $table->foreignId('approved_by')->nullable();
            $table->foreignId('created_by')->nullable();
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
        Schema::dropIfExists('permits');
    }
};
