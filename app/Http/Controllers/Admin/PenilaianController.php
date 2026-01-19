<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesPlan;
use App\Models\Data;
use App\Models\Kelas;
use PDF;
use Carbon\Carbon;

class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $csId = $user->id;
        $namaUserData = $user->name;

        // ============================
        // FILTER BULAN & TAHUN
        // ============================
        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $bulanNum = intval($bulan);

        $tanggalDipilih = Carbon::createFromDate($tahun, $bulan, 1);

        // ============================
        // 1. HITUNG OMSET REAL
        // ============================
        if ($user->role === 'cs-smi') {
            $kelasOmset = Kelas::where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulanNum) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulanNum)
                      ->where('status', 'sudah_transfer');
                }])
                ->get();
        } else {
            $kelasOmset = Kelas::whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulanNum)
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulanNum) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulanNum);
                }])
                ->get();
        }

        $kelasOmsetFiltered = $kelasOmset->map(function ($kelas) {
            $omset = $kelas->salesplans->sum('nominal');
            $targetGlobal = \App\Models\Setting::where('key', 'target_omset')->value('value') ?? 50000000;
            $target = $targetGlobal / 2;

            $komisiSementara = $omset * 0.01;
            $komisiTotal = $omset >= $target ? $komisiSementara + 300000 : $komisiSementara;

            return [
                'nama_kelas' => $kelas->nama_kelas,
                'tanggal'    => $kelas->tanggal_mulai,
                'omset'      => $omset,
                'target'     => $target,
                'persen'     => $target > 0 ? round(($omset / $target) * 100, 2) : 0,
                'komisi'     => $komisiTotal,
            ];
        });

        $totalOmset = $kelasOmsetFiltered->sum('omset');

        // ============================
        // 2. NILAI OMSET (60%)
        // ============================
        $targetGlobal = 1000000000; // Target 1 Miliar
        $nilaiOmset = $targetGlobal > 0 ? min(60, intval($totalOmset / $targetGlobal * 60)) : 0;

        // ============================
        // 3. CLOSING PAKET (REMOVED)
        // ============================
        $closingPaket = 0;
        $nilaiClosingPaket = 0;

        // ============================
        // 4. DATABASE BARU (20% or 30%)
        // ============================
        $databaseBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->count();

        // Target: 100 Database (Contoh, bisa disesuaikan)
        $bobotDb = 20; 
        $nilaiDatabaseBaru = min($bobotDb, intval($databaseBaru / 100 * $bobotDb));
        

        // ============================
        // 5. NILAI MANUAL (20% or 30%)
        // ============================
        $manual = \App\Models\PenilaianManual::where('user_id', $csId)
                    ->where('bulan', $bulanNum)
                    ->where('tahun', $tahun)
                    ->first();

        $nilaiManualPart = 0;
        $totalSumManual = 0; // Inisialisasi variabel total sum

        if ($manual) {
            // Hitung Total Sum dari 5 aspek
            $totalSumManual = $manual->kerajinan + 
                              $manual->kerjasama + 
                              $manual->tanggung_jawab + 
                              $manual->inisiatif + 
                              $manual->komunikasi;

            // Konversi ke bobot (Total Sum / 500 * Bobot)
            $bobotManual = 20;
            $nilaiManualPart = round(($totalSumManual / 500) * $bobotManual); 
        }

        // ============================
        // 6. TOTAL NILAI (SUM)
        // ============================
        $totalNilai = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru + $nilaiManualPart;
        
        $nilaiSistem = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru; // Untuk info saja

        // ============================
        // 7. CHART & HISTORY
        // ============================
        $labels = [];
        $scores = [];

        $role = $user->role;

        for ($i = 5; $i >= 0; $i--) {
            $dt = Carbon::now()->subMonths($i);
            $labels[] = $dt->format('M Y');

            $scores[] = $this->hitungTotalNilai(
                $csId,
                $namaUserData,
                $dt->month,
                $dt->year,
                $role
            );
        }

        $historyNilai = array_fill(1, 12, 0);

        for ($m = 1; $m <= 12; $m++) {
            $historyNilai[$m] = $this->hitungTotalNilai(
                $csId,
                $namaUserData,
                $m,
                $tahun,
                $role
            );
        }

        // ============================
        // 8. KIRIM KE VIEW
        // ============================
        return view('admin.penilaian.index', compact(
            'bulan',
            'tahun',
            'totalOmset',
            'nilaiOmset',
            'closingPaket',
            'nilaiClosingPaket',
            'databaseBaru',
            'nilaiDatabaseBaru',
            'totalNilai',
            'nilaiSistem',
            'nilaiManualPart',
            'totalSumManual', // Kirim total sum ke view
            'labels',
            'scores',
            'historyNilai',
            'kelasOmsetFiltered',
            'manual'
        ));
    }


    // ======================================================
    // FUNGSI HITUNG TOTAL NILAI (REUSABLE)
    // ======================================================
    private function hitungTotalNilai($csId, $namaUserData, $bulan, $tahun, $role)
    {
        // OMSET (40%)
        if ($role === 'cs-smi') {
            $kelasOmset = Kelas::where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulan)
                      ->where('status', 'sudah_transfer');
                }])
                ->get();
        } else {
            $kelasOmset = Kelas::whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulan)
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulan);
                }])
                ->get();
        }

        $totalOmset = $kelasOmset->sum(fn ($k) => $k->salesplans->sum('nominal'));
        $targetGlobal = 1000000000;
        $nilaiOmset = $targetGlobal > 0 ? min(60, intval($totalOmset / $targetGlobal * 60)) : 0;

        // CLOSING PAKET (REMOVED)
        $nilaiClosing = 0;

        // DATABASE BARU (20% or 30%)
        $dbBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();

        $bobotDb = 20;
        $nilaiDb = min($bobotDb, intval($dbBaru / 100 * $bobotDb));

        // MANUAL (20% or 30%)
        $manual = \App\Models\PenilaianManual::where('user_id', $csId)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->first();

        $nilaiManualPart = 0;
        if ($manual) {
            $sum = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
            $bobotManual = 20;
            $nilaiManualPart = round(($sum / 500) * $bobotManual);
        }

        return $nilaiOmset + $nilaiClosing + $nilaiDb + $nilaiManualPart;
    }


    // ======================================================
    // EXPORT PDF
    // ======================================================
    public function exportPdf(Request $request)
    {
        $user = auth()->user();
        $csId = $user->id;
        $namaUserData = $user->name;

        $bulan = $request->bulan ?? date('m');
        $tahun = $request->tahun ?? date('Y');
        $role = $user->role;

        // Calculate all data for PDF
        // Re-using logic from index but for the specific month/year
        
        // 1. HITUNG OMSET
        if ($role === 'cs-smi') {
            $kelasOmset = Kelas::where('nama_kelas', 'like', '%Start-Up Muda Indonesia%')
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulan)
                      ->where('status', 'sudah_transfer');
                }])
                ->get();
        } else {
            $kelasOmset = Kelas::whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulan)
                ->with(['salesplans' => function ($q) use ($csId, $tahun, $bulan) {
                    $q->where('created_by', $csId)
                      ->whereYear('updated_at', $tahun)
                      ->whereMonth('updated_at', $bulan);
                }])
                ->get();
        }
        $totalOmset = $kelasOmset->sum(fn ($k) => $k->salesplans->sum('nominal'));
        $targetOmset = 1000000000;
        $nilaiOmset = min(60, intval($totalOmset / $targetOmset * 60));

        // 2. DATABASE BARU
        $databaseBaru = Data::where('created_by', $namaUserData)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count();
        $nilaiDatabaseBaru = min(20, intval($databaseBaru / 100 * 20));

        // 3. PENILAIAN MANUAL
        $manual = \App\Models\PenilaianManual::where('user_id', $csId)
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->first();
        $totalSumManual = 0;
        $nilaiManualPart = 0;
        if ($manual) {
            $totalSumManual = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
            $nilaiManualPart = round(($totalSumManual / 500) * 20);
        }

        $totalNilai = $nilaiOmset + $nilaiDatabaseBaru + $nilaiManualPart;

        $data = [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'totalOmset' => $totalOmset,
            'nilaiOmset' => $nilaiOmset,
            'databaseBaru' => $databaseBaru,
            'nilaiDatabaseBaru' => $nilaiDatabaseBaru,
            'totalSumManual' => $totalSumManual,
            'nilaiManualPart' => $nilaiManualPart,
            'totalNilai' => $totalNilai,
            'userName' => $namaUserData
        ];

        $pdf = PDF::loadView('admin.penilaian.pdf', $data)
                ->setPaper('a4', 'portrait');

        return $pdf->download('penilaian_cs_' . now()->format('Ymd_His') . '.pdf');
    }
}
