<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Interaksi - {{ $periode }}</title>
    <style>
        @page {
            margin: 0.5cm;
        }
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 8px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header-title {
            border: 2px solid blue;
            display: inline-block;
            padding: 5px 20px;
            font-weight: bold;
            font-size: 14px;
            background-color: yellow;
        }
        .periode {
            margin-top: 5px;
            font-size: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: fixed;
        }
        th, td {
            border: 1px solid #333;
            padding: 2px;
            word-wrap: break-word;
            vertical-align: top;
        }
        th {
            background-color: cyan;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .col-no { width: 25px; }
        .col-tgl { width: 45px; }
        .col-nama { width: 60px; }
        .col-wa { width: 70px; }
        .col-fu { width: 75px; }
        
        .fu-sub-header {
            background-color: #f2f2f2;
            font-size: 7px;
        }
        .checkmark {
            color: green;
            font-weight: bold;
            text-align: center;
        }
        .box-text {
            border: 0.5px solid #ccc;
            padding: 2px;
            min-height: 25px;
            font-size: 7px;
            margin-bottom: 2px;
        }
        .timestamp {
            font-size: 6px;
            color: #666;
            margin-top: 1px;
            display: block;
        }
        .section-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title">RIWAYAT INTERAKSI FOLLOW UP LEADS ({{ strtoupper($csName) }})</div>
        <div class="periode">Periode: {{ $periode }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="3" class="col-no">NO</th>
                <th rowspan="3" class="col-tgl">TANGGAL</th>
                <th class="col-nama">NAMA</th>
                @for($i=1; $i<=10; $i++)
                    <th colspan="2" class="col-fu">FU {{ $i }}</th>
                @endfor
            </tr>
            <tr>
                <th class="col-wa">NO WA</th>
                @for($i=1; $i<=10; $i++)
                    <th class="fu-sub-header" style="width: 25px;">TELP</th>
                    <th class="fu-sub-header" style="width: 25px;">WA</th>
                @endfor
            </tr>
            <tr>
                <th>HASIL & TINDAK LANJUT</th>
                @for($i=1; $i<=10; $i++)
                    <th colspan="2" class="fu-sub-header">HASIL & TINDAK LANJUT</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
                @php
                    $fus = [];
                    foreach($item->spinInteractions as $si) {
                        $fus[$si->spin_number] = $si;
                    }
                @endphp
                <tr>
                    <td rowspan="2" style="text-align: center;">{{ $index + 1 }}</td>
                    <td rowspan="2" style="text-align: center;">{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/y') }}</td>
                    <td rowspan="2">
                        <div style="font-weight: bold;">{{ $item->nama }}</div>
                        <div style="font-size: 7px;">{{ $item->no_wa }}</div>
                    </td>
                    @for($i=1; $i<=10; $i++)
                        @php $fu = $fus[$i] ?? null; @endphp
                        <td style="text-align: center; height: 15px;">{{ $fu && $fu->telp ? '✓' : '-' }}</td>
                        <td style="text-align: center; height: 15px;">{{ $fu && $fu->wa ? '✓' : '-' }}</td>
                    @endfor
                </tr>
                <tr>
                    {{-- Row for Hasil & Tindak Lanjut content only --}}
                    @for($i=1; $i<=10; $i++)
                        @php $fu = $fus[$i] ?? null; @endphp
                        <td colspan="2" style="height: 60px;">
                            @if($fu)
                                <div class="box-text">
                                    <strong>H:</strong> {{ $fu->hasil_fu }}
                                    <span class="timestamp">{{ \Carbon\Carbon::parse($fu->created_at)->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="box-text">
                                    <strong>T:</strong> {{ $fu->tindak_lanjut }}
                                    <span class="timestamp">{{ \Carbon\Carbon::parse($fu->created_at)->format('d/m/Y H:i') }}</span>
                                </div>
                            @else
                                <div style="text-align: center; color: #ccc;">-</div>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>

    </table>

    <div class="section-title">BERIKUT YANG HARUS DI TINDAK LANJUTI</div>
    <table>
        <thead>
            <tr>
                <th style="width: 30px;">NO</th>
                <th style="width: 80px;">TANGGAL</th>
                <th style="width: 150px;">NAMA</th>
                <th style="width: 100px;">NO WA</th>
                <th>TINDAK LANJUT</th>
            </tr>
        </thead>
        <tbody>
            @php $noFollowUp = 1; @endphp
            @foreach($data as $item)
                @php
                    // Get latest interaction for follow up section
                    $latestInteraction = $item->spinInteractions->sortByDesc('created_at')->first();
                @endphp
                @if($latestInteraction && $latestInteraction->tindak_lanjut)
                    <tr>
                        <td style="text-align: center;">{{ $noFollowUp++ }}</td>
                        <td style="text-align: center;">{{ \Carbon\Carbon::parse($latestInteraction->created_at)->format('d/m/Y') }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->no_wa }}</td>
                        <td>{{ $latestInteraction->tindak_lanjut }}</td>
                    </tr>
                @endif
            @endforeach
            @if($noFollowUp == 1)
                <tr>
                    <td colspan="5" style="text-align: center; color: #999; padding: 10px;">Tidak ada tindak lanjut tertunda.</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>
