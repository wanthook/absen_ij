<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGolonganKaryawanLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('golongan_karyawan_log', function (Blueprint $table) {
            $table->date('tanggal');
            $table->integer('karyawan_id')->unsigned();
            $table->integer('golongan_id')->unsigned();
            $table->string('keterangan', 200)->nullable();
            
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('karyawan_id');
            $table->index('golongan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('golongan_karyawan_log');
    }
}
