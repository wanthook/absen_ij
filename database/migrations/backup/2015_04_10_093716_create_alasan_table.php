<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlasanTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alasan', function(Blueprint $table)
		{
                    $table->string('alasan_kode',20);
                    $table->string('alasan_nama',100);
                    $table->increments('alasan_id');
                    $table->integer('hapus')->default(1);
                    
                    $table->integer('created_by')->nullable();
					$table->integer('modified_by')->nullable();
					$table->timestamps();
                    
                    $table->index('alasan_kode');
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
		Schema::drop('alasan');
	}

}
