<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterChainsNewWidgetFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chains', function (Blueprint $table) {
            $table->tinyInteger('w_group_by_category',false,true)->default(0)->after('w_color');;
            $table->tinyInteger('w_show_any_employee',false,true)->default(0)->after('w_group_by_category');
            $table->tinyInteger('w_step_display',false,true)->nullable()->default(15)->after('w_show_any_employee');
            $table->tinyInteger('w_step_search',false,true)->nullable()->default(0)->after('w_step_display');
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
