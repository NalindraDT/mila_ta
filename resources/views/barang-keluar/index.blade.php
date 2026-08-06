@extends('layouts.app')

@section('page-title', 'Barang Keluar')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Barang Keluar</h1>

    @if(auth()->user()->role == 'Staff')
    <div>
        <a href="{{ route('barang-keluar.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Request Barang Keluar
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

<!-- Navigasi TABS -->
<ul class="nav nav-tabs mb-4" id="statusTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#tab-pending" role="tab">
            Pending
            <span class="badge badge-warning ml-1 text-dark">{{ $pending->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="acc_admin-tab" data-toggle="tab" href="#tab-acc_admin" role="tab">
            ACC Admin Gudang
            <span class="badge badge-info ml-1">{{ $accAdmin->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="selesai-tab" data-toggle="tab" href="#tab-selesai" role="tab">
            Selesai
            <span class="badge badge-success ml-1">{{ $selesai->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="ditolak-tab" data-toggle="tab" href="#tab-ditolak" role="tab">
            Ditolak
            <span class="badge badge-danger ml-1">{{ $ditolak->count() }}</span>
        </a>
    </li>
</ul>

@php
$tabContents = [
'pending' => ['data' => $pending, 'icon' => 'fa-clock text-warning', 'title' => 'Daftar Transaksi Pending'],
'acc_admin' => ['data' => $accAdmin, 'icon' => 'fa-check text-info', 'title' => 'Daftar Transaksi ACC Admin Gudang'],
'selesai' => ['data' => $selesai, 'icon' => 'fa-check-double text-success', 'title' => 'Daftar Transaksi Selesai'],
'ditolak' => ['data' => $ditolak, 'icon' => 'fa-times text-danger', 'title' => 'Daftar Transaksi Ditolak'],
];
@endphp

<div class="tab-content" id="statusTabContent">

    @foreach($tabContents as $statusKey => $tab)
    <div class="tab-pane fade {{ $statusKey == 'pending' ? 'show active' : '' }}" id="tab-{{ $statusKey }}" role="tabpanel">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center">
                <i class="fas {{ $tab['icon'] }} mr-2"></i>
                <h6 class="m-0 font-weight-bold text-dark">{{ $tab['title'] }}</h6>
            </div>

            @php
            $tampilkanInfoAksi =
                (auth()->user()->role == 'Admin Gudang' && $statusKey == 'pending') ||
                (auth()->user()->role == 'Kepala Gudang' && $statusKey == 'acc_admin');
            @endphp

            @if($tampilkanInfoAksi)
            <div class="mx-3 mt-3">
                <div class="alert alert-warning py-2 mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    <small>Silakan lakukan pengecekan dan verifikasi transaksi pada tab ini.</small>
                </div>
            </div>
            @endif

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover dataTable-custom" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="40px">No</th>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Lokasi/Rak</th>
                                <th>Jml Keluar</th>
                                <th>Keterangan</th>

                                <!-- Header Dinamis Sesuai Role -->
                                @if(auth()->user()->role == 'Staff')
                                <th class="text-center">Catatan / Aksi</th>
                                @else
                                <th width="160px" class="text-center">Aksi / Catatan</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tab['data'] as $bk)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($bk->tanggal_keluar)->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $bk->barang->nama_barang ?? '-' }}</strong><br>
                                    <small class="text-muted">ID: {{ $bk->id_barang }}</small>
                                </td>
                                <td>
                                    {{ $bk->barang->rak->lokasi->nama_lokasi ?? '-' }}<br>
                                    <small class="text-muted">Rak: {{ $bk->barang->rak->nama_rak ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-warning" style="font-size: 0.9rem;">-{{ $bk->jumlah_keluar }} {{ $bk->barang->satuan->nama_satuan ?? '' }}</span>
                                </td>
                                <td>
                                    <small>{{ $bk->keterangan ?: '-' }}</small>
                                </td>

                                <!-- 1. Tampilan Untuk Staff -->
                                @if(auth()->user()->role == 'Staff')
                                <td class="text-center">
                                    @if($bk->status == 'pending')
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-request"
                                        data-id="{{ $bk->id_barang_keluar }}" title="Batalkan Request">
                                        <i class="fas fa-trash"></i> Batalkan
                                    </button>
                                    @endif
                                    @if($bk->catatan_verifikasi)
                                    <div class="text-left bg-light p-2 rounded border mb-2">
                                        <small class="text-dark"><strong>Catatan:</strong><br>{{ $bk->catatan_verifikasi }}</small>
                                    </div>
                                    @else
                                    <span class="text-muted small d-block mb-2">-</span>
                                    @endif
                                </td>
                                @endif

                                <!-- 2. Tampilan Untuk Admin & Kepala Gudang -->
                                @if(in_array(auth()->user()->role, ['Admin Gudang', 'Kepala Gudang']))
                                <td class="text-center">

                                    <!-- LOGIKA ADMIN GUDANG -->
                                    @if(auth()->user()->role == 'Admin Gudang')
                                        @if($bk->status == 'pending')
                                        <button class="btn btn-success btn-sm btn-verifikasi-cepat" data-id="{{ $bk->id_barang_keluar }}" data-action="acc_admin" data-endpoint="verifikasi-admin" title="ACC Transaksi">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-verifikasi-cepat" data-id="{{ $bk->id_barang_keluar }}" data-action="ditolak" data-endpoint="verifikasi-admin" title="Tolak Transaksi">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @else
                                        <div class="text-left bg-light p-2 rounded border">
                                            <span class="d-block text-muted small border-bottom mb-1 pb-1"><i class="fas fa-lock"></i> Selesai Diproses</span>
                                            <small class="text-dark"><strong>Keterangan:</strong><br>{{ $bk->catatan_verifikasi ?: '-' }}</small>
                                        </div>
                                        @endif
                                    @endif

                                    <!-- LOGIKA KEPALA GUDANG -->
                                    @if(auth()->user()->role == 'Kepala Gudang')
                                        @if($bk->status == 'pending')
                                        <div class="text-left bg-light p-2 rounded border mb-1">
                                            <span class="d-block text-muted small">
                                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu ACC Admin Gudang
                                            </span>
                                        </div>
                                        @elseif($bk->status == 'acc_admin')
                                        @if($bk->catatan_verifikasi)
                                        <div class="text-left bg-light p-2 rounded border mb-2">
                                            <small class="text-dark">
                                                <i class="fas fa-comment-dots mr-1"></i>
                                                <strong>Catatan Admin Gudang:</strong><br>
                                                {{ $bk->catatan_verifikasi }}
                                            </small>
                                        </div>
                                        @endif
                                        <button class="btn btn-success btn-sm btn-verifikasi-cepat" data-id="{{ $bk->id_barang_keluar }}" data-action="selesai" data-endpoint="verifikasi-kepala" title="Selesaikan Transaksi">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-verifikasi-cepat" data-id="{{ $bk->id_barang_keluar }}" data-action="ditolak" data-endpoint="verifikasi-kepala" title="Tolak Transaksi">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @else
                                        <div class="text-left bg-light p-2 rounded border mb-1">
                                            <span class="d-block text-muted small border-bottom mb-1 pb-1"><i class="fas fa-lock"></i> Selesai Diproses</span>
                                            <small class="text-dark"><strong>Keterangan:</strong><br>{{ $bk->catatan_verifikasi ?: '-' }}</small>
                                        </div>
                                        @endif
                                    @endif

                                </td>
                                @endif

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach

</div>

<!-- Modal Konfirmasi Verifikasi Cepat (Admin Gudang & Kepala Gudang) -->
<div class="modal fade" id="modalVerifikasiCepat" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formVerifikasiCepat" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" id="inputStatusVerifikasi" value="">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="titleVerifikasiCepat">Konfirmasi</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin melakukan proses ini? <br><strong class="text-danger">Tindakan ini akan mengunci aksi Anda untuk transaksi ini.</strong></p>
                    <div class="form-group mt-3">
                        <label for="keteranganVerifikasi" class="font-weight-bold">Keterangan / Catatan (Opsional)</label>
                        <textarea name="catatan_verifikasi" id="keteranganVerifikasi" class="form-control" rows="3" placeholder="Tambahkan alasan atau catatan misal: 'Sesuai' ..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn" id="btnSubmitVerifikasiCepat">Yakin & Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Request -->
<div class="modal fade" id="modalHapusRequest" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form id="formHapusRequest" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Konfirmasi Hapus
                    </h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Yakin ingin menghapus data ini?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Tidak</button>
                    <button type="submit" class="btn btn-danger">Ya</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.dataTable-custom').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
            },
            "autoWidth": false,
            "columnDefs": [
                { "orderable": false, "targets": 6 },
                { "width": "5%",  "targets": 0 },  // No
                { "width": "9%",  "targets": 1 },  // Tanggal
                { "width": "16%", "targets": 2 },  // Barang
                { "width": "14%", "targets": 3 },  // Lokasi/Rak
                { "width": "9%",  "targets": 4 },  // Jml Keluar
                { "width": "35%", "targets": 5 },  // Keterangan
                { "width": "12%", "targets": 6, "className": "text-center" } // Aksi/Catatan
            ]
        });

        $('body').on('click', '.btn-hapus-request', function() {
            var id = $(this).data('id');
            var baseUrl = "{{ url('barang-keluar') }}";

            $('#formHapusRequest').attr('action', baseUrl + '/' + id);
            $('#modalHapusRequest').modal('show');
        });

        $('body').on('click', '.btn-verifikasi-cepat', function() {
            var id = $(this).data('id');
            var action = $(this).data('action');     // acc_admin | selesai | ditolak
            var endpoint = $(this).data('endpoint'); // verifikasi-admin | verifikasi-kepala

            var baseUrl = "{{ url('barang-keluar') }}";
            $('#formVerifikasiCepat').attr('action', baseUrl + '/' + id + '/' + endpoint);

            $('#inputStatusVerifikasi').val(action);

            if (action == 'ditolak') {
                $('#titleVerifikasiCepat').html('<i class="fas fa-times text-danger mr-2"></i> Konfirmasi Tolak');
                $('#btnSubmitVerifikasiCepat').removeClass('btn-success').addClass('btn-danger').html('Yakin, Tolak Transaksi');
            } else if (action == 'selesai') {
                $('#titleVerifikasiCepat').html('<i class="fas fa-check-double text-success mr-2"></i> Konfirmasi Selesaikan Transaksi');
                $('#btnSubmitVerifikasiCepat').removeClass('btn-danger').addClass('btn-success').html('Yakin, Selesaikan Transaksi');
            } else {
                $('#titleVerifikasiCepat').html('<i class="fas fa-check text-success mr-2"></i> Konfirmasi ACC');
                $('#btnSubmitVerifikasiCepat').removeClass('btn-danger').addClass('btn-success').html('Yakin, ACC Transaksi');
            }

            $('#keteranganVerifikasi').val('');
            $('#modalVerifikasiCepat').modal('show');
        });
    });
</script>
@endsection