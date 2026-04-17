@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Monitoring Data KPR</h1>
        <a href="{{ route('admin.salesplan.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Prospek
        </a>
    </div>

    <div class="card shadow mb-4 border-0 rounded-lg overflow-hidden">
        <div class="card-header py-3 bg-gradient-primary text-white d-flex align-items-center">
            <i class="fas fa-clipboard-list me-2 mr-2"></i>
            <h6 class="m-0 font-weight-bold text-uppercase">Daftar Monitoring KPR</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="bg-light text-center text-uppercase small font-weight-bold">
                        <tr>
                            <th width="50">No</th>
                            <th>Nama Pembeli</th>
                            <th>Tahap Terakhir</th>
                            <th>Status Global</th>
                            <th>Tanggal Update</th>
                            <th>Catatan</th>
                            <th>Next Action</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(count($kprs) > 0)
                            @foreach($kprs as $k)
                                <tr class="align-middle">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">
                                        {{ $k->nama }} <br>
                                        <small class="text-muted font-weight-normal">
                                            @if($k->phone && $k->phone != '-')
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $k->phone) }}" target="_blank" class="text-success text-decoration-none">
                                                    <i class="fab fa-whatsapp me-1"></i>{{ $k->phone }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-white p-2 shadow-sm rounded-pill" style="min-width: 120px; font-size: 0.8rem;">
                                            <i class="fas fa-tasks me-1 mr-1 small"></i> {{ $k->tahap_posisi }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusClass = 'bg-warning';
                                            $statusIcon = 'fa-spinner fa-spin';
                                            if(strtolower($k->status_global) == 'success') { $statusClass = 'bg-success'; $statusIcon = 'fa-check-double'; }
                                            if(strtolower($k->status_global) == 'failed') { $statusClass = 'bg-danger'; $statusIcon = 'fa-times-circle'; }
                                        @endphp
                                        <span class="badge {{ $statusClass }} text-white p-2 shadow-sm rounded-pill" style="min-width: 110px; font-size: 0.8rem;">
                                            <i class="fas {{ $statusIcon }} me-1 mr-1"></i> {{ $k->status_global }}
                                        </span>
                                    </td>
                                    <td class="text-center text-muted"><i class="far fa-calendar-alt me-1"></i>{{ $k->updated_at->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($k->catatan_umum, 60, '...') }}</td>
                                    <td class="text-primary fw-bold">{{ $k->next_action ?? '-' }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('admin.kpr.destroy', $k->id) }}" method="POST" class="form-delete-kpr">
                                            @csrf
                                            @method('DELETE')
                                            <div class="d-flex justify-content-center" style="gap: 5px;">
                                                <a href="{{ route('admin.kpr.show', $k->id) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="Monitoring Detail">
                                                    <i class="fas fa-search-plus"></i> Detail
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-kpr shadow-sm" data-nama="{{ $k->nama }}" title="Hapus Data">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-gray-300"></i><br>
                                    Belum ada data KPR terdaftar.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $kprs->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    }
    .table th {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
    }
    .badge-pill {
        border-radius: 50rem;
    }
    .align-middle td {
        vertical-align: middle !important;
    }
</style>
<!-- SweetAlert2 & Animate.css -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('.btn-delete-kpr').on('click', function() {
            var nama = $(this).data('nama');
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Hapus Data?',
                html: `Anda akan menghapus data monitoring KPR untuk <b>${nama}</b>. Tindakan ini tidak dapat dibatalkan!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#858796',
                confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showClass: {
                    popup: 'animate__animated animate__headShake'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
