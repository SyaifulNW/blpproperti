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

        th,
        td {
            padding: 12px 15px !important;
            vertical-align: middle !important;
        }

        .editable {
            cursor: pointer;
        }

        .editing {
            background-color: #fff3cd !important;
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

        [contenteditable]:empty:before {
            content: attr(placeholder);
            color: #adb5bd;
            pointer-events: none;
            display: block;
        }

        .hover-bg-white:hover {
            background-color: white !important;
            transform: translateX(5px);
        }

        .transition-all {
            transition: all 0.2s ease;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .spin-card {
            min-width: 280px;
            max-width: 280px;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            background: #fff;
            margin-right: 15px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .spin-card:hover {
            transform: translateY(-5px);
        }

        .spin-card-header {
            background: #ffca2c;
            color: #212529;
            padding: 8px 15px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            font-weight: 800;
        }

        .add-spin-card {
            min-width: 200px;
            border: 3px dashed #4e73df;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-left: 10px;
        }

        .add-spin-card:hover {
            background: #eaecf4;
            border-color: #224abe;
        }

        .spin-hasil,
        .spin-tindak {
            font-size: 0.8rem;
            border-radius: 8px;
            border: 1px solid #d1d3e2;
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

    {{-- ALERT MODE READ ONLY --}}
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
    @endif

    <div class="content">
        <div class="card card-info card-outline">
            @php
                use Carbon\Carbon;
                $now = Carbon::now();
                $bulanLabel = $bulanLabel ?? $now->isoFormat('MMMM YYYY');
                $databaseBaru = $databaseBaru ?? 0;
                $totalDatabase = $totalDatabase ?? 0;
                $target = $target ?? 100;
                $kurang = $kurang ?? 0;
                $data = $data ?? collect([]);
            @endphp

            <div class="card-header">
                {{-- Stats Cards --}}
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
                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                        transition: all 0.3s ease;
                        min-width: 140px;
                        position: relative;
                        overflow: hidden;
                        flex: 1;
                    }

                    .g-sc-cyan {
                        background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
                    }

                    .g-sc-blue {
                        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
                    }

                    .g-sc-yellow {
                        background: linear-gradient(135deg, #ffca2c 0%, #ffc107 100%);
                        color: #212529;
                    }

                    .g-sc-red {
                        background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
                    }

                    .g-sc-content {
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                    }

                    .g-sc-label {
                        font-size: 0.7rem;
                        text-transform: uppercase;
                        font-weight: 700;
                        opacity: 0.8;
                        margin-bottom: 4px;
                        white-space: nowrap;
                    }

                    .g-sc-value {
                        font-size: 1.5rem;
                        font-weight: 900;
                        line-height: 1;
                    }

                    .g-sc-icon {
                        margin-left: auto;
                        font-size: 1.8rem;
                        opacity: 0.3;
                    }
                </style>

                <div class="stat-card-group mb-4">
                    <div class="g-stat-card g-sc-cyan">
                        <div class="g-sc-content">
                            <span class="g-sc-label">Database Baru</span>
                            <span class="g-sc-value">{{ $databaseBaru }}</span>
                        </div>
                        <div class="g-sc-icon"><i class="fas fa-database"></i></div>
                    </div>
                    <div class="g-stat-card g-sc-blue">
                        <div class="g-sc-content">
                            <span class="g-sc-label">Total Database</span>
                            <span class="g-sc-value">{{ $totalDatabase }}</span>
                        </div>
                        <div class="g-sc-icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                    <div class="g-stat-card g-sc-yellow">
                        <div class="g-sc-content">
                            <span class="g-sc-label">Target Bulanan</span>
                            <span class="g-sc-value">{{ $target }}</span>
                        </div>
                        <div class="g-sc-icon"><i class="fas fa-bullseye"></i></div>
                    </div>
                    <div class="g-stat-card g-sc-red text-white">
                        <div class="g-sc-content">
                            <span class="g-sc-label text-white">Kurang</span>
                            <span class="g-sc-value">{{ $kurang }}</span>
                        </div>
                        <div class="g-sc-icon text-white"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        @if(!in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) && !(auth()->user()->name === 'Linda' && request('view') !== 'me'))
                            <button type="button" class="btn btn-success" id="btnTambahInline">
                                <i class="fa-solid fa-plus"></i> Tambah
                            </button>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        @php
                            $user = auth()->user();
                            $csList = \App\Models\User::whereIn('role', ['cs', 'CS', 'customer_service', 'cs-mbc', 'cs-smi'])->orderBy('name')->get();
                        @endphp

                        {{-- Elegant Filter Group --}}
                        <div class="d-flex gap-2">
                            @if((in_array(strtolower($user->role), ['administrator', 'manager']) && $user->name !== 'Agus Setyo') || ($user->name === 'Linda' && request('view') !== 'me'))
                                <select onchange="updateFilter('cs_name', this.value)"
                                    class="form-select form-select-sm border-radius-50 px-3" style="min-width: 150px;">
                                    <option value="">-- Semua Sales --</option>
                                    @foreach($csList as $cs)
                                        <option value="{{ $cs->name }}" {{ request('cs_name') == $cs->name ? 'selected' : '' }}>
                                            {{ $cs->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            
                            <select onchange="updateFilter('survei', this.value)" 
                                class="form-select form-select-sm border-radius-50 px-3" style="min-width: 120px;">
                                <option value="">-- Survei --</option>
                                <option value="Ya" {{ request('survei') == 'Ya' ? 'selected' : '' }}>Sudah Survei</option>
                                <option value="Tidak" {{ request('survei') == 'Tidak' ? 'selected' : '' }}>Belum Survei</option>
                            </select>

                            <div class="input-group input-group-sm" style="width: auto;">
                                <span class="input-group-text bg-white"><i
                                        class="far fa-calendar-alt text-muted"></i></span>
                                <select onchange="updateFilter('bulan', this.value)" class="form-select"
                                    style="min-width: 100px;">
                                    <option value="">Bulan</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('bulan') == $m ? 'selected' : '' }}>
                                            {{ Carbon::create()->month($m)->translatedFormat('F') }}
                                        </option>
                                    @endforeach
                                </select>
                                <select onchange="updateFilter('tahun', this.value)" class="form-select"
                                    style="min-width: 85px;">
                                    <option value="">Tahun</option>
                                    @foreach(range(2024, 2030) as $y)
                                        <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="input-group input-group-sm" style="width: 280px;">
                            <input type="text" id="tableSearch" class="form-control ps-3"
                                placeholder="Cari Nama Pelanggan..." value="{{ request('search') }}"
                                style="border-radius: 50px 0 0 50px;">
                            <button class="btn btn-primary px-3" type="button"
                                onclick="updateFilter('search', $('#tableSearch').val())"
                                style="border-radius: 0 50px 50px 0;">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive" style="max-height: 600px;">
                    <table id="myTable" class="table table-bordered table-striped nowrap" style="width: max-content;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th style="min-width: 250px;">
                                    Nama Calon Pelanggan
                                    <button type="button" id="btnToggleWA"
                                        class="btn btn-xs btn-light ms-2 border shadow-sm">
                                        <i class="fas fa-eye text-primary"></i>
                                    </button>
                                </th>
                                <th>Sumber Leads</th>
                                @if(strtolower(auth()->user()->role) !== 'marketing')
                                    <th>Survei</th>
                                @endif
                                <th class="text-center">B</th>
                                <th class="text-center">A</th>
                                <th class="text-center">T</th>
                                @if(!in_array(strtolower(auth()->user()->role), ['administrator', 'marketing']))
                                    <th>Sales Plan</th>
                                @endif
                                @if(in_array(strtolower(auth()->user()->role), ['administrator', 'manager']) || auth()->user()->name === 'Agus Setyo')
                                    <th>Input Oleh</th>
                                @endif
                                @if(!in_array(strtolower(auth()->user()->role), ['administrator']))
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
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $data->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}

    <!-- Modal Riwayat SPIN -->
    <div class="modal fade" id="modalRiwayatSpin" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i> SPIN: <span
                            id="spin_nama_peserta"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <div id="spinCardsContainer" class="d-flex flex-row overflow-auto p-2" style="min-height: 400px;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" id="btnSaveSpinInteractions">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Move to SalesPlan -->
    <div class="modal fade" id="moveSalesPlanModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">Move to Sales Plan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="moveSalesPlanForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p>Pindahkan <strong id="move_nama_peserta"></strong> ke Sales Plan produk:</p>
                        <div class="product-list border rounded p-3 bg-light" style="max-height: 300px; overflow-y: auto;">
                            @foreach($kelas as $k)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="kelas_ids[]" value="{{ $k->id }}"
                                        id="kelas_{{ $k->id }}">
                                    <label class="form-check-label fw-bold"
                                        for="kelas_{{ $k->id }}">{{ $k->nama_kelas }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Pindahkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Create Removed as per user request (Inline editing used instead) -->

@endsection

@push('scripts')
    <script>
        // Global Privacy State
        let isPrivacyMasked = false;

        // Filter Helper
        function updateFilter(key, val) {
            let url = new URL(window.location.href);
            if (val) url.searchParams.set(key, val); else url.searchParams.delete(key);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        $(document).ready(function () {
            // Handle Search Enter
            $('#tableSearch').on('keypress', function (e) {
                if (e.key === 'Enter') updateFilter('search', $(this).val());
            });

            // Global Privacy Masking Toggle
            $(document).on('click', '#btnToggleWA', function () {
                isPrivacyMasked = !isPrivacyMasked;
                const icon = $(this).find('i');

                if (isPrivacyMasked) {
                    icon.removeClass('fa-eye').addClass('fa-eye-slash').css('color', '#dc3545');
                    $('.wa-input').each(function () {
                        const original = $(this).val();
                        $(this).attr('data-original', original);
                        $(this).val(original.startsWith('08') ? '08xxxxxx' : original);
                    });
                } else {
                    icon.removeClass('fa-eye-slash').addClass('fa-eye').css('color', '#0d6efd');
                    $('.wa-input').each(function () {
                        $(this).val($(this).attr('data-original') || $(this).val());
                    });
                }
            });

            // Smart Behavior for WA Inputs
            $(document).on('focus', '.wa-input', function () {
                if (isPrivacyMasked) {
                    $(this).val($(this).attr('data-original') || $(this).val());
                }
            }).on('blur', '.wa-input', function () {
                if (isPrivacyMasked) {
                    const original = $(this).val();
                    $(this).attr('data-original', original);
                    $(this).val(original.startsWith('08') ? '08xxxxxx' : original);
                }
            });

            // Inline Editing
            $(document).on('focus', '.editable', function () {
                $(this).addClass('editing');
            }).on('blur', '.editable', function () {
                const $this = $(this);
                const id = $this.data('id');
                const field = $this.data('field');
                const value = $this.is('input') ? $this.val() : $this.text();

                $.ajax({
                    url: '/admin/database/update-inline',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', id, field, value },
                    success: function () {
                        $this.removeClass('editing');
                        showStatusIcon($this, true);
                    },
                    error: function () {
                        $this.removeClass('editing');
                        showStatusIcon($this, false);
                    }
                });
            });

            // Inline Selection
            $(document).on('change', '.select-inline', function () {
                const $this = $(this);
                const id = $this.data('id');
                const field = $this.data('field');
                const value = $this.val();

                $.ajax({
                    url: '/admin/database/update-inline',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', id, field, value },
                    success: function () {
                        showStatusIcon($this, true);
                    },
                    error: function () {
                        showStatusIcon($this, false);
                    }
                });
            });

            $(document).on('change', '.checkbox-inline', function () {
                const $this = $(this);
                const $row = $this.closest('tr');
                const id = $this.data('id');
                const field = $this.data('field');
                const value = $this.is(':checked') ? 'Ya' : 'Tidak';

                $.ajax({
                    url: '/admin/database/update-inline',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', id, field, value },
                    success: function () {
                        showStatusIcon($this, true);

                        // Cek apakah semua B, A, T sudah 'Ya' untuk memunculkan tombol pindah
                        if (['spin_b', 'spin_a', 'spin_t'].includes(field)) {
                            const b = $row.find('input[data-field="spin_b"]').is(':checked');
                            const a = $row.find('input[data-field="spin_a"]').is(':checked');
                            const t = $row.find('input[data-field="spin_t"]').is(':checked');

                            if (b && a && t) {
                                $row.find('.btn-move-salesplan').removeClass('d-none');
                            } else {
                                $row.find('.btn-move-salesplan').addClass('d-none');
                            }
                        }
                    },
                    error: function () {
                        showStatusIcon($this, false);
                    }
                });
            });

            function showStatusIcon($el, success) {
                $el.parent().find('.status-icon').remove();
                const icon = success ? '<i class="fas fa-check-circle status-icon status-success"></i>' : '<i class="fas fa-times-circle status-icon status-error"></i>';
                $el.after(icon);
                setTimeout(() => $el.parent().find('.status-icon').fadeOut(1000), 2000);
            }

            // SPIN Logic
            $(document).on('click', '.btn-spin-history', function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                $('#spin_nama_peserta').text(nama);
                $('#btnSaveSpinInteractions').data('id', id);

                $('#spinCardsContainer').html('<div class="p-4 w-100 text-center text-muted">Memuat data...</div>');
                $('#modalRiwayatSpin').modal('show');

                $.get(`/admin/database/${id}/spin-interactions`, function (data) {
                    let html = `
                        <div class="add-spin-card shadow-sm" id="addNewSpin">
                            <div class="text-center p-4">
                                <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                <div class="fw-bold text-primary">Tambah SPIN Baru</div>
                            </div>
                        </div>`;

                    const interactions = data.interactions || [];

                    interactions.forEach(spin => {
                        html += `
                                <div class="spin-card" data-spin-id="${spin.id}">
                                    <div class="spin-card-header">
                                        <span><i class="fas fa-calendar-alt me-1"></i> ${new Date(spin.created_at).toLocaleDateString()}</span>
                                        <span class="badge bg-white text-dark">ID: ${spin.id}</span>
                                    </div>
                                    <div class="p-3">
                                        <label class="small fw-bold">Hasil Interaksi</label>
                                        <textarea class="form-control spin-hasil mb-2" rows="3">${spin.hasil_fu || ''}</textarea>
                                        <label class="small fw-bold">Tindak Lanjut</label>
                                        <textarea class="form-control spin-tindak" rows="3">${spin.tindak_lanjut || ''}</textarea>
                                    </div>
                                </div>`;
                    });

                    $('#spinCardsContainer').html(html);
                });
            });

            $(document).on('click', '#addNewSpin', function () {
                const newCard = `
                        <div class="spin-card shadow-lg bg-light" data-spin-id="new">
                            <div class="spin-card-header bg-primary text-white">
                                <span><i class="fas fa-plus me-1"></i> SPIN BARU</span>
                                <span class="badge bg-white text-primary">NEW</span>
                            </div>
                            <div class="p-3">
                                <label class="small fw-bold text-primary">Hasil Interaksi</label>
                                <textarea class="form-control spin-hasil mb-2" rows="3" placeholder="Apa hasil interaksi hari ini?"></textarea>
                                <label class="small fw-bold text-primary">Tindak Lanjut</label>
                                <textarea class="form-control spin-tindak" rows="3" placeholder="Rencana langkah berikutnya?"></textarea>
                            </div>
                        </div>`;
                $(this).after(newCard);
                $('#spinCardsContainer').animate({ scrollLeft: 0 }, 500);
            });

            $('#btnSaveSpinInteractions').on('click', function () {
                const dataId = $(this).data('id');
                const spins = [];
                $('.spin-card').each(function () {
                    spins.push({
                        id: $(this).data('spin-id'),
                        hasil_fu: $(this).find('.spin-hasil').val(),
                        tindak_lanjut: $(this).find('.spin-tindak').val()
                    });
                });

                $.post(`/admin/database/${dataId}/save-spin-interactions`, {
                    _token: '{{ csrf_token() }}',
                    interactions: spins
                }, function () {
                    Swal.fire('Berhasil', 'Data SPIN disimpan', 'success');
                    $('#modalRiwayatSpin').modal('hide');
                });
            });

            // Move to SalesPlan Logic
            $(document).on('click', '.btn-move-salesplan', function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                const existing = $(this).data('existing-kelas') || [];

                $('#move_nama_peserta').text(nama);
                $('#moveSalesPlanForm').attr('action', `/data/${id}/pindah-ke-salesplan`);
                $('#moveSalesPlanForm input[type="checkbox"]').prop('checked', false);
                existing.forEach(kid => $(`#kelas_${kid}`).prop('checked', true));
                $('#moveSalesPlanModal').modal('show');
            });

            // Create Inline Draft Row
            $('#btnTambahInline').on('click', function () {
                const $btn = $(this);
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menambahkan...');

                $.ajax({
                    url: '/admin/database/create-draft',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function (response) {
                        if (response.success) {
                            // Prepend new row to table
                            $('#myTable tbody').prepend(response.html);

                            // Focus on the name input of the newly added row
                            const $newRow = $('#myTable tbody tr:first-child');
                            $newRow.addClass('bg-light transition-all');
                            $newRow.find('input[data-field="nama"]').focus();

                            // Visual cue
                            setTimeout(() => {
                                $newRow.removeClass('bg-light');
                            }, 2000);
                        } else {
                            Swal.fire('Error', response.message || 'Gagal membuat baris baru', 'error');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Error', 'Gagal membuat baris baru: ' + xhr.responseText, 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="fa-solid fa-plus"></i> Tambah');
                    }
                });
            });

            // Create Form (Legacy - Keep handle just in case but modal is gone)
            $('#createForm').on('submit', function (e) {
                e.preventDefault();
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function () {
                        Swal.fire('Berhasil', 'Data ditambahkan', 'success').then(() => location.reload());
                    },
                    error: function () {
                        Swal.fire('Gagal', 'Gagal menyimpan data', 'error');
                    }
                });
            });
        });
    </script>
@endpush