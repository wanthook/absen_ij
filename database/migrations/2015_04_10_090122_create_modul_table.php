<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('module', function(Blueprint $table)
		{
                    $table->string('nama',50);
                    $table->string('deskripsi',250)->nullable();
                    $table->string('route',200);
                    $table->string('param',100)->nullable();
                    $table->integer('parent');
                    $table->string('icon',150)->nullable();
                    $table->integer('order');
                    $table->increments('id');
                    $table->softDeletes();
                    $table->integer('created_by')->nullable();
                    $table->integer('updated_by')->nullable();
                    $table->timestamps();
                    
                    $table->index('nama');
                    $table->index('parent');
                    $table->index('order');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('modul');
	}

}
