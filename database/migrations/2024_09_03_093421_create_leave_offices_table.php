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
        Schema::create('leave_offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('date');
            $table->foreignId('superior_approval_by')->nullable();
            $table->foreignId('hr_approval_by')->nullable();
            $table->dateTime('leave')->nullable();
            $table->string('leave_coord')->nullable();
            $table->string('leave_pict')->nullable();
            $table->dateTime('return')->nullable();
            $table->string('return_coord')->nullable();
            $table->string('return_pict')->nullable();
            $table->string('status')->default('Pending');
            $table->text('purpose')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_offices');
    }
};
