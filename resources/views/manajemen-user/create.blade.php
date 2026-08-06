@extends('layouts.app')

@section('page-title', 'Tambah User')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah User</h1>
    <a href="{{ route('manajemen-user.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-users mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah User</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('manajemen-user.store') }}" method="POST" autocomplete="off">
            @csrf

            <div class="form-group">
                <label for="nama_user">Nama User</label>
                <input type="text"
                    class="form-control @error('nama_user') is-invalid @enderror"
                    id="nama_user"
                    name="nama_user"
                    value="{{ old('nama_user') }}"
                    placeholder="Contoh: Administrator">
                @error('nama_user')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text"
                    class="form-control @error('username') is-invalid @enderror"
                    id="username"
                    name="username"
                    value="{{ old('username') }}"
                    autocomplete="new-password"
                    placeholder="Minimal 5 karakter, huruf/angka/underscore">
                @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter, huruf besar, huruf kecil, dan angka">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control @error('role') is-invalid @enderror"
                    id="role"
                    name="role">
                    <option value="Kepala Gudang" {{ old('role') == 'Kepala Gudang' ? 'selected' : '' }}>Kepala Gudang</option>
                    <option value="Admin Gudang" {{ old('role') == 'Admin Gudang' ? 'selected' : '' }}>Admin Gudang</option>
                    <option value="Staff" {{ old('role') == 'Staff' ? 'selected' : '' }}>Staff</option>
                </select>
                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
            <a href="{{ route('manajemen-user.index') }}" class="btn btn-light ml-2">Batal</a>

        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const password = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        if (password.type === 'password') {
            password.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    });
</script>
@endsection