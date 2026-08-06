@extends('layouts.app')

@section('page-title', 'Laporan Barang Keluar')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan Barang Keluar</h1>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-filter mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('laporan-barang-keluar.generate') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select class="form-control @error('kategori') is-invalid @enderror"
                            id="kategori" name="kategori">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Consumable" {{ old('kategori', isset($request) ? $request->kategori : '') == 'Consumable' ? 'selected' : '' }}>Consumable</option>
                            <option value="Suku Cadang" {{ old('kategori', isset($request) ? $request->kategori : '') == 'Suku Cadang' ? 'selected' : '' }}>Suku Cadang</option>
                            <option value="semua" {{ old('kategori', isset($request) ? $request->kategori : '') == 'semua' ? 'selected' : '' }}>Semua</option>
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_dari">Tanggal Dari</label>
                        <input type="date"
                            class="form-control @error('tanggal_dari') is-invalid @enderror"
                            id="tanggal_dari" name="tanggal_dari"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_dari', isset($request) ? $request->tanggal_dari : '') }}">
                        @error('tanggal_dari')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_sampai">Tanggal Sampai</label>
                        <input type="date"
                            class="form-control @error('tanggal_sampai') is-invalid @enderror"
                            id="tanggal_sampai" name="tanggal_sampai"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_sampai', isset($request) ? $request->tanggal_sampai : '') }}">
                        @error('tanggal_sampai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i> Tampilkan Laporan
            </button>
        </form>
    </div>
</div>

@isset($items)
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="fas fa-file-alt mr-2 text-primary"></i>
            <h6 class="m-0 font-weight-bold text-primary">{{ $judul }}</h6>
        </div>
        <div>
            <form action="{{ route('laporan-barang-keluar.download-word') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="kategori" value="{{ $request->kategori }}">
                <input type="hidden" name="tanggal_dari" value="{{ $request->tanggal_dari }}">
                <input type="hidden" name="tanggal_sampai" value="{{ $request->tanggal_sampai }}">
                <button type="submit" class="btn btn-primary btn-sm mr-1">
                    <i class="fas fa-file-word mr-1"></i> Download Word
                </button>
            </form>
            <form action="{{ route('laporan-barang-keluar.download-excel') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="kategori" value="{{ $request->kategori }}">
                <input type="hidden" name="tanggal_dari" value="{{ $request->tanggal_dari }}">
                <input type="hidden" name="tanggal_sampai" value="{{ $request->tanggal_sampai }}">
                <button type="submit" class="btn btn-success btn-sm">
                    <i class="fas fa-file-excel mr-1"></i> Download Excel
                </button>
            </form>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="50px" class="text-center">No</th>
                        <th class="text-center">Tanggal</th>
                        <th>Nama Barang</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-center">Jumlah Keluar</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_keluar)->format('d/m/Y') }}</td>
                        <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $item->barang->satuan->nama_satuan ?? '-' }}</td>
                        <td class="text-center">{{ $item->jumlah_keluar }}</td>
                        <td>{{ $item->keterangan ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada data barang keluar untuk filter ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endisset

@endsection