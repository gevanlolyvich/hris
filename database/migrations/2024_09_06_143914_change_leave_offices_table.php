<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('leave_offices', function (Blueprint $table) {
            // Drop
            $table->dropColumn(['purpose']);
    
            // Create
            $table->string('need')->nullable()->after('location');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_offices', function (Blueprint $table) {
            // Drop
            $table->dropColumn(['need']);

            // Create
            $table->string('purpose')->nullable()->after('location');
        });
    }
};
