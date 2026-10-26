<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingAttendeesTable extends Migration
{
    public function up()
    {
        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('meeting_new_id');
            $table->integer('employee_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_attendees');
    }
}
