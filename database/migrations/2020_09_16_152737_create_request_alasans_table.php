<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRequestAlasansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_alasan', function (Blueprint $table) 
        {
            $table->string('foto',200);
            $table->string('no_dokumen', 100);
            $table->date('tanggal');
            $table->enum('status', ['new', 'send','approve', 'declined', 'return']);
            $table->unsignedInteger('approved_by')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->unsignedInteger('declined_by')->nullable();
            $table->dateTime('declined_at')->nullable();
            $table->text('catatan')->nullable();
            $table->increments('id');
            
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();            
            $table->softDeletes();
                        
            $table->index('no_dokumen');
            $table->index('tanggal');
            $table->index('status');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_alasans');
    }
}
