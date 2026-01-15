@extends('layouts.app')

@section('content')
<style>
    body {
        background: radial-gradient(circle at top right, #1e293b, #0f172a);
        min-height: 100vh;
        font-family: 'Nunito', sans-serif;
        color: white;
    }

    .login-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        height: 100vh;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 25px;
        box-shadow: 0px 20px 45px rgba(0, 0, 0, 0.4);
        overflow: hidden;
        width: 100%;
        max-width: 450px;
        animation: fadeInUp 0.8s ease;
    }

    .login-header {
        background: rgba(255, 255, 255, 0.05);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: white;
        text-align: center;
        padding: 40px 20px;
    }

    .login-header h2 {
        margin: 0;
        font-weight: 700;
    }

    .login-body {
        padding: 30px;
    }

    .form-control {
        border-radius: 50px;
        padding: 12px 20px;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.5);
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: #f97316;
        color: white;
        box-shadow: 0 0 10px rgba(249, 115, 22, 0.2);
    }

    .btn-login {
        background: linear-gradient(90deg, #f97316, #ea580c);
        border: none;
        border-radius: 50px;
        padding: 12px 25px;
        font-weight: bold;
        color: white;
        width: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(234, 88, 12, 0.4);
    }

    .btn-login:hover {
        background: linear-gradient(135deg, #5a054d, #b1022c);
        transform: translateY(-2px);
    }

    .login-footer {
        text-align: center;
        margin-top: 20px;
    }

    .login-footer a {
        color: #f97316;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .login-footer a:hover {
        color: #fb923c;
    }

    /* Tombol icon mata */
    .toggle-password {
        position: absolute;
        top: 50%;
        right: 15px;
        transform: translateY(-50%);
        cursor: pointer;
        color: #888;
        font-size: 1.1rem;
    }

    .toggle-password:hover {
        color: #e10338;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <img src="{{ asset('backend/blp_logo.png') }}" alt="Logo BLP" style="width: 80px; margin-bottom: 15px;">
            <h2>BLP PROPERTI</h2>
            <p style="opacity: 0.8;">Silakan Login Terlebih Dahulu</p>
        </div>

        <div class="login-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <input id="email" type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" placeholder="Email Address" required autofocus>
                    @error('email')
                        <span class="invalid-feedback d-block mt-1">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="mb-3 position-relative">
                    <input id="password" type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        name="password" placeholder="Password" required>

                    {{-- Tombol icon mata --}}
                    <span class="toggle-password" id="togglePassword">
                        <i class="fa fa-eye"></i>
                    </span>

                    @error('password')
                        <span class="invalid-feedback d-block mt-1">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!--{{-- Checkbox lihat password --}}-->
                <!--<div class="mb-3 form-check">-->
                <!--    <input type="checkbox" class="form-check-input" id="showPasswordCheckbox">-->
                <!--    <label class="form-check-label" for="showPasswordCheckbox">Lihat Password</label>-->
                <!--</div>-->

                {{-- Remember Me --}}
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">{{ __('Remember Me') }}</label>
                </div>

                {{-- Tombol Login --}}
                <button type="submit" class="btn btn-login">
                    {{ __('Login') }}
                </button>
            </form>

            <div class="login-footer">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">{{ __('Forgot Password?') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- FontAwesome --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/js/all.min.js"></script>

{{-- Script toggle password --}}
<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const showPasswordCheckbox = document.getElementById('showPasswordCheckbox');

    togglePassword.addEventListener('click', () => {
        const icon = togglePassword.querySelector('i');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Checkbox juga bisa menampilkan password
    showPasswordCheckbox.addEventListener('change', (e) => {
        passwordInput.type = e.target.checked ? 'text' : 'password';
    });
</script>

{{-- SweetAlert2 untuk pesan error --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('loginError'))
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: '{{ session('loginError') }}',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Coba Lagi'
    });
</script>
@endif
@endsection
