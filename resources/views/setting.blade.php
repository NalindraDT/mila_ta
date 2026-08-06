@extends('layouts.app')

@section('page-title', 'Setting Aplikasi')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Setting Aplikasi</h1>
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
        <i class="fas fa-cog mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Form Setting Aplikasi</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama_aplikasi">Nama Aplikasi</label>
                <input type="text"
                    class="form-control @error('nama_aplikasi') is-invalid @enderror"
                    id="nama_aplikasi"
                    name="nama_aplikasi"
                    value="{{ old('nama_aplikasi', $setting->nama_aplikasi) }}">
                @error('nama_aplikasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="nama_kapal">Nama Kapal</label>
                <input type="text"
                    class="form-control @error('nama_kapal') is-invalid @enderror"
                    id="nama_kapal"
                    name="nama_kapal"
                    value="{{ old('nama_kapal', $setting->nama_kapal) }}">
                @error('nama_kapal')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea
                    class="form-control @error('deskripsi') is-invalid @enderror"
                    id="deskripsi"
                    name="deskripsi"
                    rows="3">{{ old('deskripsi', $setting->deskripsi) }}</textarea>
                @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="stok_minimum">Batas Minimum Stok</label>
                <input type="number"
                    class="form-control @error('stok_minimum') is-invalid @enderror"
                    id="stok_minimum"
                    name="stok_minimum"
                    value="{{ old('stok_minimum', $setting->stok_minimum) }}"
                    min="1">
                <small class="text-muted">Barang dengan stok di bawah angka ini akan muncul sebagai peringatan di dashboard.</small>
                @error('stok_minimum')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="logo">Logo Aplikasi</label>
                @if($setting->logo)
                    <div class="mb-2">
                        <img src="{{ asset('uploads/' . $setting->logo) }}"
                            alt="Logo" style="height: 80px;">
                        <p class="text-muted small mt-1">Logo saat ini</p>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="hapus_logo" id="hapus_logo" value="1">
                        <label class="form-check-label text-danger" for="hapus_logo">
                            <i class="fas fa-trash mr-1"></i> Hapus Logo
                        </label>
                    </div>
                @endif
                <input type="file"
                    class="form-control-file @error('logo') is-invalid @enderror"
                    id="logo"
                    name="logo"
                    accept="image/*">
                <small class="text-muted">Format: jpg, jpeg, png. Maksimal 2MB.</small>
                @error('logo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>

        </form>
    </div>
</div>

@endsection