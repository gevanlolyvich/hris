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
        Schema::create('goal_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprisal_id');
            $table->foreignId('goal_id');
            $table->string('evaluation')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('score')->nullable();
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
        Schema::dropIfExists('goal_evaluations');
    }
};
