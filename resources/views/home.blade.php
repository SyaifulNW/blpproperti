@extends('layouts.masteradmin')

@section('content')
    @php
        use App\Models\Data;
    @endphp

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        .badge-lg {
            font-size: 1.1rem;
            padding: 0.8rem 1.4rem;
        }

        .card-header {
            font-size: 1rem;
        }

        .progress-bar {
            font-size: 0.9rem;
        }

        /* 🔵 Efek berdenyut lembut (pulse) */
        @keyframes pulseGlow {
            0% {
                box-shadow: 0 0 0 rgba(0, 123, 255, 0.4);
                transform: scale(1);
            }

            50% {
                box-shadow: 0 0 15px rgba(0, 123, 255, 0.5);
                transform: scale(1.03);
            }

            100% {
                box-shadow: 0 0 0 rgba(0, 123, 255, 0.4);
                transform: scale(1);
            }
        }

        /* 🎨 Tampilan cell reminder */
        .reminder-cell {
            background: linear-gradient(90deg, #e3f2fd, #bbdefb);
            border-radius: 10px;
            padding: 10px 14px;
            font-weight: 600;
            color: #0d47a1;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: pulseGlow 2s infinite ease-in-out;
            transition: transform 0.3s ease;
        }

        /* 🔔 Ikon lonceng bergetar ringan */
        .reminder-icon {
            color: #2196f3;
            animation: ring 2s infinite;
            font-size: 1.3rem;
        }

        @keyframes ring {
            0% {
                transform: rotate(0);
            }

            10% {
                transform: rotate(15deg);
            }

            20% {
                transform: rotate(-10deg);
            }

            30% {
                transform: rotate(5deg);
            }

            40% {
                transform: rotate(-5deg);
            }

            50%,
            100% {
                transform: rotate(0);
            }
        }

        /* Popup Motivasi */
        @keyframes popIn {
            from {
                transform: translate(-50%, -40%) scale(0.5);
                opacity: 0;
            }

            to {
                transform: translate(-50%, -50%) scale(1);
                opacity: 1;
            }
        }

        #popupOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 9998;
        }

        #motivasiPopup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
            animation: popIn 0.5s ease-out;
        }
    </style>

    <div class="container-fluid px-4">

        {{-- ALERT MODE READ ONLY (ADMIN) --}}
        @if(isset($user) && $readonly)
            <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
                <div>
                    <strong>Dashboard CS:</strong> <strong>{{ $user->name }} </strong> <br>
                    <span class="text-muted small">Email: {{ $user->email }} | Role: {{ ucfirst($user->role) }}</span>
                </div>
                <div>
                    <span class="text-white badge bg-primary p-2">Mode Read-Only</span>
                </div>
            </div>
        @endif

        {{-- ✨ KOMENTAR ADMIN KE CS ✨ --}}
        @if(isset($user) && $readonly)
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="fas fa-comments me-2"></i> Komentar untuk {{ $user->name }}
                </div>
                <div class="card-body">
                    {{-- Form Kirim Komentar --}}
                    <form id="formKomentar" method="POST" action="{{ route('komentar.store') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <div class="input-group mb-3">
                            <input type="text" name="pesan" class="form-control" placeholder="Tulis komentar untuk CS ini..."
                                required>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim
                            </button>
                        </div>
                    </form>
                    @if(session('success'))
                        <script>
                            Swal.fire({
                                title: 'Berhasil!',
                                text: '{{ session('success') }}',
                                icon: 'success',
                                confirmButtonText: 'OK',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        </script>
                    @endif


                    <button class="btn btn-outline-secondary btn-sm mb-2" data-toggle="modal" data-target="#modalKomentar">
                        <i class="fas fa-history"></i> Lihat Riwayat Komentar
                    </button>

                    <div class="modal fade" id="modalKomentar" tabindex="-1" role="dialog" aria-labelledby="modalKomentarLabel"
                        aria-hidden="true">
                        <div class="modal-dialog modal-dialog-scrollable" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-warning text-dark">
                                    <h5 class="modal-title" id="modalKomentarLabel">
                                        <i class="fas fa-comments me-2"></i> Riwayat Komentar
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    @foreach($komentar as $msg)
                                        <div class="alert alert-light border d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <strong>{{ $msg->admin->name ?? 'Admin' }}</strong><br>
                                                <span class="text-dark">{{ $msg->pesan }}</span><br>
                                                <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                                            </div>
                                            <i class="fas fa-comment-dots text-warning"></i>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        @endif

        @php
            $bulanDipilih = request('bulan', now()->format('Y-m'));
            $bulanParse = \Carbon\Carbon::parse($bulanDipilih . '-01');
            $namaBulan = $bulanParse->translatedFormat('F');
            $tahun = $bulanParse->year;

            use App\Models\Kelas;


            $jadwalKelas = Kelas::whereYear('tanggal_mulai', $tahun)
                ->whereMonth('tanggal_mulai', $bulanParse->month)
                ->pluck('tanggal_mulai', 'nama_kelas')
                ->toArray();
        @endphp

        <!-- Popup Motivasi HTML -->
        <div id="popupOverlay" onclick="tutupMotivasi()"></div>
        <div id="motivasiPopup">
            <h4 class="fw-bold text-primary mb-3">🌟 Motivasi Hari Ini</h4>
            <p id="motivasiText" class="fs-5 text-dark" style="font-style: italic;"></p>
            <button class="btn btn-primary mt-3 px-4" onclick="tutupMotivasi()">Semangat! 🚀</button>
        </div>

        <!-- Month Filter Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('home') }}" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="bulan" class="form-label fw-semibold">
                            Pilih Bulan Pelanggan:
                        </label>
                        <input type="month" id="bulan" name="bulan" class="form-control" value="{{ $bulanDipilih }}">
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-search me-1"></i> Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="dashboard-tab-link" data-toggle="tab"
                    data-target="#dashboard-tab" type="button" role="tab" aria-controls="dashboard-tab"
                    aria-selected="true">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard Panel 1
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="performance-tab-link" data-toggle="tab" data-target="#performance-tab"
                    type="button" role="tab" aria-controls="performance-tab" aria-selected="false">
                    <i class="fas fa-star me-2"></i> Penilaian Kinerja Saya
                </button>
            </li>
        </ul>

        <div class="tab-content" id="dashboardTabsContent">
            <!-- ================== TAB 1: DASHBOARD ================== -->
            <div class="tab-pane fade show active" id="dashboard-tab" role="tabpanel" aria-labelledby="dashboard-tab-link">

                @php
                    $namaUserData = isset($user) && $readonly ? $user->name : auth()->user()->name;

                    $databaseBaru = Data::where('created_by', $namaUserData)
                        ->whereYear('created_at', $bulanParse->year)
                        ->whereMonth('created_at', $bulanParse->month)
                        ->count();

                    $totalDatabase = Data::where('created_by', $namaUserData)->count();
                    $target = 100;

                    $sumberDatabase = Data::select('leads', \DB::raw('COUNT(*) as total'))
                        ->where('created_by', $namaUserData)
                        ->whereYear('created_at', $bulanParse->year)
                        ->whereMonth('created_at', $bulanParse->month)
                        ->groupBy('leads')
                        ->pluck('total', 'leads')
                        ->toArray();

                    $labels = array_keys($sumberDatabase);
                    $values = array_values($sumberDatabase);

                    $totalOmset = $kelasOmsetFiltered->sum('omset');
                    $totalKomisi = ($totalOmset * $commissionRate) + $bonus3BulanAmount;
                    $targetBulanan = 1250000000;
                    $persenTercapai = $targetBulanan > 0 ? round(($totalOmset / $targetBulanan) * 100, 2) : 0;
                @endphp

                <div class="row g-4 mb-4">
                    {{-- Kolom 1: OMSET PER KELAS --}}
                    <div class="col-12 col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-header bg-success text-white fw-bold">
                                <i class="fas fa-coins me-2"></i> OMSET ({{ strtoupper($namaBulan) }})
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover mb-0 align-middle" style="font-size: 1rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="fw-bold">Produk</th>
                                            <th class="text-end fw-bold">Omset</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($kelasOmsetFiltered as $k)
                                            <tr>
                                                <td class="fw-bold">{{ $k['nama_kelas'] }}</td>
                                                <td class="text-end text-success fw-bold">
                                                    Rp{{ number_format($k['omset'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted fst-italic text-center fw-bold">No data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr class="fw-bold">
                                            <td class="fw-bold">Total Omset</td>
                                            <td class="text-end text-success fw-bold">
                                                Rp{{ number_format($totalOmset, 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="small text-muted">
                                            <td class="fw-bold">Target Bulanan</td>
                                            <td class="text-end fw-bold">Rp{{ number_format($targetBulanan, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                        <tr class="fw-bold">
                                            <td class="fw-bold">Persentase</td>
                                            <td
                                                class="text-end fw-bold {{ $persenTercapai >= 100 ? 'text-success' : ($persenTercapai >= 75 ? 'text-warning' : 'text-danger') }}">
                                                {{ $persenTercapai }}%
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="p-2">
                                                <div class="progress" style="height: 10px; border-radius: 5px;">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $persenTercapai >= 100 ? 'bg-success' : ($persenTercapai >= 75 ? 'bg-warning' : 'bg-danger') }}"
                                                        role="progressbar" style="width: {{ min($persenTercapai, 100) }}%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom 2: DATABASE & SUMBER LEADS --}}
                    <div class="col-12 col-md-4 d-flex flex-column">
                        {{-- Card Database --}}
                        <div class="card shadow-lg border-0 mb-4">
                            <div class="card-header bg-info text-white fw-bold py-2 text-center">
                                <i class="fas fa-database me-2"></i> DATABASE ({{ strtoupper($namaBulan) }})
                            </div>
                            <div class="card-body text-center">
                                <h2 class="fw-bold text-dark mb-0" style="font-size: 2.5rem;">{{ $databaseBaru }}</h2>
                                <p class="text-muted small mb-3 fw-bold">Database Baru</p>

                                <div class="progress mb-3" style="height: 12px; border-radius: 10px;">
                                    <div class="progress-bar bg-success fw-bold" role="progressbar"
                                        style="width: {{ min(($databaseBaru / $target) * 100, 100) }}%">
                                        {{ number_format(($databaseBaru / $target) * 100, 0) }}%
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between small text-muted">
                                    <span>Target: {{ $target }}</span>
                                    <span>Total: {{ $totalDatabase }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Sumber Leads --}}
                        <div class="card shadow-lg border-0">
                            <div class="card-header bg-primary text-white fw-bold py-2 text-center">
                                <i class="fas fa-chart-pie me-2"></i> SUMBER LEADS
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center"
                                style="min-height: 250px;">
                                <canvas id="pieSumberDbSmall"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- Kolom 3: KOMISI & BONUS --}}
                    <div class="col-12 col-md-4 d-flex flex-column">
                        {{-- Card Komisi --}}
                        <div class="card shadow-lg border-0 mb-4 card-commission">
                            <div class="card-header bg-warning text-dark fw-bold py-2 text-center">
                                <i class="fas fa-hand-holding-usd me-2"></i> KOMISI SEMENTARA
                            </div>
                            <div class="card-body text-center">
                                <h2 class="fw-bold text-success mb-0" style="font-size: 2rem;">
                                    Rp{{ number_format($totalKomisi, 0, ',', '.') }}
                                </h2>
                                <p class="text-muted small mb-2 fw-bold">Estimasi Komisi
                                    <strong>{{ $commissionRate * 100 }}%</strong>
                                </p>

                                @if($neededForNext > 0)
                                    <div class="alert alert-info py-2 px-2 m-0" style="font-size: 0.8rem; line-height: 1.2;">
                                        <i class="fas fa-rocket me-1"></i>
                                        Kurang <strong>Rp{{ number_format($neededForNext, 0, ',', '.') }}</strong> lagi untuk
                                        naik komisi <strong>{{ $nextCommissionValue }}</strong>
                                    </div>
                                @else
                                    <div class="alert alert-success py-2 px-2 m-0" style="font-size: 0.8rem;">
                                        <i class="fas fa-crown me-1 text-warning"></i>
                                        Anda di level komisi tertinggi!
                                    </div>
                                @endif
                                <hr class="my-2">
                                <div class="text-start small mb-2">
                                    <i class="fas fa-info-circle me-1 text-info"></i>
                                    <span class="text-muted fw-bold" style="font-size: 0.75rem;">
                                        Rumus: <strong>{{ $commissionRate * 100 }}% dari Omset</strong>.
                                    </span>
                                </div>
                                {{-- Legenda Komisi --}}
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered text-center mb-0"
                                        style="font-size: 0.75rem; border-color: #dee2e6;">
                                        <thead style="background-color: #f8f9fa; color: #495057;">
                                            <tr>
                                                <th class="py-1 fw-bold">Pencapaian Omset</th>
                                                <th class="py-1 fw-bold">Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="{{ $commissionRate == 0.005 ? 'bg-light fw-bold' : '' }}">
                                                <td class="py-1 text-start ps-2 fw-bold">&lt; Rp1,25 Miliar</td>
                                                <td class="py-1 fw-bold">0,5%</td>
                                            </tr>
                                            <tr class="{{ $commissionRate == 0.0075 ? 'bg-light fw-bold' : '' }}">
                                                <td class="py-1 text-start ps-2 fw-bold">≥ Rp1,25 Miliar</td>
                                                <td class="py-1 fw-bold">0,75%</td>
                                            </tr>
                                            <tr class="{{ $commissionRate == 0.01 ? 'bg-light fw-bold' : '' }}">
                                                <td class="py-1 text-start ps-2 fw-bold">≥ Rp1,5 Miliar</td>
                                                <td class="py-1 fw-bold">1,0%</td>
                                            </tr>
                                            <tr class="{{ $commissionRate == 0.0125 ? 'bg-light fw-bold' : '' }}">
                                                <td class="py-1 text-start ps-2 fw-bold">≥ Rp1,875 Miliar</td>
                                                <td class="py-1 fw-bold">1,25%</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Card Bonus Streak --}}
                        <div class="card shadow-lg border-0 mb-4 card-streak">
                            <div class="card-header bg-primary text-white fw-bold py-2 text-center">
                                Bonus Konsistensi 3 Bulan
                            </div>
                            <div class="card-body text-center">
                                @if($consecutiveMonths >= 3)
                                    <h4 class="fw-bold text-success mb-1">Rp10.000.000</h4>
                                    <span class="badge bg-success text-white mb-2 p-2">
                                        <i class="fas fa-check-circle me-1"></i> BONUS TERCAPAI
                                    </span>
                                @else
                                    <h5 class="fw-bold text-dark mb-1">{{ $consecutiveMonths }} Bulan Streak</h5>
                                    <div class="progress mb-2" style="height: 10px; border-radius: 5px;">
                                        <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated fw-bold"
                                            role="progressbar" style="width: {{ ($consecutiveMonths / 3) * 100 }}%"></div>
                                    </div>
                                    <p class="text-muted small mb-0 fw-bold" style="font-size: 0.8rem;">
                                        <strong>{{ 3 - $consecutiveMonths }} bulan lagi</strong> untuk dapat <strong>Rp10
                                            Juta</strong>
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Card Reward Tahunan --}}
                        <div class="card shadow-lg border-0 mb-4 card-yearly">
                            <div class="card-header bg-dark text-white fw-bold py-2 text-center">
                                REWARD TAHUNAN
                            </div>
                            <div class="card-body text-center py-2">
                                <h6 class="fw-bold mb-1">{{ $rewardTahunanNama ?? 'Reward Tahunan' }}</h6>
                                <div class="progress mb-2" style="height: 18px; border-radius: 10px; position: relative;">
                                    <div class="progress-bar bg-danger fw-bold" role="progressbar"
                                        style="width: {{ min(($totalOmsetTahunan / $targetTahunan) * 100, 100) }}%">
                                        <small>{{ number_format(($totalOmsetTahunan / $targetTahunan) * 100, 1) }}%</small>
                                    </div>
                                </div>
                                @if(!$isEligibleBonusTahunan)
                                    <p class="text-muted small mb-0 fw-bold" style="font-size: 0.75rem;">
                                        Kurang <strong>Rp{{ number_format($neededForYearly, 0, ',', '.') }}</strong> lagi!
                                    </p>
                                @else
                                    <p class="text-success small fw-bold mb-0 fw-bold">🏆 Target 12M Tercapai!</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================== PIE CHART SCRIPT ================== --}}
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const ctxSmall = document.getElementById('pieSumberDbSmall').getContext('2d');
                    new Chart(ctxSmall, {
                        type: 'pie',
                        data: {
                            labels: @json($labels),
                            datasets: [{
                                data: @json($values),
                                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#fd7e14', '#20c997', '#6610f2', '#e83e8c'],
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { font: { size: 12 } }
                                }
                            }
                        }
                    });
                </script>
            </div>

            <!-- ================== TAB 2: PENILAIAN KINERJA SAYA ================== -->
            <div class="tab-pane fade" id="performance-tab" role="tabpanel" aria-labelledby="performance-tab-link">

                <div class="container-fluid mt-4">

                    {{-- JUDUL --}}
                    <div class="text-center mb-3">
                        <h3 class="fw-bold" style="color: #5a5c69;">Penilaian Sales</h3>
                    </div>

                    {{-- TABEL PENILAIAN UTAMA --}}
                    @php
                        $hValTotal = $totalNilaiHasil ?? 0;
                        if ($hValTotal > 100)
                            $cBarTotal = '#008000';
                        elseif ($hValTotal >= 80)
                            $cBarTotal = '#00ca00';
                        elseif ($hValTotal >= 60)
                            $cBarTotal = '#ffe600';
                        elseif ($hValTotal >= 40)
                            $cBarTotal = '#ff9900';
                        else
                            $cBarTotal = '#dc3545';
                    @endphp
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h5 class="fw-bold text-secondary mb-2">Total Pencapaian: {{ $totalNilaiHasil ?? 0 }}/100</h5>
                            <div class="progress" style="height: 25px; background-color: #e9ecef; border-radius: 5px;">
                                <div class="progress-bar fw-bold" role="progressbar"
                                    style="width: {{ $totalNilaiHasil ?? 0 }}%; background-color: {{ $cBarTotal }}; color: {{ ($hValTotal >= 60 && $hValTotal < 80) ? '#333' : '#fff' }}; font-size: 14px;"
                                    aria-valuenow="{{ $totalNilaiHasil ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $totalNilaiHasil ?? 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABEL PENILAIAN UTAMA --}}
                    <div class="card shadow border-0 mb-4">
                        <div class="card-header text-white text-center fw-bold" style="background-color: #00c0ef;">
                            PENILAIAN SALES
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0 text-center align-middle">
                                <thead style="background-color: #ffed8b;">
                                    <tr>
                                        <th class="fw-bold">No</th>
                                        <th class="fw-bold">Aspek Kinerja</th>
                                        <th class="fw-bold">Indikator</th>
                                        <th class="fw-bold">Bobot</th>
                                        <th class="fw-bold">Pencapaian</th>
                                        <th class="fw-bold">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- 1. Penjualan & Omset --}}
                                    <tr>
                                        <td>1</td>
                                        <td class="text-start">Penjualan & Omset</td>
                                        <td class="text-start">Target Rp 1.25 Miliar/bulan</td>
                                        <td>60%</td>
                                        <td>Rp {{ number_format($totalOmset ?? 0, 0, ',', '.') }}</td>
                                        <td>{{ $nilaiOmset ?? 0 }}</td>
                                    </tr>
                                    {{-- 2. Database Baru --}}
                                    <tr>
                                        <td>2</td>
                                        <td class="text-start">Database Baru</td>
                                        <td class="text-start">Target 100 database baru</td>
                                        <td>20%</td>
                                        <td>{{ $databaseBaru ?? 0 }}</td>
                                        <td>{{ $nilaiDatabaseBaru ?? 0 }}</td>
                                    </tr>
                                    {{-- 3. Penilaian Atasan --}}
                                    @php
                                        $manualSum = isset($manual) ? ($manual->kerajinan + $manual->kerjasama + $manual->tanggung_jawab + $manual->inisiatif + $manual->komunikasi) : 0;
                                    @endphp
                                    <tr>
                                        <td>3</td>
                                        <td class="text-start">Penilaian Atasan</td>
                                        <td class="text-start">Total Skor Kualitatif (Max 500)</td>
                                        <td>20%</td>
                                        <td>{{ $manualSum }}</td>
                                        <td>{{ $nilaiManualPart ?? 0 }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr style="background-color: #dff0d8;">
                                        <td colspan="5" class="text-start fw-bold ps-4">TOTAL NILAI</td>
                                        <td class="fw-bold">{{ $totalNilaiHasil ?? 0 }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    {{-- STATUS BOX & LEGEND --}}
                    <div class="card shadow border-0 p-4 mb-4">

                        {{-- Dinamic Status Box --}}
                        <div id="statusBoxContainer" class="p-3 text-center text-white fw-bold fs-4 mb-3"
                            style="border-radius: 5px; background-color: {{ $cBarTotal }}; color: {{ ($hValTotal >= 60 && $hValTotal < 80) ? '#333' : '#fff' }};">
                            @if($hValTotal > 100) Sangat Baik @elseif($hValTotal >= 80) Baik @elseif($hValTotal >= 60) Cukup
                            @elseif($hValTotal >= 40) Pembinaan @else Underperformance @endif ({{ $hValTotal }})
                        </div>

                        {{-- Motivasi Text --}}
                        <div class="d-flex align-items-start mb-4">
                            <i class="fas fa-comment-dots fa-lg me-2 mt-1" style="color: #aaa;"></i>
                            <em id="motivasiTextInline" style="color: #555;">
                                Ayo bangkit! Kamu belum terlambat untuk mengejar.
                            </em>
                        </div>

                        <h5 class="fw-bold mb-3">Keterangan Skala Nilai</h5>
                        <div class="table-responsive">
                            <table class="table text-center text-white fw-bold mb-0">
                                <thead style="background-color: #2c3e50;">
                                    <tr>
                                        <th>Rentang Nilai</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background-color: #008000;">
                                        <td>> 100</td>
                                        <td>Sangat Baik</td>
                                    </tr>
                                    <tr style="background-color: #00ca00;">
                                        <td>80 – 99</td>
                                        <td>Baik</td>
                                    </tr>
                                    <tr style="background-color: #ffe600; color: #333;">
                                        <td>60 – 79</td>
                                        <td>Cukup</td>
                                    </tr>
                                    <tr style="background-color: #ff9900;">
                                        <td>40 – 59</td>
                                        <td>Pembinaan</td>
                                    </tr>
                                    <tr style="background-color: #dc3545;">
                                        <td>
                                            < 40</td>
                                        <td>Underperformance</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- NOTE KOMISI --}}
                        <div class="mt-4 p-3 rounded shadow-sm"
                            style="background-color: #f8fbff; border-left: 5px solid #007bff;">
                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-info-circle me-2"></i> KETENTUAN KOMISI &
                                BONUS</h6>
                            <ul class="small text-dark mb-0 ps-3" style="line-height: 1.6;">
                                <li><strong>Komisi Bulanan:</strong>
                                    < 1.25M (0.5%), ≥ 1.25M (0.75%), ≥ 1.5M (1%), ≥ 1.875M (1.25%)</li>
                                <li><strong>Bonus Konsistensi:</strong> Rp
                                    {{ number_format($bonus3BulanAmountFixed ?? 10000000, 0, ',', '.') }} (Reward bonus konsistensi selama 3 bulan berturut-turut)
                                </li>
                                <li><strong>Apresiasi Tahunan:</strong> {{ $rewardTahunanNama ?? 'Motor Yamaha NMAX' }}
                                    (Jika total omset 1 tahun ≥ Rp
                                    {{ number_format($targetTahunan ?? 12000000000, 0, ',', '.') }})
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- HISTORY SECTION --}}
                    <h4 class="fw-bold text-secondary mb-3">G. HISTORY KINERJA PER BULAN</h4>

                    <div class="d-flex overflow-auto pb-3" style="gap: 15px;">
                        @foreach(range(1, 12) as $m)
                            @php
                                $hVal = $historyNilai[$m] ?? 0;
                                // Tentukan warna bar kecil
                                if ($hVal > 100)
                                    $cBar = '#008000';
                                elseif ($hVal >= 80)
                                    $cBar = '#00ca00';
                                elseif ($hVal >= 60)
                                    $cBar = '#ffe600';
                                elseif ($hVal >= 40)
                                    $cBar = '#ff9900';
                                elseif ($hVal > 0)
                                    $cBar = '#dc3545';
                                else
                                    $cBar = '#e9ecef';
                            @endphp
                            <div class="card shadow-sm border text-center" style="min-width: 100px;">
                                <div class="card-body p-2">
                                    <div class="fw-bold text-secondary mb-2" style="font-size: 14px;">
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('M') }}
                                    </div>
                                    <div class="w-100 rounded mb-2" style="height: 6px; background-color: #eee;">
                                        <div class="h-100 rounded" style="width: 100%; background-color: {{ $cBar }};"></div>
                                    </div>
                                    <div class="fw-bold text-dark">{{ $hVal }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>

                {{-- Script to update Status Box dynamically based on Total Nilai --}}
                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        let total = {{ $totalNilaiHasil ?? 0 }};
                        let box = document.getElementById('statusBoxContainer');
                        let quote = document.getElementById('motivasiTextInline');
                        let bar = document.querySelector('.progress-bar');

                        let bg = '#dc3545'; // Default Red
                        let label = 'Underperformance';
                        let text = 'Ayo bangkit! Kamu belum terlambat untuk mengejar.';

                        if (total > 100) {
                            bg = '#008000'; label = 'Sangat Baik';
                            text = 'Luar biasa! Konsistensi kinerjamu sangat menginspirasi!';
                        } else if (total >= 80) {
                            bg = '#00ca00'; label = 'Baik';
                            text = 'Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik.';
                        } else if (total >= 60) {
                            bg = '#ffe600'; label = 'Cukup';
                            text = 'Cukup baik, tapi masih banyak ruang untuk berkembang.';
                        } else if (total >= 40) {
                            bg = '#ff9900'; label = 'Pembinaan';
                            text = 'Jangan menyerah, ini saatnya bangkit!';
                        }

                        if (box) {
                            box.style.backgroundColor = bg;
                            box.innerText = label + ' (' + total + ')';
                            if (total >= 60 && total < 80) box.style.color = '#333'; // Dark text for yellow
                        }
                        if (quote) quote.innerText = text;
                        if (bar) {
                            bar.style.backgroundColor = bg;
                            if (total >= 60 && total < 80) bar.style.color = '#333';
                        }
                    });
                </script>
            </div>
        </div>
    </div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>


