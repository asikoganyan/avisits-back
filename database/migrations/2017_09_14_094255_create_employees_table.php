<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name',255);
            $table->string('last_name',255);
            $table->string('father_name',255);
            $table->string('photo',255)->nullable();
            $table->enum('sex',['male','female'])->nullable();
            $table->date('birthday')->nullable();
            $table->string('email',255);
            $table->string('phone',255)->nullable();
            $table->string('viber',255)->nullable();
            $table->string('whatsapp',255)->nullable();
            $table->text('address')->nullable();
            $table->bigInteger('card_number',false,true)->nullable();
            $table->bigInteger('card_number_optional',false,true)->nullable();
            $table->decimal('deposit',10,2)->nullable();
            $table->decimal('bonuses',10,2)->nullable();
            $table->decimal('invoice_sum',10,2)->nullable();
            $table->integer('position_id',false,true)->nullable();
            $table->string('public_position',255)->nullable();
            $table->text('comment')->nullable();
            $table->integer('chain_id',false,true);
            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions')->onDelete('RESTRICT');
            $table->foreign('chain_id')->references('id')->on('chains')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
