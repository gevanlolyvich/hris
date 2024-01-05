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
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['date']);
            $table->string('meeting_type')->after('title');
            $table->string('url')->nullable()->after('meeting_type');
            $table->string('password')->nullable()->after('url');
            $table->dateTime('start_time')->after('password');
            $table->dateTime('end_time')->after('start_time');
            $table->string('location')->nullable()->after('end_time');
            $table->dropColumn(['time']);
        });        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->time('time')->after('start_time');
            $table->date('date')->after('password');
            $table->dropColumn(['start_time', 'meeting_type', 'url', 'password', 'end_time', 'location']);
        });
    }
};
