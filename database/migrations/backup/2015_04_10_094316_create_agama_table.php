<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgamaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('agama', function(Blueprint $table)
		{
			$table->string('kode',20);
			$table->increments('agama_id');
			$table->integer('hapus')->default(1);
			
			$table->integer('created_by')->nullable();
			$table->integer('modified_by')->nullable();
			$table->timestamps();
			
			$table->index('kode');
			$table->index('hapus');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('agama');
	}

}
