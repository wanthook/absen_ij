<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKaryawanPendidikansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawan_pendidikans', function (Blueprint $table) {
            $table->integer('tahun_masuk')->nullable();
            $table->integer('tahun_lulus')->nullable();
            $table->string('nama_sekolah',200);
            $table->string('jurusan',150)->nullable();
            
            $table->unsignedInteger('pendidikan_id');
            $table->unsignedInteger('karyawan_id');
            
            $table->softDeletes();
            $table->increments('id');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('karyawan_id')->references('id')->on('karyawans');
            $table->foreign('pendidikan_id')->references('id')->on('master_options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('karyawan_pendidikans');
    }
}
