<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestAlasanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_alasan_detail', function (Blueprint $table) {
            $table->unsignedInteger('karyawan_id');
            $table->unsignedInteger('alasan_id');
            $table->unsignedInteger('request_alasan_id');
            $table->enum('status', ['approve', 'declined'])->nullable();
            $table->text('catatan')->nullable();
            
            $table->unsignedInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedInteger('declined_by')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->increments('id');
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();            
            $table->softDeletes();
            
            $table->index('karyawan_id');
            $table->index('alasan_id');
            $table->index('request_alasan_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_alasan_detail');
    }
}
