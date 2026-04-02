@extends('layouts.masteradmin')

@section('content')
    <!-- Chart JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .card-dashboard {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            background: #ffffff;
        }

        .card-dashboard:hover {
            transform: translateY(-5px);
        }

        .stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #2c3e50;
        }

        .trend-up {
            color: #27ae60;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .progress-custom {
            height: 12px;
            border-radius: 10px;
            background-color: #f1f2f6;
            margin-top: 10px;
        }

        .table-custom thead th {
            background-color: #f8f9fa;
            border: none;
            color: #636e72;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-custom tbody tr {
            border-bottom: 1px solid #f1f2f6;
        }

        .bg-main {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
        }

        .badge-soft-success {
            background: #e3fcef;
            color: #27ae60;
            border: none;
        }

        .badge-soft-warning {
            background: #fff9db;
            color: #f39c12;
            border: none;
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
            <h1 class="h4 mb-0 text-gray-800 fw-bold">
                <i class="fas fa-chart-pie text-primary me-2"></i> Ringkasan Eksekutif Penjualan
            </h1>
            <div class="small text-muted">Periode: {{ now()->format('M Y') }}</div>
        </div>

        {{-- HIGHLIGHT CARDS --}}
        <div class="row g-4 mb-4">
            <!-- Total Bulanan -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-dashboard p-3 h-100">
                    <div class="stat-label">Omset Bulan Ini</div>
                    <div class="stat-value text-primary">Rp{{ number_format($totalBulanan, 0, ',', '.') }}</div>
                    <div class="mt-2 text-muted" style="font-size: 0.7rem;">Dashboard Real-time</div>
                    <div class="mt-3">
                        <a href="{{ route('admin.salesplan.index') }}"
                            class="btn btn-sm btn-light text-primary fw-bold w-100 border-0">Detail Sales</a>
                    </div>
                </div>
            </div>

            <!-- Total Tahunan -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-dashboard p-3 h-100">
                    <div class="stat-label">Omset Tahun Ini</div>
                    <div class="stat-value text-success">Rp{{ number_format($totalTahunan, 0, ',', '.') }}</div>
                    <div class="trend-up mt-1">
                        <i class="fas fa-arrow-up"></i> +{{ $yoyGrowth }}% YoY
                    </div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-sm btn-light text-success fw-bold w-100 border-0">Laporan Tahunan</a>
                    </div>
                </div>
            </div>

            <!-- Avg/Day -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-dashboard p-3 h-100">
                    <div class="stat-label">Rata-rata Penjualan / Hari</div>
                    <div class="stat-value text-info">Rp{{ number_format($avgDay, 0, ',', '.') }}</div>
                    <div class="mt-2 text-muted" style="font-size: 0.7rem;">Update harian otomatis</div>
                    <div class="mt-3">
                        <a href="#" class="btn btn-sm btn-light text-info fw-bold w-100 border-0">Statistik Harian</a>
                    </div>
                </div>
            </div>

            <!-- Total Pelanggan -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card card-dashboard p-3 h-100">
                    <div class="stat-label">Database Pelanggan</div>
                    <div class="stat-value text-dark">{{ number_format($totalPelanggan, 0, ',', '.') }}</div>
                    <div class="mt-1 small text-success fw-bold">
                        +{{ $pelangganBaru }} Prospek Baru (Bulan Ini)
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.database.database') }}"
                            class="btn btn-sm btn-light text-dark fw-bold w-100 border-0">Kelola Prospek</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- PERFORMANCE METER --}}
            <div class="col-xl-6 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-bullseye text-danger me-2"></i> Target vs Realisasi Perusahaan
                        </h6>
                    </div>
                    <div class="card-body">
                        @php
                            $percent = $targetReal > 0 ? ($totalBulanan / $targetReal) * 100 : 0;
                            $percentFormatted = number_format($percent, 1);
                        @endphp
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0">Rp{{ number_format($totalBulanan, 0, ',', '.') }}</h4>
                            <div class="badge badge-soft-success py-2 px-3 fs-6 rounded-pill">{{ $percentFormatted }}%</div>
                        </div>

                        <div class="progress progress-custom">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: {{ min($percent, 100) }}%"></div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <div class="row text-center">
                                <div class="col-6 border-right">
                                    <div class="text-muted small">Target Bulan Ini</div>
                                    <div class="fw-bold fs-5">Rp{{ number_format($targetReal, 0, ',', '.') }}</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Kurang (Gap)</div>
                                    <div class="fw-bold fs-5 text-danger">
                                        Rp{{ number_format(max(0, $targetReal - $totalBulanan), 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info border-0 mt-4 py-2"
                            style="background-color: #e7f3ff; color: #0056b3; font-size: 0.85rem;">
                            <i class="fas fa-info-circle me-1"></i> Data diperbarui berdasarkan tagihan yang sudah
                            terverifikasi transfer.
                        </div>
                    </div>
                </div>
            </div>

            {{-- GROWTH CHART --}}
            <div class="col-xl-6 mb-4">
                <div class="card card-dashboard h-100">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="m-0 font-weight-bold text-dark">
                            <i class="fas fa-chart-line text-primary me-2"></i> Grafik Pendapatan Omset (Tahunan)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area" style="height: 250px;">
                            <canvas id="growthChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SALES RANKING --}}
        <div class="row mt-2">
            <div class="col-12">
                <div class="card card-dashboard">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="m-0 font-weight-bold text-dark d-flex align-items-center">
                            <i class="fas fa-chart-line text-primary me-2"></i>
                            Monitoring Pencapaian Omset Per Sales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-custom border-0" id="salesTable">
                                <thead class="bg-primary text-white">
                                    <tr>
                                        <th class="text-start py-3 px-4" style="border-top-left-radius: 10px;">Nama Staff Sales</th>
                                        <th>Target</th>
                                        <th>Tercapai</th>
                                        <th>Kekurangan</th>
                                        <th style="width: 150px;">Persentase</th>
                                        <th>Status</th>
                                        <th class="text-end px-4" style="border-top-right-radius: 10px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salesRanking as $sales)
                                        <tr>
                                            <td class="text-start py-3 px-4">
                                                <div class="fw-bold text-dark">{{ $sales->name }}</div>
                                                <div class="small text-muted">{{ $sales->email }}</div>
                                            </td>
                                            <td class="fw-bold">
                                                Rp {{ number_format($sales->target_omset, 0, ',', '.') }}
                                            </td>
                                            <td class="fw-bold text-success">
                                                Rp {{ number_format($sales->omset_bulan_ini, 0, ',', '.') }}
                                            </td>
                                            <td class="fw-bold text-danger">
                                                Rp {{ number_format($sales->kekurangan, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center flex-column">
                                                    <div class="progress w-100"
                                                        style="height: 8px; border-radius: 10px; background: #eaeff5;">
                                                        <div class="progress-bar {{ $sales->persentase >= 100 ? 'bg-success' : 'bg-warning' }}"
                                                            role="progressbar"
                                                            style="width: {{ min($sales->persentase, 100) }}%"></div>
                                                    </div>
                                                    <small class="fw-bold mt-1 text-dark">{{ $sales->persentase }}%</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if($sales->status_capaian === 'Tercapai')
                                                    <span class="badge bg-success rounded-pill px-3 py-2">Tercapai</span>
                                                @else
                                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2 shadow-sm">Belum
                                                        Tercapai</span>
                                                @endif
                                            </td>
                                            <td class="text-end px-4">
                                                <a href="{{ route('koordinasi.show', $sales->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 border-0 shadow-sm">
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const ctx = document.getElementById('growthChart').getContext('2d');

            // Gradient fill for chart
            const gradient = ctx.createLinearGradient(0, 0, 0, 250);
            gradient.addColorStop(0, 'rgba(0, 102, 255, 0.15)');
            gradient.addColorStop(1, 'rgba(0, 102, 255, 0.0)');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [{
                        label: 'Omset Penjualan',
                        data: @json($chartData),
                        borderColor: '#0066ff',
                        borderWidth: 3,
                        pointBackgroundColor: '#0066ff',
                        pointRadius: 3,
                        fill: true,
                        backgroundColor: gradient,
                        tension: 0.35,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(1) + 'M';
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(0) + 'jt';
                                    return 'Rp ' + value;
                                }
                            },
                            grid: { color: '#f8f9fa' }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
@endsection