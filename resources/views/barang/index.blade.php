@extends('layouts.app')

@section('page-title', 'Data Barang')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Data Barang</h1>
    @if(auth()->user()->role == 'Administrator')
    <a href="{{ route('barang.create') }}" class="btn btn-primary btn-sm shadow-sm">
        <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Tambah Barang
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

<ul class="nav nav-tabs mb-4" id="kategoriTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="suku-cadang-tab" data-toggle="tab" href="#suku-cadang" role="tab">
            <i class="fas fa-tools mr-1"></i> Suku Cadang
            <span class="badge badge-primary ml-1">{{ $sukuCadang->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="consumable-tab" data-toggle="tab" href="#consumable" role="tab">
            <i class="fas fa-box mr-1"></i> Consumable
            <span class="badge badge-info ml-1">{{ $consumable->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content" id="kategoriTabContent">

    <div class="tab-pane fade show active" id="suku-cadang" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center">
                <i class="fas fa-tools mr-2 text-primary"></i>
                <h6 class="m-0 font-weight-bold text-primary">Daftar Suku Cadang</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="60px">No</th>
                                <th>ID Barang</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Lokasi</th>
                                <th>Rak</th>
                                <th>Total Stok</th>
                                <th>Stok Minimum</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sukuCadang as $b)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge badge-secondary">{{ $b->id_barang }}</span></td>
                                <td>{{ $b->nama_barang }}</td>
                                <td>{{ $b->satuan->nama_satuan ?? '-' }}</td>
                                <td>{{ $b->rak->lokasi->nama_lokasi ?? '-' }}</td>
                                <td>{{ $b->rak->nama_rak ?? '-' }}</td>
                                <td>
                                    @if($b->stok <= $b->stok_minimum)
                                        <span class="badge badge-danger">{{ $b->stok }}</span>
                                    @else
                                        <span class="badge badge-success">{{ $b->stok }}</span>
                                    @endif
                                </td>
                                <td>{{ $b->stok_minimum }}</td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <button type="button" class="btn btn-info btn-sm"
                                        onclick="showDetail(
                                            '{{ $b->id_barang }}',
                                            '{{ $b->nama_barang }}',
                                            '{{ $b->kategori }}',
                                            '{{ $b->satuan->nama_satuan ?? "-" }}',
                                            '{{ $b->rak->lokasi->nama_lokasi ?? "-" }}',
                                            '{{ $b->rak->nama_rak ?? "-" }}',
                                            {{ $b->stok }},
                                            {{ $b->stok_minimum }},
                                            '{{ addslashes($b->deskripsi ?? "-") }}'
                                        )">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    @if(auth()->user()->role == 'Administrator')
                                    <a href="{{ route('barang.edit', $b->id_barang) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @if(!$b->sedang_dipakai)
                                    <form action="{{ route('barang.destroy', $b->id_barang) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin mau hapus data ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada data suku cadang.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="consumable" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center">
                <i class="fas fa-box mr-2 text-info"></i>
                <h6 class="m-0 font-weight-bold text-info">Daftar Consumable</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="60px">No</th>
                                <th>ID Barang</th>
                                <th>Nama Barang</th>
                                <th>Satuan</th>
                                <th>Lokasi</th>
                                <th>Rak</th>
                                <th>Stok</th>
                                <th>Stok Min</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($consumable as $b)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="badge badge-secondary">{{ $b->id_barang }}</span></td>
                                <td>{{ $b->nama_barang }}</td>
                                <td>{{ $b->satuan->nama_satuan ?? '-' }}</td>
                                <td>{{ $b->rak->lokasi->nama_lokasi ?? '-' }}</td>
                                <td>{{ $b->rak->nama_rak ?? '-' }}</td>
                                <td>
                                    @if($b->stok <= $b->stok_minimum)
                                        <span class="badge badge-danger">{{ $b->stok }}</span>
                                    @else
                                        <span class="badge badge-success">{{ $b->stok }}</span>
                                    @endif
                                </td>
                                <td>{{ $b->stok_minimum }}</td>
                                <td class="text-center" style="white-space: nowrap;">
                                    <button type="button" class="btn btn-info btn-sm"
                                        onclick="showDetail(
                                            '{{ $b->id_barang }}',
                                            '{{ $b->nama_barang }}',
                                            '{{ $b->kategori }}',
                                            '{{ $b->satuan->nama_satuan ?? "-" }}',
                                            '{{ $b->rak->lokasi->nama_lokasi ?? "-" }}',
                                            '{{ $b->rak->nama_rak ?? "-" }}',
                                            {{ $b->stok }},
                                            {{ $b->stok_minimum }},
                                            '{{ addslashes($b->deskripsi ?? "-") }}'
                                        )">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                    @if(auth()->user()->role == 'Administrator')
                                    <a href="{{ route('barang.edit', $b->id_barang) }}"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    @if(!$b->sedang_dipakai)
                                    <form action="{{ route('barang.destroy', $b->id_barang) }}"
                                        method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin mau hapus data ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada data consumable.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-boxes mr-2"></i> Detail Barang</h5>
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
                                <td class="font-weight-bold text-gray-700">Kategori</td>
                                <td>: <span id="detail-kategori"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Satuan</td>
                                <td>: <span id="detail-satuan"></span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="font-weight-bold text-gray-700">Lokasi</td>
                                <td>: <span id="detail-lokasi"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Rak</td>
                                <td>: <span id="detail-rak"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Stok</td>
                                <td>: <span id="detail-stok"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Stok Minimum</td>
                                <td>: <span id="detail-stok-minimum"></span></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold text-gray-700">Deskripsi</td>
                                <td>: <span id="detail-deskripsi"></span></td>
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
    function showDetail(id, nama, kategori, satuan, lokasi, rak, stok, stokMinimum, deskripsi) {
        document.getElementById('detail-id').innerText = id;
        document.getElementById('detail-nama').innerText = nama;
        document.getElementById('detail-kategori').innerText = kategori;
        document.getElementById('detail-satuan').innerText = satuan;
        document.getElementById('detail-lokasi').innerText = lokasi;
        document.getElementById('detail-rak').innerText = rak;
        document.getElementById('detail-stok').innerText = stok;
        document.getElementById('detail-stok-minimum').innerText = stokMinimum;
        document.getElementById('detail-deskripsi').innerText = deskripsi;
        $('#detailModal').modal('show');
    }
</script>
@endsection