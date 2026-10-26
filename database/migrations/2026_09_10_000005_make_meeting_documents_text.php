<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeMeetingDocumentsText extends Migration
{
    public function up()
    {
        Schema::table('meetings_new', function (Blueprint $table) {
            $table->text('document')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('meetings_new', function (Blueprint $table) {
            $table->string('document')->nullable()->change();
        });
    }
}