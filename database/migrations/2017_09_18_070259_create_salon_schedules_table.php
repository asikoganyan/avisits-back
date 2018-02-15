<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalonSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salon_schedules', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('salon_id',false, true);
            $table->time('start')->nullabel();
            $table->time('end')->nullabel();
            $table->tinyInteger('num_of_day'); /* the number of day of the week ( 1 - 7) */
            $table->tinyInteger('working_status',false,true)->default(1);
            $table->timestamps();

            $table->foreign('salon_id')->references('id')->on('salons')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salon_schedules');
    }
}
