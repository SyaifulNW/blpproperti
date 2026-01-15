@extends('layouts.masteradmin')
@section('content')

<style>
 thead {
        background-color: #25799E;
        color: white;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    
  
</style>
@if(auth()->user()->role !== 'administrator')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Database Calon Pelanggan</h1>
    <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item active">Database Calon Pelanggan</li>
        </ol>
    </div>
</div>
@endif





        </div>
    </form>





    {{-- ALERT MODE READ ONLY (ADMIN) --}}
    @if(isset($user) && $readonly)
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-4 shadow-sm" role="alert">
            <div>
                <strong>Database CS:</strong> <strong>{{ $user->name }} </strong> <br>
                <span class="text-muted small">Email: {{ $user->email }} | Role: {{ ucfirst($user->role) }}</span>
            </div>
            <div>
                <span class="text-white badge bg-primary p-2">Mode Read-Only</span>
            </div>
        </div>
        
    @if(auth()->user()->name !== 'Agus Setyo')
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
                <input type="text" name="pesan" class="form-control" placeholder="Tulis komentar untuk CS ini..." required>
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

<div class="modal fade" id="modalKomentar" tabindex="-1" role="dialog" aria-labelledby="modalKomentarLabel" aria-hidden="true">
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
@endif

    



<div class="content">
    <div class="card card-info card-outline">
@php
use Carbon\Carbon;
use App\Models\Data;

// $currentUser used in logic below
$currentUser = auth()->user(); 

// Ensure variables are defined if not passed (fallback for edge cases)
$now = Carbon::now();
if(!isset($bulanLabel)) $bulanLabel = $now->isoFormat('MMMM YYYY');
if(!isset($databaseBaru)) $databaseBaru = 0;
if(!isset($totalDatabase)) $totalDatabase = 0;
if(!isset($target)) $target = 50;
if(!isset($kurang)) $kurang = 0;
if(!isset($data)) $data = collect([]);

@endphp



<div class="card-header">
    {{-- Stats Cards Section (Moved to Top) --}}
    <style>
        .stat-card-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .g-stat-card {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            min-width: 140px; /* Slightly reduced */
            position: relative;
            overflow: hidden;
            flex: 1; /* Allow growing */
        }
        .g-stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.15); }
        .g-stat-card::after {
            content: ''; position: absolute; top: 0; right: 0; bottom: 0; left: 0;
            background: linear-gradient(to bottom right, rgba(255,255,255,0.2), transparent); pointer-events: none;
        }
        /* Gradients */
        .g-sc-cyan { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); }
        .g-sc-blue { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
        .g-sc-yellow { background: linear-gradient(135deg, #ffca2c 0%, #ffc107 100%); color: #212529; }
        .g-sc-red { background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); }

        .g-sc-content { display: flex; flex-direction: column; z-index: 1; }
        .g-sc-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; font-weight: 700; margin-bottom: 2px; }
        .g-sc-value { font-size: 1.25rem; font-weight: 800; line-height: 1.1; }
        .g-sc-sub { font-size: 0.65rem; opacity: 0.9; margin-top: 2px; }
        .g-sc-icon { margin-left: auto; font-size: 1.8rem; opacity: 0.3; z-index: 1; margin-bottom: -5px; }
    </style>

    <div class="stat-card-group mb-4">
        <!-- Database Baru -->
        <div class="g-stat-card g-sc-cyan">
            <div class="g-sc-content">
                <span class="g-sc-label">Database Baru</span>
                <span class="g-sc-value">{{ $databaseBaru }}</span>
                <span class="g-sc-sub">{{ $bulanLabel }}</span>
            </div>
            <div class="g-sc-icon"><i class="fas fa-database"></i></div>
        </div>

        <!-- Total Database -->
        <div class="g-stat-card g-sc-blue">
            <div class="g-sc-content">
                <span class="g-sc-label">Total Database</span>
                <span class="g-sc-value">{{ $totalDatabase }}</span>
            </div>
            <div class="g-sc-icon"><i class="fas fa-layer-group"></i></div>
        </div>

        <!-- Target -->
        <div class="g-stat-card g-sc-yellow">
            <div class="g-sc-content">
                <span class="g-sc-label">Target Bulanan</span>
                <span class="g-sc-value">{{ $target }}</span>
            </div>
            <div class="g-sc-icon"><i class="fas fa-bullseye"></i></div>
        </div>

        <!-- Kurang -->
        <div class="g-stat-card g-sc-red text-white">
            <div class="g-sc-content">
                <span class="g-sc-label text-white">Kurang</span>
                <span class="g-sc-value">{{ $kurang }}</span>
            </div>
            <div class="g-sc-icon text-white"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>

    {{-- Toolbar Actions Row --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <!-- Kiri: Tombol Tambah -->
        <div class="d-flex align-items-center">
            @if(!in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) && !(auth()->user()->name === 'Linda' && request('view') !== 'me'))
                <a href="#" class="btn btn-success" id="btnAddRow" onclick="createNewRow(event)">
                    <i class="fa-solid fa-plus"></i> Tambah
                </a>
            @endif
        </div>

<!-- Kanan: Toolbar Filter & Search -->
<!-- Kanan: Toolbar Filter & Search -->
<div class="d-flex align-items-center justify-content-end gap-2" style="flex: 1;">
    <style>
        .modern-filter-container { display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
        .modern-select {
            border-radius: 50px !important; border: 1px solid #e0e0e0; background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.03); font-size: 0.85rem; padding: 6px 30px 6px 15px;
            transition: all 0.2s ease; cursor: pointer; min-height: 34px;
        }
        .modern-select:hover { border-color: #b0c4de; box-shadow: 0 4px 8px rgba(0,0,0,0.08); transform: translateY(-1px); }
        .modern-select:focus { border-color: #86b7fe; box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15); outline: 0; }
        .modern-search-group { box-shadow: 0 2px 5px rgba(0,0,0,0.03); border-radius: 50px; overflow: hidden; display: flex; }
        .modern-search-input { border: 1px solid #e0e0e0; border-right: none; padding-left: 20px; font-size: 0.9rem; border-top-left-radius: 50px; border-bottom-left-radius: 50px; }
        .modern-search-input:focus { box-shadow: none; border-color: #e0e0e0; }
        .modern-search-btn { border-radius: 0 50px 50px 0 !important; padding-left: 20px; padding-right: 20px; font-weight: 600; }
    </style>
    <div class="modern-filter-container">
@php
    use App\Models\User;

    $user = auth()->user();
    $csList = collect();

    // Daftar CS hanya untuk admin/manager
    if (in_array(strtolower($user->role), ['administrator', 'manager']) || $user->name === 'Agus Setyo' || $user->name === 'Linda') {
        $csList = User::whereIn('role', ['cs', 'CS', 'customer_service', 'cs-mbc', 'cs-smi'])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
@endphp

    {{-- Filter Input Oleh (Admin/Manager) --}}
    @if((in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) && auth()->user()->name !== 'Agus Setyo') || (auth()->user()->name === 'Linda' && request('view') !== 'me'))
        <select id="filterUser" class="form-select form-select-sm modern-select" onchange="updateFilter('cs_name', this.value)">
            <option value="">-- Pilih CS --</option>
            @foreach($csList as $cs)
                  <option value="{{ $cs->name }}" {{ request('cs_name') == $cs->name ? 'selected' : '' }}>{{ $cs->name }}</option>
            @endforeach
        </select>
    @endif
    &nbsp;

    &nbsp;

    {{-- Filter Sumber & Kota (Only for Administrator & Linda) --}}
    @if(strtolower(auth()->user()->role) === 'administrator' || auth()->user()->name === 'Linda')
        {{-- Filter Sumber Leads --}}
        <select id="filterSumber" class="form-select form-select-sm modern-select" style="min-width: 130px;" onchange="updateFilter('sumber', this.value)">
            <option value="">-- Semua Sumber --</option>
            <option value="Marketing" {{ request('sumber') == 'Marketing' ? 'selected' : '' }}>Marketing</option>
            <option value="Iklan" {{ request('sumber') == 'Iklan' ? 'selected' : '' }}>Iklan</option>
            <option value="Alumni" {{ request('sumber') == 'Alumni' ? 'selected' : '' }}>Alumni</option>
            <option value="Mandiri" {{ request('sumber') == 'Mandiri' ? 'selected' : '' }}>Mandiri</option>
        </select>

        {{-- Filter Provinsi --}}
        <select id="filterProvinsi" class="form-select form-select-sm modern-select" style="min-width: 140px;" onchange="updateFilter('provinsi', this.value)">
            <option value="">-- Semua Provinsi --</option>
            @if(isset($provinsiList))
                @foreach($provinsiList as $prov)
                    <option value="{{ $prov }}" {{ request('provinsi') == $prov ? 'selected' : '' }}>{{ $prov }}</option>
                @endforeach
            @endif
        </select>


        {{-- Filter Kota --}}
        <select id="filterKota" class="form-select form-select-sm modern-select" style="min-width: 140px;" onchange="updateFilter('kota', this.value)">
            <option value="">-- Semua Kota --</option>
            @if(isset($kotaList))
                @foreach($kotaList as $kota)
                     <option value="{{ $kota }}" {{ request('kota') == $kota ? 'selected' : '' }}>{{ $kota }}</option>
                @endforeach
            @endif
        </select>
        
        {{-- Filter Spin --}}
        <select id="filterSpin" class="form-select form-select-sm modern-select" style="min-width: 120px;" onchange="updateFilter('spin', this.value)">
            <option value="">-- Spin --</option>
            <option value="1" {{ request('spin') === '1' ? 'selected' : '' }}>Sudah Spin</option>
            <option value="0" {{ request('spin') === '0' ? 'selected' : '' }}>Belum Spin</option>
        </select>

        {{-- Filter Zoom --}}
        <select id="filterZoom" class="form-select form-select-sm modern-select" style="min-width: 120px;" onchange="updateFilter('zoom', this.value)">
            <option value="">-- Zoom --</option>
            <option value="1" {{ request('zoom') === '1' ? 'selected' : '' }}>Sudah Zoom</option>
            <option value="0" {{ request('zoom') === '0' ? 'selected' : '' }}>Belum Zoom</option>
        </select>


    @endif
    
    {{-- Filter Bulan (Server Side) --}}
    <select id="filterBulan" class="form-select form-select-sm modern-select" onchange="updateFilter('bulan', this.value)">
        <option value="">-- Semua Bulan --</option>
        @foreach(range(1,12) as $m)
            <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
            </option>
        @endforeach
    </select>

    {{-- Filter Tahun (Server Side) --}}
    <select id="filterTahun" class="form-select form-select-sm modern-select" onchange="updateFilter('tahun', this.value)">
        <option value="">-- Tahun --</option>
        @foreach(range(date('Y'), 2024) as $y)
            <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>

    <script>
        function updateFilter(key, val) {
            var url = new URL(window.location.href);
            if (val) {
                url.searchParams.set(key, val);
            } else {
                url.searchParams.delete(key); 
            }
            url.searchParams.delete('page'); // Reset pagination
            window.location.href = url.toString();
        }
    </script>

    {{-- Search --}}
    <div class="input-group input-group-sm modern-search-group" style="width: auto;">
        <input type="text" id="tableSearch" class="form-control modern-search-input" placeholder="Cari Nama..." value="{{ request('search') }}">
        <button class="btn btn-primary modern-search-btn" type="button" onclick="updateFilter('search', document.getElementById('tableSearch').value)">
            <i class="fas fa-search"></i>
        </button>
    </div>
  </div>
</div>
</div>
</div>

<script>
    function updateFilter(key, val) {
        var url = new URL(window.location.href);
        if (val) {
            url.searchParams.set(key, val);
        } else {
            url.searchParams.delete(key); 
        }
        url.searchParams.delete('page'); // Reset pagination
        window.location.href = url.toString();
    }
</script>

<script>
    document.getElementById('tableSearch').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            updateFilter('search', this.value);
        }
    });

    $(document).ready(function() {
         // Re-implement visual filter for Sumber Leads & Kota since we moved them out of the table header
         // NOTE: The previous script looked for specific elements. We need to ensure the IDs match.
         
         $('#filterSumber, #filterKota').on('change', function() {
            var fSumber = $('#filterSumber').val().toLowerCase();
            var fKotaName = $('#filterKota option:selected').text().trim().toLowerCase();
            if (fKotaName.includes('--')) fKotaName = '';

            $('#myTable tbody tr').each(function() {
                var $tr = $(this);
                // We need to find the hidden/visible values in the row.
                // Since row structure is changing, we must be careful.
                var trSumber = $tr.find('.select-sumber').val() || '';
                var trKota = $tr.find('.select-kota option:selected').text().trim() || $tr.data('kota') || '';

                var show = true;
                if (fSumber && trSumber.toLowerCase() !== fSumber) show = false;
                if (fKotaName && trKota.toLowerCase() !== fKotaName) show = false;
                
                $tr.toggle(show);
            });
         });
    });
</script>

        <div class="card-body">
            <div style="overflow-x: auto; overflow-y: auto; width: 100%; max-height: 500px;">

                <table id="myTable" class="table table-bordered table-striped nowrap" style="width: max-content;">
                    <thead>
                        <tr>
                            <th>No</th>

                            @php
                                $userRole = strtolower(auth()->user()->role);
                                $isCs = in_array($userRole, ['cs', 'cs-mbc', 'cs-smi', 'customer_service']);
                            @endphp

                            @if($userRole === 'administrator')
                                {{-- === LAYOUT ADMINISTRATOR === --}}
                                <th style="min-width: 250px;">Nama Calon Pelanggan</th> {{-- Merged Name+WA+CTA --}}
                                <th>Sumber Leads</th> {{-- Filter at top --}}
                                <!--<th>Kota</th>         {{-- Filter at top --}}-->
                                <th>Bisnis & Situasi</th>       {{-- Merged Bisnis & Situasi --}}
                            @else
                                {{-- === LAYOUT NON-ADMIN (CS, Manager, Marketing, etc) === --}}
                                <th>Nama Pelanggan</th>
                                <th>
                                    Sumber Leads <br>
                                    <select id="filterSumber" class="form-control form-control-sm">
                                        <option value="">-- Semua Sumber --</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Iklan">Iklan</option>
                                        <option value="Alumni">Alumni</option>
                                        <option value="Mandiri">Mandiri</option>
                                    </select>
                                </th>
                                @if(strtolower(auth()->user()->role) !== 'administrator')
                                    <th>
                                        Provinsi <br>
                                        <select id="filterProvinsi" class="form-control form-control-sm" style="min-width: 150px;">
                                            <option value="">-- Semua Provinsi --</option>
                                        </select>
                                    </th>
                                @endif
                                <th>
                                    Kota <br>
                                    <select id="filterKota" class="form-control form-control-sm" style="min-width: 150px;">
                                        <option value="">-- Semua Kota --</option>
                                    </select>
                                </th>
                                <th>Nama Bisnis</th>
                                <th>Jenis Bisnis</th>
                                <th>No.WA</th>
                                <th>CTA</th>
                            @endif

                            @if($userRole !== 'administrator')
                                <th>Situasi Bisnis</th>
                            @endif

                            <th>Kendala</th>
                            
                            <!-- New Columns (All Roles) -->
                            <th class="text-center">Berhasil SPIN</th>
                            <th class="text-center">Ikut Zoom</th>

                            {{-- Hanya tampil jika bukan marketing --}}
                            @if(strtolower(auth()->user()->role) !== 'marketing')
                                <th>
                                    Potensi Kelas Pertama
                                    <div style="min-width: 200px;">
                                        <select id="filterKelas" class="form-control-sm">
                                            <option value="">-- Semua Potensi Kelas --</option>
                                            @foreach($kelas as $k)
                                                <option value="{{ $k->nama_kelas }}">{{ $k->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </th>
                            @endif

                            @if(Auth::user()->email !== "mbchamasah@gmail.com"  && Auth::user()->role !== 'marketing')    
                            <th>Sales Plan</th>
                            @endif
                            
                            @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) || auth()->user()->name === 'Agus Setyo')
                            <th>
                                <div class="d-flex flex-column">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'created_by', 'order' => (request('sort_by') == 'created_by' && request('order') == 'asc') ? 'desc' : 'asc']) }}" class="text-white text-decoration-none d-flex align-items-center justify-content-between mb-1">
                                        <span>Input Oleh</span>
                                        <span>
                                            @if(request('sort_by') == 'created_by')
                                                <i class="fas fa-sort-{{ request('order') == 'asc' ? 'up' : 'down' }}"></i>
                                            @else
                                                <i class="fas fa-sort text-white-50"></i>
                                            @endif
                                        </span>
                                    </a>
                                    <select class="form-control form-control-sm text-dark" onchange="updateFilterUser(this.value)" style="min-width: 100px;">
                                        <option value="">-- Semua --</option>
                                        @foreach($csList as $cs)
                                            <option value="{{ $cs->name }}" {{ request('cs_name') == $cs->name ? 'selected' : '' }}>
                                                {{ $cs->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </th>
                            {{-- Role Column: Removed for Administrator (Rule 5), Shown for Others (if permitted) --}}
                            @if(strtolower(auth()->user()->role) !== 'administrator')
                                <th>Role</th>
                            @endif
                            @endif
                            
                            @if($userRole !== 'administrator')
                                <th>Tanggal Input</th>
                                <th>Action</th>
                            @endif
                        </tr>

                    </thead>
                    <tbody>

                        @foreach($data as $item)
                            @include('admin.database.partials.row', ['item' => $item, 'loop' => $loop, 'kelas' => $kelas])
                        @endforeach


                    </tbody>
                </table>
                
                <!-- Script FIlter -->
                <script>
                    $(document).ready(function() {
                        $('#filterLeads, #filterProvinsi, #filterKota, #filterJenisBisnis, #filterInputOleh').on('change', function() {
                            let filters = {
                                leads: $('#filterLeads').val(),
                                provinsi: $('#filterProvinsi').val(),
                                kota: $('#filterKota').val(),
                                jenisbisnis: $('#filterJenisBisnis').val(),
                                created_by: $('#filterInputOleh').val(),
                            };

                            $.ajax({
                                url: "{{ route('admin.database.filter') }}",
                                type: "GET",
                                data: filters,
                                success: function(response) {
                                    $('#tableData').html(response);
                                },
                                // error: function() {
                                //     alert('Gagal memuat data filter');
                                // }
                            });
                        });
                    });
                </script>


                <!-- Script JQuery -->
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {

                        // Untuk kolom text
                        $(document).on('blur', '.editable', function() {
                            let value = $(this).text();
                            let field = $(this).data('field');
                            let id = $(this).closest('tr').data('id');

                            $.ajax({
                                url: '/admin/database/update-inline',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: field,
                                    value: value
                                },
                                success: function(res) {
                                    console.log('Updated:', field);
                                },
                                // error: function() {
                                //     alert('Gagal update data');
                                // }
                            });
                        });

                        // Untuk dropdown Potensi Kelas
                        $(document).on('change', '.select-potensi', function() {
                            let id = $(this).data('id');
                            let kelas_id = $(this).val();

                            $.ajax({
                                url: `/admin/database/update-potensi/${id}`,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    kelas_id: kelas_id
                                },
                                success: function(response) {
                                    console.log('Potensi kelas updated');
                                },
                                error: function() {
                                    alert('Gagal update potensi kelas');
                                }
                            });
                        });

                    });
                </script>

