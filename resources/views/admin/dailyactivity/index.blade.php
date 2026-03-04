@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid py-4" style="background: #fdfdfd; min-height: 100vh;">
    {{-- Header Section MBC Style --}}
    <div class="text-center mb-4">
        <h3 class="fw-light text-primary d-inline-block" style="border-bottom: 2px solid #007bff; padding-bottom: 5px;">
            📋 DAILY ACTIVITY
        </h3>
    </div>

    <div class="card border-0 bg-transparent mb-4">
        <div class="card-body p-0">
            <div class="d-flex flex-column flex-md-row justify-content-start align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 text-muted small">Tanggal:</label>
                    <div class="bg-white p-2 rounded shadow-sm border d-flex align-items-center" style="width: 250px;">
                        <input type="date" name="tanggal" class="form-control form-control-sm border-0 bg-transparent fw-bold" 
                               value="{{ $tanggal }}"
                               onchange="window.location='?tanggal=' + this.value">
                    </div>
                </div>
                <a href="{{ route('admin.daily-activity.exportPdf', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m')]) }}" 
                   class="btn btn-danger btn-sm shadow-sm px-4" target="_blank">
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
            @php $categoryName = $list->first()->kategori->nama ?? ("Kategori " . $kategoriId); @endphp
            <div class="card shadow-sm border-0 overflow-hidden mb-4" style="border-radius: 8px;">
                <div class="card-header bg-primary py-2 px-3 border-0">
                    <h6 class="m-0 font-weight-bold text-white small">{{ $categoryName }}</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="text-center py-2" style="width: 50px;">No</th>
                                    <th class="py-2 px-3">Aktivitas</th>
                                    <th class="text-center py-2" style="width: 120px;">Target Harian</th>
                                    <th class="text-center py-2" style="width: 120px;">Target Bulan</th>
                                    <th class="text-center py-2" style="width: 120px;">Bobot</th>
                                    <th class="text-center py-2" style="width: 150px;">Realisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($list as $index => $act)
                                    <tr>
                                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                                        <td class="px-3 text-dark">{{ $act->nama }}</td>
                                        <td class="text-center text-muted">
                                             @if($act->nama === 'Transfer Masuk') 2.000.000
                                             @else {{ number_format($act->target_daily, 0, ',', '.') }}
                                             @endif
                                        </td>
                                        <td class="text-center text-muted">
                                             @if($act->nama === 'Transfer Masuk') 50.000.000
                                             @else {{ number_format($act->target_bulanan, 0, ',', '.') }}
                                             @endif
                                        </td>
                                        <td class="text-center text-muted">{{ (int)$act->bobot }}</td>
                                        <td class="p-1 px-3">
                                            <input type="number" 
                                                   name="realisasi[{{ $act->id }}]" 
                                                   class="form-control form-control-sm text-center border-soft shadow-none"
                                                   min="0"
                                                   step="0.01"
                                                   value="{{ $daily[$act->id] ?? 0 }}">
                                            
                                            @php
                                                $isAuto = in_array($act->nama, ['Database baru', 'Telepon Database Prospek', 'Edukasi & Membangun Hubungan (WA)', 'List Building / Database']);
                                            @endphp
                                            @if($isAuto)
                                                <div class="text-center" style="margin-top: 2px;">
                                                    <a href="#" class="text-primary" style="font-size: 0.65rem; text-decoration: none;">
                                                        <i class="fas fa-magic me-1"></i> Otomatis dari Database
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="mb-5 mt-4">
            <button type="submit" class="btn btn-success px-4 shadow-sm fw-bold">
                <i class="fas fa-save me-2"></i> Simpan Aktivitas
            </button>
        </div>
    </form>
</div>

<style>
    .bg-primary { background-color: #4e73df !important; }
    .table-bordered th, .table-bordered td { border-color: #e3e6f0 !important; }
    .border-soft { border-color: #d1d3e2; }
    .card { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1) !important; }
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
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
