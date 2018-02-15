<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalonEmployeeServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salon_employee_services', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shm_id',false,true);
            $table->integer('service_id',false,true);
            $table->decimal('price',10,2);
            $table->tinyInteger('duration',false,true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salon_employee_services');
    }
}
