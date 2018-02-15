<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToChainForWigdget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chains', function (Blueprint $table) {
            $table->string('w_color',255)->nullable()->after('user_id');
            $table->tinyInteger('w_let_check_steps',false,true)->nullable()->after('w_color');
            $table->string('w_steps_g',255)->nullable()->after('w_let_check_steps');
            $table->string('w_steps_service',255)->nullable()->after('w_steps_g');
            $table->string('w_steps_employee',255)->nullable()->after('w_steps_service');
            $table->enum('w_contact_step',['at_first','after_address','at_the_end'])->nullable()->after('w_steps_employee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chains', function (Blueprint $table) {
            //
        });
    }
}
