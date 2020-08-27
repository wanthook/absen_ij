<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->string('pin',10);
            $table->date('tanggal');
            $table->integer('verivied');
            $table->integer('status');
            $table->integer('workcode');
            $table->unsignedInteger('mesin_id');
            $table->unsignedInteger('karyawan_id');
            $table->increments('id');
            
            $table->integer('created_by')->nullable();
            $table->timestamps();            
            $table->softDeletes();
            
            $table->index('pin');
            $table->index('tanggal');
            
            $table->foreign('mesin_id')->references('id')->on('mesins');
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
        Schema::dropIfExists('activities');
    }
}
