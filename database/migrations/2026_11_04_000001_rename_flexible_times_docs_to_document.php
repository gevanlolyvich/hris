<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flexible_times')) {
            return;
        }

        if (Schema::hasColumn('flexible_times', 'docs') && ! Schema::hasColumn('flexible_times', 'document')) {
            Schema::table('flexible_times', function (Blueprint $table) {
                $table->renameColumn('docs', 'document');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('flexible_times')) {
            return;
        }

        if (Schema::hasColumn('flexible_times', 'document') && ! Schema::hasColumn('flexible_times', 'docs')) {
            Schema::table('flexible_times', function (Blueprint $table) {
                $table->renameColumn('document', 'docs');
            });
        }
    }
};
