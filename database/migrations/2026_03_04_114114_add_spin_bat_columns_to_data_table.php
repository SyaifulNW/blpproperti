<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpinBatColumnsToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            $table->string('spin')->nullable()->after('survei_lokasi');
            $table->enum('spin_b', ['Ya', 'Tidak'])->default('Tidak')->after('spin');
            $table->enum('spin_a', ['Ya', 'Tidak'])->default('Tidak')->after('spin_b');
            $table->enum('spin_t', ['Ya', 'Tidak'])->default('Tidak')->after('spin_a');
            $table->timestamp('spin_updated_at')->nullable()->after('spin_t');
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
            $table->dropColumn(['spin', 'spin_b', 'spin_a', 'spin_t', 'spin_updated_at']);
        });
    }
}
