@extends('layouts.masteradmin')

@section('content')
    <!-- High Contrast Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body, .content-wrapper, h1, h2, h3, h4, h5, h6, table, div, span, p {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            color: #0f172a;
        }

        .card-dashboard {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            background: #ffffff;
        }

        .card-dashboard:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .stat-label {
            font-size: 0.8rem;
            color: #475569;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
        }

        .trend-up {
            color: #059669;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .progress-custom {
            height: 12px;
            border-radius: 10px;
            background-color: #f1f5f9;
            margin-top: 10px;
        }

        .table thead th {
            font-weight: 800 !important;
            color: #0f172a !important;
            font-size: 0.75rem !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: #f8fafc;
        }

        .table tbody td {
            font-weight: 600;
            color: #1e293b;
        }

        .text-primary { color: #2563eb !important; }
        .text-dark { color: #000000 !important; }
        .fw-bold { font-weight: 800 !important; }
        .fw-semibold { font-weight: 700 !important; }

        .bg-main {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: white;
        }

        .badge-soft-success {
            background: #dcfce7;
            color: #15803d;
            border: none;
            font-weight: 700;
        }

        .badge-soft-warning {
            background: #fef9c3;
            color: #a16207;
            border: none;
            font-weight: 700;
        }

        /* Responsive adjustment */
        @media (max-width: 576px) {
            .stat-value {
                font-size: 1.1rem;
            }
        }
    </style>

    <div class="container-fluid pb-5">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h4 mb-0 text-gray-800 fw-bold text-primary">
                <i class="fa-solid fa-cart-shopping me-2"></i> DASHBOARD PENJUALAN
            </h1>
            <form action="{{ route('administrator') }}" method="GET" id="filterForm" class="d-flex gap-2">
                <select name="bulan" class="form-select form-select-sm border-0 shadow-sm fw-bold rounded-pill" onchange="this.form.submit()" style="width: 140px;">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $bulanNum == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
                <select name="tahun" class="form-select form-select-sm border-0 shadow-sm fw-bold rounded-pill" onchange="this.form.submit()" style="width: 100px;">
                    @foreach(range(date('Y') - 5, date('Y')) as $y)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- HIGHLIGHT CARDS --}}
        <div class="row g-4 mb-4">
            <!-- Total Bulanan -->
            <div class="col-12">
                <div class="card card-dashboard p-4" style="background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);">
                    <div class="stat-label text-center">TOTAL PENDAPATAN (BULAN INI)</div>
                    <div class="stat-value text-primary text-center" style="font-size: 2.5rem;">RP {{ number_format($totalBulanan, 0, ',', '.') }}</div>
                    <div class="mt-2 text-center">
                        <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill fw-bold">
                            <i class="fa-solid fa-chart-line me-1"></i> MONITORING PENDAPATAN REAL-TIME
                        </span>
                    </div>
                </div>
            </div>
        </div>



        {{-- FULL WIDTH TABLE ROW --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-secondary">
                                <span class="me-2">🎯</span> Pencapaian Target Per Sales
                            </h4>
                        </div>
                        <div class="table-responsive rounded-4 shadow-sm border">
                            <table class="table align-middle mb-0">
                                <thead style="background-color: #0d6efd; color: white;">
                                    <tr style="height: 60px;">
                                        <th class="ps-4 border-0">Nama Sales</th>
                                        <th class="border-0">Target Omset</th>
                                        <th class="border-0">Tercapai</th>
                                        <th class="border-0">Kekurangan</th>
                                        <th class="border-0">Persentase</th>
                                        <th class="pe-4 border-0 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white">
                                    @php 
                                        $totalTarget = 0; 
                                        $totalTercapai = 0; 
                                        $totalKekurangan = 0;
                                        $labelsChart = [];
                                        $dataChart = [];
                                    @endphp
                                    @forelse($salesRanking as $sales)
                                        @php 
                                            $totalTarget += 1250000000; 
                                            $totalTercapai += $sales->omset_bulan_ini;
                                            $totalKekurangan += max(0, 1250000000 - $sales->omset_bulan_ini);
                                            $labelsChart[] = $sales->name;
                                            $dataChart[] = $sales->omset_bulan_ini;
                                        @endphp
                                        <tr class="border-bottom" style="height: 70px;">
                                            <td class="ps-4 fw-bold text-dark">{{ $sales->name }}</td>
                                            <td class="text-dark fw-semibold">Rp {{ number_format(1250000000, 0, ',', '.') }}</td>
                                            <td class="text-success fw-bold">Rp {{ number_format($sales->omset_bulan_ini, 0, ',', '.') }}</td>
                                            @php 
                                                $curPersen = 1250000000 > 0 ? round(($sales->omset_bulan_ini / 1250000000) * 100, 1) : 0;
                                                $curKekurangan = max(0, 1250000000 - $sales->omset_bulan_ini);
                                            @endphp
                                            <td class="text-danger fw-bold">Rp {{ number_format($curKekurangan, 0, ',', '.') }}</td>
                                            <td style="width: 250px;">
                                                <div class="d-flex align-items-center">
                                                    <div class="progress rounded-pill bg-light me-3" style="height: 10px; flex-grow: 1;">
                                                        <div class="progress-bar {{ $sales->omset_bulan_ini >= 1250000000 ? 'bg-success' : 'bg-primary' }}" 
                                                             role="progressbar" 
                                                             style="width: {{ min($curPersen, 100) }}%">
                                                        </div>
                                                    </div>
                                                    <small class="fw-bold text-dark">{{ $curPersen }}%</small>
                                                </div>
                                            </td>
                                            <td class="pe-4 text-center">
                                                <span class="badge {{ $sales->omset_bulan_ini >= 1250000000 ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-2 rounded-pill fw-bold" style="background-color: #fce7b2 !important; color: #856404 !important; border: none;">
                                                    {{ $sales->omset_bulan_ini >= 1250000000 ? 'Tercapai' : 'Belum Tercapai' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted fst-italic">Belum ada data sales tercatat.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot style="background-color: #f8f9fa;">
                                    <tr class="fw-bold" style="height: 70px;">
                                        <td class="ps-4 text-center">TOTAL</td>
                                        <td>Rp {{ number_format($totalTarget, 0, ',', '.') }}</td>
                                        <td class="text-success">Rp {{ number_format($totalTercapai, 0, ',', '.') }}</td>
                                        <td class="text-danger">Rp {{ number_format($totalTarget - $totalTercapai, 0, ',', '.') }}</td>
                                        <td>
                                            @php $avgPersen = $totalTarget > 0 ? round(($totalTercapai / $totalTarget) * 100, 1) : 0; @endphp
                                            <div class="d-flex align-items-center">
                                                <div class="progress rounded-pill bg-white me-3" style="height: 10px; flex-grow: 1; border: 1px solid #dee2e6;">
                                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ min($avgPersen, 100) }}%"></div>
                                                </div>
                                                <small class="text-dark">{{ $avgPersen }}%</small>
                                            </div>
                                        </td>
                                        <td class="pe-4 text-center"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FULL WIDTH CHART ROW --}}
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 20px;">
                    <div class="card-header bg-white py-4 border-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="bg-soft-primary p-2 rounded-circle me-3">
                                    <i class="fa-solid fa-chart-bar text-primary"></i>
                                </div>
                                <h5 class="fw-bold text-secondary mb-0">Grafik Penjualan Per Sales</h5>
                            </div>
                            <div class="small text-muted fw-bold">Periode: {{ \Carbon\Carbon::create()->month($bulanNum)->format('F') }} {{ $tahun }}</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:400px; width:100%">
                            <canvas id="rankingChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var ctx = document.getElementById('rankingChart').getContext('2d');
                var rankingChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($labelsChart) !!},
                        datasets: [{
                            label: 'Total Penjualan',
                            data: {!! json_encode($dataChart) !!},
                            backgroundColor: 'rgba(13, 110, 253, 0.7)',
                            borderColor: 'rgba(13, 110, 253, 1)',
                            borderWidth: 1,
                            borderRadius: 10,
                            barThickness: 40
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: {
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    fontColor: '#1e293b',
                                    fontStyle: 'bold',
                                    fontSize: 12,
                                    callback: function(value) {
                                        if (value >= 1000000000) return 'Rp' + (value / 1000000000).toFixed(1) + 'B';
                                        if (value >= 1000000) return 'Rp' + (value / 1000000).toFixed(0) + 'M';
                                        return 'Rp' + value;
                                    }
                                },
                                gridLines: { borderDash: [2], zeroLineBorderDash: [2], drawBorder: false, color: "rgba(226, 232, 240, 1)", zeroLineColor: "rgba(203, 213, 225, 1)" }
                            }],
                            xAxes: [{
                                gridLines: { display: false, drawBorder: false },
                                ticks: { 
                                    maxTicksLimit: 15,
                                    fontColor: '#1e293b',
                                    fontStyle: 'bold',
                                    fontSize: 12
                                }
                            }]
                        },
                        legend: { display: false },
                        tooltips: {
                            titleMarginBottom: 10,
                            titleFontColor: '#6e707e',
                            titleFontSize: 14,
                            backgroundColor: "rgb(255,255,255)",
                            bodyFontColor: "#858796",
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            xPadding: 15,
                            yPadding: 15,
                            displayColors: false,
                            caretPadding: 10,
                            callbacks: {
                                label: function(tooltipItem, chart) {
                                    return 'Total: Rp' + tooltipItem.yLabel.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                });
            });
        </script>
        @endpush

        <style>
            .bg-soft-primary { background-color: #e7f3ff; }
            .bg-soft-success { background-color: #e6fff1; }
            .bg-soft-warning { background-color: #fff8eb; }
            .bg-soft-info { background-color: #eefbff; }
            
            .badge.border-primary-subtle { border-color: #b3d7ff !important; }
            .badge.border-success-subtle { border-color: #b1f0c0 !important; }
        </style>
    </div>


@endsection