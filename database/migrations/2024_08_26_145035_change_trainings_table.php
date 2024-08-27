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
        Schema::table('trainings', function (Blueprint $table) {
            // Drop
            $table->dropColumn(['trainer_option']);

            // Create
            $table->string('organizer')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainings', function (Blueprint $table) {
            // Drop
            $table->dropColumn(['organizer']);

            // Create
            $table->integer('trainer_option')->after('name');
        });
    }
};
