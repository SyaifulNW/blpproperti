<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Interaksi - {{ $data->nama }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #4e73df;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        .interaction-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .interaction-table th, .interaction-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .interaction-table th {
            background-color: #f8f9fc;
            color: #4e73df;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }
        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
        }
        .bg-success { background-color: #1cc88a; }
        .bg-secondary { background-color: #858796; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rekap Interaksi Calon Pelanggan</h2>
        <div style="font-size: 14px; margin-top: 5px;">BLP Properti</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Nama Pelanggan</td>
            <td>: {{ $data->nama }}</td>
            <td class="info-label">Tanggal Input</td>
            <td>: {{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>

            <td class="info-label">Sales / CS</td>
            <td>: {{ $data->created_by }}</td>
        </tr>
        <tr>
            <td class="info-label">Sumber Leads</td>
            <td>: {{ $data->leads }}</td>
            <td class="info-label">Status SPIN</td>
            <td>: 
                <span class="badge {{ $data->spin_b == 'Ya' ? 'bg-success' : 'bg-secondary' }}">B</span>
                <span class="badge {{ $data->spin_a == 'Ya' ? 'bg-success' : 'bg-secondary' }}">A</span>
                <span class="badge {{ $data->spin_t == 'Ya' ? 'bg-success' : 'bg-secondary' }}">T</span>
            </td>
        </tr>
    </table>

    <h3 style="color: #4e73df; border-left: 4px solid #4e73df; padding-left: 10px;">Riwayat Interaksi</h3>
    
    <table class="interaction-table">
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">No</th>
                <th style="width: 100px;">Tanggal</th>
                <th>Hasil Interaksi (FU)</th>
                <th>Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data->spinInteractions as $index => $interaction)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($interaction->created_at)->translatedFormat('d/m/Y H:i') }}</td>
                    <td>{!! nl2br(e($interaction->hasil_fu)) !!}</td>
                    <td>{!! nl2br(e($interaction->tindak_lanjut)) !!}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: 20px; color: #999;">Belum ada riwayat interaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }} | BLP Properti Systems
    </div>
</body>
</html>
