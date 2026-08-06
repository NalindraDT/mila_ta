@extends('layouts.app')

@section('page-title', 'Edit Barang Masuk')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Barang Masuk</h1>
    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    Hanya jumlah dan tanggal yang bisa diubah. Barang tidak bisa diganti — kalau salah pilih barang, hapus transaksi ini dan input ulang.
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-arrow-circle-down mr-2 text-success"></i>
        <h6 class="m-0 font-weight-bold text-success">Form Edit Barang Masuk</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('barang-masuk.update', $barangMasuk->id_barang_masuk) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Barang</label>
                <input type="text" class="form-control" value="{{ $barangMasuk->barang->nama_barang ?? '-' }}"
                    readonly style="background-color: #f8f9fc;">
            </div>

            <div class="form-group">
                <label>Satuan</label>
                <input type="text" class="form-control" value="{{ $barangMasuk->barang->satuan->nama_satuan ?? '-' }}"
                    readonly style="background-color: #f8f9fc;">
            </div>

            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" class="form-control" value="{{ $barangMasuk->rak->lokasi->nama_lokasi ?? '-' }}"
                    readonly style="background-color: #f8f9fc;">
            </div>

            <div class="form-group">
                <label>Rak</label>
                <input type="text" class="form-control" value="{{ $barangMasuk->rak->nama_rak ?? '-' }}"
                    readonly style="background-color: #f8f9fc;">
            </div>

            <div class="form-group">
                <label for="jumlah_masuk">Jumlah Masuk</label>
                <input type="number"
                    class="form-control @error('jumlah_masuk') is-invalid @enderror"
                    id="jumlah_masuk" name="jumlah_masuk"
                    value="{{ old('jumlah_masuk', $barangMasuk->jumlah_masuk) }}"
                    min="1">
                @error('jumlah_masuk')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="tanggal_masuk">Tanggal Masuk</label>
                <input type="date"
                    class="form-control @error('tanggal_masuk') is-invalid @enderror"
                    id="tanggal_masuk" name="tanggal_masuk"
                    max="{{ date('Y-m-d') }}"
                    value="{{ old('tanggal_masuk', \Carbon\Carbon::parse($barangMasuk->tanggal_masuk)->format('Y-m-d')) }}">
                @error('tanggal_masuk')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update
            </button>
            <a href="{{ route('barang-masuk.index') }}" class="btn btn-light ml-2">Batal</a>

        </form>
    </div>
</div>

@endsection