<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Daily Activity - {{ $bulan }}</title>
    <style>
        @page {
            margin: 0.7cm;
            size: F4 landscape;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 6.8px; 
            margin: 0;
            padding: 0;
            color: #333;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; padding-left: 2px; }
        .font-bold { font-weight: bold; }
        
        .header-section {
            text-align: center;
            margin-bottom: 5px;
        }
        .header-title {
            font-size: 10px;
            font-weight: bold;
            display: block;
        }
        .header-subtitle {
            font-size: 8px;
            font-weight: bold;
            display: block;
        }
        
        .info-container {
            width: 100%;
            margin-bottom: 3px;
            font-size: 7.5px;
            font-weight: bold;
        }
        .info-left {
            float: left;
            width: 50%;
        }
        .info-right {
            float: right;
            width: 50%;
            text-align: right;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 8px; 
            table-layout: fixed;
        }
        th, td { 
            border: 0.5px solid #000; 
            padding: 1.5px 0.5px; 
            text-align: center; 
            vertical-align: middle;
            word-wrap: break-word;
        }
        th { 
            background-color: #d9edf7; 
            font-weight: bold; 
            font-size: 6.5px;
        }
        .category-row {
            background-color: #d9edf7;
            text-align: left !important;
            font-weight: bold;
            padding-left: 5px;
            font-size: 7.5px;
        }
        .total-row {
            background-color: #e6ffe6;
            font-weight: bold;
        }
        
        /* Optimized Column Widths */
        .col-no { width: 15px; }
        .col-act { width: 310px; }
        .col-tgt-h { width: 35px; }
        .col-tgt-b { width: 35px; }
        .col-bbt { width: 22px; }
        .col-real { width: 35px; }
        .col-nilai { width: 30px; }
        .col-day { width: 10.5px; }

        .bg-red { background-color: #ff4d4d !important; color: #ffffff; }
        .bg-yellow { background-color: #ffff99 !important; color: #333; }
        .bg-white { background-color: #ffffff !important; }

    </style>
</head>
<body>
    <div class="header-section">
        <span class="header-title">Laporan Daily Activity</span>
        <span class="header-subtitle">Bulan: {{ $bulan }}</span>
    </div>

    <div class="info-container clearfix">
        <div class="info-left">
            Nama Karyawan: {{ $csName }}
        </div>
        <div class="info-right">
            Diunduh pada: {{ $downloadDate }}
        </div>
    </div>

    @foreach($categories as $kategori => $aktivitasList)
    @php
        $pageBreak = '';
        if ($kategori === 'Aktivitas Merawat Customer') {
            $pageBreak = 'page-break-before: always; margin-top: 10px;';
        }
    @endphp
    <div style="{{ $pageBreak }}">
    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-act">Aktivitas</th>
                <th class="col-tgt-h">Target/Hari</th>
                <th class="col-tgt-b">Target/Bula<br>n</th>
                <th class="col-bbt">Bobot</th>
                <th class="col-real">Realisasi</th>
                <th class="col-nilai">Nilai</th>
                @for($d=1; $d<=$jumlahHari; $d++)
                    <th class="col-day">{{ $d }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="{{ 7 + $jumlahHari }}" class="category-row">{{ strtoupper($kategori) }}</td>
            </tr>

            @foreach($aktivitasList as $i => $act)
            <tr>
                <td>{{ $i+1 }}</td>
                <td class="text-left font-bold" style="white-space: normal;">{{ $act['nama'] }}</td>
                <td>
                    @if($act['nama'] === 'Transfer Masuk') 40.000.000
                    @else {{ number_format($act['target_daily'], 0, ',', '.') }} @endif
                </td>
                <td>
                    @if($act['nama'] === 'Transfer Masuk') 1.000.000.000
                    @else {{ number_format($act['target_bulanan'], 0, ',', '.') }} @endif
                </td>
                <td>{{ (int)$act['bobot'] }}</td>
                <td>{{ number_format($act['real'], 0, ',', '.') }}</td>
                <td>{{ number_format($act['nilai'], 2, ',', '.') }}</td>
                @for($d=1; $d<=$jumlahHari; $d++)
                    @php
                        $realDaily = $act['harian'][$d] ?? 0;
                        $targetDaily = (float)$act['target_daily'];
                        
                        $dateObj = \Carbon\Carbon::create($tahun, $bulan_int, $d)->startOfDay();
                        $isSunday = $dateObj->isSunday();
                        $isPast = $dateObj->isPast() && !$dateObj->isToday();

                        $cellClass = 'bg-white';
                        if ($realDaily > 0) {
                            if ($realDaily < $targetDaily) {
                                $cellClass = 'bg-yellow';
                            }
                        } else {
                            if ($isPast && !$isSunday && strpos(strtolower($kategori), 'bulanan') === false) {
                                $cellClass = 'bg-red';
                            }
                        }
                    @endphp
                    <td class="{{ $cellClass }}" style="font-size: 6.5px;">{{ $realDaily ?: '' }}</td>
                @endfor
            </tr>
            @endforeach

            <tr class="total-row">
                <td colspan="2" style="text-align: center;">TOTAL</td>
                <td>{{ number_format($total[$kategori]['target_daily'], 0, ',', '.') }}</td>
                <td>{{ number_format($total[$kategori]['target_bulanan'], 0, ',', '.') }}</td>
                <td>{{ (int)$total[$kategori]['bobot'] }}</td>
                <td>{{ number_format($total[$kategori]['real'], 0, ',', '.') }}</td>
                <td>{{ number_format($total[$kategori]['nilai'], 2, ',', '.') }}</td>
                @for($d=1; $d<=$jumlahHari; $d++)
                    @php
                        $totalRealDaily = $total[$kategori]['harian'][$d] ?? 0;
                        $totalTargetDaily = $total[$kategori]['target_daily'];
                        
                        $dateObj = \Carbon\Carbon::create($tahun, $bulan_int, $d)->startOfDay();
                        $isSunday = $dateObj->isSunday();
                        $isPast = $dateObj->isPast() && !$dateObj->isToday();

                        $totalCellClass = 'bg-white';
                        if ($totalRealDaily > 0) {
                            if ($totalRealDaily < $totalTargetDaily) {
                                $totalCellClass = 'bg-yellow';
                            }
                        } else {
                            if ($isPast && !$isSunday && strpos(strtolower($kategori), 'bulanan') === false) {
                                $totalCellClass = 'bg-red';
                            }
                        }
                    @endphp
                    <td class="{{ $totalCellClass }}" style="font-size: 6.5px;">{{ $totalRealDaily ?: '' }}</td>
                @endfor
            </tr>
        </tbody>
    </table>
    </div>
    @endforeach

    {{-- REKAP KPI --}}
    <div style="margin-top: 20px; page-break-inside: avoid;">
        <h3 style="text-align: left; margin-bottom: 5px; border-bottom: 1.5px solid #333; padding-bottom: 2px; font-size: 10px; text-transform: uppercase; font-weight: bold;">
            REKAPITULASI PERFORMANCE / KPI
        </h3>
        <table style="width: 60%; font-size: 8px;">
            <thead>
                <tr>
                    <th style="width:30px">No</th>
                    <th style="width:200px; text-align: left; padding-left: 5px;">Dimensi Aktivitas</th>
                    <th style="width:60px">Target</th>
                    <th style="width:60px">Bobot</th>
                    <th style="width:80px">Pencapaian (%)</th>
                    <th style="width:80px">Skor Akhir</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kpiData as $i => $row)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td class="text-left font-bold" style="padding-left: 5px;">{{ $row['nama'] }}</td>
                    <td>{{ $row['target'] }}</td>
                    <td>{{ $row['bobot'] }}</td>
                    <td>{{ $row['persentase'] }}%</td>
                    <td style="font-weight: bold;">{{ number_format($row['nilai'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="background-color: #f2f2f2; font-weight: bold; font-size: 9px;">
                    <td colspan="3" style="text-align: right; padding: 5px;">OVERALL PERFORMANCE SCORE</td>
                    <td>{{ $totalBobot }}</td>
                    <td>—</td>
                    <td style="background-color: #333; color: #fff; font-size: 11px;">{{ number_format($totalNilai, 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- LEGENDA WARNA --}}
    <div style="margin-top: 20px; font-size: 8px;">
        <p style="margin-bottom: 5px; font-weight: bold;">Keterangan Warna:</p>
        <div style="margin-bottom: 4px;">
            <div style="display: inline-block; width: 15px; height: 10px; background-color: #ff4d4d; border: 0.5px solid #000; vertical-align: middle;"></div>
            <span style="vertical-align: middle; margin-left: 10px;">Merah: Tidak ada aktivitas (pada hari kerja yang sudah terlewat)</span>
        </div>
        <div style="margin-bottom: 4px;">
            <div style="display: inline-block; width: 15px; height: 10px; background-color: #ffff99; border: 0.5px solid #000; vertical-align: middle;"></div>
            <span style="vertical-align: middle; margin-left: 10px;">Kuning: Dilakukan, namun tidak mencapai target harian</span>
        </div>
        <div style="margin-bottom: 4px;">
            <div style="display: inline-block; width: 15px; height: 10px; background-color: #ffffff; border: 0.5px solid #000; vertical-align: middle;"></div>
            <span style="vertical-align: middle; margin-left: 10px;">Putih: Mencapai target, hari libur (Minggu), atau hari yang belum dijalani</span>
        </div>
    </div>
</body>
</html>
