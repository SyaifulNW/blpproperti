<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSpinInteractionsToData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            for ($i = 1; $i <= 5; $i++) {
                $table->boolean("spin{$i}_wa")->default(false);
                $table->boolean("spin{$i}_telp")->default(false);
            }
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
            for ($i = 1; $i <= 5; $i++) {
                $table->dropColumn(["spin{$i}_wa", "spin{$i}_telp"]);
            }
        });
    }
}
