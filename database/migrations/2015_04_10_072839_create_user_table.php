<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
                    $table->string('photo',50);
                    $table->string('username',50);
                    $table->string('password',60);
                    $table->string('name',255);
                    $table->string('email',50);
                    $table->string('ttd_img',50);
                    $table->string('type',50);  
                    $table->rememberToken();
                    $table->unsignedInteger('perusahaan_id');
                    $table->increments('id');
                    $table->softDeletes();
                    $table->integer('created_by')->nullable();
                    $table->integer('updated_by')->nullable();
                    $table->timestamps();
                    
                    $table->index('username');
                    $table->index('password');
                    $table->index('type');
                    
                    $table->foreign('perusahaan_id')->references('id')->on('master_options');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
