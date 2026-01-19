<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\SalesPlan;
use App\Models\Activity;
use App\Models\DailyActiviti;
use App\Models\Data;
use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // ====================== 📅 FILTER BULAN ======================
        $bulan = $request->input('bulan') ?? Carbon::now()->format('Y-m');
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $tahun = $carbonBulan->year;
        $bulanNum = $carbonBulan->month;

        // ====================== 👤 USER LOGIN ======================
        $csId   = auth()->id();
        $csName = auth()->user()->name;

        // ====================== 🔔 NOTIFIKASI ======================
        $notifikasi = Notifikasi::where('user_id', $csId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $notifCount = Notifikasi::where('user_id', $csId)
            ->where('is_read', false)
            ->count();

        // ====================== 💰 OMSET & KOMISI ======================
        $isCsSmi = auth()->user()->role === 'cs-smi';
        $isCsMbc = auth()->user()->role === 'cs-mbc';

        // Ambil SEMUA produk dari Kelas
        $allProducts = Kelas::all();

        if ($isCsSmi) {
            // Khusus CS SMI: Filter salesplans sesuai CS dan waktu
            $allProducts = $allProducts->filter(function($q) {
                return \Illuminate\Support\Str::contains($q->nama_kelas, 'Start-Up Muda Indonesia') || \Illuminate\Support\Str::contains($q->nama_kelas, 'Start-Up Muslim Indonesia');
            });
        }

        $kelasOmsetFiltered = $allProducts->map(function ($kelas) use ($csId, $tahun, $bulanNum) {
            // Hitung omset untuk produk ini pada bulan yang dipilih
            $omset = $kelas->salesplans()
                ->where('created_by', $csId)
                ->whereYear('updated_at', $tahun)
                ->whereMonth('updated_at', $bulanNum)
                ->where('status', 'sudah_transfer')
                ->sum('nominal');

            $target = 500000000; // Target 500 juta tiap produk

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
        })->values();

        $totalKomisi = $kelasOmsetFiltered->sum('komisi');

        // ====================== 📊 PERHITUNGAN NILAI HASIL CS ======================
     

        // ====================== 📈 LEADS ======================
        $leads = SalesPlan::select('status', DB::raw('count(*) as total'))
            ->where('created_by', $csId)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulanNum)
            ->groupBy('status')
            ->pluck('total', 'status');

        $cold           = $leads['cold'] ?? 0;
        $tertarik       = $leads['tertarik'] ?? 0;
        $mau_transfer   = $leads['mau_transfer'] ?? 0;
        $sudah_transfer = $leads['sudah_transfer'] ?? 0;
        $no             = $leads['no'] ?? 0;

        $totalLeadAktif = $cold + $tertarik + $mau_transfer + $sudah_transfer + $no;

        // ====================== ⚙️ KPI ======================
        $hariKerja = 0;
        for ($d = 1; $d <= $carbonBulan->daysInMonth; $d++) {
            $day = Carbon::create($tahun, $bulanNum, $d);
            if ($day->dayOfWeek != Carbon::SUNDAY) $hariKerja++;
        }

        $activities = Activity::with('kategori')
            ->orderBy('categories_id')
            ->get()
            ->groupBy('categories_id');

        $categoryKpiWeights = [
            'Aktivitas Pribadi' => 10,
            'Aktivitas Mencari Leads' => 20,
            'Aktivitas Memprospek' => 20,
            'Aktivitas Closing' => 40,
            'Aktivitas Merawat Customer' => 10,
        ];

        $kpiData = [];
        $totalKpi = 0;
        $totalBobot = 0;

        foreach ($activities as $kategoriId => $list) {

            $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
            $activityPercents = [];

            foreach ($list as $act) {
                $targetDaily = (float) ($act->target_daily ?? 0);
                $targetBulanan = $targetDaily * $hariKerja;

                $totalRealisasi = (float) DailyActiviti::where('user_id', $csId)
                    ->where('activity_id', $act->id)
                    ->whereMonth('tanggal', $bulanNum)
                    ->whereYear('tanggal', $tahun)
                    ->sum('realisasi');

                $percent = 0;
                if ($targetBulanan > 0) {
                    $percent = ($totalRealisasi / $targetBulanan) * 100;
                    if ($percent > 100) $percent = 100;
                }

                $activityPercents[] = $percent;
            }

            $skorKategori = count($activityPercents)
                ? (array_sum($activityPercents) / count($activityPercents))
                : 0;

            $bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;
            $nilaiKategori = ($skorKategori / 100) * $bobotKategori;

            $kpiData[] = [
                'categories_id' => $kategoriId,
                'nama'        => $categoryName,
                'target'      => '100%',
                'bobot'       => $bobotKategori,
                'persentase'  => round($skorKategori, 2),
                'nilai'       => round($nilaiKategori, 2),
            ];

            $totalKpi += $nilaiKategori;
            $totalBobot += $bobotKategori;
        }

        $totalNilai = round($totalKpi, 2);

        // ====================== DATABASE PERSEN ======================
        $databaseTotal = Data::where('created_by', $csId)->count();
        $persentaseDatabaseBaru = $databaseTotal > 0 ? round(($databaseBaru / $databaseTotal) * 100, 2) : 0;
        $persentaseDatabaseLama = 100 - $persentaseDatabaseBaru;


            // ====================== 📊 PERHITUNGAN NILAI HASIL CS ======================
    
    // OMSET
    $totalOmset = $kelasOmsetFiltered->sum('omset'); 
    $targetBulananOmset = 1000000000; // Target Tetap 1 Miliar
    
    // 🔥 Pencapaian Omset untuk ditampilkan di tabel
    $pencapaianOmset = $totalOmset;
    
    // Nilai Omset (0-100)
    $nilaiOmset = $targetBulananOmset > 0
        ? min(100, round(($totalOmset / $targetBulananOmset) * 100))
        : 0;
    
    // Bobot 60%
    $nilaiOmset = round(($nilaiOmset / 100) * 60, 2);
    
    
    // ============ Closing Paket (REMOVED) ============
    $closingPaket = 0;
    $pencapaianClosingPaket = 0;
    $nilaiClosingPaket = 0;
    
    
    // ============ Database Baru ============
    $databaseBaru = Data::where('created_by', $csId)
        ->whereYear('created_at', $tahun)
        ->whereMonth('created_at', $bulanNum)
        ->count();
    
    // 🔥 Pencapaian Database Baru untuk tabel
    $pencapaianDatabaseBaru = $databaseBaru;
    
    // Target 100 Database, Bobot 20%
    $nilaiDatabaseBaru = min(20, round(($databaseBaru / 100) * 20, 2));



    // ====================== MANUAL ASSESSMENT ======================
    $manual = \App\Models\PenilaianManual::where('user_id', $csId)
        ->where('bulan', $bulanNum)
        ->where('tahun', $tahun)
        ->first();

    $nilaiManualPart = 0;
    if ($manual) {
        $sum = $manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi;
        $bobotManual = ($isCsSmi) ? 30 : 20;
        $nilaiManualPart = round(($sum / 500) * $bobotManual);
    }

    // ====================== TOTAL NILAI HASIL ======================
    // Note: $nilaiOmset, $nilaiClosingPaket, $nilaiDatabaseBaru are already calculated above.
    $totalNilaiHasil = $nilaiOmset + $nilaiClosingPaket + $nilaiDatabaseBaru + $nilaiManualPart;


    // ====================== HISTORY KINERJA (12 BULAN) ======================
    $historyNilai = [];
    $role = auth()->user()->role;

    for ($m = 1; $m <= 12; $m++) {
        $historyNilai[$m] = $this->hitungTotalNilaiHasil($csId, auth()->user()->name, $m, $tahun, $role);
    }

    // ====================== RETURN ======================
    return view('home', compact(
        'kelasOmsetFiltered',
        'totalKomisi',

        // Nilai hasil CS
        'nilaiOmset',
        'nilaiClosingPaket',
        'nilaiDatabaseBaru',
        'nilaiManualPart',
        'totalNilaiHasil',
        'manual',
        'historyNilai',

        'cold',
        'tertarik',
        'mau_transfer',
        'sudah_transfer',
        'no',
        'totalLeadAktif',

        'csName',
        'bulan',

        'kpiData',
        'totalBobot',
        'totalNilai',

        'databaseBaru',
        'databaseTotal',
        'persentaseDatabaseBaru',
        'persentaseDatabaseLama',

        'pencapaianOmset',
        'pencapaianClosingPaket',
        'pencapaianDatabaseBaru',
        
        // Closing Paket
        'closingPaket',  

    
        'notifikasi',
        'notifCount'
    ));
}

private function hitungTotalNilaiHasil($csId, $namaUserData, $bulan, $tahun, $role)
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
    
    // Nilai Omset (0-100) -> Bobot 60%
    $nilaiOmsetSkor = $targetGlobal > 0 ? min(100, round(($totalOmset / $targetGlobal) * 100)) : 0;
    $nilaiOmset = round(($nilaiOmsetSkor / 100) * 60, 2);


    // CLOSING PAKET (REMOVED)
    $nilaiClosing = 0;

    // DATABASE BARU (20%)
    $dbBaru = Data::where('created_by', $csId)
        ->whereYear('created_at', $tahun)
        ->whereMonth('created_at', $bulan)
        ->count();

    $dbScore = min(20, round(($dbBaru / 100) * 20, 2));
    $nilaiDb = $dbScore;

    // MANUAL (20%)
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
}
