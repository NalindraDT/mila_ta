@extends('layouts.app')

@section('page-title', 'Edit Rak')

@section('content')

<!-- Page Heading -->
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Rak</h1>
    <a href="{{ route('rak.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<!-- Card Form -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-th-large mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Rak</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('rak.update', $rak->id_rak) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="nama_rak">Nama Rak</label>
                <input type="text"
                    class="form-control @error('nama_rak') is-invalid @enderror"
                    id="nama_rak"
                    name="nama_rak"
                    value="{{ old('nama_rak', $rak->nama_rak) }}"
                    placeholder="Contoh: Rak A, Rak B">
                @error('nama_rak')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="id_lokasi">Lokasi</label>
                <select class="form-control @error('id_lokasi') is-invalid @enderror"
                    id="id_lokasi"
                    name="id_lokasi">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach ($lokasi as $l)
                        <option value="{{ $l->id_lokasi }}"
                            {{ old('id_lokasi', $rak->id_lokasi) == $l->id_lokasi ? 'selected' : '' }}>
                            {{ $l->nama_lokasi }} @if($l->status == 'Nonaktif') (Nonaktif) @endif
                        </option>
                    @endforeach
                </select>
                @error('id_lokasi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control @error('status') is-invalid @enderror"
                    id="status" name="status">
                    <option value="Aktif" {{ old('status', $rak->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Nonaktif" {{ old('status', $rak->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <small class="text-muted d-block mt-1">Rak Nonaktif tidak akan muncul lagi di pilihan saat menambah Barang baru, tapi data lama yang sudah memakainya tetap aman.</small>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update
            </button>
            <a href="{{ route('rak.index') }}" class="btn btn-light ml-2">Batal</a>

        </form>
    </div>
</div>

@endsection