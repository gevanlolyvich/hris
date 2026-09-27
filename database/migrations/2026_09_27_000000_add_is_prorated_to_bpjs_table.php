<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('bpjs', 'is_prorated')) {
            return;
        }

        Schema::table('bpjs', function (Blueprint $table) {
            $table->boolean('is_prorated')->default(false)->after('is_recurring');
        });
    }

    public function down()
    {
        if (! Schema::hasColumn('bpjs', 'is_prorated')) {
            return;
        }

        Schema::table('bpjs', function (Blueprint $table) {
            $table->dropColumn(['is_prorated']);
        });
    }
};
