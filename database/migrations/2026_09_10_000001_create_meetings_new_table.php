<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingsNewTable extends Migration
{
    public function up()
    {
        Schema::create('meetings_new', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('meeting_type');
            $table->date('meeting_date');
            $table->time('meeting_time');
            $table->string('document')->nullable();
            $table->dateTime('deadline');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meetings_new');
    }
}
