<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMeetingResultsTable extends Migration
{
    public function up()
    {
        Schema::create('meeting_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('meeting_new_id');
            $table->integer('employee_id');
            $table->longText('content')->nullable();
            $table->dateTime('filled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_results');
    }
}
