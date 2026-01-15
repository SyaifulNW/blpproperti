@extends('layouts.masteradmin')

@section('content')
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<!-- Hidden form for creating new data -->
<form id="formTambah" action="{{ route('peserta-smi.store') }}" method="POST">
    @csrf
</form>
<form id="sppFilterForm" method="GET" action="{{ route('peserta-smi.index') }}">
    <!-- Hidden Form for Filters -->
</form>

<div class="row">
    {{-- Filter Section --}}
    {{-- Filter Section Moved to Table Header --}}

<style>
    /* Styling Table */
    .table-hover tbody tr:hover {
        background-color: #f1f7fd; /* Soft blue on hover */
    }
    
    /* Elegant Header */
    thead.thead-dark-blue {
        background: linear-gradient(135deg, #2e59d9, #224abe);
        color: white;
    }
    thead.thead-dark-blue th {
        border-color: #4e73df;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    thead.thead-dark-blue a {
        color: white !important;
        text-transform: uppercase;
        font-size: 0.75rem;
    }

    /* Input Styling in Table */
    .table-input {
        border: 1px solid transparent;
        border-radius: 4px;
        padding: 4px;
        transition: all 0.2s;
        background: transparent;
        color: #333;
        width: 100%;
    }
    .table-input:hover {
        background: #f8f9fc;
        border-color: #e3e6f0;
    }
    .table-input:focus {
        background: #fff;
        border-color: #4e73df;
        box-shadow: 0 0 0 0.1rem rgba(78, 115, 223, 0.25);
        outline: none;
    }

    /* Checkbox Styling */
    .custom-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #4e73df; /* Modern browsers support this */
    }

    /* Fixed Layout */
    .table td, .table th {
        vertical-align: middle !important;
    }
