<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Penilaian CS</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        thead th { background: #f0f0f0; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h3 class="center">Laporan Penilaian Sales</h3>
    <p>Nama: {{ $userName }}</p>
    <p>Periode: {{ $bulan }}/{{ $tahun }}</p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Aspek</th>
                <th>Pencapaian</th>
                <th>Bobot</th>
                <th>Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Penjualan & Omset</td>
                <td>Rp {{ number_format($totalOmset,0,',','.') }}</td>
                <td class="center">60%</td>
                <td class="center">{{ $nilaiOmset }}</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Database Baru</td>
                <td>{{ $databaseBaru }}</td>
                <td class="center">20%</td>
                <td class="center">{{ $nilaiDatabaseBaru }}</td>
            </tr>
            <tr>
                <td>3</td>
                <td>Penilaian Atasan</td>
                <td>{{ $totalSumManual }}</td>
                <td class="center">20%</td>
                <td class="center">{{ $nilaiManualPart }}</td>
            </tr>
        </tbody>
    </table>

    <h4>Total Nilai: {{ $totalNilai }}</h4>
</body>
</html>
