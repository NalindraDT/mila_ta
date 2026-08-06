@extends('layouts.app')

@section('page-title', 'Edit Barang Keluar')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Barang Keluar</h1>
    <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    Hanya jumlah, tanggal, dan keterangan yang bisa diubah. Barang tidak bisa diganti — kalau salah pilih barang, hapus transaksi ini dan input ulang.
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-arrow-circle-up mr-2 text-warning"></i>
        <h6 class="m-0 font-weight-bold text-warning">Form Edit Barang Keluar</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('barang-keluar.update', $barangKeluar->id_barang_keluar) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Barang</label>
                <input type="text" class="form-control" value="{{ $barangKeluar->barang->nama_barang ?? '-' }}"
                    readonly style="background-color: #f8f9fc;">
            </div>

            <div class="form-group">
                <label>Satuan</label>
                <input type="text" class="form-control" value="{{ $barangKeluar->barang->satuan->nama_satuan ?? '-' }}"
                    readonly style="background-color: #f8f9fc;">
            </div>

            <div class="form-group">
                <label for="jumlah_keluar">Jumlah Keluar</label>
                <input type="number"
                    class="form-control @error('jumlah_keluar') is-invalid @enderror"
                    id="jumlah_keluar" name="jumlah_keluar"
                    value="{{ old('jumlah_keluar', $barangKeluar->jumlah_keluar) }}"
                    min="1">
                @error('jumlah_keluar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="tanggal_keluar">Tanggal Keluar</label>
                <input type="date"
                    class="form-control @error('tanggal_keluar') is-invalid @enderror"
                    id="tanggal_keluar" name="tanggal_keluar"
                    max="{{ date('Y-m-d') }}"
                    value="{{ old('tanggal_keluar', \Carbon\Carbon::parse($barangKeluar->tanggal_keluar)->format('Y-m-d')) }}">
                @error('tanggal_keluar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan <small class="text-muted">(opsional)</small></label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="3">{{ old('keterangan', $barangKeluar->keterangan) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update
            </button>
            <a href="{{ route('barang-keluar.index') }}" class="btn btn-light ml-2">Batal</a>

        </form>
    </div>
</div>

@endsection