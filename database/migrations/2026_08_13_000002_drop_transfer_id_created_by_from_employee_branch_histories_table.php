<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the transfer_id and created_by columns that are no longer part of
     * the employee branch history design. The transfer timeline now lives in
     * the transfers table and the initial branch reference is immutable.
     */
    public function up(): void
    {
        Schema::table('employee_branch_histories', function (Blueprint $table) {
            if (Schema::hasColumn('employee_branch_histories', 'transfer_id')) {
                $table->dropColumn('transfer_id');
            }

            if (Schema::hasColumn('employee_branch_histories', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_branch_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_branch_histories', 'transfer_id')) {
                $table->foreignId('transfer_id')->nullable();
            }

            if (!Schema::hasColumn('employee_branch_histories', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
        });
    }
};
