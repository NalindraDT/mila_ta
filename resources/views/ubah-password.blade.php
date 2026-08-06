@extends('layouts.app')

@section('page-title', 'Ubah Password')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Ubah Password</h1>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-key mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Form Ubah Password</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('ubah-password.update') }}" method="POST" autocomplete="off">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="password_lama">Password Lama</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control @error('password_lama') is-invalid @enderror"
                        id="password_lama"
                        name="password_lama"
                        placeholder="Masukkan password lama">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="toggleLama">
                            <i class="fas fa-eye" id="eyeLama"></i>
                        </button>
                    </div>
                    @error('password_lama')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="password_baru">Password Baru</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control @error('password_baru') is-invalid @enderror"
                        id="password_baru"
                        name="password_baru"
                        placeholder="Minimal 8 karakter, huruf besar, huruf kecil, dan angka">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="toggleBaru">
                            <i class="fas fa-eye" id="eyeBaru"></i>
                        </button>
                    </div>
                    @error('password_baru')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control @error('konfirmasi_password') is-invalid @enderror"
                        id="konfirmasi_password"
                        name="konfirmasi_password"
                        placeholder="Ulangi password baru">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="toggleKonfirmasi">
                            <i class="fas fa-eye" id="eyeKonfirmasi"></i>
                        </button>
                    </div>
                    @error('konfirmasi_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>

        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function togglePassword(buttonId, inputId, iconId) {
        document.getElementById(buttonId).addEventListener('click', function() {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    togglePassword('toggleLama', 'password_lama', 'eyeLama');
    togglePassword('toggleBaru', 'password_baru', 'eyeBaru');
    togglePassword('toggleKonfirmasi', 'konfirmasi_password', 'eyeKonfirmasi');
</script>
@endsection