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
            $table->dropColumn([
                'branch', 'employee', 'rating', 'customer_experience', 'marketing', 'administration',
                'appraisal_date', 'professionalism', 'integrity', 'attendance', 'remark',
            ]);
            $table->foreignId('employee_id')->after('id');
            $table->foreignId('created_by')->change()->after('employee_id');
            $table->date('start_month')->nullable()->after('created_by');
            $table->date('end_month')->nullable()->after('start_month');
            $table->integer('total_goal_weight')->after('end_month');
            $table->integer('total_goal_score')->after('total_goal_weight');
            $table->double('total_goal_overall', 8, 2)->after('total_goal_score');
            $table->integer('total_competency_weight')->after('total_goal_overall');
            $table->integer('total_competency_score')->after('total_competency_weight');
            $table->double('total_competency_overall', 8, 2)->after('total_competency_score');
            $table->double('total_apprisal', 8, 2)->after('total_competency_overall');
            $table->string('category')->nullable()->after('total_apprisal');
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
            $table->dropColumn([
                'employee_id', 'start_month', 'end_month', 'total_goal_weight',
                'total_goal_score', 'total_goal_overall', 'total_competency_weight', 'total_competency_score',
                'total_competency_overall', 'total_apprisal', 'category'
            ]);
            $table->integer('branch')->default(0)->after('id');
            $table->integer('employee')->default(0)->after('branch');
            $table->string('rating')->after('employee');
            $table->string('appraisal_date')->after('rating');
            $table->integer('customer_experience')->default(0)->after('appraisal_date');
            $table->integer('marketing')->default(0)->after('customer_experience');
            $table->integer('administration')->default(0)->after('marketing');
            $table->integer('professionalism')->default(0)->after('administration');
            $table->integer('integrity')->default(0)->after('professionalism');
            $table->integer('attendance')->default(0)->after('integrity');
            $table->text('remark')->nullable()->after('attendance');
            $table->integer('created_by')->change()->after('id');
        });
    }
};
