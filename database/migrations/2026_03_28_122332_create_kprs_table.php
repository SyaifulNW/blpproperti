<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKprsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kprs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salesplan_id')->nullable();
            $table->string('nama')->nullable();
            $table->string('phone')->nullable();

            // 4. Booking Fee
            $table->date('bf_tanggal_bayar')->nullable();
            $table->decimal('bf_nominal', 15, 2)->nullable();
            $table->string('bf_unit')->nullable();
            $table->date('bf_deadline_dp')->nullable();

            // 5. Pengumpulan Berkas
            $table->boolean('berkas_ktp_kk')->default(0);
            $table->boolean('berkas_slip_gaji')->default(0);
            $table->boolean('berkas_rek_koran')->default(0);
            $table->boolean('berkas_npwp')->default(0);
            $table->string('berkas_status')->default('Belum Lengkap'); // Lengkap / Belum Lengkap
            $table->date('berkas_tanggal_submit')->nullable();

            // 6. Pengajuan Bank
            $table->string('bank_tujuan')->nullable();
            $table->date('bank_tanggal_pengajuan')->nullable();
            $table->string('bank_status')->nullable(); // Proses / Revisi / Pending

            // 7. Appraisal
            $table->date('appraisal_tanggal')->nullable();
            $table->decimal('appraisal_hasil_nilai', 15, 2)->nullable();
            $table->text('appraisal_catatan')->nullable();

            // 8. SP3K / Approval
            $table->string('sp3k_status')->nullable(); // Approve / Reject
            $table->decimal('sp3k_plafon', 15, 2)->nullable();
            $table->integer('sp3k_tenor')->nullable(); // Dalam bulan/tahun
            $table->decimal('sp3k_cicilan', 15, 2)->nullable();

            // 9. Akad Kredit
            $table->date('akad_tanggal')->nullable();
            $table->string('akad_notaris')->nullable();
            $table->boolean('akad_dp_lunas')->default(0);
            $table->boolean('akad_dokumen_lengkap')->default(0);

            // 10. Pencairan & Serah Terima
            $table->date('serah_terima_pencairan')->nullable();
            $table->string('serah_terima_status_unit')->nullable();
            $table->date('serah_terima_kunci')->nullable();

            // Monitoring Global
            $table->string('tahap_posisi')->default('Booking Fee');
            $table->string('status_global')->default('Ongoing'); // Ongoing, Success, Failed
            $table->string('next_action')->nullable();
            $table->text('catatan_umum')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('salesplan_id')->references('id')->on('salesplans')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('kprs');
    }
}
