<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('period_start')->nullable()->after('period');
            $table->string('period_end')->nullable()->after('period_start');
        });
    }

    public function down()
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end']);
        });
    }
};
