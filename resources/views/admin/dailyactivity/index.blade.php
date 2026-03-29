@extends('layouts.masteradmin')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

<div class="container-fluid py-4" style="font-family: 'Outfit', sans-serif; background: #f8fafc; min-height: 100vh;">
    {{-- Header Section --}}
    <div class="text-center mb-5 mt-3">
        <h2 class="fw-bold d-inline-block position-relative" style="color: #334155; letter-spacing: -0.5px; text-transform: uppercase;">
            <i class="fas fa-clipboard-list me-2 text-primary"></i> DAILY ACTIVITY
        </h2>
    </div>

    {{-- Control Panel Card --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-end gap-3">
                <div>
                    <label class="small text-muted fw-bold d-block mb-1">Tanggal:</label>
                    <input type="date" name="tanggal" class="form-control border shadow-sm fw-bold px-3 py-2" 
                           style="font-size: 1rem; color: #1e293b; width: 220px; border-radius: 8px;"
                           value="{{ $tanggal }}"
                           onchange="window.location='?tanggal=' + this.value">
                </div>
                <a href="{{ route('admin.daily-activity.exportPdf', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m')]) }}" 
                   class="btn shadow-sm px-4 py-2 fw-semibold d-inline-flex align-items-center text-white" 
                   target="_blank" style="background-color: #e74c3c; border-radius: 6px; transition: all 0.3s ease; height: 42px;">
                    <i class="fas fa-file-pdf me-2"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    {{-- Main Activity Form --}}
    <form id="daily-activity-form" action="{{ route('admin.daily-activity.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        @foreach($activities as $kategoriId => $list)
            @php 
                $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId); 
                $totalTargetHari = 0;
                $totalTargetBulan = 0;
                $totalBobot = 0;
                $totalRealisasiBulan = 0;
                $totalNilai = 0;
            @endphp
            
            <div class="card border-0 shadow-sm mb-5 overflow-hidden" style="border-radius: 12px; border: 1px solid #e2e8f0 !important;">
                <div class="p-3 text-white d-flex align-items-center justify-content-between" style="background-color: #4f70e9;">
                    <h5 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ $categoryName }}</h5>
                    <i class="fas fa-chevron-right small opacity-50"></i>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="border-color: #e2e8f0;">
                        <thead>
                            <tr class="bg-white">
                                <th class="text-center py-3 fw-bold text-muted border-bottom" style="width: 60px; font-size: 0.85rem;">No</th>
                                <th class="py-3 px-3 fw-bold text-muted border-bottom" style="font-size: 0.85rem;">Aktivitas</th>
                                <th class="text-center py-3 fw-bold text-muted border-bottom" style="width: 140px; font-size: 0.85rem;">Target Harian</th>
                                <th class="text-center py-3 fw-bold text-muted border-bottom" style="width: 140px; font-size: 0.85rem;">Target Bulan</th>
                                <th class="text-center py-3 fw-bold text-muted border-bottom" style="width: 100px; font-size: 0.85rem;">Bobot</th>
                                <th class="text-center py-3 fw-bold text-muted border-bottom" style="width: 150px; font-size: 0.85rem;">Realisasi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($list as $index => $act)
                                @php
                                    $targetDaily = (float)$act->target_daily;
                                    $targetBulanan = (float)$act->target_bulanan;
                                    if ($act->nama === 'Transfer Masuk') {
                                        $targetDaily = 0;
                                        $targetBulanan = 1250000000;
                                    }

                                    $realisasiHariIni = $daily[$act->id] ?? 0;
                                    $realisasiBulanIni = $monthlyTotals[$act->id] ?? 0;
                                    
                                    $nilai = ($targetBulanan > 0) ? ($realisasiBulanIni / $targetBulanan) * $act->bobot : 0;
                                    if($nilai > $act->bobot) $nilai = $act->bobot;

                                    $totalTargetHari += $targetDaily;
                                    $totalTargetBulan += $targetBulanan;
                                    $totalBobot += (float)$act->bobot;
                                    $totalRealisasiBulan += (float)$realisasiBulanIni;
                                    $totalNilai += $nilai;
                                @endphp
                                <tr>
                                    <td class="text-center text-muted font-small">{{ $loop->iteration }}</td>
                                    <td class="px-3">
                                        <div class="text-dark" style="font-size: 0.95rem;">{{ $act->nama }}</div>
                                    </td>
                                    <td class="text-center text-muted">
                                         {{ number_format($targetDaily, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center text-muted">
                                         {{ number_format($targetBulanan, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center text-muted">{{ (int)$act->bobot }}</td>
                                    <td class="p-2">
                                        <input type="number" 
                                               name="realisasi[{{ $act->id }}]" 
                                               class="form-control form-control-sm text-center border-secondary-subtle mx-auto"
                                               min="0"
                                               step="1"
                                               value="{{ (int)$realisasiHariIni }}"
                                               style="font-size: 0.9rem; border-radius: 4px; width: 100px;">
                                        
                                        @php
                                            $isAuto = in_array($act->nama, ['Database Baru', 'Telepon Database Prospek', 'Edukasi & Membangun Hubungan (WA)', 'List Building / Database']);
                                        @endphp
                                        @if($isAuto)
                                            <div class="text-center mt-1">
                                                <span class="text-primary fw-bold" style="font-size: 0.6rem; opacity: 0.8;">
                                                    <i class="fas fa-sync-alt small-spin me-1"></i> AUTO
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="background-color: #e8f1ff;">
                            <tr class="fw-bold text-dark" style="border-top: 2px solid #e2e8f0; background-color: #e8f1ff;">
                                <td class="text-center border-0"></td>
                                <td class="px-3 border-0">Total {{ $categoryName }}</td>
                                <td class="text-center border-0 font-small">{{ number_format($totalTargetHari, 0, ',', '.') }}</td>
                                <td class="text-center border-0 font-small">{{ number_format($totalTargetBulan, 0, ',', '.') }}</td>
                                <td class="text-center border-0 font-small">{{ (int)$totalBobot }}</td>
                                <td class="text-center border-0 font-small text-primary"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="text-center pb-5">
            <button type="submit" class="btn btn-primary btn-lg px-5 shadow fw-bold text-white mb-4" 
                    style="border-radius: 8px; padding-top: 12px; padding-bottom: 12px; background-color: #4f70e9; border: none;">
                <i class="fas fa-save me-2"></i> SIMPAN LAPORAN
            </button>
        </div>
    </form>
</div>

<style>
    .font-small { font-size: 0.85rem; }
    
    /* Make table lines more visible */
    .table-bordered {
        border: 1px solid #cbd5e1 !important;
    }
    .table-bordered th, .table-bordered td {
        border: 1px solid #cbd5e1 !important;
    }
    
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    
    .small-spin { animation: spin 4s linear infinite; }
    @keyframes spin { 100% { transform: rotate(360deg); } }
    
    .btn:hover { filter: brightness(95%); transform: translateY(-1px); }
    
    /* Hover effect for rows */
    tr:hover td {
        background-color: #f8fafc;
    }
</style>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('#daily-activity-form').on('submit', function(e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');
        let data = form.serialize();

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Harap tunggu',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.post(url, data)
            .done(function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message || 'Data berhasil disimpan',
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .fail(function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan',
                });
            });
    });
});
</script>
@endpush
