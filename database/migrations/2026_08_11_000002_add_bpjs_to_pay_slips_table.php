<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBpjsToPaySlipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            $table->text('bpjs')->nullable()->after('saturation_deduction');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            $table->dropColumn('bpjs');
        });
    }
}
