<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKaryawanKeluargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_keluargas', function (Blueprint $table) {
            $table->string('nama',150);
            $table->string('ktp',50);
            $table->string('tempat_lahir',100);
            $table->date('tanggal_lahir');
            $table->string('telpon',50)->nullable();
            $table->string('kota',100);
            $table->string('kode_pos',10);
            $table->string('alamat',200);
            
            $table->unsignedInteger('relasi_id');
            $table->unsignedInteger('jenis_kelamin_id');
            $table->unsignedInteger('agama_id');
            $table->unsignedInteger('karyawan_id');
            
            $table->softDeletes();
            $table->increments('id');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('nama');
            
            $table->foreign('relasi_id')->references('id')->on('master_options');
            $table->foreign('jenis_kelamin_id')->references('id')->on('master_options');
            $table->foreign('agama_id')->references('id')->on('master_options');
            $table->foreign('karyawan_id')->references('id')->on('master_options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('karyawan_keluargas');
    }
}
