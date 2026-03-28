<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpinInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spin_interactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_id');
            $table->integer('spin_number')->default(1);
            $table->boolean('wa')->default(false);
            $table->boolean('telp')->default(false);
            $table->text('hasil_fu')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->timestamps();

            $table->foreign('data_id')->references('id')->on('data')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spin_interactions');
    }
}