<script>
// Delegated event for Sumber Leads Select
$(document).on('change', '.select-sumber', function() {
    let id = $(this).data('id');
    let value = $(this).val();

    $.ajax({
        url: '/admin/database/update-inline',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            id: id,
            field: 'leads',
            value: value
        }
    });
});

// Delegated event for Spin and Zoom Checkboxes
$(document).on('change', '.check-spin, .check-zoom', function() {
    let $this = $(this);
    let id = $this.data('id');
    let field = $this.hasClass('check-spin') ? 'berhasil_spin' : 'ikut_zoom';
    let value = $this.is(':checked') ? 1 : 0;

    $.ajax({
        url: '/admin/database/update-inline',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            id: id,
            field: field,
            value: value
        },
        success: function(res) {
            console.log('Updated checkbox:', field);
        },
        error: function(xhr) {
            console.log('Error updating checkbox');
            // Revert checkbox state if error?
            // $this.prop('checked', !value);
            alert('Gagal update status.');
        }
    });
});

function createNewRow(e) {
    if(e) e.preventDefault();
    
    $.ajax({
        url: '{{ route("admin.database.createDraft") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}' },
        success: function(response) {
            if(response.success) {
                // Prepend to tbody
                $('#myTable tbody').prepend(response.html);
                
                let $newRow = $('#myTable tbody tr:first');
                
                // Populate Provinces for the new row
                if(window.populateProvinceRow) {
                    window.populateProvinceRow($newRow);
                }
                
                // Optional: Highlight row or focus name
                $newRow.css('background-color', '#d4edda').animate({backgroundColor: '#fff'}, 2000);
            }
        },
        error: function(xhr) {
            let msg = 'Gagal menambah baris baru.';
            if(xhr.responseJSON && xhr.responseJSON.message) {
                msg += '\n' + xhr.responseJSON.message;
            }
            alert(msg);
        }
    });
}
</script>
                <style>
                    .editable {
                        cursor: pointer;
                    }

                    .editing {
                        background-color: #fff3cd !important;
                        /* kuning saat edit */
                    }

                    .status-icon {
                        margin-left: 5px;
                        font-size: 14px;
                    }

                    .status-success {
                        color: green;
                    }

                    .status-error {
                        color: red;
                    }
                </style>
                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    $(document).ready(function() {

                        // Untuk kolom text
                        $('.editable').on('focus', function() {
                            $(this).addClass('editing');
                        });

                        $('.editable').on('blur', function() {
                               let $this = $(this);
                            let value = $this.text();
                            let field = $this.data('field');
                            let id = $this.closest('tr').data('id');

                            $this.removeClass('editing');

                            $.ajax({
                                url: '/admin/database/update-inline',
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    id: id,
                                    field: field,
                                    value: value
                                },
                                success: function() {
                                    showStatusIcon($this, true);
                                },
                                error: function() {
                                    showStatusIcon($this, false);
                                }
                            });
                        });

                        // Untuk dropdown Potensi Kelas
                        $('.select-potensi').on('change', function() {
                            let $this = $(this);
                            let id = $this.data('id');
                            let kelas_id = $this.val();
                            let iconSpan = $this.next('.status-icon');

                            $.ajax({
                                url: `/admin/database/update-potensi/${id}`,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    kelas_id: kelas_id
                                },
                                success: function() {
                                    iconSpan.html('<i class="fa fa-check status-success"></i>');
                                    setTimeout(() => iconSpan.html(''), 2000);
                                },
                                error: function() {
                                    iconSpan.html('<i class="fa fa-times status-error"></i>');
                                    setTimeout(() => iconSpan.html(''), 2000);
                                }
                            });
                        });

                        // Fungsi tampil icon centang atau silang
                        function showStatusIcon($element, success) {
                            let iconHtml = success ?
                                '<i class="fa fa-check status-success"></i>' :
                                '<i class="fa fa-times status-error"></i>';

                            let iconSpan = $('<span class="status-icon">' + iconHtml + '</span>');
                            $element.after(iconSpan);

                            setTimeout(() => {
                                iconSpan.fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }, 2000);
                        }

                    });
                </script>



            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $data->withQueryString()->links('pagination::bootstrap-4') }}
            </div>

        </div>
        

    </div>
