<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackingColumnsToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->enum('berhasil_spin', ['Ya', 'Tidak'])->nullable()->after('status_peserta');
            $table->enum('ikut_zoom', ['Ya', 'Tidak'])->nullable()->after('berhasil_spin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->dropColumn(['berhasil_spin', 'ikut_zoom']);
        });
    }
}
