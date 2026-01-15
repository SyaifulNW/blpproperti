<?php

namespace App\Http\Controllers;

use App\Models\PesertaSmi;
use Illuminate\Http\Request;

class PembelajaranSiswaController extends Controller
{
    public function index()
    {
        $peserta = PesertaSmi::orderBy('nama')->get();
        return view('admin.pembelajaran-siswa.index', compact('peserta'));
    }
}
