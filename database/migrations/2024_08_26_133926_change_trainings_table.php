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
        Schema::table('trainings', function (Blueprint $table) {
            // Drop
            $table->dropColumn(['branch', 'trainer', 'performance', 'remarks']);
    
            // Create
            $table->string('name')->after('id');
            $table->bigInteger('approved_by')->nullable()->after('created_by');
            $table->text('result_file')->nullable()->after('approved_by');
            
            // Change
            $table->decimal('training_cost', 11, 2)->default("0")->change();
            $table->string('status')->default('Pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainings', function (Blueprint $table) {
            // Drop
            $table->dropColumn(['name', 'approved_by', 'result_file']);

            // Create
            $table->integer('branch')->after('id');
            $table->integer('trainer')->after('training_type');
            $table->integer('performance')->default(0)->after('description');
            $table->text('remarks')->nullable()->after('status');

            // Change
            $table->float('training_cost')->default(0.00)->change();
            $table->integer('status')->default(0)->change();
        });
    }
};
