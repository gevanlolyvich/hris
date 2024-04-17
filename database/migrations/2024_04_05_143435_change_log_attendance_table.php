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
        Schema::table('log_attendances', function (Blueprint $table) {
            $table->string('min_source')->nullable()->after('max');
            $table->string('max_source')->nullable()->after('min_source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_attendances', function (Blueprint $table) {
            $table->dropColumn(['min_source', 'max_source']);
        });
    }
};
