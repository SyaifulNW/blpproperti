<!DOCTYPE html>
<html>
<head>
    <title>Daily Activity - {{ $namaUser }} - {{ $tanggal }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #333; padding: 5px; }
        .bg-light { background-color: #f8f9fa; }
        .text-uppercase { text-transform: uppercase; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-5 { margin-bottom: 3rem; }
        .h4 { font-size: 18px; }
        .checked-box { font-family: DejaVu Sans, sans-serif; } /* For checkmarks */
    </style>
</head>
<body>

    <div class="text-center mb-5">
        <h2 class="text-uppercase" style="margin-bottom: 5px;">Daily Activity</h2>
        <h4 class="text-uppercase" style="color: #666; margin-top: 0;">Staff Operasional & Keuangan</h4>
        <p>Nama: {{ $namaUser }} | Tanggal: {{ $tanggal }}</p>
    </div>

    <!-- Harian (35%) -->
    <div style="background-color: #eee; padding: 5px; font-weight: bold; border-left: 5px solid #4e73df; margin-bottom: 10px;">
        Harian (35%)
    </div>
    <table class="table">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="width: 5%;">No</th>
                <th style="width: 50%;">Aktivitas</th>
                <th style="width: 25%;">Checklist / Count</th>
                <th style="width: 20%;">Poin</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Scoring Logic
                $totalSpp = isset($listPesertaSMI) ? $listPesertaSMI->count() : 0;
                $checkedSpp = isset($data['daily_spp_checked']) ? count($data['daily_spp_checked']) : 0;
                $scoreSpp = $totalSpp > 0 ? ($checkedSpp / $totalSpp) * 8.75 : 0;

                $totalNet = isset($listPesertaSMI) ? $listPesertaSMI->count() : 0;
                $checkedNet = isset($data['daily_networking_checked']) ? count($data['daily_networking_checked']) : 0;
                $scoreNet = $totalNet > 0 ? ($checkedNet / $totalNet) * 8.75 : 0;

                $totalCoach = isset($listPesertaSMI) ? $listPesertaSMI->count() : 0;
                $checkedCoach = isset($data['daily_coaching_checked']) ? count($data['daily_coaching_checked']) : 0;
                $scoreCoach = $totalCoach > 0 ? ($checkedCoach / $totalCoach) * 8.75 : 0;

                $checkedSchedule = isset($data['daily_scheduling_checked']) && $data['daily_scheduling_checked'] ? true : false;
                $scoreSchedule = $checkedSchedule ? 8.75 : 0;
            @endphp

            <!-- 1. SPP -->
            <tr>
                <td class="text-center">1</td>
                <td>Penagihan SPP Mahasiswa SMI</td>
                <td class="text-center">
                    {{ $checkedSpp }} / {{ $totalSpp }} Mahasiswa
                </td>
                <td class="text-center">{{ number_format($scoreSpp, 2) }}</td>
            </tr>
            <!-- 2. Networking -->
            <tr>
                <td class="text-center">2</td>
                <td>Networking dengan Mahasiswa</td>
                <td class="text-center">
                    {{ $checkedNet }} / {{ $totalNet }} Mahasiswa
                </td>
                <td class="text-center">{{ number_format($scoreNet, 2) }}</td>
            </tr>
            <!-- 3. Coaching -->
            <tr>
                <td class="text-center">3</td>
                <td>Jalankan 1 on 1 Coaching</td>
                <td class="text-center">
                    {{ $checkedCoach }} / {{ $totalCoach }} Mahasiswa
                </td>
                <td class="text-center">{{ number_format($scoreCoach, 2) }}</td>
            </tr>
            <!-- 4. Scheduling -->
            <tr>
                <td class="text-center">4</td>
                <td>Jadwalkan sesi 1 on 1</td>
                <td class="text-center checked-box">
                    {{ $checkedSchedule ? '☑' : '☐' }}
                </td>
                <td class="text-center">{{ number_format($scoreSchedule, 2) }}</td>
            </tr>
            <tr class="bg-light font-weight-bold">
                <td colspan="3" class="text-right">Total Harian</td>
                <td class="text-center">{{ isset($data['total_daily']) ? $data['total_daily'] : '0.00%' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Mingguan (40%) -->
    <div style="background-color: #eee; padding: 5px; font-weight: bold; border-left: 5px solid #1cc88a; margin-bottom: 10px;">
        Mingguan (40%)
    </div>
    <table class="table">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="width: 5%;">No</th>
                <th style="width: 50%;">Aktivitas</th>
                <th style="width: 25%;">Checklist</th>
                <th style="width: 20%;">Poin</th>
            </tr>
        </thead>
        <tbody>
            @php
                $checkedWeekly = isset($data['weekly']) ? $data['weekly'] : [];
                $scorePerItem = 40 / 6; // ~6.67
            @endphp

            <!-- 1. Kebersihan -->
            <tr>
                <td class="text-center">1</td>
                <td>Pemeriksaan kebersihan dan kelayakan semua ruang kantor</td>
                <td class="text-center checked-box">
                    {{ in_array(1, $checkedWeekly) ? '☑' : '☐' }}
                </td>
                <td class="text-center">{{ number_format(in_array(1, $checkedWeekly) ? $scorePerItem : 0, 2) }}</td>
            </tr>

            <!-- 2. Perlengkapan -->
            <tr>
                <td class="text-center">2</td>
                <td>Cek perlengkapan kantor</td>
                <td class="text-center checked-box">
                    {{ in_array(2, $checkedWeekly) ? '☑' : '☐' }}
                </td>
                <td class="text-center">{{ number_format(in_array(2, $checkedWeekly) ? $scorePerItem : 0, 2) }}</td>
            </tr>

            <!-- 3. SMI & MBC -->
            <tr>
                <td class="text-center">3</td>
                <td>Menyiapkan Kelas SMI dan MBC</td>
                <td class="text-center checked-box">
                    <span style="margin-right: 10px;">{{ in_array(3, $checkedWeekly) ? '☑' : '☐' }} SMI</span>
                    <span>{{ in_array(6, $checkedWeekly) ? '☑' : '☐' }} MBC</span>
                </td>
                @php
                    $poin3 = (in_array(3, $checkedWeekly) ? $scorePerItem : 0) + (in_array(6, $checkedWeekly) ? $scorePerItem : 0);
                @endphp
                <td class="text-center">{{ number_format($poin3, 2) }}</td>
            </tr>

            <!-- 4. Rapat -->
            <tr>
                <td class="text-center">4</td>
                <td>Rapat evaluasi internal</td>
                <td class="text-center checked-box">
                    {{ in_array(4, $checkedWeekly) ? '☑' : '☐' }}
                </td>
                <td class="text-center">{{ number_format(in_array(4, $checkedWeekly) ? $scorePerItem : 0, 2) }}</td>
            </tr>

            <!-- 5. EF -->
            <tr>
                <td class="text-center">5</td>
                <td>Menyiapkan Kelas EF</td>
                <td class="text-center checked-box">
                    {{ in_array(5, $checkedWeekly) ? '☑' : '☐' }}
                </td>
                <td class="text-center">{{ number_format(in_array(5, $checkedWeekly) ? $scorePerItem : 0, 2) }}</td>
            </tr>

            <tr class="bg-light font-weight-bold">
                <td colspan="3" class="text-right">Total Mingguan</td>
                <td class="text-center">{{ isset($data['total_weekly']) ? $data['total_weekly'] : '0.00%' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Bulanan (25%) -->
    <div style="background-color: #eee; padding: 5px; font-weight: bold; border-left: 5px solid #36b9cc; margin-bottom: 10px;">
        Bulanan (25%)
    </div>
    <table class="table">
        <thead>
            <tr style="background-color: #f8f9fa;">
                <th style="width: 5%;">No</th>
                <th style="width: 50%;">Aktivitas</th>
                <th style="width: 25%;">Checklist</th>
                <th style="width: 20%;">Poin</th>
            </tr>
        </thead>
        <tbody>
            @php
                $monthlyItems = [
                    1 => 'Update status Progres mahasiswa',
                    2 => 'Rekap sesi 1-on-1',
                    3 => 'Cek inventaris aset',
                    4 => 'Laporan operasional dan kemahasiswaan SMI',
                    5 => 'Rekap feedback mahasiswa',
                ];
                $checkedMonthly = isset($data['monthly']) ? $data['monthly'] : [];
                $scorePerItem = 25 / 5;
            @endphp
            @foreach($monthlyItems as $id => $label)
                @php
                    $isChecked = in_array($id, $checkedMonthly);
                    $poin = $isChecked ? $scorePerItem : 0.00;
                @endphp
                <tr>
                    <td class="text-center">{{ $id }}</td>
                    <td>{{ $label }}</td>
                    <td class="text-center checked-box">
                        {{ $isChecked ? '☑' : '☐' }}
                    </td>
                    <td class="text-center">{{ number_format($poin, 2) }}</td>
                </tr>
            @endforeach
            <tr class="bg-light font-weight-bold">
                <td colspan="3" class="text-right">Total Bulanan</td>
                <td class="text-center">{{ isset($data['total_monthly']) ? $data['total_monthly'] : '0.00%' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Rekapitulasi -->
    <div style="background-color: #eee; padding: 5px; font-weight: bold; border-left: 5px solid #e74a3b; margin-bottom: 10px; margin-top: 30px;">
        REKAPITULASI PENILAIAN
    </div>
    <table class="table table-bordered">
        <thead>
            <tr style="background-color: #343a40; color: white;">
                <th style="width: 70%;">Kategori Penilaian</th>
                <th style="width: 30%;" class="text-center">Total Skor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Harian (35%)</strong></td>
                <td class="text-center font-weight-bold">{{ isset($data['total_daily']) ? $data['total_daily'] : '0.00%' }}</td>
            </tr>
            <tr>
                <td><strong>Mingguan (40%)</strong></td>
                <td class="text-center font-weight-bold">{{ isset($data['total_weekly']) ? $data['total_weekly'] : '0.00%' }}</td>
            </tr>
            <tr>
                <td><strong>Bulanan (25%)</strong></td>
                <td class="text-center font-weight-bold">{{ isset($data['total_monthly']) ? $data['total_monthly'] : '0.00%' }}</td>
            </tr>
            <tr style="background-color: #4e73df; color: white;">
                <td class="text-right"><strong>TOTAL SKOR AKHIR</strong></td>
                <td class="text-center font-weight-bold" style="font-size: 1.2em;">{{ isset($data['grand_total']) ? $data['grand_total'] : '0.00%' }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>