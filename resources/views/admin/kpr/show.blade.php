@extends('layouts.masteradmin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Monitoring KPR: <strong>{{ $kpr->nama }}</strong></h1>
        <a href="{{ route('admin.kpr.index') }}" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Kembali ke Daftar KPR
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

    <form action="{{ route('admin.kpr.update', $kpr->id) }}" method="POST">
        @csrf
        <div class="row">
            {{-- Bagian Kiri: Ringkasan & Monitoring Global --}}
            <div class="col-lg-4">
                <div class="card shadow mb-4 border-left-primary">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle me-1"></i> Informasi Pembeli</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="small font-weight-bold text-dark text-uppercase">Nama</label>
                            <input type="text" name="nama" class="form-control" value="{{ $kpr->nama }}">
                        </div>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-dark text-uppercase">No HP (WhatsApp)</label>
                            <input type="text" name="phone" class="form-control" value="{{ $kpr->phone }}">
                        </div>
                        <hr>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-dark text-uppercase">Tahap Posisi Saat Ini</label>
                            <select name="tahap_posisi" class="form-control text-primary font-weight-bold">
                                @foreach(['Booking Fee', 'Berkas KPR', 'Pengajuan Bank', 'Appraisal', 'SP3K/Approval', 'Akad Kredit', 'Pencairan/Final'] as $t)
                                    <option value="{{ $t }}" {{ $kpr->tahap_posisi == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-dark text-uppercase">Status Global</label>
                            <select name="status_global" class="form-control">
                                <option value="Ongoing" {{ $kpr->status_global == 'Ongoing' ? 'selected' : '' }}>Ongoing 🏃</option>
                                <option value="Success" {{ $kpr->status_global == 'Success' ? 'selected' : '' }}>Success 🏆</option>
                                <option value="Failed" {{ $kpr->status_global == 'Failed' ? 'selected' : '' }}>Failed ❌</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-warning text-uppercase">Next Action</label>
                            <input type="text" name="next_action" class="form-control border-warning bg-light" value="{{ $kpr->next_action }}" placeholder="Apa langkah selanjutnya?">
                        </div>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-dark text-uppercase">Catatan Umum</label>
                            <textarea name="catatan_umum" rows="4" class="form-control">{{ $kpr->catatan_umum }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block shadow-sm">
                            <i class="fas fa-save me-1"></i> SIMPAN SEMUA PERUBAHAN
                        </button>
                    </div>
                </div>
            </div>

            {{-- Bagian Kanan: Detail Tahapan KPR --}}
            <div class="col-lg-8">
                <div class="accordion" id="accordionKPR">
                    
                    {{-- Section 4: Booking Fee --}}
                    <div class="card shadow-sm mb-2 border-0 overflow-hidden rounded-lg">
                        <div class="card-header bg-gradient-info p-0" id="headingBF">
                          <button class="btn btn-link btn-block text-left text-white font-weight-bold p-3 text-decoration-none d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapseBF">
                            <span><i class="fas fa-money-bill-wave me-2"></i> 4. Booking Fee (Tanda Jadi)</span>
                            <i class="fas fa-chevron-down"></i>
                          </button>
                        </div>
                        <div id="collapseBF" class="collapse show" data-parent="#accordionKPR">
                          <div class="card-body bg-white border">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Tanggal Bayar</label>
                                    <input type="date" name="bf_tanggal_bayar" class="form-control" value="{{ $kpr->bf_tanggal_bayar }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Nominal Booking</label>
                                    <input type="text" name="bf_nominal" class="form-control thousand-separator" value="{{ number_format($kpr->bf_nominal ?? 0, 0, ',', '.') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Unit yang Dipilih</label>
                                    <input type="text" name="bf_unit" class="form-control" value="{{ $kpr->bf_unit }}" placeholder="Contoh: Blok A-01">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold text-danger">Deadline Lanjut DP</label>
                                    <input type="date" name="bf_deadline_dp" class="form-control border-danger" value="{{ $kpr->bf_deadline_dp }}">
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    {{-- Section 5: Pengumpulan Berkas --}}
                    <div class="card shadow-sm mb-2 border-0 overflow-hidden rounded-lg">
                        <div class="card-header bg-gradient-secondary p-0" id="headingBerkas">
                          <button class="btn btn-link btn-block text-left text-white font-weight-bold p-3 text-decoration-none collapsed d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapseBerkas">
                            <span><i class="fas fa-file-invoice me-2"></i> 5. Pengumpulan Berkas KPR</span>
                            <i class="fas fa-chevron-down"></i>
                          </button>
                        </div>
                        <div id="collapseBerkas" class="collapse" data-parent="#accordionKPR">
                          <div class="card-body bg-white border">
                            <h6 class="text-secondary font-weight-bold small mb-3"> Checklist Dokumen:</h6>
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" name="berkas_ktp_kk" value="1" class="custom-control-input" id="checkKtp" {{ $kpr->berkas_ktp_kk ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="checkKtp">KTP & Kartu Keluarga</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" name="berkas_slip_gaji" value="1" class="custom-control-input" id="checkSlip" {{ $kpr->berkas_slip_gaji ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="checkSlip">Slip Gaji / Lap. Usaha</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" name="berkas_rek_koran" value="1" class="custom-control-input" id="checkRek" {{ $kpr->berkas_rek_koran ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="checkRek">Rekening Koran (3-6 Bln)</label>
                                    </div>
                                    <div class="custom-control custom-checkbox mb-2">
                                        <input type="checkbox" name="berkas_npwp" value="1" class="custom-control-input" id="checkNpwp" {{ $kpr->berkas_npwp ? 'checked' : '' }}>
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
                                    <input type="date" name="berkas_tanggal_submit" class="form-control" value="{{ $kpr->berkas_tanggal_submit }}">
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    {{-- Section 6: Pengajuan Bank --}}
                    <div class="card shadow-sm mb-2 border-0 overflow-hidden rounded-lg">
                        <div class="card-header bg-gradient-primary p-0" id="headingBank">
                          <button class="btn btn-link btn-block text-left text-white font-weight-bold p-3 text-decoration-none collapsed d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapseBank">
                            <span><i class="fas fa-university me-2"></i> 6. Pengajuan ke Bank</span>
                            <i class="fas fa-chevron-down"></i>
                          </button>
                        </div>
                        <div id="collapseBank" class="collapse" data-parent="#accordionKPR">
                          <div class="card-body bg-white border text-dark">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="small font-weight-bold">Bank Tujuan</label>
                                    <input type="text" name="bank_tujuan" class="form-control" value="{{ $kpr->bank_tujuan }}" placeholder="Contoh: BTN, BRI, Mandiri">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small font-weight-bold">Tanggal Pengajuan</label>
                                    <input type="date" name="bank_tanggal_pengajuan" class="form-control" value="{{ $kpr->bank_tanggal_pengajuan }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small font-weight-bold">Status Pengajuan</label>
                                    <select name="bank_status" class="form-control">
                                        <option value="">- Pilih Status -</option>
                                        <option value="Proses" {{ $kpr->bank_status == 'Proses' ? 'selected' : '' }}>Proses Analisa</option>
                                        <option value="Revisi" {{ $kpr->bank_status == 'Revisi' ? 'selected' : '' }}>Revisi Data</option>
                                        <option value="Pending" {{ $kpr->bank_status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                    </select>
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    {{-- Section 7: Appraisal --}}
                    <div class="card shadow-sm mb-2 border-0 overflow-hidden rounded-lg">
                        <div class="card-header bg-gradient-warning p-0" id="headingAppraisal">
                          <button class="btn btn-link btn-block text-left text-dark font-weight-bold p-3 text-decoration-none collapsed d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapseAppraisal">
                            <span><i class="fas fa-search-dollar me-2"></i> 7. Appraisal (Penilaian Rumah)</span>
                            <i class="fas fa-chevron-down"></i>
                          </button>
                        </div>
                        <div id="collapseAppraisal" class="collapse" data-parent="#accordionKPR">
                          <div class="card-body bg-white border">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Tanggal Appraisal</label>
                                    <input type="date" name="appraisal_tanggal" class="form-control" value="{{ $kpr->appraisal_tanggal }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Hasil Nilai Appraisal</label>
                                    <input type="text" name="appraisal_hasil_nilai" class="form-control thousand-separator" value="{{ number_format($kpr->appraisal_hasil_nilai ?? 0, 0, ',', '.') }}" placeholder="Rp">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="small font-weight-bold">Catatan Bank (Appraisal)</label>
                                    <textarea name="appraisal_catatan" rows="2" class="form-control">{{ $kpr->appraisal_catatan }}</textarea>
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    {{-- Section 8: SP3K --}}
                    <div class="card shadow-sm mb-2 border-0 overflow-hidden rounded-lg">
                        <div class="card-header bg-gradient-success p-0" id="headingSP3K">
                          <button class="btn btn-link btn-block text-left text-white font-weight-bold p-3 text-decoration-none collapsed d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapseSP3K">
                            <span><i class="fas fa-file-signature me-2"></i> 8. SP3K / Approval Kredit</span>
                            <i class="fas fa-chevron-down"></i>
                          </button>
                        </div>
                        <div id="collapseSP3K" class="collapse" data-parent="#accordionKPR">
                          <div class="card-body bg-white border text-dark">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Status Approval</label>
                                    <select name="sp3k_status" class="form-control font-weight-bold">
                                        <option value="">- Pilih Status -</option>
                                        <option value="Approve" {{ $kpr->sp3k_status == 'Approve' ? 'selected' : '' }} class="text-success">APPROVE ✅</option>
                                        <option value="Reject" {{ $kpr->sp3k_status == 'Reject' ? 'selected' : '' }} class="text-danger">REJECT ❌</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Plafon yang Disetujui</label>
                                    <input type="text" name="sp3k_plafon" class="form-control border-success fw-bold text-success thousand-separator" value="{{ number_format($kpr->sp3k_plafon ?? 0, 0, ',', '.') }}" placeholder="Rp">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Tenor (Tahun)</label>
                                    <input type="number" name="sp3k_tenor" class="form-control" value="{{ $kpr->sp3k_tenor }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Estimasi Cicilan per Bulan</label>
                                    <input type="text" name="sp3k_cicilan" class="form-control thousand-separator" value="{{ number_format($kpr->sp3k_cicilan ?? 0, 0, ',', '.') }}" placeholder="Rp">
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    {{-- Section 9: Akad Kredit --}}
                    <div class="card shadow-sm mb-2 border-0 overflow-hidden rounded-lg">
                        <div class="card-header bg-gradient-dark p-0" id="headingAkad">
                          <button class="btn btn-link btn-block text-left text-white font-weight-bold p-3 text-decoration-none collapsed d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapseAkad">
                            <span><i class="fas fa-handshake me-2"></i> 9. Akad Kredit</span>
                            <i class="fas fa-chevron-down"></i>
                          </button>
                        </div>
                        <div id="collapseAkad" class="collapse" data-parent="#accordionKPR">
                          <div class="card-body bg-white border">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Tanggal Akad</label>
                                    <input type="date" name="akad_tanggal" class="form-control" value="{{ $kpr->akad_tanggal }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="small font-weight-bold">Notaris</label>
                                    <input type="text" name="akad_notaris" class="form-control" value="{{ $kpr->akad_notaris }}">
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" name="akad_dp_lunas" value="1" class="custom-control-input" id="switchDp" {{ $kpr->akad_dp_lunas ? 'checked' : '' }}>
                                        <label class="custom-control-label fw-bold" for="switchDp">DP Lunas?</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" name="akad_dokumen_lengkap" value="1" class="custom-control-input" id="switchDocs" {{ $kpr->akad_dokumen_lengkap ? 'checked' : '' }}>
                                        <label class="custom-control-label fw-bold" for="switchDocs">Dokumen Lengkap?</label>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>

                    {{-- Section 10: Pencairan --}}
                    <div class="card shadow-sm mb-2 border-0 overflow-hidden rounded-lg">
                        <div class="card-header bg-dark p-0" id="headingFinal">
                          <button class="btn btn-link btn-block text-left text-warning font-weight-bold p-3 text-decoration-none collapsed d-flex justify-content-between align-items-center" type="button" data-toggle="collapse" data-target="#collapseFinal">
                            <span><i class="fas fa-key me-2"></i> 10. Pencairan & Serah Terima</span>
                            <i class="fas fa-chevron-down"></i>
                          </button>
                        </div>
                        <div id="collapseFinal" class="collapse" data-parent="#accordionKPR">
                          <div class="card-body bg-white border">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="small font-weight-bold">Tanggal Pencairan</label>
                                    <input type="date" name="serah_terima_pencairan" class="form-control" value="{{ $kpr->serah_terima_pencairan }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small font-weight-bold">Status Unit</label>
                                    <input type="text" name="serah_terima_status_unit" class="form-control" value="{{ $kpr->serah_terima_status_unit }}" placeholder="Contoh: Selesai 100%, Finishing">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="small font-weight-bold">Tanggal Serah Terima Kunci</label>
                                    <input type="date" name="serah_terima_kunci" class="form-control" value="{{ $kpr->serah_terima_kunci }}">
                                </div>
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
    .bg-gradient-info { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); }
    .bg-gradient-secondary { background: linear-gradient(135deg, #858796 0%, #60616f 100%); }
    .bg-gradient-primary { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .bg-gradient-warning { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }
    .bg-gradient-success { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .bg-gradient-dark { background: linear-gradient(135deg, #5a5c69 0%, #373840 100%); }
    
    .card-header .btn-link:hover { color: #f1f1f1 !important; }
    .custom-control-label { cursor: pointer; }
    .accordion .card { border-radius: 8px !important; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const separatorInputs = document.querySelectorAll('.thousand-separator');

        separatorInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                // Hapus semua karakter kecuali angka
                let value = this.value.replace(/\D/g, '');
                
                // Format dengan titik setiap 3 digit
                if (value !== "") {
                    value = parseInt(value, 10).toLocaleString('id-ID');
                }
                
                this.value = value;
            });
        });

        // Bersihkan titik sebelum form dikirim
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            separatorInputs.forEach(input => {
                input.value = input.value.replace(/\D/g, '');
            });
        });
    });
</script>
@endsection
