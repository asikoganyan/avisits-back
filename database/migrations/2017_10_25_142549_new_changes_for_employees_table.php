<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewChangesForEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
//            $table->integer('position_id',false,true)->after('chain_id')->unsigned()->nullable();
//            $table->index('position_id');
//            $table->string('public_position',255)->after('position_id')->nullable();
//            $table->foreign('position_id')->references('id')->on('positions')->onDelete('RESTRICT');
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
