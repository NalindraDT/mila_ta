@extends('layouts.app')

@section('page-title', 'Tambah Lokasi')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Lokasi</h1>
    <a href="{{ route('lokasi.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<!-- Card Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-map-marker-alt mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Lokasi</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('lokasi.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="nama_lokasi">Nama Lokasi</label>
                <input type="text"
                    class="form-control @error('nama_lokasi') is-invalid @enderror"
                    id="nama_lokasi"
                    name="nama_lokasi"
                    value="{{ old('nama_lokasi') }}"
                    placeholder="Contoh: Deck A, Deck B">
                @error('nama_lokasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
            <a href="{{ route('lokasi.index') }}" class="btn btn-light ml-2">Batal</a>

        </form>
    </div>
</div>

@endsection