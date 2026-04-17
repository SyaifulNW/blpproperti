@extends('layouts.masteradmin')

@section('content')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Detail Monitoring KPR: <strong>{{ $kpr->nama }}</strong></h1>
            <a href="{{ route('admin.salesplan.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Prospek
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <form action="{{ route('admin.kpr.update', $kpr->id) }}" id="kprForm" method="POST">
            @csrf
            {{-- Top Info Bar --}}
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-uppercase">Nama Pembeli</label>
                            <input type="text" name="nama" class="form-control font-weight-bold" value="{{ $kpr->nama }}">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="small font-weight-bold text-uppercase">Tahap Posisi Saat Ini</label>
                            <select name="tahap_posisi" id="main_tahap_posisi"
                                class="form-control text-primary font-weight-bold">
                                @foreach(['Booking Fee', 'Berkas KPR', 'Pengajuan Bank', 'Appraisal', 'SP3K/Approval', 'Akad Kredit', 'Pencairan/Final'] as $t)
                                    <option value="{{ $t }}" {{ $kpr->tahap_posisi == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small font-weight-bold text-uppercase">Status Global</label>
                            <select name="status_global" class="form-control">
                                <option value="Ongoing" {{ $kpr->status_global == 'Ongoing' ? 'selected' : '' }}>Ongoing 🏃
                                </option>
                                <option value="Success" {{ $kpr->status_global == 'Success' ? 'selected' : '' }}>Success 🏆
                                </option>
                                <option value="Failed" {{ $kpr->status_global == 'Failed' ? 'selected' : '' }}>Failed ❌
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-save me-1"></i> SIMPAN SEMUA
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                {{-- Bagian Utama: Wizard View --}}
                <div class="col-lg-12">
                    <!-- Step Indicator -->
                    <div class="card shadow mb-4">
                        <div class="card-body p-0">
                            <div
                                class="wizard-steps d-flex align-items-center justify-content-between text-center overflow-auto py-3 px-4 bg-light border-bottom">
                                @php
                                    $steps = [
                                        1 => ['icon' => 'fa-money-bill-wave', 'label' => 'Booking', 'color' => '#36b9cc', 'target' => 'step1'],
                                        2 => ['icon' => 'fa-file-invoice', 'label' => 'Berkas', 'color' => '#858796', 'target' => 'step2'],
                                        3 => ['icon' => 'fa-university', 'label' => 'Bank', 'color' => '#4e73df', 'target' => 'step3'],
                                        4 => ['icon' => 'fa-search-dollar', 'label' => 'Appraisal', 'color' => '#f6c23e', 'target' => 'step4'],
                                        5 => ['icon' => 'fa-file-signature', 'label' => 'SP3K', 'color' => '#1cc88a', 'target' => 'step5'],
                                        6 => ['icon' => 'fa-handshake', 'label' => 'Akad', 'color' => '#5a5c69', 'target' => 'step6'],
                                        7 => ['icon' => 'fa-key', 'label' => 'Serah Terima', 'color' => '#212529', 'target' => 'step7'],
                                    ];
                                @endphp
                                @foreach($steps as $num => $s)
                                    <div class="step-item d-flex align-items-center px-2 {{ $num == 1 ? 'active' : '' }}"
                                        data-target="{{ $s['target'] }}" style="cursor: pointer;">
                                        <div class="text-center">
                                            <div class="step-icon mx-auto mb-1 d-flex align-items-center justify-content-center shadow-sm"
                                                style="width: 40px; height: 40px; border-radius: 50%; background-color: {{ $s['color'] }}; color: white; transition: 0.3s; opacity: 0.6;">
                                                <i class="fas {{ $s['icon'] }} fa-sm"></i>
                                            </div>
                                            <div class="small font-weight-bold text-muted"
                                                style="font-size: 10px; line-height: 1.1;">{{ $num }}. {{ $s['label'] }}</div>
                                        </div>
                                    </div>
                                    @if($num < 7)
                                        <div class="step-arrow px-2 text-muted opacity-50">
                                            <i class="fas fa-chevron-right"></i>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div id="wizardContent">
                        {{-- Step 1: Booking Fee --}}
                        <div class="wizard-step" id="step1">
                            <div class="card shadow mb-4 border-left-info">
                                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-money-bill-wave me-1"></i>
                                        1. Booking Fee (Tanda Jadi)</h6>
                                    <span class="badge badge-info">TAHAP 1</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="small font-weight-bold">Tanggal Bayar</label>
                                            <input type="date" name="bf_tanggal_bayar" class="form-control"
                                                value="{{ $kpr->bf_tanggal_bayar }}">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="small font-weight-bold">Nominal Booking</label>
                                            <input type="text" name="bf_nominal" class="form-control thousand-separator"
                                                value="{{ number_format($kpr->bf_nominal ?? 0, 0, ',', '.') }}">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="small font-weight-bold">Unit yang Dipilih</label>
                                            <input type="text" name="bf_unit" class="form-control"
                                                value="{{ $kpr->bf_unit }}" placeholder="Contoh: Blok A-01">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="small font-weight-bold text-danger">Deadline Lanjut DP</label>
                                            <input type="date" name="bf_deadline_dp" class="form-control border-danger"
                                                value="{{ $kpr->bf_deadline_dp }}">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="submit" class="btn btn-info"
                                            onclick="document.getElementById('main_tahap_posisi').value='Booking Fee'"><i
                                                class="fas fa-save me-1"></i> Simpan
                                            Progres Tahap 1</button>
                                        <button type="button" class="btn btn-primary btn-next" data-next="step2"
                                            data-tahap="Berkas KPR">Lanjut Ke Berkas <i
                                                class="fas fa-chevron-right ms-1"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Pengumpulan Berkas --}}
                        <div class="wizard-step d-none" id="step2">
                            <div class="card shadow mb-4 border-left-secondary">
                                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-secondary"><i class="fas fa-file-invoice me-1"></i>
                                        2. Pengumpulan Berkas KPR</h6>
                                    <span class="badge badge-secondary">TAHAP 2</span>
                                </div>
                                <div class="card-body">
                                    <h6 class="text-secondary font-weight-bold small mb-3"> Checklist Dokumen:</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 border-right">
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" name="berkas_ktp_kk" value="1"
                                                    class="custom-control-input" id="checkKtp" {{ $kpr->berkas_ktp_kk ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="checkKtp">KTP & Kartu
                                                    Keluarga</label>
                                            </div>
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" name="berkas_slip_gaji" value="1"
                                                    class="custom-control-input" id="checkSlip" {{ $kpr->berkas_slip_gaji ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="checkSlip">Slip Gaji / Lap.
                                                    Usaha</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" name="berkas_rek_koran" value="1"
                                                    class="custom-control-input" id="checkRek" {{ $kpr->berkas_rek_koran ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="checkRek">Rekening Koran (3-6
                                                    Bln)</label>
                                            </div>
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" name="berkas_npwp" value="1"
                                                    class="custom-control-input" id="checkNpwp" {{ $kpr->berkas_npwp ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="checkNpwp">NPWP</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Status Dokumen</label>
                                            <select name="berkas_status" class="form-control">
                                                <option value="Belum Lengkap" {{ $kpr->berkas_status == 'Belum Lengkap' ? 'selected' : '' }}>Belum Lengkap</option>
                                                <option value="Lengkap" {{ $kpr->berkas_status == 'Lengkap' ? 'selected' : '' }}>Lengkap</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Tanggal Submit (Lengkap)</label>
                                            <input type="date" name="berkas_tanggal_submit" class="form-control"
                                                value="{{ $kpr->berkas_tanggal_submit }}">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary btn-prev"
                                            data-prev="step1"><i class="fas fa-chevron-left me-1"></i> Kembali</button>
                                        <div>
                                            <button type="submit" class="btn btn-secondary mr-2"
                                                onclick="document.getElementById('main_tahap_posisi').value='Berkas KPR'"><i
                                                    class="fas fa-save me-1"></i> Simpan Progres</button>
                                            <button type="button" class="btn btn-primary btn-next" data-next="step3"
                                                data-tahap="Pengajuan Bank">Lanjut Ke Pengajuan Bank <i
                                                    class="fas fa-chevron-right ms-1"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Pengajuan Bank --}}
                        <div class="wizard-step d-none" id="step3">
                            <div class="card shadow mb-4 border-left-primary">
                                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-university me-1"></i> 3.
                                        Pengajuan ke Bank</h6>
                                    <span class="badge badge-primary">TAHAP 3</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="small font-weight-bold">Bank Tujuan</label>
                                            <input type="text" name="bank_tujuan" class="form-control"
                                                value="{{ $kpr->bank_tujuan }}" placeholder="Contoh: BTN, BRI, Mandiri">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="small font-weight-bold">Tanggal Pengajuan</label>
                                            <input type="date" name="bank_tanggal_pengajuan" class="form-control"
                                                value="{{ $kpr->bank_tanggal_pengajuan }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="small font-weight-bold">Status Pengajuan</label>
                                            <select name="bank_status" class="form-control">
                                                <option value="">- Pilih Status -</option>
                                                <option value="Proses" {{ $kpr->bank_status == 'Proses' ? 'selected' : '' }}>
                                                    Proses Analisa</option>
                                                <option value="Revisi" {{ $kpr->bank_status == 'Revisi' ? 'selected' : '' }}>
                                                    Revisi Data</option>
                                                <option value="Pending" {{ $kpr->bank_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary btn-prev"
                                            data-prev="step2"><i class="fas fa-chevron-left me-1"></i> Kembali</button>
                                        <div>
                                            <button type="submit" class="btn btn-primary mr-2"
                                                onclick="document.getElementById('main_tahap_posisi').value='Pengajuan Bank'"><i
                                                    class="fas fa-save me-1"></i> Simpan Progres</button>
                                            <button type="button" class="btn btn-primary btn-next" data-next="step4"
                                                data-tahap="Appraisal">Lanjut Ke Appraisal <i
                                                    class="fas fa-chevron-right ms-1"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 4: Appraisal --}}
                        <div class="wizard-step d-none" id="step4">
                            <div class="card shadow mb-4 border-left-warning">
                                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-search-dollar me-1"></i>
                                        4. Appraisal (Penilaian Rumah)</h6>
                                    <span class="badge badge-warning">TAHAP 4</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Tanggal Appraisal</label>
                                            <input type="date" name="appraisal_tanggal" class="form-control"
                                                value="{{ $kpr->appraisal_tanggal }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Hasil Nilai Appraisal</label>
                                            <input type="text" name="appraisal_hasil_nilai"
                                                class="form-control thousand-separator"
                                                value="{{ number_format($kpr->appraisal_hasil_nilai ?? 0, 0, ',', '.') }}"
                                                placeholder="Rp">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="small font-weight-bold">Catatan Bank (Appraisal)</label>
                                            <textarea name="appraisal_catatan" rows="2"
                                                class="form-control">{{ $kpr->appraisal_catatan }}</textarea>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary btn-prev"
                                            data-prev="step3"><i class="fas fa-chevron-left me-1"></i> Kembali</button>
                                        <div>
                                            <button type="submit" class="btn btn-warning mr-2"
                                                onclick="document.getElementById('main_tahap_posisi').value='Appraisal'"><i
                                                    class="fas fa-save me-1"></i> Simpan Progres</button>
                                            <button type="button" class="btn btn-primary btn-next" data-next="step5"
                                                data-tahap="SP3K/Approval">Lanjut Ke SP3K <i
                                                    class="fas fa-chevron-right ms-1"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 5: SP3K --}}
                        <div class="wizard-step d-none" id="step5">
                            <div class="card shadow mb-4 border-left-success">
                                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-file-signature me-1"></i>
                                        5. SP3K / Approval Kredit</h6>
                                    <span class="badge badge-success">TAHAP 5</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Status Approval</label>
                                            <select name="sp3k_status" class="form-control font-weight-bold">
                                                <option value="">- Pilih Status -</option>
                                                <option value="Approve" {{ $kpr->sp3k_status == 'Approve' ? 'selected' : '' }}
                                                    class="text-success">APPROVE ✅</option>
                                                <option value="Reject" {{ $kpr->sp3k_status == 'Reject' ? 'selected' : '' }}
                                                    class="text-danger">REJECT ❌</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Plafon yang Disetujui</label>
                                            <input type="text" name="sp3k_plafon"
                                                class="form-control border-success fw-bold text-success thousand-separator"
                                                value="{{ number_format($kpr->sp3k_plafon ?? 0, 0, ',', '.') }}"
                                                placeholder="Rp">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Tenor (Tahun)</label>
                                            <input type="number" name="sp3k_tenor" class="form-control"
                                                value="{{ $kpr->sp3k_tenor }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Estimasi Cicilan per Bulan</label>
                                            <input type="text" name="sp3k_cicilan" class="form-control thousand-separator"
                                                value="{{ number_format($kpr->sp3k_cicilan ?? 0, 0, ',', '.') }}"
                                                placeholder="Rp">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary btn-prev"
                                            data-prev="step4"><i class="fas fa-chevron-left me-1"></i> Kembali</button>
                                        <div>
                                            <button type="submit" class="btn btn-success mr-2"
                                                onclick="document.getElementById('main_tahap_posisi').value='SP3K/Approval'"><i
                                                    class="fas fa-save me-1"></i> Simpan Progres</button>
                                            <button type="button" class="btn btn-primary btn-next" data-next="step6"
                                                data-tahap="Akad Kredit">Lanjut Ke Akad Kredit <i
                                                    class="fas fa-chevron-right ms-1"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 6: Akad Kredit --}}
                        <div class="wizard-step d-none" id="step6">
                            <div class="card shadow mb-4 border-left-dark">
                                <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-handshake me-1"></i> 6. Akad
                                        Kredit</h6>
                                    <span class="badge badge-dark">TAHAP 6</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Tanggal Akad</label>
                                            <input type="date" name="akad_tanggal" class="form-control"
                                                value="{{ $kpr->akad_tanggal }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="small font-weight-bold">Notaris</label>
                                            <input type="text" name="akad_notaris" class="form-control"
                                                value="{{ $kpr->akad_notaris }}">
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-switch mb-2">
                                                <input type="checkbox" name="akad_dp_lunas" value="1"
                                                    class="custom-control-input" id="switchDp" {{ $kpr->akad_dp_lunas ? 'checked' : '' }}>
                                                <label class="custom-control-label fw-bold" for="switchDp">DP Lunas?</label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="custom-control custom-switch mb-2">
                                                <input type="checkbox" name="akad_dokumen_lengkap" value="1"
                                                    class="custom-control-input" id="switchDocs" {{ $kpr->akad_dokumen_lengkap ? 'checked' : '' }}>
                                                <label class="custom-control-label fw-bold" for="switchDocs">Dokumen
                                                    Lengkap?</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary btn-prev"
                                            data-prev="step5"><i class="fas fa-chevron-left me-1"></i> Kembali</button>
                                        <div>
                                            <button type="submit" class="btn btn-dark mr-2"
                                                onclick="document.getElementById('main_tahap_posisi').value='Akad Kredit'"><i
                                                    class="fas fa-save me-1"></i>
                                                Simpan Progres</button>
                                            <button type="button" class="btn btn-primary btn-next" data-next="step7"
                                                data-tahap="Pencairan/Final">Lanjut Ke Pencairan <i
                                                    class="fas fa-chevron-right ms-1"></i></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Step 7: Pencairan --}}
                        <div class="wizard-step d-none" id="step7">
                            <div class="card shadow mb-4 border-left-dark">
                                <div
                                    class="card-header py-3 bg-white d-flex justify-content-between align-items-center text-dark">
                                    <h6 class="m-0 font-weight-bold "><i class="fas fa-key me-1"></i> 7. Pencairan & Serah
                                        Terima</h6>
                                    <span class="badge badge-dark">TAHAP 7</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="small font-weight-bold">Tanggal Pencairan</label>
                                            <input type="date" name="serah_terima_pencairan" class="form-control"
                                                value="{{ $kpr->serah_terima_pencairan }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="small font-weight-bold">Status Unit</label>
                                            <input type="text" name="serah_terima_status_unit" class="form-control"
                                                value="{{ $kpr->serah_terima_status_unit }}"
                                                placeholder="Contoh: Selesai 100%, Finishing">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="small font-weight-bold">Tanggal Serah Terima Kunci</label>
                                            <input type="date" name="serah_terima_kunci" class="form-control"
                                                value="{{ $kpr->serah_terima_kunci }}">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <button type="button" class="btn btn-outline-secondary btn-prev"
                                            data-prev="step6"><i class="fas fa-chevron-left me-1"></i> Kembali</button>
                                        <button type="submit" class="btn btn-dark"
                                            onclick="document.getElementById('main_tahap_posisi').value='Pencairan/Final'"><i
                                                class="fas fa-save me-1"></i> Simpan
                                            Final</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        /* Wizard Step Indicators */
        .wizard-steps .step-item {
            transition: 0.3s;
            padding-bottom: 5px;
            border-bottom: 2px solid transparent;
        }

        .wizard-steps .step-item.active {
            border-bottom: 2px solid #4e73df;
        }

        .wizard-steps .step-item.active .step-icon {
            transform: scale(1.15);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            opacity: 1 !important;
        }

        .wizard-steps .step-item.active .small {
            color: #4e73df !important;
        }

        .step-arrow {
            font-size: 14px;
            position: relative;
            top: -10px;
        }

        .wizard-step {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .thousand-separator {
            font-weight: bold;
            color: #1cc88a;
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
        }

        .bg-gradient-secondary {
            background: linear-gradient(135deg, #858796 0%, #60616f 100%);
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .bg-gradient-warning {
            background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        }

        .bg-gradient-dark {
            background: linear-gradient(135deg, #5a5c69 0%, #373840 100%);
        }

        /* Custom Control & Shadow */
        .custom-control-label {
            cursor: pointer;
        }

        .card {
            border-radius: 12px !important;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        /* Darker Text for Better Contrast */
        body,
        .form-control,
        label,
        .wizard-steps .small,
        h1,
        h6 {
            color: #000 !important;
        }

        .text-muted {
            color: #333 !important;
            /* Darker than standard muted */
        }

        input::placeholder {
            color: #555 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Wizard Logic
            const steps = document.querySelectorAll('.wizard-step');
            const stepItems = document.querySelectorAll('.step-item');
            const btnNexts = document.querySelectorAll('.btn-next');
            const btnPrevs = document.querySelectorAll('.btn-prev');

            function showStep(stepId) {
                steps.forEach(s => s.classList.add('d-none'));
                const targetStep = document.getElementById(stepId);
                if (targetStep) targetStep.classList.remove('d-none');

                stepItems.forEach(item => {
                    const icon = item.querySelector('.step-icon');
                    if (item.getAttribute('data-target') === stepId) {
                        item.classList.add('active');
                        if (icon) icon.style.opacity = '1';
                    } else {
                        item.classList.remove('active');
                        if (icon) icon.style.opacity = '0.6';
                    }
                });

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            stepItems.forEach(item => {
                item.addEventListener('click', function () {
                    showStep(this.getAttribute('data-target'));
                });
            });

            btnNexts.forEach(btn => {
                btn.addEventListener('click', function () {
                    const nextStep = this.getAttribute('data-next');
                    const nextTahap = this.getAttribute('data-tahap');

                    // Update dropdown automatically when moving to next stage
                    if (nextTahap) {
                        document.getElementById('main_tahap_posisi').value = nextTahap;
                    }

                    showStep(nextStep);
                });
            });

            btnPrevs.forEach(btn => {
                btn.addEventListener('click', function () {
                    showStep(this.getAttribute('data-prev'));
                });
            });

            // Check for step in URL
            const urlParams = new URLSearchParams(window.location.search);
            const stepParam = urlParams.get('step');

            const mapping = {
                '1': 'step1', '2': 'step2', '3': 'step3', '4': 'step4',
                '5': 'step5', '6': 'step6', '7': 'step7',
                'Booking Fee': 'step1',
                'Berkas KPR': 'step2',
                'Pengajuan Bank': 'step3',
                'Appraisal': 'step4',
                'SP3K/Approval': 'step5',
                'Akad Kredit': 'step6',
                'Pencairan/Final': 'step7',
                'Pencairan & Serah Terima': 'step7'
            };

            if (stepParam && mapping[stepParam]) {
                showStep(mapping[stepParam]);
            } else {
                // Auto-select step based on current positioning
                const currentTahap = document.querySelector('select[name="tahap_posisi"]').value;
                if (mapping[currentTahap]) {
                    showStep(mapping[currentTahap]);
                }
            }

            // Thousand Separator Logic
            const separatorInputs = document.querySelectorAll('.thousand-separator');
            separatorInputs.forEach(input => {
                input.addEventListener('input', function (e) {
                    let value = this.value.replace(/\D/g, '');
                    if (value !== "") {
                        value = parseInt(value, 10).toLocaleString('id-ID');
                    }
                    this.value = value;
                });
            });

            const form = document.querySelector('form');
            form.addEventListener('submit', function () {
                separatorInputs.forEach(input => {
                    input.value = input.value.replace(/\D/g, '');
                });
            });
        });
    </script>
@endsection