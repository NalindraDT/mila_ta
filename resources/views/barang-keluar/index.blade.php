@extends('layouts.app')

@section('page-title', 'Barang Keluar')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Barang Keluar</h1>
    @if(auth()->user()->role == 'Administrator')
    <a href="{{ route('barang-keluar.create') }}" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Barang Keluar
    </a>
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
        <i class="fas fa-arrow-circle-up mr-2 text-warning"></i>
        <h6 class="m-0 font-weight-bold text-warning">Daftar Barang Keluar</h6>
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
                        <th>Jumlah Keluar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangKeluar as $bk)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse($bk->tanggal_keluar)->format('d/m/Y') }}</td>
                        <td><span class="badge badge-secondary">{{ $bk->id_barang }}</span></td>
                        <td>{{ $bk->barang->nama_barang ?? '-' }}</td>
                        <td>{{ $bk->barang->satuan->nama_satuan ?? '-' }}</td>
                        <td>{{ $bk->barang->rak->lokasi->nama_lokasi ?? '-' }}</td>
                        <td>{{ $bk->barang->rak->nama_rak ?? '-' }}</td>
                        <td><span class="badge badge-warning">{{ $bk->jumlah_keluar }}</span></td>
                        <td class="text-center" style="white-space: nowrap;">
                            <button type="button" class="btn btn-info btn-sm"
                                onclick="showDetail(
                                    '{{ $bk->id_barang }}',
                                    '{{ $bk->barang->nama_barang ?? "-" }}',
                                    '{{ $bk->barang->satuan->nama_satuan ?? "-" }}',
                                    '{{ $bk->barang->rak->lokasi->nama_lokasi ?? "-" }}',
                                    '{{ $bk->barang->rak->nama_rak ?? "-" }}',
                                    '{{ \Carbon\Carbon::parse($bk->tanggal_keluar)->format("d/m/Y") }}',
                                    {{ $bk->jumlah_keluar }},
                                    '{{ addslashes($bk->keterangan ?? "-") }}'
                                )">
                                <i class="fas fa-eye"></i> Detail
                            </button>
                            @if(auth()->user()->role == 'Administrator')
                                @if($bk->boleh_edit)
                                <a href="{{ route('barang-keluar.edit', $bk->id_barang_keluar) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                @endif
                                @if($bk->boleh_hapus)
                                <form action="{{ route('barang-keluar.destroy', $bk->id_barang_keluar) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin mau hapus? Stok barang akan dikembalikan!')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">Belum ada data barang keluar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Barang Keluar -->
<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-arrow-circle-up mr-2 text-warning"></i> Detail Barang Keluar</h5>
                <button class="close" type="button" data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="font-weight-bold text-gray-700">ID Barang</td>
                                <td>: <span id="detail-id"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Nama Barang</td>
                                <td>: <span id="detail-nama"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Satuan</td>
                                <td>: <span id="detail-satuan"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Lokasi</td>
                                <td>: <span id="detail-lokasi"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="font-weight-bold text-gray-700">Rak</td>
                                <td>: <span id="detail-rak"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Tanggal Keluar</td>
                                <td>: <span id="detail-tanggal"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Jumlah Keluar</td>
                                <td>: <span id="detail-jumlah"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Keterangan</td>
                                <td>: <span id="detail-keterangan"></span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function showDetail(id, nama, satuan, lokasi, rak, tanggal, jumlah, keterangan) {
        document.getElementById('detail-id').innerText = id;
        document.getElementById('detail-nama').innerText = nama;
        document.getElementById('detail-satuan').innerText = satuan;
        document.getElementById('detail-lokasi').innerText = lokasi;
        document.getElementById('detail-rak').innerText = rak;
        document.getElementById('detail-tanggal').innerText = tanggal;
        document.getElementById('detail-jumlah').innerText = jumlah;
        document.getElementById('detail-keterangan').innerText = keterangan;
        $('#detailModal').modal('show');
    }
</script>
@endsection