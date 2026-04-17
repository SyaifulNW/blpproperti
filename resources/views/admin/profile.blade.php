@extends('layouts.masteradmin')

@section('content')
<div class="row">
    <div class="col-lg-4">
        {{-- Profile Card (Left) --}}
        <div class="card shadow-lg mb-4 border-0" style="border-radius: 20px; overflow: hidden;">
            <div class="profile-card-header" style="background: linear-gradient(135deg, #4b0022 0%, #aa0044 100%); height: 120px; position: relative;">
            </div>
            <div class="card-body text-center" style="margin-top: -60px; position: relative; z-index: 1;">
                <div class="position-relative d-inline-block shadow-sm rounded-circle">
                    <img class="rounded-circle border border-white border-5 profile-img-preview"
                        src="{{ $user->photo ? asset('uploads/profiles/' . $user->photo) : asset('backend/img/undraw_profile.svg') }}" 
                        alt="Profile Image" 
                        style="width: 130px; height: 130px; background: #eee; object-fit: cover; cursor: pointer;">
                    <div class="position-absolute" style="bottom: 5px; right: 0;">
                        <button type="button" class="btn btn-danger btn-sm rounded-circle d-flex align-items-center justify-content-center btn-upload-trigger" 
                            style="width: 30px; height: 30px; border: 2px solid white;">
                            <i class="fas fa-camera fa-xs"></i>
                        </button>
                    </div>
                </div>
                
                <h4 class="mt-3 fw-bold text-dark mb-1">{{ $user->name }}</h4>
                <p class="text-danger small fw-bold text-uppercase mb-4">{{ $user->role }}</p>

                <div class="text-start px-3">
                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Email</small>
                        <span class="text-dark fw-bold">{{ $user->email }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">WhatsApp</small>
                        <span class="text-dark fw-bold">{{ $user->wa ?? '-' }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted fw-bold d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 1px;">Biodata</small>
                        <p class="text-dark small mb-0">{{ $user->biodata ?? 'Belum ada biodata.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        {{-- Edit Card (Right) --}}
        <div class="card shadow-lg mb-4 border-0" style="border-radius: 20px;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                <i class="fas fa-user-edit text-primary mr-2"></i>
                <h6 class="m-0 font-weight-bold text-dark">Lengkapi Profile & Ganti Password</h6>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" style="border-radius: 10px;">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show" style="border-radius: 10px;">
                        <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif

                <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="photo" id="photoInput" class="d-none" accept="image/*">

                    <h6 class="text-primary fw-bold text-uppercase small mb-3"><i class="fas fa-info-circle mr-1"></i> Informasi Umum</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">EMAIL (TETAP)</label>
                            <input type="email" class="form-control bg-light border-0" value="{{ $user->email }}" readonly disabled style="border-radius: 10px; font-weight: 600;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">NO. WHATSAPP</label>
                            <input type="text" name="wa" class="form-control border shadow-sm" value="{{ $user->wa }}" style="border-radius: 10px; font-weight: 600;" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="small fw-bold text-muted">BIODATA</label>
                        <textarea name="biodata" class="form-control border shadow-sm" rows="3" style="border-radius: 10px; font-weight: 600;" placeholder="Tuliskan biodata singkat Anda...">{{ $user->biodata }}</textarea>
                    </div>

                    <div class="mt-4 mb-4">
                        <div class="d-flex align-items-center">
                            <h6 class="text-danger fw-bold text-uppercase small mb-0 mr-2"><i class="fas fa-shield-alt mr-1"></i> Ganti Password</h6>
                            <div style="flex-grow: 1; height: 1px; background: #eee;"></div>
                        </div>
                        <p class="text-muted mb-3" style="font-size: 0.75rem;">Kosongkan jika tidak ingin merubah password.</p>
                    </div>

                    <div class="form-group mb-3">
                        <label class="small fw-bold text-muted">PASSWORD SAAT INI <span class="text-danger" style="font-size: 0.65rem;">(WAJIB UNTUK GANTI PASSWORD)</span></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control border shadow-sm" style="border-radius: 10px 0 0 10px;">
                            <div class="input-group-append">
                                <span class="input-group-text bg-white border shadow-sm toggle-password" style="border-radius: 0 10px 10px 0; cursor: pointer;">
                                    <i class="fas fa-eye text-muted"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">PASSWORD BARU</label>
                            <div class="input-group">
                                <input type="password" name="new_password" class="form-control border shadow-sm" style="border-radius: 10px 0 0 10px;">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white border shadow-sm toggle-password" style="border-radius: 0 10px 10px 0; cursor: pointer;">
                                        <i class="fas fa-eye text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small fw-bold text-muted">KONFIRMASI PASSWORD BARU</label>
                            <div class="input-group">
                                <input type="password" name="new_password_confirmation" class="form-control border shadow-sm" style="border-radius: 10px 0 0 10px;">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white border shadow-sm toggle-password" style="border-radius: 0 10px 10px 0; cursor: pointer;">
                                        <i class="fas fa-eye text-muted"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn text-white px-5 py-2 fw-bold shadow-lg" style="background: linear-gradient(135deg, #4b0022 0%, #aa0044 100%); border-radius: 12px;">
                            <i class="fas fa-id-card-alt mr-1"></i> SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .fw-bold { font-weight: 700 !important; }
    .profile-img-preview:hover { opacity: 0.9; transform: scale(1.02); transition: 0.3s; }
    .form-control:focus { border-color: #aa0044; box-shadow: 0 0 0 0.2rem rgba(170, 0, 68, 0.1); }
</style>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.btn-upload-trigger, .profile-img-preview').click(function() {
            $('#photoInput').click();
        });

        $('#photoInput').change(function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $('.profile-img-preview').attr('src', event.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Toggle Password Visibility
        $('.toggle-password').mousedown(function() {
            let input = $(this).closest('.input-group').find('input');
            input.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        }).mouseup(function() {
            let input = $(this).closest('.input-group').find('input');
            input.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }).mouseleave(function() {
            let input = $(this).closest('.input-group').find('input');
            input.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        });
    });
</script>
@endpush
@endsection
