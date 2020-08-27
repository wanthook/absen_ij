<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProsesabsensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prosesabsens', function (Blueprint $table) {
            $table->integer('karyawan_id');
            $table->integer('alasan_id')->nullable();
            $table->date('tanggal');
            $table->time('jam_masuk');
            $table->time('jam_keluar');
            $table->string('kode_jam_kerja', 20);
            $table->time('jadwal_jam_masuk');
            $table->time('jadwal_jam_keluar');
            $table->integer('libur');
            $table->integer('libur_nasional');
            $table->integer('pendek');
            $table->integer('mangkir');
            $table->integer('lembur_off');
            $table->time('jam_masuk_random')->nullable();
            $table->time('jam_keluar_random')->nullable();
                        
            $table->timestamps();
            
            $table->integer('created_by')->nullable();
            
            $table->index('karyawan_id');
            $table->index('alasan_id');
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
        Schema::dropIfExists('prosesabsens');
    }
}
