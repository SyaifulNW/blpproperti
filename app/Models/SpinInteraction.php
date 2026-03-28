<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpinInteraction extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_id',
        'spin_number',
        'wa',
        'telp',
        'hasil_fu',
        'tindak_lanjut'
    ];

    public function data()
    {
        return $this->belongsTo(Data::class, 'data_id');
    }
}