</div>

<script>
$(document).ready(function() {
    // Global variables to cache default province list
    let cachedProvinces = [];

    // Helper: Populate specific select elements
    function populateProvinceSelect($elements) {
        if(cachedProvinces.length === 0) return;

        $elements.each(function() {
            let $select = $(this);
            // check if already populated to avoid potential overwrite issues if logic changes
            if($select.children('option').length > 1) return; 

            let currentNama = $select.data('nama');
            
            // Keep existing "Pilih" if exists
            let $default = $select.find('option:first');
            $select.empty().append($default);

            cachedProvinces.forEach(function(prov) {
                let isSelected = (currentNama && currentNama.toUpperCase() === prov.name.toUpperCase()) ? 'selected' : '';
                $select.append(`<option value="${prov.id}" data-name="${prov.name}" ${isSelected}>${prov.name}</option>`);
            });
        });
    }

    // 1. Fetch Provinces & Populate
    $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function(provinces) {
        // Sort: Alphabetical
        provinces.sort((a, b) => a.name.localeCompare(b.name));
        cachedProvinces = provinces;

        // Populate existing rows
        populateProvinceSelect($('.select-provinsi'));
        
        // Also populate Header Filter
        let $filterProv = $('#filterProvinsi');
        cachedProvinces.forEach(function(prov) {
             // Avoid duplicate append if run multiple times
             if($filterProv.find(`option[value="${prov.name}"]`).length === 0) {
                 $filterProv.append(`<option value="${prov.name}" data-id="${prov.id}">${prov.name}</option>`);
             }
        });
    });

    // Expose populate function purely for local usage pattern if needed, 
    // but better to attach a listener or just call it from createNewRow.
    
    // We attach it to window so createNewRow can access it if defined outside (though it is defined outside doc.ready)
    window.populateProvinceRow = function($row) {
         if(cachedProvinces.length > 0) {
             populateProvinceSelect($row.find('.select-provinsi'));
         } else {
             // retry if not yet loaded? usually loaded by the time user clicks add
         }
    };

    // 2. Change Province -> Find Cities & Save
    $(document).on('change', '.select-provinsi', function() {
        let $select = $(this);
        let id = $select.data('id');
        let provId = $select.val();
        let provName = $select.find(':selected').data('name');
        
        let $kotaSelect = $select.closest('tr').find('.select-kota');
        
        // Save to DB
        if(provId) {
             $.post('/admin/database/update-location', {
                _token: '{{ csrf_token() }}',
                id: id,
                provinsi_id: provId,
                provinsi_nama: provName
            }).done(function() {
                 console.log('Provinsi saved');
            });
            
            // Load Cities
            loadCities(provId, $kotaSelect);
        } else {
            $kotaSelect.empty().append('<option value="">-- Pilih Kota --</option>');
        }
    });

    // 3. Change City -> Save
    $(document).on('change', '.select-kota', function() {
        let $select = $(this);
        let id = $select.data('id');
        let kotaId = $select.val();
        let kotaName = $select.find(':selected').data('name');

        if(kotaId) {
            $.post('/admin/database/update-location', {
                 _token: '{{ csrf_token() }}',
                 id: id,
                 kota_id: kotaId,
                 kota_nama: kotaName
            }).done(function() {
                 console.log('Kota saved');
            });
        }
    });

    // 4. Lazy Load Cities on Click (if not populated)
    $(document).on('click', '.select-kota', function() {
        let $kotaSelect = $(this);
        // Only load if we haven't loaded options yet (length <= 1 means only default option)
        // And ensure we have a province selected
        if($kotaSelect.children('option').length <= 1) {
             let $provSelect = $kotaSelect.closest('tr').find('.select-provinsi');
             let provId = $provSelect.val();
             
             if(provId) {
                 loadCities(provId, $kotaSelect);
             } else {
                 // Try to resolve province ID from its text if user hasn't touched it? 
                 // Difficult because we haven't mapped ID to the initial text unless content matched.
                 if($provSelect.find('option:selected').val()) {
                     loadCities($provSelect.find('option:selected').val(), $kotaSelect);
                 }
             }
        }
    });

    function loadCities(provId, $targetSelect) {
        $targetSelect.empty().append('<option value="">Loading...</option>');
        
        $.getJSON(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`, function(cities) {
             cities.sort((a, b) => a.name.localeCompare(b.name));
             
             $targetSelect.empty().append('<option value="">-- Pilih Kota --</option>');
             
             let currentKota = $targetSelect.data('nama');
             
             cities.forEach(function(city) {
                 let isSelected = (currentKota && currentKota.toUpperCase() === city.name.toUpperCase()) ? 'selected' : '';
                 $targetSelect.append(`<option value="${city.id}" data-name="${city.name}" ${isSelected}>${city.name}</option>`);
             });
        });
    }

    // ==========================================
    // FILTER HEADER (Baru)
    // ==========================================
    
    // A. Populate Header Filter Provinsi
    $.getJSON('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json', function(provinces) {
        provinces.sort((a, b) => a.name.localeCompare(b.name));
        let $filterProv = $('#filterProvinsi');
        
        // Prevent duplicates (in case other scripts populated it)
        $filterProv.find('option:not(:first)').remove();

        provinces.forEach(function(prov) {
            $filterProv.append(`<option value="${prov.name}" data-id="${prov.id}">${prov.name}</option>`);
        });

        // Initialize Select2 with search
        $filterProv.select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: "-- Semua Provinsi --",
            allowClear: true
        });
    });

    // B. Event Listener Filter Provinsi
    $('#filterProvinsi').on('change', function() {
        let selectedProvName = $(this).val();
        let selectedProvId = $(this).find(':selected').data('id');
        let $filterKota = $('#filterKota');
        
        // 1. Reset & Reload Kota Filter
        $filterKota.empty().append('<option value="">-- Semua Kota --</option>');
        
        if(selectedProvId) {
            $.getJSON(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${selectedProvId}.json`, function(cities) {
                 cities.sort((a, b) => a.name.localeCompare(b.name));
                 cities.forEach(function(city) {
                     $filterKota.append(`<option value="${city.name}">${city.name}</option>`);
                 });
            });
        }
        
        // 2. Trigger Main Filter
        applyTableFilters();
    });

    // C. Event Listener Filter Kota
    $('#filterKota').on('change', function() {
        applyTableFilters();
    });

    // D. Main Filtering Logic (Combines existing logic)
    function applyTableFilters() {
         var userRole = "{{ strtolower(auth()->user()->role) }}";
         
         // Get values
         var fUser = $('#filterUser').val() ? $('#filterUser').val().toLowerCase() : '';
         var fBulan = $('#filterBulan').val();
         var fSumber = $('#filterSumber').val();
         var fKelas = $('#filterKelas').val();
         // Header Filters
         var fProv = $('#filterProvinsi').val();
         var fKota = $('#filterKota').val();
         
         var search = $('#tableSearch').val() ? $('#tableSearch').val().toLowerCase() : '';
 
         $('#myTable tbody tr').each(function() {
             var $tr = $(this);
             var trUser = $tr.data('created-by'); 
             var trBulan = $tr.data('bulan');
             var trYear = $tr.data('year');
             var currentYear = new Date().getFullYear();
             
             // Row Values
             var trText = $tr.text().toLowerCase();
             var trSumber = $tr.find('.select-sumber').val();
             
             // Get Province/City from the dropdown data (most accurate) or text if fallback
             var $trProvSelect = $tr.find('.select-provinsi');
             var trProvinsi = $trProvSelect.length ? $trProvSelect.data('nama') : $tr.find('td[data-field="provinsi_nama"]').text();
             
             var $trKotaSelect = $tr.find('.select-kota');
             var trKota = $trKotaSelect.length ? $trKotaSelect.data('nama') : $tr.find('td[data-field="kota_nama"]').text();

             // Normalizing string for comparison (uppercase/trim)
             if(trProvinsi) trProvinsi = trProvinsi.trim();
             if(trKota) trKota = trKota.trim();
             
             var show = true;
 
             // Filter User
             if (fUser && trUser !== fUser) show = false;
             
             // Filter Bulan
             if (show && fBulan) {
                 if (trBulan != fBulan) show = false;
                 else if (trYear != currentYear) show = false;
             }
             
             // Filter Sumber
             if (show && fSumber && trSumber !== fSumber) show = false;
             
             // Filter Kelas (existing logic already covers this or we add it if not)
             var trKelas = '';
             var $kelasSelect = $tr.find('.select-potensi');
             if ($kelasSelect.length > 0) {
                 trKelas = $kelasSelect.find('option:selected').text().trim();
             }
             if (show && fKelas && trKelas !== fKelas) show = false;
             
             // --- NEW FILTERS ---
             // Filter Provinsi
             if (show && fProv && trProvinsi !== fProv) show = false;
             
             // Filter Kota
             if (show && fKota && trKota !== fKota) show = false;
             
             // Search
             if (show && search && !trText.includes(search)) show = false;
             
             $tr.toggle(show);
         });
    }
    
    // Hook into existing events to also call our unified filter
    $('#filterSumber, #filterKelas').on('change', applyTableFilters);
    
    // Note: older applyFilters function defined in document.ready above might conflict if not careful.
    // We are overriding or extending functionality. The previous script block used "applyFilters" name. 
    // Since we are inside the same doc.ready (effectively), we should be careful. 
    // To be safe, we'll assume the previous separate scripts might need consolidation, 
    // but typically later script specific listeners will run.
    // We explicitly attach applyTableFilters to the new inputs.
});
</script>
@endsection
<!-- Modal Create -->


