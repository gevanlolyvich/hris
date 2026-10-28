<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flexible_times')) {
            return;
        }

        Schema::table('flexible_times', function (Blueprint $table) {
            if (! Schema::hasColumn('flexible_times', 'normal_start_time')) {
                $table->time('normal_start_time')->nullable()->after('date');
            }
            if (! Schema::hasColumn('flexible_times', 'normal_end_time')) {
                $table->time('normal_end_time')->nullable()->after('normal_start_time');
            }
            if (! Schema::hasColumn('flexible_times', 'requested_end_time')) {
                $table->time('requested_end_time')->nullable()->after('requested_start_time');
            }
            if (! Schema::hasColumn('flexible_times', 'rejected_by')) {
                $table->foreignId('rejected_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('flexible_times', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (! Schema::hasColumn('flexible_times', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('rejected_at');
            }
            if (! Schema::hasColumn('flexible_times', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('flexible_times', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });

        // Widen status enum to include cancelled (table has 0 rows in this deployment;
        // MySQL requires a full MODIFY for enum changes).
        $statusCol = DB::selectOne("SHOW COLUMNS FROM flexible_times WHERE Field = 'status'");
        if ($statusCol && strpos((string) $statusCol->Type, 'cancelled') === false) {
            DB::statement("ALTER TABLE flexible_times MODIFY status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('flexible_times')) {
            return;
        }

        Schema::table('flexible_times', function (Blueprint $table) {
            $columns = [];
            foreach (['normal_start_time', 'normal_end_time', 'requested_end_time', 'rejected_at', 'rejection_reason', 'cancelled_at'] as $col) {
                if (Schema::hasColumn('flexible_times', $col)) {
                    $columns[] = $col;
                }
            }
            if ($columns) {
                $table->dropColumn($columns);
            }
            foreach (['rejected_by', 'cancelled_by'] as $col) {
                if (Schema::hasColumn('flexible_times', $col)) {
                    $table->dropForeign([$col]);
                    $table->dropColumn($col);
                }
            }
        });
    }
};
