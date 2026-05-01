@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Data Pembeli</h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Data Pembeli</li>
        </ol>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background-color: #25799E;">
            <h6 class="m-0 font-weight-bold text-white">Filter Data Pembeli</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pembeli.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Kelas/Produk</label>
                    <select name="kelas" class="form-control form-control-sm">
                        <option value="">Semua Produk</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->nama_kelas }}" {{ $kelasFilter == $k->nama_kelas ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Bulan</label>
                    <select name="bulan" class="form-control form-control-sm">
                        <option value="">Semua Bulan</option>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $bulanFilter == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->locale('id')->monthName }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold">Tahun</label>
                    <select name="tahun" class="form-control form-control-sm">
                        @for($y=date('Y'); $y>=2023; $y--)
                            <option value="{{ $y }}" {{ $tahunFilter == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @if(in_array(auth()->user()->role, ['administrator', 'manager']))
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Input Oleh (Sales)</label>
                    <select name="created_by" class="form-control form-control-sm">
                        <option value="">Semua Sales</option>
                        @foreach($csList as $cs)
                            <option value="{{ $cs->id }}" {{ $csFilter == $cs->id ? 'selected' : '' }}>{{ $cs->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Total Pembeli: {{ $pesertaTransfer->total() }}</h5>
                <div style="width: 200px;">
                    <label class="small fw-bold mb-0">Filter Status Tabel:</label>
                    <select id="filterStatusDaftar" class="form-control form-control-sm">
                        <option value="all">Semua Status</option>
                        <option value="Tunai">Tunai</option>
                        <option value="KPR">KPR</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table id="tabelPeserta" class="table table-bordered table-hover text-center" style="font-size: 14px;">
                    <thead>
                        <tr style="background: linear-gradient(to right, #376bb9ff, #1c7f91ff); color: white;">
                            <th>No</th>
                            <th>Nama Peserta</th>
                            <th>Produk</th>
                            <th>Status</th>
                            <th>Nominal</th>
                            <th>KPR Action</th>
                            <th>Monitoring KPR</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody style="font-weight: bold; color: #000;">
                        @php $totalNominal = 0; @endphp
                        @forelse($pesertaTransfer as $i => $p)
                            @php 
                                $statusLabel = ($p->status == 'mau_transfer') ? 'KPR' : 'Tunai'; 
                                $statusBadge = ($p->status == 'mau_transfer') ? 'bg-success' : 'bg-info';
                            @endphp
                            <tr class="peserta-row" data-status="{{ $statusLabel }}">
                                <td>{{ $pesertaTransfer->firstItem() + $i }}</td>
                                <td class="text-start">{{ $p->nama }}</td>
                                <td>{{ $p->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $statusBadge }} text-white">{{ $statusLabel }}</span>
                                </td>
                                <td>Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                <td>
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
                                <td>
                                    @if($p->status == 'mau_transfer')
                                        @php $kprP = $p->kpr; @endphp
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
                                                    <div class="kpr-step-item" style="margin: 0 2px;">
                                                        <div class="kpr-step-dot {{ $isCompleted ? 'completed' : ($isActive ? 'active' : '') }} kpr-clickable-dot" 
                                                             data-kpr-id="{{ $kprP->id }}" 
                                                             data-stage="{{ $stage }}"
                                                             style="width: 15px; height: 15px; line-height: 15px; font-size: 8px; cursor: pointer;"
                                                             title="Klik untuk ubah ke tahap {{ $stage }}">
                                                            @if($isCompleted)<i class="fas fa-check"></i>@endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-2">
                                                <a href="{{ route('admin.kpr.show', $kprP->id) }}" class="btn btn-sm btn-primary py-1 px-3 shadow-sm" style="font-size: 12px; font-weight: bold; border-radius: 50px;">
                                                    <i class="fas fa-eye me-1"></i> Detail
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted small">Belum diinput</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger btn-hapus-pembeli" 
                                        data-id="{{ $p->id }}"
                                        data-nama="{{ $p->nama }}"
                                        title="Hapus data">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @php $totalNominal += $p->nominal; @endphp
                        @empty
                            <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada data pembeli.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3 d-flex justify-content-center">
                {{ $pesertaTransfer->appends(request()->input())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<style>
    .kpr-step-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #ddd;
        display: inline-block;
        text-align: center;
        line-height: 20px;
        color: white;
        font-size: 10px;
    }
    .kpr-step-dot.completed { background-color: #28a745; }
    .kpr-step-dot.active { border: 2px solid #007bff; background-color: #fff; color: #007bff; }
    .btn-xs { padding: 0.1rem 0.3rem; font-size: 0.75rem; }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        // Filter logic for the table rows
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

        // Move to KPR
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

        // Delete Pembeli
        $(document).on('click', '.btn-hapus-pembeli', function() {
            let id = $(this).data('id');
            let nama = $(this).data('nama');
            let $row = $(this).closest('tr');

            Swal.fire({
                title: 'Hapus Data?',
                html: `Yakin ingin menghapus data <b>${nama}</b> dari Data Pelanggan?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/salesplan/' + id,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            $row.fadeOut(400, function() {
                                $(this).remove();
                                // Update total count
                                let total = parseInt($('.fw-bold.mb-0').text().replace('Total Pembeli: ', '')) - 1;
                                $('.fw-bold.mb-0').text('Total Pembeli: ' + total);
                            });
                            Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                        },
                        error: function() {
                            Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus data.', 'error');
                        }
                    });
                }
            });
        });

        // Interactive Dots
        $(document).on('click', '.kpr-clickable-dot', function() {
            let id = $(this).data('kpr-id');
            let stage = $(this).data('stage');
            window.location.href = "/admin/kpr/" + id + "?step=" + encodeURIComponent(stage);
        });
    });
</script>
@endpush
@endsection
