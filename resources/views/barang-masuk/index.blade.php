@extends('layouts.app')

@section('page-title', 'Barang Masuk')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Barang Masuk</h1>
    @if(auth()->user()->role == 'Administrator')
    <div>
        <a href="{{ route('barang-masuk.foto.index') }}" class="btn btn-info btn-sm shadow-sm">
            <i class="fas fa-camera fa-sm mr-1"></i> Upload Foto Bukti
        </a>
        <a href="{{ route('barang-masuk.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Barang Masuk
        </a>
    </div>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-arrow-circle-down mr-2 text-success"></i>
        <h6 class="m-0 font-weight-bold text-success">Daftar Barang Masuk</h6>
    </div>
    @if(auth()->user()->role == 'Administrator')
    <div class="mx-3 mt-3">
        <div class="alert alert-warning py-2 mb-0">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            <small>Edit/Hapus hanya bisa dilakukan pada transaksi terbaru untuk barang tersebut.</small>
        </div>
    </div>
    @endif
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="60px">No</th>
                        <th>Tanggal</th>
                        <th>ID Barang</th>
                        <th>Nama Barang</th>
                        <th>Satuan</th>
                        <th>Lokasi</th>
                        <th>Rak</th>
                        <th>Jumlah Masuk</th>
                        @if(auth()->user()->role == 'Administrator')
                        <th width="140px" class="text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangMasuk as $bm)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($bm->tanggal_masuk)->format('d/m/Y') }}</td>
                        <td><span class="badge badge-secondary">{{ $bm->id_barang }}</span></td>
                        <td>{{ $bm->barang->nama_barang ?? '-' }}</td>
                        <td>{{ $bm->barang->satuan->nama_satuan ?? '-' }}</td>
                        <td>{{ $bm->rak->lokasi->nama_lokasi ?? '-' }}</td>
                        <td>{{ $bm->rak->nama_rak ?? '-' }}</td>
                        <td><span class="badge badge-success">{{ $bm->jumlah_masuk }}</span></td>
                        @if(auth()->user()->role == 'Administrator')
                        <td class="text-center" style="white-space: nowrap;">
                            @if($bm->boleh_edit)
                            <a href="{{ route('barang-masuk.edit', $bm->id_barang_masuk) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            @endif
                            @if($bm->boleh_hapus)
                            <form action="{{ route('barang-masuk.destroy', $bm->id_barang_masuk) }}"
                                method="POST" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                    onclick="return confirm('Yakin mau hapus? Stok barang akan dikurangi!')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                            @endif
                            @if(!$bm->boleh_edit && !$bm->boleh_hapus)
                            <span class="text-muted small">Terkunci</span>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Belum ada data barang masuk.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection