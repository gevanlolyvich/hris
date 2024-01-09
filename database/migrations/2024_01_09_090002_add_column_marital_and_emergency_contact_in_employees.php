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
            $table->string('domicile_address')->after('address')->nullable();
            $table->string('marital_status')->after('domicile_address')->nullable();
            $table->string('emergency_contact_number')->after('marital_status')->nullable();
            $table->string('emergency_contact_relation')->after('emergency_contact_number')->nullable();
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
            $table->dropColumn([
                'marital_status',
                'emergency_contact_number',
                'emergency_contact_relation',
                'domicile_address'
            ]);
        });
    }
};