<script>
    $('#createForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert('Berhasil disimpan!');
                $('#createPesertaModal').modal('hide');
                location.reload(); // atau refresh tabel data
            },
            error: function(err) {
                alert('Gagal menyimpan.');
            }
        });
    });
</script>

<script>
    function create() {
        $('#createPesertaModal').modal('show');
    }

    $('#createForm').on('submit', function(e) {
        e.preventDefault();
        // Add your AJAX call here to save the data
        alert('Data saved successfully!');
        $('#createPesertaModal').modal('hide');
    });
</script>
{{--    <script>
        $(document).ready(function() {
            $('#myTable').DataTable({
                responsive: true,
                autoWidth: false,
            });
        });
    </script> --}}



<!-- Modal Create -->
<div class="modal fade" id="createPesertaModal" tabindex="-1" role="dialog" aria-labelledby="createPesertaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPesertaModalLabel">Tambah Peserta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createForm" action="{{ route('admin.database.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- Nama Peserta --}}
                    <div class="form-group">
                        <label for="nama">Nama Peserta</label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                    </div>

                    {{-- Status Peserta --}}
      

             {{-- Potensi Kelas --}}
<div class="form-group">
    <label for="kelas_id">Potensi Kelas</label>
    <select name="kelas_id" id="kelas_id" class="form-control" required>
        <option value="">Pilih Potensi Kelas</option>

        @forelse($kelas as $item)
            <option value="{{ $item->id }}">{{ $item->nama_kelas }}</option>
        @empty
            <option disabled>Tidak ada kelas tersedia</option>
        @endforelse
    </select>
</div>


                    {{-- Sumber Leads --}}
                    <div class="form-group">
                        <label for="leads">Sumber Leads</label>
                        <select name="leads" id="leads" class="form-control">
                            <option value="Marketing">Marketing</option>
                            <option value="Iklan">Iklan</option>
                            <option value="Alumni">Alumni</option>
                            <option value="Mandiri">Mandiri</option>
                        </select>
                    </div>

                    {{-- Provinsi --}}
                    <div class="form-group">
                        <label for="provinsi">Provinsi</label>
                        <select id="provinsi" class="form-control" name="provinsi_id" required>
                            <option value="">Pilih Provinsi</option>
                        </select>
                        <input type="hidden" name="provinsi_nama" id="provinsi_nama">
                    </div>

                    {{-- Kota --}}
                    <div class="form-group">
                        <label for="kota">Kota</label>
                        <select id="kota" class="form-control" name="kota_id" required>
                            <option value="">Pilih Kota</option>
                        </select>
                        <input type="hidden" name="kota_nama" id="kota_nama">
                    </div>

                    {{-- Script Ambil Wilayah --}}
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        fetch('/wilayah/provinsi')
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(prov => {
                                    $('#provinsi').append(`<option value="${prov.id}" data-nama="${prov.name}">${prov.name}</option>`);
                                });
                            });

                        $('#provinsi').on('change', function() {
                            const id = $(this).val();
                            const nama = $(this).find('option:selected').text();
                            $('#provinsi_nama').val(nama);

                            fetch(`/wilayah/kota/${id}`)
                                .then(res => res.json())
                                .then(data => {
                                    $('#kota').html('<option value="">Pilih Kota</option>');
                                    data.forEach(kota => {
                                        $('#kota').append(`<option value="${kota.id}" data-nama="${kota.name}">${kota.name}</option>`);
                                    });
                                });
                        });

                        $('#kota').on('change', function() {
                            const nama = $(this).find('option:selected').text();
                            $('#kota_nama').val(nama);
                        });
                    </script>

                    {{-- Nama Bisnis --}}
                    <div class="form-group">
                        <label for="nama_bisnis">Nama Bisnis</label>
                        <input type="text" class="form-control" id="nama_bisnis" name="nama_bisnis" required>
                    </div>

                    {{-- Jenis Bisnis --}}
                    <div class="form-group">
                        <label for="jenisbisnis">Jenis Bisnis</label>
                        <select name="jenisbisnis" id="jenisbisnis" class="form-control">
                            <option value="Bisnis Properti">Bisnis Properti</option>
                            <option value="Bisnis Manufaktur">Bisnis Manufaktur</option>
                            <option value="Bisnis F&B (Food & Beverage)">Bisnis F&B (Food & Beverage)</option>
                            <option value="Bisnis Jasa">Bisnis Jasa</option>
                            <option value="Bisnis Digital">Bisnis Digital</option>
                            <option value="Bisnis Online">Bisnis Online</option>
                            <option value="Bisnis Franchise">Bisnis Franchise</option>
                            <option value="Bisnis Edukasi & Pelatihan">Bisnis Edukasi & Pelatihan</option>
                            <option value="Bisnis Kreatif">Bisnis Kreatif</option>
                            <option value="Bisnis Agribisnis">Bisnis Agribisnis</option>
                            <option value="Bisnis Kesehatan & Kecantikan">Bisnis Kesehatan & Kecantikan</option>
                            <option value="Bisnis Keuangan">Bisnis Keuangan</option>
                            <option value="Bisnis Transportasi & Logistik">Bisnis Transportasi & Logistik</option>
                            <option value="Bisnis Pariwisata & Hospitality">Bisnis Pariwisata & Hospitality</option>
                            <option value="Bisnis Sosial (Social Enterprise)">Bisnis Sosial (Social Enterprise)</option>
                        </select>
                    </div>

                    {{-- No WA --}}
                    <div class="form-group">
                        <label for="no_wa">No. WA</label>
                        <input type="text" class="form-control" id="no_wa" name="no_wa" required>
                    </div>

                    {{-- Situasi Bisnis --}}
                    <div class="form-group">
                        <label for="situasi_bisnis">Situasi Bisnis</label>
                        <textarea class="form-control" id="situasi_bisnis" name="situasi_bisnis" rows="3"></textarea>
                    </div>

                    {{-- Kendala --}}
                    <div class="form-group">
                        <label for="kendala">Kendala</label>
                        <textarea class="form-control" id="kendala" name="kendala" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>




<!-- End Modal Create -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Logic filter kelas sudah digabung di applyFilters()
</script>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<!-- ✅ Tambahkan ini di atas tabel kamu -->
<link rel="stylesheet" 
      href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // Logic for new columns: Berhasil Spin & Ikut Zoom
    $(document).on('change', '.check-spin, .check-zoom', function() {
        let $this = $(this);
        let id = $this.data('id');
        let field = $this.hasClass('check-spin') ? 'berhasil_spin' : 'ikut_zoom';
        let value = $this.is(':checked') ? 1 : 0;
    
        $.ajax({
            url: '/admin/database/update-inline',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                id: id,
                field: field,
                value: value
            },
            success: function(res) {
                console.log('Updated checkbox:', field);
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                })
                Toast.fire({
                    icon: 'success',
                    title: 'Status Updated'
                })
            },
            error: function(xhr) {
                console.log('Error updating checkbox');
                // alert('Gagal update status.');
            }
        });
    });
</script>
