<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesPlanToStatusPesertaEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `data` MODIFY COLUMN `status_peserta` ENUM('alumni', 'peserta_baru', 'sales_plan') DEFAULT 'peserta_baru'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `data` MODIFY COLUMN `status_peserta` ENUM('alumni', 'peserta_baru') DEFAULT 'peserta_baru'");
    }
}
