<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->unsignedInteger('karyawan_id');
            $table->unsignedInteger('jenis_id');
            $table->date('tanggal');
            $table->string('nilai', 200);
            $table->enum('tipe', ['debit', 'kredit']); 	
            $table->unsignedInteger('created_by');
            $table->dateTime('created_at');
            $table->foreign('jenis_id')->references('id')->on('master_options');
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
        Schema::dropIfExists('salaries');
    }
}
