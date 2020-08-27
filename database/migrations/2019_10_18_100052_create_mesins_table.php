<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMesinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mesins', function (Blueprint $table) {
            $table->string('kode',50);
            $table->string('lokasi',100);
            $table->string('merek',100);
            $table->string('keterangan',100)->nullable();
            $table->string('ip',30);
            $table->string('key',50)->nullable();
            $table->dateTime('lastlog')->nullable();
            $table->increments('mesin_id');
            $table->softDeletes();
            
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
            
            $table->index('kode');
            $table->index('ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mesins');
    }
}
