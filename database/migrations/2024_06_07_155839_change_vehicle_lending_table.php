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
        // Create new colomn
        Schema::table('vehicle_lendings', function (Blueprint $table) {
            $table->date('end_date')->nullable()->after('date');
        });

        // Set default value for old data
        DB::statement('UPDATE vehicle_lendings SET end_date = date');

        // Change new colomn to be not null
        Schema::table('vehicle_lendings', function (Blueprint $table) {
            $table->date('end_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_lendings', function (Blueprint $table) {
            $table->dropColumn(['end_date']);
        });
    }
};
