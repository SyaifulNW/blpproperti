<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;
    protected $table = 'data'; // Specify the table name if it doesn't follow Laravel's naming convention
    protected $fillable = [
        'nama',
        'leads',
        'leads_custom',
        'provinsi_id',
        'provinsi_nama',
        'kota_id',
        'kota_nama',
        'jenis_bisnis',
        'nama_bisnis',
        'no_wa',
        'status',
        'nominal',
        'situasi_bisnis',
        'kendala',
        'kelas_id', 'created_by', 'created_by_role',
        'ikut_kelas',
        'survei_lokasi',
        'spin',
        'spin_b',
        'spin_a',
        'spin_t',
        'spin_updated_at',
        'spin1_wa', 'spin1_telp',
        'spin2_wa', 'spin2_telp',
        'spin3_wa', 'spin3_telp',
        'spin4_wa', 'spin4_telp',
        'spin5_wa', 'spin5_telp',
    ];
    public function kelas()
    {
       return $this->belongsTo(Kelas::class, 'kelas_id');
    }
    public function provinsi()
    {
        return $this->belongsTo('App\Models\Provinsi', 'provinsi_id');
    }
    public function kota()
    {
        return $this->belongsTo('App\Models\Kota', 'kota_id');
    
}
    public function getLeadsAttribute($value)
    {
        return ucfirst($value);
    }
    /*
    public function jenisBisnis()
    {
        return $this->belongsTo('App\Models\jenisbisnis', 'jenis_bisnis');
    }
    */
    public function salesplan()
    {
        return $this->hasMany('App\Models\SalesPlan', 'data_id');
    }
    
    public function createdBy()
{
    return $this->belongsTo(User::class, 'created_by');
}

    public function spinInteractions()
    {
        return $this->hasMany(SpinInteraction::class, 'data_id')->orderBy('spin_number', 'asc');
    }
}

