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
        Schema::create('employee_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('start_period');
            $table->date('end_period');
            $table->text('reason')->nullable();
            $table->string('status')->nullable();
            $table->boolean('sync')->default(false);
            $table->boolean('active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_periods');
    }
};
