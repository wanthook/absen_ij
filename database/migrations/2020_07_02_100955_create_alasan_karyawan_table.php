<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlasanKaryawanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alasan_karyawan', function (Blueprint $table) {
            $table->date('tanggal');
            $table->unsignedInteger('alasan_id');
            $table->unsignedInteger('karyawan_id');
            $table->softDeletes();
            
            $table->integer('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('alasan_id')->references('id')->on('alasans');
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
        Schema::dropIfExists('alasan_karyawan');
    }
}
