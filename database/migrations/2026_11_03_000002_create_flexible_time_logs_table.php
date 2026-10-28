<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('flexible_time_logs')) {
            return;
        }

        Schema::create('flexible_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flexible_time_id')->constrained('flexible_times')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('flexible_time_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flexible_time_logs');
    }
};
