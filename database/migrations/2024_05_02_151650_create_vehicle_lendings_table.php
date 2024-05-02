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
        Schema::create('vehicle_lendings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_by');
            $table->foreignId('vehicle_id');
            $table->date('date');
            $table->text('purpose');
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable();
            $table->dateTime('pickup_time')->nullable();
            $table->dateTime('return_time')->nullable();
            $table->decimal('pickup_km', 10, 3)->nullable();
            $table->decimal('return_km', 10, 3)->nullable();
            $table->string('pickup_file')->nullable();
            $table->string('return_file')->nullable();
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
        Schema::dropIfExists('vehicle_lendings');
    }
};
