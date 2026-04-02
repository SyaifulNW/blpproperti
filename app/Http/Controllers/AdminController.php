<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Data;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        $now = \Carbon\Carbon::now();
        $bulanNum = $now->month;
        $tahun = $now->year;

        // 1. Total Penjualan Bulanan
        $totalBulanan = \App\Models\SalesPlan::whereMonth('updated_at', $bulanNum)
            ->whereYear('updated_at', $tahun)
            ->where('status', 'sudah_transfer')
            ->sum('nominal');

        // 2. Total Penjualan Tahunan
        $totalTahunan = \App\Models\SalesPlan::whereYear('updated_at', $tahun)
            ->where('status', 'sudah_transfer')
            ->sum('nominal');

        // 3. YoY Growth
        $totalLalu = \App\Models\SalesPlan::whereYear('updated_at', $tahun - 1)
            ->where('status', 'sudah_transfer')
            ->sum('nominal');
        $yoyGrowth = $totalLalu > 0 ? round((($totalTahunan - $totalLalu) / $totalLalu) * 100, 1) : 15.0;

        // 4. Rata-rata Penjualan / Hari
        $avgDay = $now->day > 0 ? $totalBulanan / $now->day : 0;

        // 5. Total Pelanggan Aktif
        $totalPelanggan = \App\Models\Data::count();
        $pelangganBaru = \App\Models\Data::whereMonth('created_at', $bulanNum)
            ->whereYear('created_at', $tahun)
            ->count();

        // 6. Target Global
        $targetReal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 1250000000;

        // 7. Grafik Pertumbuhan (Monthly)
        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = (float) \App\Models\SalesPlan::whereMonth('updated_at', $m)
                ->whereYear('updated_at', $tahun)
                ->where('status', 'sudah_transfer')
                ->sum('nominal');
        }

        // 8. List Sales & Omset (Ranking)
        $salesRanking = User::whereIn('role', ['cs', 'cs-smi', 'cs-mbc', 'marketing'])
            ->get()
            ->map(function ($user) use ($bulanNum, $tahun, $targetReal) {
                $user->omset_bulan_ini = \App\Models\SalesPlan::where('created_by', $user->id)
                    ->whereMonth('updated_at', $bulanNum)
                    ->whereYear('updated_at', $tahun)
                    ->where('status', 'sudah_transfer')
                    ->sum('nominal');

                $user->target_omset = $targetReal;
                $user->kekurangan = max(0, $targetReal - $user->omset_bulan_ini);
                $user->persentase = $targetReal > 0 ? round(($user->omset_bulan_ini / $targetReal) * 100, 1) : 0;
                $user->status_capaian = ($user->omset_bulan_ini >= $targetReal) ? 'Tercapai' : 'Belum Tercapai';

                return $user;
            })->sortByDesc('omset_bulan_ini');

        return view('administrator', compact(
            'totalBulanan',
            'totalTahunan',
            'yoyGrowth',
            'avgDay',
            'totalPelanggan',
            'pelangganBaru',
            'targetReal',
            'chartData',
            'salesRanking'
        ));
    }

    public function salesplan($id)
    {
        $cs = User::findOrFail($id);
        $salesplan = $cs->salesplans; // relasi ke tabel salesplan
        return view('admin.cs.salesplan', compact('cs', 'salesplan'));
    }

    public function database($id)
    {
        $cs = User::findOrFail($id);
        $database = $cs->databases; // relasi ke tabel database peserta
        return view('admin.cs.database', compact('cs', 'database'));
    }
}
