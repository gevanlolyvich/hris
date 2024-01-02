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
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_identifier_code', 'branch_location']);
            $table->foreignId('bank_id')->nullable()->after('documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('bank_name')->nullable()->after('account_number');
            $table->string('bank_identifier_code')->nullable()->after('bank_name');
            $table->string('branch_location')->nullable()->after('bank_identifier_code');
            $table->dropColumn('bank_id');
        });
    }
};
