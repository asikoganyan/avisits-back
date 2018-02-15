<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('father_name')->nullable();
            $table->enum('sex',['none','male','female'])->nullable();
            $table->date('birthday')->nullable();
            $table->date('email')->nullable();
            $table->integer('card_number')->nullable();
            $table->integer('card_number_optional')->nullable();
            $table->text('comment')->nullable();
            $table->float('decimal')->nullable();
            $table->float('bonuses')->nullable();
            $table->float('invoice_sum')->nullable();
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
        Schema::dropIfExists('clients');
    }
}
