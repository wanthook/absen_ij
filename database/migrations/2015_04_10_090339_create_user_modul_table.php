<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserModulTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('module_user', function(Blueprint $table)
            {
                $table->unsignedInteger('module_id');
                $table->unsignedInteger('user_id');
                
                $table->foreign('module_id')->references('id')->on('module');
                $table->foreign('user_id')->references('id')->on('users');
            });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('module_user');
	}

}
