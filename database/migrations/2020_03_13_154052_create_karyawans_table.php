<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKaryawansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->string('pin',10);
            $table->string('nik',20);
            $table->string('nama',150);
            $table->string('foto',50);
            $table->string('email',150);
            $table->string('ktp',50);
            $table->string('tempat_lahir',100);
            $table->date('tanggal_lahir');
            $table->string('telpon',50)->nullable();
            $table->string('hp',50);
            $table->string('kota',100);
            $table->string('kode_pos',10);
            $table->string('alamat',200);
            $table->date('tanggal_masuk');
            $table->date('tanggal_probation')->nullable();
            $table->date('tanggal_kontrak')->nullable();
            $table->string('ukuran_baju',5)->nullable();
            $table->string('ukuran_sepatu',5)->nullable();
            
            $table->unsignedInteger('status_karyawan_id');
            $table->unsignedInteger('jenis_kelamin_id');
            $table->unsignedInteger('perkawinan_id');
            $table->unsignedInteger('darah_id');
            $table->unsignedInteger('jabatan_id');
            $table->unsignedInteger('divisi_id');
            $table->unsignedInteger('perusahaan_id');
            $table->unsignedInteger('agama_id');
            $table->unsignedInteger('jadwal_id');
            
            $table->softDeletes();
            $table->increments('id');

            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            $table->index('pin');
            $table->index('nik');
            $table->index('ktp');
            $table->index('nama');
            $table->index('email');
            $table->index('tanggal_masuk');
            $table->index('tanggal_probation');
            $table->index('tanggal_kontrak');
            
            $table->foreign('status_karyawan_id')->references('id')->on('master_options');
            $table->foreign('jenis_kelamin_id')->references('id')->on('master_options');
            $table->foreign('perkawinan_id')->references('id')->on('master_options');
            $table->foreign('darah_id')->references('id')->on('master_options');
            $table->foreign('jabatan_id')->references('id')->on('master_options');
            $table->foreign('divisi_id')->references('id')->on('master_options');
            $table->foreign('perusahaan_id')->references('id')->on('master_options');
            $table->foreign('agama_id')->references('id')->on('master_options');
            $table->foreign('jadwal_id')->references('id')->on('jadwals');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('karyawans');
    }
}
