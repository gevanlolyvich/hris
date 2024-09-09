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
            $table->dropColumn(['ht_note']);
    
            // Create
            $table->text('hr_note')->nullable()->after('superior_note');
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
            $table->dropColumn(['hr_note']);

            // Create
            $table->text('ht_note')->nullable()->after('superior_note');
        });
    }
};
