<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJabatanTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('jabatan', function(Blueprint $table)
		{
                    $table->string('jabatan_kode',20);
                    $table->string('jabatan_nama',100);
                    $table->increments('jabatan_id');
                    $table->integer('hapus')->default(1);
                    
                    $table->integer('created_by')->nullable();
                    $table->integer('modified_by')->nullable();
                    $table->timestamps();
                    
                    $table->index('jabatan_kode');
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
		Schema::drop('jabatan');
	}

}
