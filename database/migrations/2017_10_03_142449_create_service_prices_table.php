<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicePricesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_prices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('price_level_id',false,true);
            $table->integer('service_id',false,true);
            $table->decimal('price',8,2)->nullable();
            $table->tinyInteger('inactive')->default(0)->nullable();
            $table->date('from');
            $table->timestamps();

            $table->foreign('price_level_id')->references('id')->on('price_levels')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_prices');
    }
}
