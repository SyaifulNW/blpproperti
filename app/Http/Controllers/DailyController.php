<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Activity;        // Master data aktivitas
use App\Models\DailyActiviti;   // Input realisasi harian
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class DailyController extends Controller
{
    public function index(Request $request)
    {
        
        $tanggal = $request->input('tanggal', now()->toDateString());
        $userId  = auth()->id();

        // Ambil master aktivitas, dikelompokkan per kategori (key = categories_id)
        $activities = Activity::orderBy('categories_id')->get()->groupBy('categories_id');

        // Ambil realisasi user untuk TANGGAL yang dipilih (dipakai saat render form)
        $daily = DailyActiviti::where('user_id', $userId)
            ->whereDate('tanggal', $tanggal)
            ->pluck('realisasi', 'activity_id');

        // Persiapan periode (bulan & tahun) untuk rekap bulanan
        $carbon = Carbon::parse($tanggal);
        $bulan   = $carbon->month;
        $tahun   = $carbon->year;

        // Ambil rekap bulanan per aktivitas (cumulative)
        $monthlyTotals = DailyActiviti::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('activity_id')
            ->select('activity_id', \DB::raw('SUM(realisasi) as total'))
            ->pluck('total', 'activity_id');

        // 🔥 AUTO-UPDATE Realisasi untuk "Database Baru" dari Tabel Data (Real-time)
        $dbBaruAct = \App\Models\Activity::where('nama', 'Database Baru')->first();
        if ($dbBaruAct) {
            $realtimeHari = \App\Models\Data::where('created_by', auth()->user()->name)
                ->whereDate('created_at', $tanggal)->count();
            $realtimeBulan = \App\Models\Data::where('created_by', auth()->user()->name)
                ->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)->count();
            
            $daily[$dbBaruAct->id] = $realtimeHari;
            $monthlyTotals[$dbBaruAct->id] = $realtimeBulan;
        }

        // 🔥 AUTO-UPDATE Realisasi untuk "Edukasi & Membangun Hubungan (WA)" dari SpinInteraction
        $edukasiWaAct = \App\Models\Activity::where('nama', 'Edukasi & Membangun Hubungan (WA)')->first();
        if ($edukasiWaAct) {
            $realtimeHari = \App\Models\SpinInteraction::whereHas('data', function($q) {
                $q->where('created_by', auth()->user()->name);
            })->where('wa', 1)->whereDate('created_at', $tanggal)->count();
            
            $realtimeBulan = \App\Models\SpinInteraction::whereHas('data', function($q) {
                $q->where('created_by', auth()->user()->name);
            })->where('wa', 1)->whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)->count();

            $daily[$edukasiWaAct->id] = $realtimeHari;
            $monthlyTotals[$edukasiWaAct->id] = $realtimeBulan;
        }

        // Hari kerja: semua hari kecuali MINGGU (Sabtu masuk kerja)
        $daysInMonth = $carbon->daysInMonth;
        $hariKerja = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $day = Carbon::create($tahun, $bulan, $d);
            if ($day->dayOfWeek != Carbon::SUNDAY) {
                $hariKerja++;
            }
        }

        // Bobot KPI per kategori (disesuaikan dengan spreadsheet baru)
        $categoryKpiWeights = [
            'Aktivitas Mencari Leads' => 25,
            'Aktivitas Memprospek' => 25,
            'Aktivitas Closing' => 25,
            'Aktivitas Merawat Customer' => 25,
        ];

        // Hitung rekap KPI per kategori (kpiData) dan total KPI akhir
        $kpiData = [];
        $totalKpi = 0;
        $totalBobot = 0;

        foreach ($activities as $kategoriId => $list) {
            $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);

            $activityPercents = []; // tiap aktivitas -> persentase cap 100%

            foreach ($list as $act) {
                $targetDaily = (float) ($act->target_daily ?? 0);
                $targetBulanan = $targetDaily * $hariKerja;

                if ($act->nama === 'Transfer Masuk') {
                    $targetDaily = 0;
                    $targetBulanan = 1250000000;
                }

                // Ambil total realisasi dari $monthlyTotals yang sudah di-auto-override jika perlu
                $totalRealisasi = (float) ($monthlyTotals[$act->id] ?? 0);

                $percent = 0;
                if ($targetBulanan > 0) {
                    $percent = ($totalRealisasi / $targetBulanan) * 100;
                    if ($percent > 100) $percent = 100;
                }

                $activityPercents[] = $percent;
            }

            // skor kategori = rata-rata persentase aktivitas di kategori
            $skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;

            // ambil bobot kategori dari mapping; default 0 kalau tidak ditemukan
            $bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;

            // nilai kategori = (skorKategori% / 100) * bobotKategori
            $nilaiKategori = ($skorKategori / 100) * $bobotKategori;

            $kpiData[] = [
                'categories_id' => $kategoriId,
                'nama'        => $categoryName,
                'target'      => '100%',
                'bobot'       => $bobotKategori,
                'persentase'  => round($skorKategori, 2),   // tampilkan sebagai % (mis. 86.00)
                'nilai'       => round($nilaiKategori, 2),
            ];
            $totalKpi += $nilaiKategori;
            $totalBobot += $bobotKategori;
        }

        // Hitung Total Nilai Akhir
        $totalNilai = $totalKpi;

        // Kirim ke view: activities, daily (harian), monthlyTotals, tanggal, dan kpiData + totals
        return view('admin.dailyactivity.index', compact(
            'activities', 'daily', 'monthlyTotals', 'tanggal',
            'kpiData', 'totalNilai', 'totalBobot'
        ));
    }

    public function store(Request $request)
    {
        $tanggal = $request->input('tanggal');

        foreach ($request->realisasi as $activityId => $value) {
            DailyActiviti::updateOrCreate(
                [
                    'user_id'    => auth()->id(),
                    'tanggal'    => $tanggal,
                    'activity_id'=> $activityId,
                ],
                [
                    'realisasi'  => $value ?? 0
                ]
            );
        }

        if ($request->ajax()) {
            return response()->json(['message' => 'Berhasil disimpan']);
        }

        return redirect()->back()->with('success', 'Aktivitas berhasil disimpan!');
    }
    
    public function exportPdf($bulan)
    {
        $carbonBulan = Carbon::createFromFormat('Y-m', $bulan);
        $jumlahHari = $carbonBulan->daysInMonth;
        $tahun = $carbonBulan->year;
        $bulan_int = $carbonBulan->month;

        // Calculate hari kerja (excluding Sundays)
        $hariKerja = 0;
        for ($d = 1; $d <= $jumlahHari; $d++) {
            $day = Carbon::create($tahun, $bulan_int, $d);
            if ($day->dayOfWeek != Carbon::SUNDAY) {
                $hariKerja++;
            }
        }

        $downloadDate = now()->translatedFormat('d F Y H:i');
        $csName = auth()->user()->name ?? 'Unknown User';

        $activities = DailyActiviti::with('activity')
            ->where('user_id', auth()->id()) // atau 'created_by'
            ->whereMonth('tanggal', $carbonBulan->month)
            ->whereYear('tanggal', $carbonBulan->year)
            ->get();

        $allActivities = Activity::all();
        $categories = [];
        $total = [];

        foreach ($allActivities as $act) {
            $kategori = $act->kategori->nama ?? 'Tanpa Kategori';
            if (!isset($categories[$kategori])) {
                $categories[$kategori] = [];
                $total[$kategori] = [
                    'target_daily' => 0,
                    'target_bulanan' => 0,
                    'bobot' => 0,
                    'real' => 0,
                    'nilai' => 0,
                    'harian' => []
                ];
            }

            $tDaily = (float)($act->target_daily ?? 0);
            $tBulanan = $tDaily * $hariKerja;
            if ($act->nama === 'Transfer Masuk') {
                $tDaily = 0;
                $tBulanan = 1250000000;
            }

            $totalRealisasi = $activities->where('activity_id', $act->id)->sum('realisasi');
            
            // 🔥 AUTO-OVERRIDE for PDF too
            if ($act->nama === 'Database Baru') {
                $totalRealisasi = \App\Models\Data::where('created_by', auth()->user()->name)
                    ->whereMonth('created_at', $bulan_int)
                    ->whereYear('created_at', $tahun)
                    ->count();
            }

            // 🔥 AUTO-OVERRIDE for Edukasi WA
            if ($act->nama === 'Edukasi & Membangun Hubungan (WA)') {
                $totalRealisasi = \App\Models\SpinInteraction::whereHas('data', function($q) {
                    $q->where('created_by', auth()->user()->name);
                })->where('wa', 1)->whereMonth('created_at', $bulan_int)->whereYear('created_at', $tahun)->count();
            }

            $persentase = $tBulanan > 0
                ? min(100, ($totalRealisasi / $tBulanan) * 100)
                : 0;
            $nilai = ($persentase / 100) * $act->bobot;

            $harian = [];
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $harian[$d] = $activities
                    ->where('activity_id', $act->id)
                    ->where('tanggal', $carbonBulan->format('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT))
                    ->sum('realisasi');
                
                // Override daily if Database Baru
                if ($act->nama === 'Database Baru') {
                    $harian[$d] = \App\Models\Data::where('created_by', auth()->user()->name)
                        ->whereDate('created_at', $carbonBulan->format('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT))
                        ->count();
                }

                // Override daily if Edukasi WA
                if ($act->nama === 'Edukasi & Membangun Hubungan (WA)') {
                    $harian[$d] = \App\Models\SpinInteraction::whereHas('data', function($q) {
                        $q->where('created_by', auth()->user()->name);
                    })->where('wa', 1)
                    ->whereDate('created_at', $carbonBulan->format('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT))
                    ->count();
                }
            }

            $categories[$kategori][] = [
                'nama' => $act->nama,
                'target_daily' => $tDaily,
                'target_bulanan' => $tBulanan,
                'bobot' => $act->bobot,
                'real' => $totalRealisasi,
                'nilai' => round($nilai, 2),
                'harian' => $harian
            ];

            $total[$kategori]['target_daily'] += $tDaily;
            $total[$kategori]['target_bulanan'] += $tBulanan;
            $total[$kategori]['bobot'] += $act->bobot;
            $total[$kategori]['real'] += $totalRealisasi;
            $total[$kategori]['nilai'] += $nilai;
            for ($d = 1; $d <= $jumlahHari; $d++) {
                $total[$kategori]['harian'][$d] = ($total[$kategori]['harian'][$d] ?? 0) + $harian[$d];
            }
        }

        // KPI Calculation logic (same as index)
        $categoryKpiWeights = [
            'Aktivitas Mencari Leads' => 25,
            'Aktivitas Memprospek' => 25,
            'Aktivitas Closing' => 25,
            'Aktivitas Merawat Customer' => 25,
        ];

        $kpiData = [];
        $totalKpi = 0;
        $totalBobotSum = 0;

    // Group activities for KPI calculation
    $groupedActivities = Activity::orderBy('categories_id')->get()->groupBy('categories_id');

    foreach ($groupedActivities as $kategoriId => $list) {
        $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId);
        $activityPercents = [];

        foreach ($list as $act) {
            $tDaily = (float) ($act->target_daily ?? 0);
            $tBulanan = $tDaily * $hariKerja;
            if ($act->nama === 'Transfer Masuk') {
                $tDaily = 0;
                $tBulanan = 1250000000;
            }

            $tRealisasi = (float) DailyActiviti::where('user_id', auth()->id())
                ->where('activity_id', $act->id)
                ->whereMonth('tanggal', $bulan_int)
                ->whereYear('tanggal', $tahun)
                ->sum('realisasi');
            
            // 🔥 AUTO-OVERRIDE for KPI in PDF
            if ($act->nama === 'Database Baru') {
                $tRealisasi = \App\Models\Data::where('created_by', auth()->user()->name)
                    ->whereMonth('created_at', $bulan_int)
                    ->whereYear('created_at', $tahun)
                    ->count();
            }

            $percent = 0;
            if ($tBulanan > 0) {
                $percent = ($tRealisasi / $tBulanan) * 100;
                if ($percent > 100) $percent = 100;
            }
            $activityPercents[] = $percent;
        }

        $skorKategori = count($activityPercents) ? (array_sum($activityPercents) / count($activityPercents)) : 0;
        $bobotKategori = $categoryKpiWeights[$categoryName] ?? 0;
        $nilaiKategori = ($skorKategori / 100) * $bobotKategori;

        $kpiData[] = [
            'nama'       => $categoryName,
            'target'     => '100%',
            'bobot'      => $bobotKategori,
            'persentase' => round($skorKategori, 2),
            'nilai'      => round($nilaiKategori, 2),
        ];
        $totalKpi += $nilaiKategori;
        $totalBobotSum += $bobotKategori;
    }

    $pdf = PDF::loadView('admin.dailyactivity.pdf', [
        'categories' => $categories,
        'total' => $total,
        'jumlahHari' => $jumlahHari,
        'tahun' => $tahun,
        'bulan_int' => $bulan_int,
        'bulan' => $carbonBulan->translatedFormat('F Y'),
        'csName' => $csName,
        'downloadDate' => $downloadDate,
        'kpiData' => $kpiData,
        'totalNilai' => $totalKpi,
        'totalBobot' => $totalBobotSum
    ])->setPaper('F4', 'landscape');

    return $pdf->download("Laporan_Activity_KPI_{$bulan}_{$csName}.pdf");
}

}
