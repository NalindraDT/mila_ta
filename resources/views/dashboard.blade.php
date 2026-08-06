@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')

@php
// Mapping label & warna badge status, dipakai bareng di tabel Barang Masuk & Barang Keluar
$statusBadge = [
    'pending'   => ['label' => 'Pending',    'class' => 'badge-warning text-dark'],
    'acc_admin' => ['label' => 'ACC Admin',  'class' => 'badge-info'],
    'selesai'   => ['label' => 'Selesai',    'class' => 'badge-success'],
    'ditolak'   => ['label' => 'Ditolak',    'class' => 'badge-danger'],
];
@endphp

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
</div>

<!-- Card Statistik -->
<div class="row">

    <!-- Total Barang -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2 cursor-pointer"
            data-toggle="modal" data-target="#modalBarang" style="cursor:pointer;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Data Barang</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalBarang }}</div>
                        <small class="text-muted">
                            Suku Cadang: {{ $totalSukuCadang }} | Consumable: {{ $totalConsumable }}
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Barang Masuk -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2"
            data-toggle="modal" data-target="#modalMasuk" style="cursor:pointer;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Barang Masuk</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalMasuk }} Transaksi</div>
                        <small class="text-muted">Total: {{ $totalJumlahMasuk }} barang masuk (status selesai)</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-circle-down fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Barang Keluar -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2"
            data-toggle="modal" data-target="#modalKeluar" style="cursor:pointer;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Barang Keluar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalKeluar }} Transaksi</div>
                        <small class="text-muted">Total: {{ $totalJumlahKeluar }} barang keluar (status selesai)</small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-arrow-circle-up fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total User -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2"
            data-toggle="modal" data-target="#modalUser" style="cursor:pointer;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total User</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUser }}</div>
                        <small class="text-muted">
                            Admin: {{ $totalAdmin }} | Kepala Gudang: {{ $totalKepala }}
                        </small>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Tabel Stok Menipis -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
            <h6 class="m-0 font-weight-bold text-warning">Stok Barang Menipis</h6>
        </div>
        <small class="text-muted">Barang dengan stok ≤ batas minimum masing-masing</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="60px">No</th>
                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Satuan</th>
                        <th>Lokasi</th>
                        <th>Rak</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($barangMenipis as $b)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $b->nama_barang }}</td>
                        <td><span class="badge badge-danger">{{ $b->stok }}</span></td>
                        <td>{{ $b->satuan->nama_satuan ?? '-' }}</td>
                        <td>{{ $b->rak->lokasi->nama_lokasi ?? '-' }}</td>
                        <td>{{ $b->rak->nama_rak ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            Semua stok barang masih aman!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Total Barang -->
<div class="modal fade" id="modalBarang" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-boxes mr-2 text-primary"></i> Detail Data Barang</h5>
                <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-4 text-center">
                        <div class="h4 font-weight-bold text-primary">{{ $totalBarang }}</div>
                        <small class="text-muted">Total Barang</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="h4 font-weight-bold text-success">{{ $totalSukuCadang }}</div>
                        <small class="text-muted">Suku Cadang</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="h4 font-weight-bold text-info">{{ $totalConsumable }}</div>
                        <small class="text-muted">Consumable</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Kategori</th>
                                <th>Satuan</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detailBarang as $b)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $b->nama_barang }}</td>
                                <td>
                                    @if($b->kategori == 'Suku Cadang')
                                        <span class="badge badge-success">Suku Cadang</span>
                                    @else
                                        <span class="badge badge-info">Consumable</span>
                                    @endif
                                </td>
                                <td>{{ $b->satuan->nama_satuan ?? '-' }}</td>
                                <td>{{ $b->stok }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada data barang.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Total Barang Masuk -->
<div class="modal fade" id="modalMasuk" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-arrow-circle-down mr-2 text-success"></i> Detail Barang Masuk</h5>
                <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6 text-center">
                        <div class="h4 font-weight-bold text-success">{{ $totalMasuk }}</div>
                        <small class="text-muted">Total Transaksi Selesai</small>
                    </div>
                    <div class="col-6 text-center">
                        <div class="h4 font-weight-bold text-success">{{ $totalJumlahMasuk }}</div>
                        <small class="text-muted">Total Barang Masuk (Selesai)</small>
                    </div>
                </div>
                <p class="text-muted small mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tabel di bawah menampilkan seluruh riwayat transaksi (semua status). Hanya transaksi berstatus
                    <span class="badge badge-success">Selesai</span> yang dihitung pada statistik di atas dan memengaruhi stok.
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Jumlah Masuk</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detailMasuk as $bm)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($bm->tanggal_masuk)->format('d/m/Y') }}</td>
                                <td>{{ $bm->barang->nama_barang ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $bm->status == 'selesai' ? 'badge-success' : 'badge-secondary' }}">
                                        +{{ $bm->jumlah_masuk }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadge[$bm->status]['class'] ?? 'badge-secondary' }}">
                                        {{ $statusBadge[$bm->status]['label'] ?? ucfirst($bm->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada data barang masuk.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Total Barang Keluar -->
<div class="modal fade" id="modalKeluar" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-arrow-circle-up mr-2 text-warning"></i> Detail Barang Keluar</h5>
                <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6 text-center">
                        <div class="h4 font-weight-bold text-warning">{{ $totalKeluar }}</div>
                        <small class="text-muted">Total Transaksi Selesai</small>
                    </div>
                    <div class="col-6 text-center">
                        <div class="h4 font-weight-bold text-warning">{{ $totalJumlahKeluar }}</div>
                        <small class="text-muted">Total Barang Keluar (Selesai)</small>
                    </div>
                </div>
                <p class="text-muted small mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tabel di bawah menampilkan seluruh riwayat transaksi (semua status). Hanya transaksi berstatus
                    <span class="badge badge-success">Selesai</span> yang dihitung pada statistik di atas dan memengaruhi stok.
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Jumlah Keluar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detailKeluar as $bk)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($bk->tanggal_keluar)->format('d/m/Y') }}</td>
                                <td>{{ $bk->barang->nama_barang ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $bk->status == 'selesai' ? 'badge-warning' : 'badge-secondary' }}">
                                        -{{ $bk->jumlah_keluar }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $statusBadge[$bk->status]['class'] ?? 'badge-secondary' }}">
                                        {{ $statusBadge[$bk->status]['label'] ?? ucfirst($bk->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted">Belum ada data barang keluar.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Total User -->
<div class="modal fade" id="modalUser" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users mr-2 text-info"></i> Detail User</h5>
                <button class="close" type="button" data-dismiss="modal"><span>×</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-4 text-center">
                        <div class="h4 font-weight-bold text-info">{{ $totalUser }}</div>
                        <small class="text-muted">Total User</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="h4 font-weight-bold text-primary">{{ $totalAdmin }}</div>
                        <small class="text-muted">Administrator</small>
                    </div>
                    <div class="col-4 text-center">
                        <div class="h4 font-weight-bold text-success">{{ $totalKepala }}</div>
                        <small class="text-muted">Kepala Gudang</small>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Nama User</th>
                                <th>Username</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($detailUser as $u)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $u->nama_user }}</td>
                                <td>{{ $u->username }}</td>
                                <td>
                                    @if($u->role == 'Administrator')
                                        <span class="badge badge-primary">Administrator</span>
                                    @else
                                        <span class="badge badge-info">Kepala Gudang</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data user.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection