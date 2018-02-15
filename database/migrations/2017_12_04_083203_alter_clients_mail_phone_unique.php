<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterClientsMailPhoneUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table('clients', function (Blueprint $table) {
            $table->bigInteger('card_number',false,true)->nullable()->change();
            $table->bigInteger('card_number_optional',false,true)->nullable()->change();
            $table->string('email',100)->nullable()->change();
            $table->unique('email');
            $table->string('phone')->unique()->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_email_unique');
            $table->drop('phone');
        });
    }
}
