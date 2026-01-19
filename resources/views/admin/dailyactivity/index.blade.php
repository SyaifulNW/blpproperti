@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid py-4">
    {{-- Header Section --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1">📅 Daily Activity</h3>
            <p class="text-muted small mb-0">Monitor and track your daily intake performance</p>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <div class="bg-white p-2 rounded shadow-sm border d-flex align-items-center">
                <i class="fas fa-calendar-alt text-primary me-2"></i>
                <input type="date" name="tanggal" class="form-control form-control-sm border-0 bg-transparent fw-bold" 
                       value="{{ $tanggal }}"
                       onchange="window.location='?tanggal=' + this.value">
            </div>
            <a href="{{ route('admin.daily-activity.exportPdf', ['bulan' => \Carbon\Carbon::parse($tanggal)->format('Y-m')]) }}" 
               class="btn btn-primary shadow-sm hover-lift" target="_blank">
                <i class="fas fa-file-pdf me-2"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Stats Overview --}}
    <div class="row mb-4">
        @php
            $intakeActivities = collect();
            foreach($activities as $list) {
                if($list->first() && $list->first()->kategori->nama === 'Intake Activity') {
                    $intakeActivities = $list;
                    break;
                }
            }
        @endphp
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 rounded-lg">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Activities</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $intakeActivities->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 rounded-lg">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Working Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ \Carbon\Carbon::parse($tanggal)->format('F Y') }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Activity Form --}}
    <form id="daily-activity-form" action="{{ route('admin.daily-activity.store') }}" method="POST">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="card shadow border-0 overflow-hidden rounded-xl">
            <div class="card-header bg-gradient-primary py-3 d-flex align-items-center justify-content-between border-0">
                <h6 class="m-0 font-weight-bold text-white">
                    <i class="fas fa-chart-line me-2"></i> Rangkuman Intake Activity
                </h6>
                <span class="badge badge-light text-primary py-2 px-3 rounded-pill fw-bold">Manual Input Mode</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-dark text-uppercase small fw-bold">
                            <tr>
                                <th class="px-4 py-3 border-0">Aktivitas</th>
                                <th class="text-center py-3 border-0">Target Bulanan</th>
                                <th class="text-center py-3 border-0" style="width: 250px;">Realisasi Hari Ini</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($intakeActivities as $act)
                                <tr class="transition-all">
                                    <td class="px-4 py-3 font-weight-bold text-dark">
                                        <div class="d-flex align-items-center">
                                            <div class="activity-icon-sm bg-primary-soft text-primary rounded me-3 d-flex align-items-center justify-content-center">
                                                <i class="fas fa-check-circle small"></i>
                                            </div>
                                            {{ $act->nama }}
                                        </div>
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="badge border bg-white text-muted px-3 py-2 rounded-pill fw-bold">
                                            @if($act->nama === 'Database baru') 100
                                            @elseif($act->nama === 'Follow-up aktif') 80–120
                                            @elseif($act->nama === 'Presentasi') 8–12
                                            @elseif($act->nama === 'Visit lokasi') 10
                                            @elseif($act->nama === 'Closing') 1–2
                                            @else {{ number_format($act->target_bulanan, 0) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="input-group input-group-sm">
                                            <input type="number" 
                                                   name="realisasi[{{ $act->id }}]" 
                                                   class="form-control text-center fw-bold border-soft shadow-none focus-primary"
                                                   min="0"
                                                   placeholder="Input hasil..."
                                                   value="{{ $daily[$act->id] ?? 0 }}">
                                            <!-- <span class="input-group-text bg-white border-soft text-muted px-3">
                                                Units
                                            </span> -->
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5 text-muted fst-italic">
                                        <img src="https://img.icons8.com/isometric/100/null/empty-box.png" class="mb-3 d-block mx-auto opacity-50" style="width: 60px;">
                                        Belum ada data Intake Activity untuk tanggal ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($intakeActivities->count() > 0)
            <div class="card-footer bg-white py-4 px-4 text-end border-0">
                <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm hover-lift fw-bold">
                    <i class="fas fa-save me-2"></i> Simpan Aktivitas
                </button>
            </div>
            @endif
        </div>
    </form>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    .rounded-xl { border-radius: 1.25rem !important; }
    .rounded-lg { border-radius: 0.85rem !important; }
    .bg-primary-soft { background-color: rgba(78, 115, 223, 0.1); }
    .border-soft { border-color: #e3e6f0; }
    .activity-icon-sm { width: 32px; height: 32px; flex-shrink: 0; }
    .hover-lift { transition: transform 0.2s; }
    .hover-lift:hover { transform: translateY(-3px); }
    .transition-all { transition: all 0.2s ease; }
    .table-hover tbody tr:hover { background-color: rgba(78, 115, 223, 0.03); }
    .focus-primary:focus {
        border-color: #bac8f3 !important;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.1) !important;
    }
    .btn-lg { font-size: 0.95rem; }
    .border-left-primary { border-left: 0.25rem solid #4e73df !important; }
    .border-left-success { border-left: 0.25rem solid #1cc88a !important; }
    .text-xs { font-size: .7rem; }
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