</style>

    <div class="col-xl-12 col-md-12 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-primary text-white">
                <h6 class="m-0 font-weight-bold"><i class="fas fa-list-alt mr-2"></i>Daftar Peserta SMI</h6>
                <button class="btn btn-light text-primary btn-sm font-weight-bold shadow-sm" onclick="toggleInputRow()">
                    <i class="fas fa-plus-circle"></i> Tambah Peserta
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-0" id="dataTable" width="100%" cellspacing="0" style="font-size: 0.85rem;">
                        <thead class="thead-dark-blue text-white">
                            <tr>
                                <th rowspan="2" class="align-middle text-center border-right" style="width: 3%">No</th>
                                <th rowspan="2" class="align-middle text-center border-right" style="min-width: 250px;">Nama</th>
                                <th rowspan="2" class="align-middle text-center border-right" style="width: 15%">One On One Coaching</th>
                                <th rowspan="2" class="align-middle text-center border-right" style="width: 15%">Tanggal Masuk - Selesai</th>
                                <th rowspan="2" class="align-middle text-center border-right" style="width: 10%">Biaya Pendaftaran</th>
                                <th rowspan="2" class="align-middle text-center border-right" style="width: 10%">CS Closing</th>
                                <th colspan="12" class="text-center font-weight-bold text-uppercase border-bottom">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="mr-2">SPP</span>
                                        <select form="sppFilterForm" name="filter_spp_month" class="form-control form-control-sm mr-1 border-0 bg-light" style="width: 100px; font-size: 0.8rem; height: 25px; padding: 0 5px;" onchange="document.getElementById('sppFilterForm').submit()">
                                            <option value="">- Bulan -</option>
                                            @php
                                                $monthsRaw = [
                                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                                ];
                                            @endphp
                                            @foreach($monthsRaw as $key => $val)
                                                <option value="{{ $key }}" {{ request('filter_spp_month') == $key ? 'selected' : '' }}>{{ $val }}</option>
                                            @endforeach
                                        </select>
                                        
                                        <select form="sppFilterForm" name="filter_spp_status" class="form-control form-control-sm mr-1 border-0 bg-light" style="width: 80px; font-size: 0.8rem; height: 25px; padding: 0 5px;" onchange="document.getElementById('sppFilterForm').submit()">
                                            <option value="">- Status -</option>
                                            <option value="1" {{ request('filter_spp_status') === '1' ? 'selected' : '' }}>Lunas</option>
                                            <option value="0" {{ request('filter_spp_status') === '0' ? 'selected' : '' }}>Belum</option>
                                        </select>

                                        <select form="sppFilterForm" name="filter_year" class="form-control form-control-sm mr-1 border-0 bg-light" style="width: 70px; font-size: 0.8rem; height: 25px; padding: 0 5px;" onchange="document.getElementById('sppFilterForm').submit()">
                                            <option value="">- Thn -</option>
                                            @for($y = date('Y'); $y >= 2024; $y--)
                                                <option value="{{ $y }}" {{ request('filter_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                            @endfor
                                        </select>
                                        
                                        @if(request()->has('filter_spp_month') || request()->has('filter_spp_status') || request()->has('filter_year'))
                                        <a href="{{ route('peserta-smi.index') }}" class="text-white ml-2" title="Reset Filter"><i class="fas fa-sync-alt"></i></a>
                                        @endif
                                    </div>
                                </th>
                                <th rowspan="2" class="align-middle text-center border-left" style="width: 5%">Aksi</th>
                            </tr>
                            <tr>
                                @php
                                    $bulan = [
                                        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                                        7 => 'Jul', 8 => 'Ags', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
                                    ];
                                @endphp
                                @for($i=1; $i<=12; $i++)
                                <th class="text-center" style="min-width: 45px; width: 45px; font-size: 11px;">
                                    <a href="{{ route('peserta-smi.index', ['sort_spp' => $i, 'sort_dir' => request('sort_spp') == $i && request('sort_dir') == 'desc' ? 'asc' : 'desc']) }}" class="text-white text-decoration-none">
                                        {{ $bulan[$i] }}
                                        {!! request('sort_spp') == $i ? (request('sort_dir') == 'desc' ? '<i class="fas fa-caret-down"></i>' : '<i class="fas fa-caret-up"></i>') : '' !!}
                                    </a>
                                </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Input Row (Hidden by default) -->
                            <tr id="inputRow" style="display: none; background-color: #fff3cd;">
                                <td class="text-center align-middle">
                                    <span class="badge badge-warning text-dark">New</span>
                                </td>
                                <td>
                                    <input form="formTambah" type="text" name="nama" class="form-control form-control-sm border-warning" placeholder="Nama..." required>
                                </td>
                                <td>
                                    <input form="formTambah" type="datetime-local" name="one_on_one_coaching" class="form-control form-control-sm border-warning">
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input form="formTambah" type="date" name="tanggal_masuk" class="form-control form-control-sm border-warning mb-1" placeholder="Masuk">
                                        <input form="formTambah" type="date" name="tanggal_selesai" class="form-control form-control-sm border-warning" placeholder="Selesai">
                                    </div>
                                </td>
                                <td>
                                    <input form="formTambah" type="number" name="biaya_pendaftaran" class="form-control form-control-sm border-warning" placeholder="Rp...">
                                </td>
                                <td>
                                    <select form="formTambah" name="closing_cs_id" class="form-control form-control-sm border-warning">
                                        <option value="">- Pilih CS -</option>
                                        @foreach($listCs as $cs)
                                            <option value="{{ $cs->id }}">{{ $cs->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                @for($i = 1; $i <= 12; $i++)
                                <td class="text-center align-middle p-0">
                                    <input form="formTambah" type="checkbox" name="spp_{{ $i }}" value="1" class="custom-checkbox">
                                </td>
                                @endfor
                                <td class="text-center align-middle">
                                    <button type="submit" form="formTambah" class="btn btn-success btn-sm btn-circle" title="Simpan">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm btn-circle" title="Batal" onclick="toggleInputRow()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>

                            @foreach($data as $key => $item)
                            <tr>
                                <form action="{{ route('peserta-smi.update', $item->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td class="text-center align-middle font-weight-bold text-secondary">{{ $key + 1 }}</td>
                                    
                                    {{-- Nama --}}
                                    <td class="p-1 align-middle">
                                        <input type="text" name="nama" class="table-input" value="{{ $item->nama }}">
                                    </td>
                                    
                                    {{-- Coaching --}}
                                    <td class="p-1 align-middle">
                                        <input type="datetime-local" name="one_on_one_coaching" class="table-input" value="{{ $item->one_on_one_coaching }}">
                                    </td>
                                    
                                    {{-- Tanggal Masuk & Selesai --}}
                                    <td class="p-1 align-middle">
                                        <input type="date" name="tanggal_masuk" class="table-input mb-1" value="{{ $item->tanggal_masuk }}" title="Tgl Masuk">
                                        <input type="date" name="tanggal_selesai" class="table-input" value="{{ $item->tanggal_selesai }}" title="Tgl Selesai">
                                    </td>
                                    
                                    {{-- Biaya --}}
                                    <td class="p-1 align-middle">
                                        <input type="number" name="biaya_pendaftaran" class="table-input" value="{{ $item->biaya_pendaftaran }}">
                                    </td>
                                    
                                    {{-- CS Closing --}}
                                    <td class="p-1 align-middle">
                                        <select name="closing_cs_id" class="table-input">
                                            <option value="">- CS -</option>
                                            @foreach($listCs as $cs)
                                                <option value="{{ $cs->id }}" {{ $item->closing_cs_id == $cs->id ? 'selected' : '' }}>{{ $cs->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    
                                    {{-- SPP Checkboxes --}}
                                    @php
                                        // Calculate active months
                                        $activeMonths = [];
                                        if($item->tanggal_masuk && $item->tanggal_selesai) {
                                            $start = \Carbon\Carbon::parse($item->tanggal_masuk)->startOfMonth();
                                            $end = \Carbon\Carbon::parse($item->tanggal_selesai)->endOfMonth();
                                            
                                            // Handle potential data errors where start > end
                                            if($start->lte($end)) {
                                                $curr = $start->copy();
                                                // Loop through months
                                                while($curr->lte($end)) {
                                                    $activeMonths[] = $curr->month;
                                                    $curr->addMonth();
                                                }
                                                // Make unique in case of multi-year spanning same months (though logic handles it)
                                                // For a 12-col layout, if user spans > 1 year, the month is active.
                                                $activeMonths = array_unique($activeMonths);
                                            }
                                        } else {
                                            // Fallback if dates are missing: show all or none? 
                                            // Showing all allows fixing data.
                                            $activeMonths = range(1, 12);
                                        }
                                    @endphp

                                    @for($i = 1; $i <= 12; $i++)
                                    <td class="text-center align-middle p-0">
                                        @if(in_array($i, $activeMonths))
                                            <input type="checkbox" name="spp_{{ $i }}" value="1" {{ $item->{"spp_$i"} ? 'checked' : '' }} class="custom-checkbox">
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    @endfor
                                    
                                    {{-- Action Buttons --}}
                                    <td class="text-center align-middle">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="submit" class="btn btn-primary btn-sm btn-icon-split shadow-sm" title="Update" style="padding: 2px 6px;">
                                                <span class="icon text-white">
                                                    <i class="fas fa-save"></i>
                                                </span>
                                            </button>
                                    </form> 
                                            {{-- Delete Form (Separate) --}}
                                            <form action="{{ route('peserta-smi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm btn-icon-split shadow-sm" title="Hapus" style="padding: 2px 6px;">
                                                    <span class="icon text-white">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                </button>
                                            </form>
                                        </div>
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

<script>
    function toggleInputRow() {
        var row = document.getElementById('inputRow');
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    }
</script>
@endsection
