<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJadwalJamKerjaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jadwal_jam_kerja', function (Blueprint $table) {
            $table->integer('day')->nullable();
            $table->date('tanggal')->nullable();
            $table->unsignedInteger('jam_kerja_id');
            $table->unsignedInteger('jadwal_id');
            
            $table->increments('id');
            $table->softDeletes();
            
            $table->integer('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            
            $table->index('day');
            $table->index('tanggal');
            
            $table->foreign('jam_kerja_id')->references('id')->on('jam_kerjas');
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
        Schema::dropIfExists('jadwal_jam_kerja');
    }
}
