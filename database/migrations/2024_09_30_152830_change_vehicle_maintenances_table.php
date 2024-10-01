<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            // Drop
            $table->dropColumn('location');

            // Create
            $table->foreignId('workshop_id')->default(1)->after('next_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            // Drop
            $table->dropColumn('workshop_id');

            // Create
            $table->string('location')->after('next_date');
        });
    }
};
