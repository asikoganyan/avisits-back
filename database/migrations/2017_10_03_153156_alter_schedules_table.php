<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['start_time','end_time','day']);
            $table->enum('type',[1,2,3]);
            $table->tinyInteger('working_days',false,true)->nullable();
            $table->tinyInteger('weekend',false,true)->nullable();
            $table->enum('num_of_day',[1,2,3,4,5,6,7])->nullable();
            $table->date('date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['type','working_days','weekend','date']);
        });
    }
}
