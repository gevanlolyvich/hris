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
            $table->dropColumn(['leave_coord', 'leave_pict']);
    
            // Create
            $table->text('superior_note')->nullable()->after('hr_approval_by');
            $table->text('ht_note')->nullable()->after('superior_note');
            $table->text('description')->nullable()->after('purpose');

            // Change
            $table->time('leave')->nullable()->change();
            $table->string('purpose')->nullable()->change();
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
            $table->dropColumn(['superior_note', 'ht_note', 'description']);

            // Create
            $table->string('leave_coord')->nullable()->after('leave');
            $table->string('leave_pict')->nullable()->after('leave_coord');

            // Change
            $table->dateTime('leave')->nullable()->change();
            $table->text('purpose')->nullable()->change();
        });
    }
};
