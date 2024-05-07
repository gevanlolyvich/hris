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
            $table->decimal('pickup_km', 10, 0)->change();
            $table->decimal('return_km', 10, 0)->change();
            $table->decimal('pickup_emoney_balance', 11, 2)->default(0)->after('return_km');
            $table->decimal('return_emoney_balance', 11, 2)->default(0)->after('pickup_emoney_balance');
            $table->string('pickup_file_2')->nullable()->after('return_file');
            $table->string('return_file_2')->nullable()->after('pickup_file_2');
            $table->renameColumn('pickup_file', 'pickup_file_1');
            $table->renameColumn('return_file', 'return_file_1');
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
            $table->dropColumn(['pickup_emoney_balance', 'return_emoney_balance', 'pickup_file_2', 'return_file_2']);
            $table->decimal('pickup_km', 10, 3)->change();
            $table->decimal('return_km', 10, 3)->change();
            $table->renameColumn('pickup_file_1', 'pickup_file');
            $table->renameColumn('return_file_1', 'return_file');
        });
    }
};
