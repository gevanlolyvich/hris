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
        Schema::table('vehicle_lendings', function (Blueprint $table) {
            $table->dropColumn(['is_approved']);
            $table->string('status')->default('Pending')->after('purpose');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_lendings', function (Blueprint $table) {
            $table->dropColumn(['status']);
            $table->boolean('is_approved')->default(false)->after('purpose');
        });
    }
};
