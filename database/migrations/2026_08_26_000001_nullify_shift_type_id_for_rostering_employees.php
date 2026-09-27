<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Rostering employees (is_shift = 1) get their shifts from the monthly
        // roster; any leftover fixed shift reference is cleared.
        DB::table('employees')
            ->where('is_shift', 1)
            ->whereNotNull('shift_type_id')
            ->update(['shift_type_id' => null]);
    }

    public function down(): void
    {
        // Data that was nulled cannot be restored reliably; intentionally a no-op.
    }
};
