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
        Schema::table('event_employees', function (Blueprint $table) {
            $table->dateTime('clock_in')->change();
            $table->dateTime('clock_out')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('event_employees', function (Blueprint $table) {
            $table->time('clock_in')->change();
            $table->time('clock_out')->change();
        });
    }
};
