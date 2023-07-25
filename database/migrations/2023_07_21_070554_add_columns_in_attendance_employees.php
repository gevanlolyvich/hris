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
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->foreignId('attendance_type_id')->nullable();
            $table->string('coord_in')->nullable();
            $table->string('coord_out')->nullable();
            $table->boolean('is_valid')->nullable();
            $table->integer('validate_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendance_employees', function (Blueprint $table) {
            $table->dropColumn(['attendance_type_id', 'coord_in', 'coord_out', 'is_valid', "validate_by"]);
        });
    }
};
