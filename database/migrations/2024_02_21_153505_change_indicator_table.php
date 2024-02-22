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
        Schema::table('indicators', function (Blueprint $table) {
            $table->foreignId('level_id')->nullable();
            $table->dropColumn(['branch', 'department', 'designation', 'rating', 'customer_experience', 'marketing', 'administration', 'professionalism', 'integrity', 'attendance', 'created_user']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropColumn(['level_id']);
            $table->integer('branch')->default(0)->after('id');
            $table->integer('department')->default(0)->after('branch');
            $table->integer('designation')->default(0)->after('department');
            $table->string('rating')->nullable()->after('designation');
            $table->integer('customer_experience')->default(0)->after('rating');
            $table->integer('marketing')->default(0)->after('customer_experience');
            $table->integer('administration')->default(0)->after('marketing');
            $table->integer('professionalism')->default(0)->after('administration');
            $table->integer('integrity')->default(0)->after('professionalism');
            $table->integer('attendance')->default(0)->after('integrity');
            $table->integer('created_user')->default(0)->after('attendance');
        });
    }
};
