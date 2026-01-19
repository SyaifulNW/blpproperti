<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Activity Report - {{ $bulan }}</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 9px; 
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #4e73df;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
            border-radius: 0 0 10px 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0 0;
            opacity: 0.8;
            font-size: 10px;
        }
        .info-section {
            padding: 0 20px;
            margin-bottom: 20px;
        }
        .info-box {
            width: 100%;
            border-bottom: 2px solid #e3e6f0;
            padding-bottom: 10px;
        }
        .info-box td {
            border: none;
            padding: 2px 0;
            font-size: 10px;
        }
        .label {
            font-weight: bold;
            color: #4e73df;
            width: 100px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0 20px 0;
            page-break-inside: avoid;
        }
        th { 
            background-color: #4e73df;
            color: white;
            font-weight: bold; 
            text-align: center;
            padding: 8px 5px;
            text-transform: uppercase;
            font-size: 8px;
            border: 1px solid #2e59d9;
        }
        td { 
            border: 1px solid #e3e6f0; 
            padding: 6px 5px; 
            text-align: center; 
        }
        tr:nth-child(even) {
            background-color: #f8f9fc;
        }
        .text-left { text-align: left; padding-left: 10px; }
        .category-header {
            background-color: #f1f3f9;
            font-weight: bold;
            color: #2e59d9;
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            border-left: 4px solid #4e73df;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 7px;
            color: #999;
            padding: 10px 0;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-target {
            background-color: #eaecf4;
            color: #5a5c69;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAILY ACTIVITY REPORT</h1>
        <p>Performance tracking and activity summary for BLP Properti</p>
    </div>

    <div class="info-section">
        <table class="info-box">
            <tr>
                <td class="label">Customer Service:</td>
                <td>{{ $csName }}</td>
                <td class="label" style="text-align: right;">Report Month:</td>
                <td style="text-align: right;">{{ $bulan }}</td>
            </tr>
            <tr>
                <td class="label">Generated On:</td>
                <td>{{ $downloadDate }}</td>
                <td colspan="2" style="text-align: right; color: #999; font-style: italic;">
                    BLP Properti Management System
                </td>
            </tr>
        </table>
    </div>

    <div style="padding: 0 20px;">
        @php
            // Filter only Intake Activity as requested by user
            $intakeCategory = $categories['Intake Activity'] ?? null;
        @endphp

        @if($intakeCategory)
            <div class="category-header">
                RANGKUMAN INTAKE ACTIVITY
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 35%;">Aktivitas</th>
                        <th style="width: 15%;">Target Bulanan</th>
                        <th style="width: 15%;">Realisasi / Hasil</th>
                        <th style="width: 15%;">Pencapaian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($intakeCategory as $i => $act)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td class="text-left font-weight-bold" style="color: #333;">{{ $act['nama'] }}</td>
                            <td>
                                <span class="badge badge-target">
                                    @if($act['nama'] === 'Database baru') 100
                                    @elseif($act['nama'] === 'Follow-up aktif') 80–120
                                    @elseif($act['nama'] === 'Presentasi') 8–12
                                    @elseif($act['nama'] === 'Visit lokasi') 10
                                    @elseif($act['nama'] === 'Closing') 1–2
                                    @else {{ $act['target_bulanan'] }}
                                    @endif
                                </span>
                            </td>
                            <td style="font-weight: bold; color: #1cc88a;">
                                {{ number_format($act['real'], 0) }}
                            </td>
                            <td>
                                @php
                                    $target = $act['target_bulanan'] > 0 ? $act['target_bulanan'] : 1;
                                    $percent = ($act['real'] / $target) * 100;
                                    if($percent > 100) $percent = 100;
                                @endphp
                                <span style="font-weight: bold; color: {{ $percent >= 100 ? '#1cc88a' : ($percent >= 50 ? '#f6c23e' : '#e74a3b') }}">
                                    {{ number_format($percent, 0) }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div style="text-align: center; color: #999; margin-top: 50px;">
                Belum ada data Intake Activity untuk periode ini.
            </div>
        @endif
    </div>

    <div class="footer">
        &copy; {{ date('Y') }} BLP Properti. This report is automatically generated and contains confidential performance data.
    </div>
</body>
</html>
