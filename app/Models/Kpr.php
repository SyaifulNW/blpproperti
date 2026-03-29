<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpr extends Model
{
    protected $fillable = [
        'salesplan_id',
        'nama',
        'phone',
        'bf_tanggal_bayar',
        'bf_nominal',
        'bf_unit',
        'bf_deadline_dp',
        'berkas_ktp_kk',
        'berkas_slip_gaji',
        'berkas_rek_koran',
        'berkas_npwp',
        'berkas_status',
        'berkas_tanggal_submit',
        'bank_tujuan',
        'bank_tanggal_pengajuan',
        'bank_status',
        'appraisal_tanggal',
        'appraisal_hasil_nilai',
        'appraisal_catatan',
        'sp3k_status',
        'sp3k_plafon',
        'sp3k_tenor',
        'sp3k_cicilan',
        'akad_tanggal',
        'akad_notaris',
        'akad_dp_lunas',
        'akad_dokumen_lengkap',
        'serah_terima_pencairan',
        'serah_terima_status_unit',
        'serah_terima_kunci',
        'tahap_posisi',
        'status_global',
        'next_action',
        'catatan_umum',
        'created_by'
    ];

    public function salesplan()
    {
        return $this->belongsTo(SalesPlan::class, 'salesplan_id');
    }
}
