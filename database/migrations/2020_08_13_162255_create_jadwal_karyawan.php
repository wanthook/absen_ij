<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJadwalKaryawan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwal_karyawan', function (Blueprint $table) {
            $table->date('tanggal');
            $table->unsignedInteger('jadwal_id');
            $table->unsignedInteger('karyawan_id');

            $table->foreign('jadwal_id')->references('id')->on('jadwals');
            $table->foreign('karyawan_id')->references('id')->on('karyawans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jadwal_karyawan');
    }
}
