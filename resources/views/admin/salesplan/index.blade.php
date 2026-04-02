    @extends('layouts.masteradmin')
    @section('content')
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-scroll {
        max-height: calc(100vh - 50px);
        overflow-y: auto;
        }
        
        
        thead {
            background-color: #25799E;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        

        th {
            font-size: 14px;
            padding: 6px;
            text-align: left;
            color: white; /* Ensure header remains white */
        }

        td {
            font-size: 14px;
            padding: 6px;
            text-align: left;
            color: #000 !important; /* Force Black for data */
            font-weight: 500; 
        }

        @media only screen and (max-width: 768px) {

            table,
            thead,
            tbody,
            th,
            td,
            tr {
                display: block;
            }

            thead {
                display: none;
            }

            td {
                position: relative;
                padding-left: 50%;
            }

            td:before {
                position: absolute;
                left: 6px;
                white-space: nowrap;
                font-weight: bold;
            }

            td:nth-of-type(1):before {
                content: "Nama";
            }

            td:nth-of-type(2):before {
                content: "Kelas";
            }

            td:nth-of-type(3):before {
                content: "FU1 Hasil";
            }

            td:nth-of-type(4):before {
                content: "FU1 TL";
            }

            td:nth-of-type(5):before {
                content: "FU2 Hasil";
            }

            td:nth-of-type(6):before {
                content: "FU2 TL";
            }

            td:nth-of-type(7):before {
                content: "FU3 Hasil";
            }

            td:nth-of-type(8):before {
                content: "FU3 TL";
            }

            td:nth-of-type(9):before {
                content: "FU4 Hasil";
            }

            td:nth-of-type(10):before {
                content: "FU4 TL";
            }

            td:nth-of-type(11):before {
                content: "FU5 Hasil";
            }

            td:nth-of-type(12):before {
                content: "FU5 TL";
            }
            
            
            
            
        }
    </style>

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Sales Plan
            @if($kelasFilter)
            / {{ $kelasFilter }}
            @endif
        </h1>

        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item">Sales Plan</li>
                @if($kelasFilter)
                <li class="breadcrumb-item active">{{ $kelasFilter }}</li>
                @endif
            </ol>
        </div>
    </div>

    @if(session('message'))
    <div class="alert alert-info">
        {{ session('message') }}
    </div>
    @endif

    @if($salesplans->isEmpty())
    <div class="alert alert-info">
        Tidak ada data yang sesuai dengan filter.
    </div>
    @else
    {{-- tampilkan tabel atau isi salesplans --}}
    @endif

    <div class="container">
    @php
        $targetOmset = 1250000000; // Rp 1.250.000.000
        $groupedByCS = $salesplans->groupBy('created_by');

        $namaCS = [
            1 => 'Administrator',
            2 => 'Linda',
            3 => 'Yasmin',
            4 => 'Tursia',
            10 => 'Qiyya',
            6 => 'Shafa',
        ];

        // Hitung total keseluruhan
        $totalSeluruhCS = $salesplans->sum('nominal');
        $totalTargetSemua = $targetOmset * count($groupedByCS);
        $totalKekurangan = max(0, $totalTargetSemua - $totalSeluruhCS);
        $persentaseTotal = $totalTargetSemua > 0 ? round(($totalSeluruhCS / $totalTargetSemua) * 100, 1) : 0;
    @endphp

    <!-- Filter hanya administrator -->
    @if(auth()->id() == 1 || auth()->id() == 13 || (auth()->user()->name == 'Linda' && empty($isRestrictedView)))
    <style>
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label {
            font-weight: 600;
            color: #333;
            white-space: nowrap;
        }

        .filter-select {
            min-width: 180px;
            padding: 0.45rem 0.75rem;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .filter-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            outline: none;
        }

        .btn-reset {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.85rem;
            padding: 0.45rem 0.75rem;
            border-radius: 8px;
        }

        .btn-reset i {
            font-size: 0.9rem;
        }

        @media (max-width: 576px) {
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-select {
                width: 100%;
            }
            .filter-group {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
    <form method="GET" action="{{ route('admin.salesplan.index') }}" class="filter-container">
        {{-- Filter Sales (Hanya untuk Admin/Manager/Linda) --}}
        @if(auth()->user()->role === 'administrator' || auth()->user()->role === 'manager' || auth()->user()->name === 'Linda')
        <div class="filter-group">
            <label for="cs_filter_top" class="filter-label">
                <i class="fas fa-user-tie text-primary"></i> Sales:
            </label>
            <select name="created_by" id="cs_filter_top" class="form-select filter-select" onchange="this.form.submit()">
                <option value="">👤 Semua Sales</option>
                @foreach($csList as $cs)
                    <option value="{{ $cs->id }}" {{ request('created_by') == $cs->id ? 'selected' : '' }}>
                        {{ $cs->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @endif

        <div class="filter-group">
            <label for="bulan_filter" class="filter-label">
                <i class="fas fa-calendar-alt text-info"></i> Bulan:
            </label>
            <select name="bulan" id="bulan_filter" class="form-select filter-select" onchange="this.form.submit()">
                <option value="">-- Semua Bulan --</option>
                @foreach([
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember'
                ] as $num => $name)
                    <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filter-group">
            <label for="tahun_filter" class="filter-label">
                <i class="fas fa-calendar text-secondary"></i> Tahun:
            </label>
            <select name="tahun" id="tahun_filter" class="form-select filter-select" onchange="this.form.submit()">
                <option value="">-- Semua Tahun --</option>
                @php $currentYear = date('Y'); @endphp
                @for ($i = $currentYear; $i >= $currentYear - 3; $i--)
                    <option value="{{ $i }}" {{ request('tahun', $currentYear) == $i ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
        </div>
    </form>
    @endif







    {{-- Font Awesome CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">



    @php
        // Ambil nilai dari filter
        $bulanFilter = request('bulan');
        $kelasFilter = request('kelas');

        $totalTargetSemua = 0;
        $totalSeluruhCS   = 0;
    @endphp


    <h4 class="mt-5 mb-4 fw-bold text-center text-dark">🎯 Pencapaian Target Per Sales</h4>

    <div class="table-responsive">
        <table class="table table-hover table-striped align-middle shadow-sm">
            <thead class="table-primary text-center">
                <tr>
                    <th>Nama Sales</th>
                    <th>Target Omset</th>
                    <th>Tercapai</th>
                    <th>Kekurangan</th>
                    <th>Persentase</th>
                    <th>Status</th>
                </tr>
            </thead>
        <tbody>
    @php
        $isSmiClass = ($kelasFilter == 'Start-Up Muda Indonesia' || $kelasFilter == 'Start-Up Muslim Indonesia');

        $usersToLoop = collect();

        if ($isSmiClass && empty(request('created_by'))) {
            // Jika filter kelas SMI & Semua CS -> Tampilkan semua CS dengan role cs-mbc & cs-smi
            $usersToLoop = $csList->filter(function($u) {
                return in_array(strtolower($u->role), ['cs-mbc', 'cs-smi']);
            });
        } else {
        // Jika Filter Tim tidak dipilih -> Tampilkan semua CS di list (termasuk yang 0 pencapaian)
            if (empty(request('created_by'))) {
                $usersToLoop = $csList;
            } else {
                // Jika pilih CS spesifik -> Tampilkan hanya dia
                $existingIds = $salesplansByCS->keys();
                // Fallback: jika user filter CS tapi datanya kosong, tetap tampilkan CS tersebut dari $csList
                $usersToLoop = $csList->where('id', request('created_by'));
            }
        }
    @endphp

    @foreach ($usersToLoop as $cs)
        @php
            // Skip administrator jika bukan filter spesifik
            if (strtolower($cs->name) === 'administrator' && empty(request('created_by'))) continue;

            // Skip jika user biasa mencoba melihat orang lain (logic pertahanan)
            if (auth()->user()->role !== 'administrator' && auth()->user()->role !== 'manager' && auth()->user()->name !== 'Linda' && $cs->id !== auth()->id()) continue;

            // Ambil items salesplan untuk CS ini (bisa kosong)
            $items = $salesplansByCS->get($cs->id, collect());

        // Hitung nominal (Gunakan Real Stats agar Total tidak terpotong pagination)
            if (isset($salesplanStats[$cs->id])) {
                $totalNominal = $salesplanStats[$cs->id];
            } else {
                $totalNominal = $items->sum('nominal');
            }

            // Tentukan Target Omset: 1.25 Miliar per bulan
            $targetOmset = 1250000000;
            $selisih = max(0, $targetOmset - $totalNominal);
            $tercapai = $totalNominal >= $targetOmset;
            $persentase = $targetOmset > 0 ? round(($totalNominal / $targetOmset) * 100, 1) : 0;
            
            $totalTargetSemua += $targetOmset;
            $totalSeluruhCS   += $totalNominal;
        @endphp

        <tr>
            <td class="fw-semibold">{{ $cs->name }}</td>
            <td class="text-end">Rp {{ number_format($targetOmset, 0, ',', '.') }}</td>
            <td class="text-end text-success fw-semibold">
                Rp {{ number_format($totalNominal, 0, ',', '.') }}
            </td>
            <td class="text-end text-danger">
                Rp {{ number_format($selisih, 0, ',', '.') }}
            </td>
            <td style="min-width: 150px;">
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar {{ $tercapai ? 'bg-success' : 'bg-warning' }}"
                        role="progressbar"
                        style="width: {{ min($persentase, 100) }}%;"
                        aria-valuenow="{{ $persentase }}" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <small class="d-block text-center mt-1 fw-semibold">{{ $persentase }}%</small>
            </td>
            <td class="text-center">
                @if($tercapai)
                    <span class="badge bg-success">Tercapai</span>
                @else
                    <span class="badge bg-warning text-dark">Belum Tercapai</span>
                @endif
            </td>
        </tr>
    @endforeach

    </tbody>




            
            
            
            <tfoot class="table-light fw-bold">
                <tr>
                    <td class="text-center">TOTAL</td>
                    <td class="text-end">
                        Rp {{ number_format($totalTargetSemua, 0, ',', '.') }}
                    </td>
                    <td class="text-end text-success">
                        Rp {{ number_format($totalSeluruhCS, 0, ',', '.') }}
                    </td>
                    <td class="text-end text-danger">
                        Rp {{ number_format($totalKekurangan, 0, ',', '.') }}
                    </td>
                    <td>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $persentaseTotal >= 100 ? 'bg-success' : 'bg-info' }}"
                                role="progressbar"
                                style="width: {{ min($persentaseTotal, 100) }}%;"
                                aria-valuenow="{{ $persentaseTotal }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="d-block text-center mt-1 fw-semibold">{{ $persentaseTotal }}%</small>
                    </td>
                
                </tr>
            </tfoot>
        </table>
    </div>



    <style>
        table {
            border-radius: 10px;
            overflow: hidden;
        }
        thead {
            background: linear-gradient(90deg, #0d6efd, #0b5ed7);
            color: #fff;
        }
        tbody tr {
            font-weight: bold;
            color: #000;
        }
        tbody tr:hover {
            background-color: #f8f9fa;
            transition: background 0.2s ease;
        }
        tfoot {
            background-color: #e7f0ff;
            font-weight: 600;
        }
        tfoot td {
            border-top: 2px solid #0d6efd;
        }
        .badge {
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
            border-radius: 8px;
        }
        .table th, .table td { vertical-align: middle; }

        /* Row Status Colors (Brighter - Vibrant) */
        .row-tunai { background-color: #48e7ecff !important; }
        .row-kpr { background-color: #1cc600 !important; }
        .row-tertarik { background-color: #ffd900ff !important; }
        .row-no { background-color: #ff4d4d !important; color: #fff !important; }
        .row-cold { background-color: #ffffff !important; }

        /* Ensure all TDs in colored rows follow the background */
        .row-tunai td, .row-kpr td, .row-tertarik td, .row-no td {
            background-color: transparent !important;
        }

        /* Dropdown Styling matching Status */
        .status-dropdown.status-sudah_transfer { background-color: #48e7ecff; color: #000; font-weight: bold; }
        .status-dropdown.status-mau_transfer { background-color: #1cc600; color: #000; font-weight: bold; }
        .status-dropdown.status-tertarik { background-color: #ffd900ff; color: #000; font-weight: bold; }
        .status-dropdown.status-no { background-color: #ff4d4d; color: #fff; font-weight: bold; }
        .status-dropdown.status-cold { background-color: #ffffff; color: #000; }

        /* Custom Badge Colors for Leads */
        .badge-leads-iklan { background-color: #28a745; color: white; }
        .badge-leads-referal { background-color: #dc3545; color: white; }
        .badge-leads-marketing { background-color: #ffc107; color: black; }
        .badge-leads-mandiri { background-color: #0d6efd; color: white; }
        .badge-leads-pameran { background-color: #fd7e14; color: white; }
        .badge-leads-sosmed { background-color: #6f42c1; color: white; }
        .badge-leads-canvasing { background-color: #20c997; color: white; }
        .badge-leads-lain { background-color: #6c757d; color: white; }

        .editable {
            cursor: text;
            transition: background-color 0.2s;
        }
        .editable:hover {
            background-color: #f1f8ff !important;
            outline: 1px dashed #ccc;
        }
        .editable:focus {
            background-color: #fff !important;
            outline: 2px solid #0d6efd;
            color: #000;
            min-width: 100px;
        }

        /* KPR Tracker Mini Stepper */
        .kpr-tracker-mini {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 0;
            padding: 8px 0;
        }
        .kpr-step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 65px;
            position: relative;
        }
        .kpr-step-dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 2;
        }
        .kpr-step-label {
            font-size: 8px;
            margin-top: 5px;
            color: #94a3b8;
            font-weight: 800;
            text-transform: uppercase;
        }
        .kpr-step-dot.active { background-color: #3b82f6; border-color: #3b82f6; box-shadow: 0 0 10px rgba(59, 130, 246, 0.4); }
        .kpr-step-dot.active + .kpr-step-label { color: #3b82f6; }
        .kpr-step-dot.completed { background-color: #10b981; border-color: #10b981; }
        .kpr-step-dot.completed + .kpr-step-label { color: #10b981; }
        .kpr-step-line-chevron {
            align-self: flex-start;
            margin-top: 4px;
            font-size: 14px;
            color: #e2e8f0;
        }
        .kpr-step-line-chevron.completed { color: #10b981 !important; }
        .kpr-step-line-chevron.active { color: #3b82f6 !important; }
    </style>




        <!--<a href="{{ route('salesplan.export') }}" class="btn btn-success mb-3">-->
        <!--    Download Excel-->
        <!--</a>-->
        <div class="card shadow-lg border-0 rounded-lg mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Daftar Sales Plan</h5>
            </div>
            <div class="card-body">
        @php
        $countTertarik = $salesplans->where('status', 'tertarik')->count();
        $countMauTransfer = $salesplans->where('status', 'mau_transfer')->count();
        $countNo = $salesplans->where('status', 'no')->count();
        $countSudahTransfer = $salesplans->where('status', 'sudah_transfer')->count();
        $countCold = $salesplans->where('status', 'cold')->count();

        $totalSalesplan = $countTertarik + $countMauTransfer + $countNo + $countSudahTransfer + $countCold;

        $targetSalesplan = 2;
        $selisihTarget = $targetSalesplan - $totalSalesplan;
    @endphp

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between gap-5">
            
            <!-- Target -->
            <div class="text-center">
                <div class="mb-1 fw-semibold text-dark">
                    Target
                </div>
                <span class="badge bg-primary fs-5 px-4 py-2 fw-bold text-white">
                    {{ $targetSalesplan }} unit
                </span>
            </div>
            &nbsp;
            <!-- Sudah -->
            <div class="text-center">
                <div class="mb-1 fw-semibold text-dark">
                    Sudah
                </div>
                <span class="badge bg-success fs-5 px-4 py-2 fw-bold text-white">
                    {{ $totalSalesplan }}
                </span>
            </div>
            &nbsp;
            <!-- Belum -->
            <div class="text-center">
                <div class="mb-1 fw-semibold text-dark">
                    Belum
                </div>
                <span class="badge bg-danger fs-5 px-4 py-2 fw-bold text-white">
                    {{ max(0, $targetSalesplan - $totalSalesplan) }}
                </span>
            </div>
            &nbsp;
            <!-- Keterangan -->
            <div class="text-center">
                <div class="mb-1 fw-semibold text-dark">
                    Keterangan
                </div>
                @if($totalSalesplan >= $targetSalesplan)
                    <span class="badge bg-success fs-6 px-4 py-2 fw-bold text-white">
                        🎉 Tercapai
                    </span>
                @else
                    <span class="badge bg-danger fs-6 px-4 py-2 fw-bold text-white">
                        ⚠️ Belum tercapai
                    </span>
                @endif
            </div>

        </div>

        <!-- Progress bar -->

    </div>


    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="input-group" style="max-width: 350px;">
        
            <input type="text" id="searchSalesPlan" class="form-control" placeholder="Cari nama peserta...">
        </div>
        
        
    <!-- FILTER STATUS (Modern Style) -->



            <form method="GET" class="d-flex gap-2">
                
                @if(request('created_by') && (auth()->user()->role !== 'administrator' && auth()->user()->role !== 'manager' && auth()->user()->name !== 'Linda'))
                <input type="hidden" name="created_by" value="{{ request('created_by') }}">
                @endif

                <select name="kelas" id="kelas_filter_cs"
                    class="form-select filter-select"
                    onchange="this.form.submit()">
                    <option value="">🏠 Semua Produk</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->nama_kelas }}" {{ request('kelas') == $kelas->nama_kelas ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>

                <select name="bulan" id="bulan_filter_cs"
                    class="form-select filter-select"
                    onchange="this.form.submit()">
                    <option value="">📅 Semua Bulan</option>
                    @foreach([
                        '01' => 'Januari',
                        '02' => 'Februari',
                        '03' => 'Maret',
                        '04' => 'April',
                        '05' => 'Mei',
                        '06' => 'Juni',
                        '07' => 'Juli',
                        '08' => 'Agustus',
                        '09' => 'September',
                        '10' => 'Oktober',
                        '11' => 'November',
                        '12' => 'Desember'
                    ] as $num => $name)
                        <option value="{{ $num }}" {{ request('bulan') == $num ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                
                        <select name="tahun" id="tahun_filter_cs"
                    class="form-select filter-select"
                    onchange="this.form.submit()">
                    <option value="" {{ request()->has('tahun') && request('tahun') == '' ? 'selected' : '' }}>📅 Semua Tahun</option>
                    @php $currentYear = date('Y'); @endphp
                    @for ($i = $currentYear; $i >= $currentYear - 3; $i--)
                        <option value="{{ $i }}" {{ request('tahun', $currentYear) == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>

                <select name="status" id="status_filter"
                    class="form-select filter-select"
                    onchange="this.form.submit()">

                    <option value="">🔍 Semua Status</option>

                    <option value="cold" {{ request('status') == 'cold' ? 'selected' : '' }}>
                        ⚪ Cold 
                    </option>

                    <option value="tertarik" {{ request('status') == 'tertarik' ? 'selected' : '' }}>
                        🟡 Tertarik 
                    </option>

                    <option value="mau_transfer" {{ request('status') == 'mau_transfer' ? 'selected' : '' }}>
                        🟢 Mau Transfer 
                    </option>

                    <option value="sudah_transfer" {{ request('status') == 'sudah_transfer' ? 'selected' : '' }}>
                        🔵 Sudah Transfer 
                    </option>

                    <option value="no" {{ request('status') == 'no' ? 'selected' : '' }}>
                        🔴 No 
                    </option>

                </select>
            </form>

        


    <style>
        /* Card ringan pembungkus */
        .filter-container {
            background: #ffffff;
            border-radius: 12px;
            border-left: 5px solid #ffb300;
            transition: 0.2s ease-in-out;
        }

        .filter-container:hover {
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
            transform: translateY(-1px);
        }

        /* Select Style */
        .filter-select {
            min-width: 230px;
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid #ddd;
            transition: 0.2s ease-in-out;
            background-color: #fafafa;
            cursor: pointer;
        }

        .filter-select:hover {
            box-shadow: 0 0 8px rgba(255, 179, 0, 0.4);
            border-color: #ffb300;
        }

        .filter-select:focus {
            border-color: #ffb300;
            box-shadow: 0 0 8px rgba(255, 179, 0, 0.6);
        }
    </style>
        
    </div>




    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#searchSalesPlan').on('keyup', function() {
            let query = $(this).val().toLowerCase();

            $('table tbody tr').each(function() {
                let nama = $(this).find('td:nth-child(2)').text().toLowerCase(); // kolom Nama
                if (nama.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>


    </div>

                <div class="table-responsive table-scroll">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="text-white" style="background-color:#25799E;">


                            <tr>
                                <th rowspan="3">No</th>
                                <th rowspan="3">Nama</th>
                                <th rowspan="3">Potensi</th>
                                <th rowspan="3">Status</th>
                                @if(strtolower(auth()->user()->role) !== 'administrator')
                                    <th rowspan="3">Sumber Leads</th>
                                @endif
                            {{-- <th rowspan="3">
        {{ $kelasFilter == 'Start-Up Muda Indonesia' ? 'Situasi Anak' : 'Situasi Bisnis' }}
    </th> --}}
                                <th rowspan="3">KEBUTUHAN</th>

                                {{-- Header grup untuk FU --}}
                                <th colspan="10" class="text-center">Follow Up</th>

                                @if(Auth::user()->email == "mbchamasah@gmail.com")
                                <th rowspan="3">Input Oleh</th>
                                @endif
                                <th rowspan="3">Hapus</th>
                            </tr>
                            <tr>
                                {{-- Header FU 1 - 5 --}}
                                @for ($i = 1; $i <= 5; $i++)
                                    <th colspan="2" class="text-center">FU {{ $i }}</th>
                                    @endfor
                            </tr>
                            <tr>
                                {{-- Sub kolom Hasil & Tindak Lanjut --}}
                                @for ($i = 1; $i <= 5; $i++)
                                    <th>Hasil FU</th>
                                    <th>Rencana Tindak Lanjut</th>
                                    @endfor
                            </tr>
                        </thead>



                        <tbody id="salesPlanBody">
                            @php $currentMonth = null; @endphp
                            @forelse ($salesplans as $plan)
                                @if($plan->created_at)
                                    @php
                                        $planMonth = \Carbon\Carbon::parse($plan->created_at)->locale('id')->isoFormat('MMMM Y');
                                    @endphp
                                    @if($currentMonth !== $planMonth)
                                        <tr class="table-light">
                                            <td colspan="25" class="fw-bold text-start ps-4 py-2" style="background-color: #e9ecef;">
                                                🗓️ {{ $planMonth }}
                                            </td>
                                        </tr>
                                        @php $currentMonth = $planMonth; @endphp
                                    @endif
                                @endif

                                @php
                                    $rowClass = 'row-cold';
                                    if ($plan->status == 'sudah_transfer') $rowClass = 'row-tunai';
                                    elseif ($plan->status == 'mau_transfer') $rowClass = 'row-kpr';
                                    elseif ($plan->status == 'tertarik') $rowClass = 'row-tertarik';
                                    elseif ($plan->status == 'no') $rowClass = 'row-no';

                                    // Check KPR Status
                                    $kpr = \App\Models\Kpr::where('salesplan_id', $plan->id)->first() 
                                           ?? \App\Models\Kpr::where('nama', $plan->nama)->first();
                                @endphp

                                <tr class="{{ $rowClass }}">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $plan->nama ?? '-' }}</div>
                                    </td>
                                    {{-- Potensi (Nominal) --}}
                                    <td @if(!isset($plan->total_nominal_aggregated)) contenteditable="true" @endif class="editable fw-bold text-dark text-center"
                                        data-id="{{ $plan->id }}"
                                        data-field="nominal">
                                        @if(isset($plan->total_nominal_aggregated))
                                            {{ number_format($plan->total_nominal_aggregated, 0, ',', '.') }}
                                        @else
                                            {{ number_format($plan->nominal, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    {{-- Status Dropdown --}}
                                    <td class="text-center">
                                        <select class="form-control form-control-sm status-dropdown status-{{ $plan->status }}"
                                            data-id="{{ $plan->id }}"
                                            style="min-width: 140px;">
                                            <option value="sudah_transfer" {{ $plan->status == 'sudah_transfer' ? 'selected' : '' }}>Tunai</option>
                                            <option value="mau_transfer" {{ $plan->status == 'mau_transfer' ? 'selected' : '' }}>KPR</option>
                                            <option value="tertarik" {{ $plan->status == 'tertarik' ? 'selected' : '' }}>Tertarik</option>
                                            <option value="cold" {{ $plan->status == 'cold' ? 'selected' : '' }}>Cold</option>
                                            <option value="no" {{ $plan->status == 'no' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </td>
                                    @if(strtolower(auth()->user()->role) !== 'administrator')
                                        @php
                                            $leadSource = $plan->data->leads ?? ($dataMap[$plan->nama]->leads ?? '-');
                                            $leadLower = strtolower($leadSource);
                                            $badgeClass = 'badge-leads-lain';
                                            if (str_contains($leadLower, 'iklan')) $badgeClass = 'badge-leads-iklan';
                                            elseif (str_contains($leadLower, 'referal') || str_contains($leadLower, 'alumni')) $badgeClass = 'badge-leads-referal';
                                            elseif (str_contains($leadLower, 'marketing')) $badgeClass = 'badge-leads-marketing';
                                            elseif (str_contains($leadLower, 'mandiri')) $badgeClass = 'badge-leads-mandiri';
                                            elseif (str_contains($leadLower, 'pameran')) $badgeClass = 'badge-leads-pameran';
                                            elseif (str_contains($leadLower, 'sosmed')) $badgeClass = 'badge-leads-sosmed';
                                            elseif (str_contains($leadLower, 'canvasing')) $badgeClass = 'badge-leads-canvasing';
                                        @endphp
                                        <td><span class="badge {{ $badgeClass }}">{{ $leadSource }}</span></td>
                                    @endif
                                    
                                    <td contenteditable="true" class="editable bg-light"
                                        data-id="{{ $plan->id }}"
                                        data-field="kebutuhan">
                                        {{ $plan->kebutuhan ?? '-' }}
                                    </td>

                                    @for ($i = 1; $i <= 5; $i++)
                                        <td contenteditable="true" class="editable bg-light"
                                            data-id="{{ $plan->id }}"
                                            data-field="fu{{ $i }}_hasil">
                                            {{ $plan->{'fu'.$i.'_hasil'} ?? '-' }}
                                        </td>
                                        <td contenteditable="true" class="editable text-dark"
                                            data-id="{{ $plan->id }}"
                                            data-field="fu{{ $i }}_tindak_lanjut">
                                            {{ $plan->{'fu'.$i.'_tindak_lanjut'} ?? '-' }}
                                        </td>
                                    @endfor

                                    @if(Auth::user()->email == "mbchamasah@gmail.com")
                                    <td>{{ \App\Models\User::find($plan->created_by)->name ?? '-' }}</td>
                                    @endif

                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm btn-delete" data-id="{{ $plan->id }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                        <form id="delete-form-{{ $plan->id }}" action="{{ route('admin.salesplan.destroy', $plan->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="25" class="text-center text-muted">Tidak ada data sales plan ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
                <div class="d-flex justify-content-center mt-3">
        @if(method_exists($salesplans, 'links'))
                {{ $salesplans->links('pagination::bootstrap-4') }}
    @endif

                </div>
    <style>
        /* Fix for giant pagination icons if Tailwind view leaks in */
        nav svg {
            max-height: 20px;
            width: auto;
        }
    </style>


            </div>
        </div>


        <script>
            // Simpan nilai awal saat fokus
            $(document).on('focus', '.editable', function() {
                let currentText = $(this).text().trim();
                $(this).data('original', currentText);
                
                // UX: Jika isinya hanya strip '-', kosongkan saat user mau ngetik
                if (currentText === '-') {
                    $(this).text('');
                }
            });

            $(document).on('blur', '.editable', function() {
                let id = $(this).data('id');
                let field = $(this).data('field');
                let value = $(this).text().trim();
                let original = $(this).data('original');
                let $element = $(this); // Capture element reference

                // Jika kosong, kembalikan ke '-' agar rapi
                if (value === '') {
                    value = '-';
                    $element.text('-');
                }

                // Jika tidak ada perubahan, jangan kirim request
                if (value === original) return;

                $.ajax({
                    url: "{{ route('admin.salesplan.inline-update') }}", 
                    type: "POST",
                    context: this, // Ensure 'this' refers to the DOM element in callbacks
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        field: field,
                        value: value
                    },
                    success: function(res) {
                        console.log("✅ Update sukses:", res);
                        $element.data('original', value);
                        
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: 'Tersimpan'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error("❌ Gagal update:", xhr.responseText);
                        $element.text(original); // Revert safely
                        
                        let msg = "Gagal update data!";
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            msg += "\n" + xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg += "\n" + xhr.responseJSON.error;
                        } else {
                            msg += "\nStatus: " + xhr.status + " " + xhr.statusText;
                            if (xhr.responseText) {
                                msg += "\n" + xhr.responseText.substring(0, 50);
                            }
                        }
                        alert(msg);
                    }
                });
            });
        </script>






    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="badge bg-warning text-white p-2 me-2 fs-6" style="font-size: 13px">
                Tertarik: {{ $countTertarik }}
            </span>
            <span class="badge bg-success text-white p-2 me-2 fs-6" style="font-size: 13px">
                KPR: {{ $countMauTransfer }}
            </span>
            <span class="badge bg-danger text-white p-2 me-2 fs-6" style="font-size: 13px">
                No: {{ $countNo }}
            </span>
            <span class="badge bg-info text-white p-2 me-2  fs-6" style="font-size: 13px">
                Tunai: {{ $countSudahTransfer }}
            </span>
            <span class="badge bg-secondary text-white p-2 fs-6"style="font-size: 13px">
                Cold: {{ $countCold }}
            </span>
        </div>
    </div>
    </div>



    {{-- Tabel Sales Plan yang sudah ada --}}

    {{-- Tabel Daftar Pembeli (Eks Peserta) --}}
    <h4 class="mt-4 fw-bold text-center">
        Daftar Pembeli / {{ $kelasFilter }}
        @if(auth()->check() && strtolower(auth()->user()->role) === 'cs-mbc')
            - {{ auth()->user()->name }}
        @endif
    </h4>


    <!-- Dropdown contoh -->

    <hr>


    <!-- Tabel daftar peserta -->
    <div class="d-flex justify-content-end align-items-center mb-2">
        <div style="width: 200px;">
            <label class="small fw-bold mb-0">Filter Status:</label>
            <select id="filterStatusDaftar" class="form-control form-control-sm">
                <option value="all">Semua Status</option>
                <option value="Tunai">Tunai</option>
                <option value="KPR">KPR</option>
            </select>
        </div>
    </div>

    <div style="overflow-x: auto; white-space: nowrap;">
        <table id="tabelPeserta" style="border-collapse: collapse; width: 100%; text-align: center; font-family: Arial, sans-serif; font-size: 14px; min-width: 500px;">
            <thead>
                <tr style="background: linear-gradient(to right, #376bb9ff, #1c7f91ff); color: white;">
                    <th style="padding: 10px; border: 1px solid #ccc;">No</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Nama Peserta</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Status</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Nominal</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">KPR Action</th>
                    <th style="padding: 10px; border: 1px solid #ccc;">Monitoring KPR</th>
                </tr>
            </thead>
    <tbody style="font-weight: bold; color: #000;">
        @php $totalNominal = 0; @endphp
        @forelse(($pesertaTransfer ?? collect()) as $i => $p)
            @php 
                $statusLabel = ($p->status == 'mau_transfer') ? 'KPR' : 'Tunai'; 
                $statusBadge = ($p->status == 'mau_transfer') ? 'bg-success' : 'bg-info';
            @endphp
            <tr class="peserta-row" data-status="{{ $statusLabel }}">
                <td style="padding: 8px; border: 1px solid #ccc;">{{ $i+1 }}</td>
                <td style="padding: 8px; border: 1px solid #ccc;">{{ $p->nama }}</td>
                <td style="padding: 8px; border: 1px solid #ccc;">
                    <span class="badge {{ $statusBadge }} text-white">{{ $statusLabel }}</span>
                </td>
                <td style="padding: 8px; border: 1px solid #ccc;">
                    Rp {{ number_format($p->nominal, 0, ',', '.') }}
                </td>
                <td style="padding: 8px; border: 1px solid #ccc;">
                    @if($p->status == 'mau_transfer' && !$p->kpr)
                    <form action="{{ route('admin.kpr.move', $p->id) }}" method="POST" class="form-kpr-move">
                        @csrf
                        <button type="button" class="btn btn-sm btn-primary btn-move-kpr" data-nama="{{ $p->nama }}">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                    @else
                        -
                    @endif
                </td>
                <td style="padding: 8px; border: 1px solid #ccc;">
                    @if($p->status == 'mau_transfer')
                        @php
                            $kprP = $p->kpr; 
                        @endphp
                        @if($kprP)
                            <div class="kpr-tracker-mini d-flex justify-content-center">
                                @php
                                    $stepOrder = ['Booking Fee', 'Berkas KPR', 'Pengajuan Bank', 'Appraisal', 'SP3K/Approval', 'Akad Kredit', 'Pencairan/Final'];
                                    $stepLabels = ['Booking', 'Berkas', 'Bank', 'Appraisal', 'Approval', 'Akad', 'Final'];
                                    $currentIndex = array_search($kprP->tahap_posisi, $stepOrder);
                                    if ($currentIndex === false) $currentIndex = 0;
                                    if ($kprP->status_global == 'Success') $currentIndex = 99;
                                @endphp
                                @foreach($stepOrder as $idx => $stage)
                                    @php
                                        $isCompleted = ($idx < $currentIndex || $kprP->status_global == 'Success');
                                        $isActive = ($idx === $currentIndex && $kprP->status_global != 'Success');
                                    @endphp
                                    <div class="kpr-step-item">
                                        <div class="kpr-step-dot {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }} kpr-clickable-dot" 
                                             data-kpr-id="{{ $kprP->id }}" 
                                             data-stage="{{ $stage }}"
                                             style="cursor: pointer;"
                                             title="Klik untuk ubah ke tahap {{ $stage }}">
                                            @if($isCompleted)<i class="fas fa-check font-size-10 text-white"></i>@endif
                                        </div>
                                        <span class="kpr-step-label">{{ $stepLabels[$idx] }}</span>
                                    </div>
                                    @if(!$loop->last)
                                        <i class="fas fa-chevron-right kpr-step-line-chevron {{ $isCompleted ? 'completed' : '' }}"></i>
                                    @endif
                                @endforeach
                            </div>
                            <div class="mt-1">
                                <a href="{{ route('admin.kpr.show', $kprP->id) }}" class="btn btn-xs btn-outline-primary py-0" style="font-size: 10px;">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </div>
                        @else
                            <span class="text-muted small">Belum diinput</span>
                        @endif
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
            </tr>
            @php $totalNominal += $p->nominal; @endphp
        @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 15px; color: #999;">
                    Salesplan belum ada
                </td>
            </tr>
        @endforelse
    </tbody>

            <tfoot>
                <tr style="background: #f2f2f2; font-weight: bold; color: #040e0fff;">
                    <td colspan="3" style="padding: 10px; border: 1px solid #ccc; text-align: right;">Total Omset</td>
                    <td style="padding: 10px; border: 1px solid #ccc;">
                        Rp {{ number_format($totalNominal, 0, ',', '.') }}
                    </td>
                    <td style="padding: 10px; border: 1px solid #ccc;"></td>
                    <td style="padding: 10px; border: 1px solid #ccc;"></td>
                </tr>

                <!-- Target Omset -->
                <tr style="background: #d1e7dd; font-weight: bold; color: #0f5132;">
                    <td colspan="3" style="padding: 10px; border: 1px solid #ccc; text-align: right;">Target Omset</td>
                    <td style="padding: 10px; border: 1px solid #ccc;">
                        @php $targetOmsetVal = 1250000000; @endphp
                        Rp {{ number_format($targetOmsetVal, 0, ',', '.') }}
                    </td>
                    <td style="padding: 10px; border: 1px solid #ccc;"></td>
                    <td style="padding: 10px; border: 1px solid #ccc;"></td>
                </tr>
            </tfoot>
        </table>
    </div>



@push('scripts')
    <script>
        $(document).ready(function() {
            // Helper: Strip Thousand Separator
            function stripDots(str) {
                return str.replace(/\./g, '');
            }

            // Helper: Format to Indonesian Thousand Separator
            function formatRupiah(num) {
                return new Intl.NumberFormat('id-ID').format(num);
            }

            // 1. Search Logic
            $('#searchSalesPlan').on('keyup', function() {
                let query = $(this).val().toLowerCase();
                $('#salesPlanBody tr').each(function() {
                    let nama = $(this).find('td:nth-child(2)').text().toLowerCase();
                    $(this).toggle(nama.includes(query));
                });
            });

            // 2. Nominal Formatting (Live while typing)
            $(document).on('input', '.editable[data-field="nominal"]', function() {
                let val = $(this).text().replace(/[^0-9]/g, '');
                if (val !== '') {
                    $(this).text(formatRupiah(val));
                    // Place cursor at the end
                    let range = document.createRange();
                    let sel = window.getSelection();
                    range.selectNodeContents(this);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });

            // 3. Inline Update Logic
            $(document).on('focus', '.editable', function() {
                let current = $(this).text().trim();
                $(this).data('original', current);
                if (current === '-') $(this).text('');
            });

            $(document).on('blur', '.editable', function() {
                let id = $(this).data('id');
                let field = $(this).data('field');
                let value = $(this).text().trim();
                let original = $(this).data('original');
                let $el = $(this);

                if (value === '') {
                    value = '-';
                    $el.text('-');
                }

                if (value === original) return;

                // Strip dots for nominal field
                let finalValue = (field === 'nominal') ? stripDots(value) : value;

                $.ajax({
                    url: "{{ route('admin.salesplan.inline-update') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        field: field,
                        value: finalValue
                    },
                    success: function() {
                        $el.data('original', value);
                        if(field === 'nominal') $el.text(formatRupiah(finalValue));
                        
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Tersimpan',
                            showConfirmButton: false,
                            timer: 1000
                        });
                    },
                    error: function(xhr) {
                        $el.text(original);
                        Swal.fire('Error', 'Gagal update data: ' + xhr.responseText, 'error');
                    }
                });
            });

            // 4. Status Update Logic (Dropdown)
            $(document).on('change', '.status-dropdown', function() {
                let id = $(this).data('id');
                let val = $(this).val();
                let $dropdown = $(this);
                let $row = $dropdown.closest('tr');

                $.ajax({
                    url: "/admin/salesplan/update-status/" + id,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        status: val
                    },
                    success: function() {
                        // Update Dropdown Class
                        $dropdown.removeClass("status-sudah_transfer status-mau_transfer status-tertarik status-cold status-no")
                                 .addClass("status-" + val);
                        
                        // Update Row Class
                        $row.removeClass("row-tunai row-kpr row-tertarik row-no row-cold");
                        if (val === "sudah_transfer") $row.addClass("row-tunai");
                        else if (val === "mau_transfer") $row.addClass("row-kpr");
                        else if (val === "tertarik")     $row.addClass("row-tertarik");
                        else if (val === "no")           $row.addClass("row-no");
                        else                             $row.addClass("row-cold");

                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Status Diperbarui',
                            showConfirmButton: false,
                            timer: 1000
                        });
                    }
                });
            });

            // 5. Delete Button
            $(document).on('click', '.btn-delete', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Hapus Data?',
                    text: "Tindakan ini tidak bisa dibatalkan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Ya, Hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#delete-form-' + id).submit();
                    }
                });
            });

            // 6. Move to KPR
            $(document).on('click', '.btn-move-kpr', function() {
                let nama = $(this).data('nama');
                let $form = $(this).closest('form');
                Swal.fire({
                    title: 'Monitoring KPR',
                    html: `Masukkan <b>${nama}</b> ke data monitoring KPR?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Masukkan'
                }).then((result) => {
                    if (result.isConfirmed) $form.submit();
                });
            });

            // 7. Filter Status Daftar Pembeli
            $('#filterStatusDaftar').on('change', function() {
                let filter = $(this).val();
                $('.peserta-row').each(function() {
                    let rowStatus = $(this).data('status');
                    if (filter === 'all' || rowStatus === filter) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // 8. Go to KPR Detail (Interactive Dots)
            $(document).on('click', '.kpr-clickable-dot', function() {
                let id = $(this).data('kpr-id');
                let stage = $(this).data('stage');
                
                // Navigate to detail page with step parameter
                window.location.href = "/admin/kpr/" + id + "?step=" + encodeURIComponent(stage);
            });
        });
    </script>

    @if(session('warning'))
    <script>
        Swal.fire({
            title: '<strong>Update Status Otomatis</strong>',
            html: `{!! session('warning') !!}`,
            icon: 'info',
            confirmButtonText: 'Mengerti',
            confirmButtonColor: '#4e73df'
        });
    </script>
    @endif
@endpush
@endsection
