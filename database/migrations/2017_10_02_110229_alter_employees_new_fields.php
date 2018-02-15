<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterEmployeesNewFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->date("employment_date")->nullable();
            $table->tinyInteger("dismissed",false,true)->default(0);
            $table->date("dismissed_date")->nullable();
            $table->tinyInteger('displayed_in_records',false, true)->nullable();
            $table->tinyInteger('available_for_online_recording', false, true)->nullable();
            $table->integer('access_profile_id',false,true)->nullable();
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
            //
        });
    }
}
