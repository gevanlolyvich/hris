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
            // Create
            $table->string('organizer_type')->after('organizer');
            $table->string('related_to')->nullable()->after('end_date');
            $table->text('file')->nullable()->after('approved_by');
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
            $table->dropColumn(['organizer_type', 'related_to', 'file']);
        });
    }
};
