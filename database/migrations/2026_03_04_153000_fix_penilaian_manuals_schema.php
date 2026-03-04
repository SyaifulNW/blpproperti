<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class FixPenilaianManualsSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Periksa apakah tabel ada. Jika tidak ada, jangan buat di sini, 
        // biarkan migration asli yang berjalan (jika mereka memperbaikinya).
        // Tapi jika tabel ada tapi tidak tercatat di migration, kita perbaiki.
        
        if (Schema::hasTable('penilaian_manuals')) {
            // Pastikan ID adalah AUTO_INCREMENT
            try {
                // Gunakan Raw SQL karena ini cara paling pasti di MySQL/MariaDB untuk memaksa AUTO_INCREMENT
                DB::statement("ALTER TABLE penilaian_manuals MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
            } catch (\Exception $e) {
                // Jika sudah primary key, coba modify saja tanpa PRIMARY KEY keyword
                try {
                    DB::statement("ALTER TABLE penilaian_manuals MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT");
                } catch (\Exception $ex) {
                    // Log or ignore if already correct
                }
            }

            // 2. Perbaiki total_nilai agar mendukung desimal (misal 2.8)
            // Dari Integer ke Decimal(10,2)
            DB::statement("ALTER TABLE penilaian_manuals MODIFY total_nilai DECIMAL(10,2) DEFAULT 0");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('penilaian_manuals')) {
            DB::statement("ALTER TABLE penilaian_manuals MODIFY total_nilai INT DEFAULT 0");
        }
    }
}
