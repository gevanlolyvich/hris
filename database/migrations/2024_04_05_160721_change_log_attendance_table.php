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
        Schema::table('log_attendances', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('max');
            $table->string('coordinate_out')->nullable()->after('coordinate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_attendances', function (Blueprint $table) {
            $table->dropColumn(['shift_id', 'coordinate_out']);
        });
    }
};
