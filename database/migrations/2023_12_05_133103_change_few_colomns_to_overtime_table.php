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
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropColumn(['number_of_days', 'rate', 'hours']);
            $table->date('date')->after('title');
            $table->dateTime('clock_in')->nullable()->after('date');
            $table->dateTime('clock_out')->nullable()->after('clock_in');
            $table->string('coord_in')->nullable()->after('clock_out');
            $table->string('coord_out')->nullable()->after('coord_in');
            $table->string('picture_in')->nullable()->after('coord_out');
            $table->string('picture_out')->nullable()->after('picture_in');
            $table->string('description')->nullable()->after('picture_out');
            $table->string('document')->nullable()->after('description');
            $table->string('report_document')->nullable()->after('document');
            $table->string('report_note')->nullable()->after('report_document');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('overtimes', function (Blueprint $table) {
            $table->dropColumn(['date', 'clock_in', 'clock_out', 'coord_in', 'coord_out', 'picture_in', 'picture_out', 'document', 'report_document', 'description', 'report_note']);
            $table->integer('number_of_days')->after('title');
            $table->integer('hours')->after('number_of_days');
            $table->integer('rate')->after('hours');
        });
    }
};
