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
        Schema::table('appraisals', function (Blueprint $table) {
            $table->foreignId('indicator_id')->nullable()->after('employee_id');
            $table->dropColumn(['total_goal_weight', 'total_competency_weight']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropColumn(['indicator_id']);
            $table->integer('total_goal_weight')->after('end_month');
            $table->integer('total_competency_weight')->after('total_goal_overall');
        });
    }
};
