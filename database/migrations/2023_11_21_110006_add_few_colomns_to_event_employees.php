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
            $table->time('clock_in')->nullable()->after('created_by');
            $table->time('clock_out')->nullable()->after('clock_in');
            $table->string('coord_in')->nullable()->after('clock_out');
            $table->string('coord_out')->nullable()->after('coord_in');
            $table->string('picture_in')->nullable()->after('coord_out');
            $table->string('picture_out')->nullable()->after('picture_in');
            $table->string('report_document')->nullable()->after('picture_out');
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
            $table->dropColumn(['clock_in', 'clock_out', 'coord_in', 'coord_out', 'picture_in', 'picture_out', 'report_document']);
        });
    }
};
