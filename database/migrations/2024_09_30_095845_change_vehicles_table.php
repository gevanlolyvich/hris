<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Add new column 'status' and drop 'is_active' in one go
            $table->string('status')->default('inactive')->after('name');
        });

        // Safely update the old data using DB facade
        DB::table('vehicles')->where('is_active', true)->update(['status' => 'active']);

        // Now drop the 'is_active' column
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the 'status' column and re-add 'is_active' column
            $table->dropColumn('status');
            $table->boolean('is_active')->default(1)->after('name');
        });
    }
};
