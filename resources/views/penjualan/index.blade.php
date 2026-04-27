@extends('layouts.masteradmin')

@section('content')
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap 5 JS Bundle (termasuk Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid py-4">

    {{-- Header --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold text-primary mb-0 text-uppercase">
        <i class="fa-solid fa-cart-shopping me-2"></i> DASHBOARD PENJUALAN
    </h2>

    <form action="{{ route('penjualan.index') }}" method="GET" 
          class="d-flex align-items-center bg-light px-3 py-2 rounded shadow-sm">
        <label for="bulan" class="me-2 fw-semibold text-secondary mb-0">
            <i class="fa-solid fa-calendar-alt me-1 text-primary"></i> Filter Bulan:
        </label>
        &nbsp;
        <select name="bulan" id="bulan" 
                class="form-select form-select-sm border-primary fw-semibold text-primary me-3"
                style="width: 140px;" onchange="this.form.submit()">
            @foreach(range(1, 12) as $m)
                @php
                    $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                @endphp
                <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                    {{ $monthName }}
                </option>
            @endforeach
        </select>

        <!--<span class="badge bg-success shadow-sm px-3 py-2 text-white">-->
        <!--    <i class="fa-solid fa-clock me-1"></i> -->
        <!--    {{ now()->translatedFormat('F Y') }}-->
        <!--</span>-->
    </form>
</div>



{{-- Row: KPI Utama --}}
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm text-center" style="background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);">
            <div class="card-body py-4">
                <h6 class="text-muted mb-2 text-uppercase fw-bold" style="letter-spacing: 1px;">Total Pendapatan (Bulan Ini)</h6>
                <h2 class="fw-bold text-primary mb-0" style="font-size: 2.5rem;">
                    Rp {{ number_format($totalBulanan ?? 0, 0, ',', '.') }}
                </h2>
                <div class="mt-2">
                    <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill">
                        <i class="fa-solid fa-chart-line me-1"></i> Monitoring Real-time
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>








    <div class="row g-3 mb-4">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary fw-bold text-white py-3 text-uppercase">
                    <i class="fa-solid fa-users-gear me-2"></i> MONITORING CAPAIAN PENJUALAN PER SALES
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-4">Nama Staff Sales</th>
                                    <th>Total Unit Terjual</th>
                                    <th>Capaian (%)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($salesData as $sales)
                                <tr>
                                    <td class="text-start ps-4">
                                        <div class="fw-bold">{{ $sales['nama'] }}</div>
                                    </td>
                                    <td><span class="fs-5 fw-bold">{{ $sales['penjualan'] }}</span> unit</td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="progress w-50 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $sales['realisasi'] }}%"></div>
                                            </div>
                                            <span class="small fw-bold">{{ $sales['realisasi'] }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-success text-success border border-success px-3 py-2 rounded-pill">
                                            Aktif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-muted fst-italic">Data penjualan belum tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>



</div>


@endsection
