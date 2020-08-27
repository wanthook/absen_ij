<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJadwalTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('jadwals', function(Blueprint $table)
            {
		$table->string('kode', 100);
                $table->string('tipe', 100);
                $table->string('deskripsi', 250);
                $table->increments('id');
                $table->softDeletes();
                $table->integer('created_by')->nullable();
                $table->integer('updated_by')->nullable();
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
		Schema::drop('jadwal');
	}

}
