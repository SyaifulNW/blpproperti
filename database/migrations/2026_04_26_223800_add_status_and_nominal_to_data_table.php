<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndNominalToDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('data', function (Blueprint $table) {
            if (!Schema::hasColumn('data', 'status')) {
                $table->string('status')->default('Cold')->after('no_wa');
            }
            if (!Schema::hasColumn('data', 'nominal')) {
                $table->decimal('nominal', 15, 2)->default(0)->after('status');
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
            $table->dropColumn(['status', 'nominal']);
        });
    }
}