<style>
    #kategoriBox.pulse {
        animation: pulseBox 1.2s infinite;
    }

    @keyframes pulseBox {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.04);
        }

        100% {
            transform: scale(1);
        }
    }
</style>

<script>
    // Ambil total nilai dari backend
    let totalNilaiHasil = {{ $totalNilaiHasil ?? 0 }};

    // Elemen target
    const box = document.getElementById("kategoriBox");
    const motivasi = document.getElementById("motivasiBox");

    // =============================
    // SKALA & MOTIVASI
    // =============================
    const kategori = [
        {
            min: 100, label: "Sangat Baik", bg: "#d1f7d3", border: "#8edb92", color: "#155724",
            motivasi: ["Luar biasa! Konsistensi kinerjamu sangat menginspirasi!"]
        },
        {
            min: 80, label: "Baik", bg: "#e9ffd6", border: "#c8eca2", color: "#35630a",
            motivasi: ["Kerja bagus! Tinggal sedikit lagi untuk mencapai level terbaik."]
        },
        {
            min: 60, label: "Cukup", bg: "#fff7d1", border: "#f0dc8a", color: "#8a6d00",
            motivasi: ["Cukup baik, tapi masih banyak ruang untuk berkembang."]
        },
        {
            min: 40, label: "Pembinaan", bg: "#ffe4d1", border: "#f3b693", color: "#7a2f00",
            motivasi: ["Jangan menyerah, ini saatnya bangkit!"]
        },
        {
            min: 0, label: "Underperformance", bg: "#fcd2d0", border: "#e39a96", color: "#811d1a",
            motivasi: ["Ayo bangkit! Kamu belum terlambat untuk mengejar."]
        }
    ];

    if (box && motivasi) {
        let hasil = kategori.find(k => totalNilaiHasil >= k.min) || kategori[kategori.length - 1];

        box.style.background = hasil.bg;
        box.style.borderColor = hasil.border;
        box.style.color = hasil.color;
        box.innerHTML = `${hasil.label} (${totalNilaiHasil})`;

        if (hasil.label === "Pembinaan" || hasil.label === "Underperformance") {
            box.classList.add("pulse");
        }

        motivasi.innerHTML = `
        <p style="padding:12px; border-left:5px solid ${hasil.color}">
            💬 <em>${hasil.motivasi[0]}</em>
        </p>
    `;
    }

    // === POPUP MOTIVASI LOGIC ===
    const motivasiQuotes = [
        "Kerja kerasmu hari ini adalah kesuksesanmu besok!",
        "Tetap fokus, kamu sudah sangat dekat dengan target!",
        "Percaya proses, hasil terbaik sedang menunggumu!",
        "Sedikit lagi! Kamu pasti bisa!",
        "Lakukan yang terbaik, Tuhan yang menyempurnakan!",
        "Jangan menyerah, kegagalan adalah awal dari keberhasilan!",
        "Setiap langkah kecil membawamu lebih dekat ke tujuan.",
        "Jadilah versi terbaik dari dirimu setiap hari.",
        "Tantangan adalah peluang untuk tumbuh.",
        "Sukses tidak datang dari apa yang kamu lakukan sesekali, tapi apa yang kamu lakukan secara konsisten."
    ];

    function tampilMotivasi() {
        // Pilih quote acak
        const quote = motivasiQuotes[Math.floor(Math.random() * motivasiQuotes.length)];
        const motivasiTextElement = document.getElementById('motivasiText');

        if (motivasiTextElement) {
            motivasiTextElement.innerText = '"' + quote + '"';

            document.getElementById('popupOverlay').style.display = 'block';
            document.getElementById('motivasiPopup').style.display = 'block';

            // Tandai sudah muncul di sesi ini
            sessionStorage.setItem('motivasi_shown', 'true');
        }
    }

    function tutupMotivasi() {
        document.getElementById('popupOverlay').style.display = 'none';
        document.getElementById('motivasiPopup').style.display = 'none';
    }

    // Muncul otomatis setelah 1.5 detik hanya jika belum pernah muncul di sesi ini
    if (!sessionStorage.getItem('motivasi_shown')) {
        setTimeout(tampilMotivasi, 1500);
    }

</script>