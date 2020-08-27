<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLiburTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('libur', function(Blueprint $table)
		{
                    $table->date('tanggal');
                    $table->string('keterangan',255);
                    $table->increments('id');
                    $table->softDeletes();                    
                    $table->integer('created_by')->nullable();
                    $table->integer('updated_by')->nullable();
                    $table->timestamps();
                    
                    $table->index('tanggal');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('libur');
	}

}
