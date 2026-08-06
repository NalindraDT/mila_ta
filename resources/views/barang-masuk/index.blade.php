@extends('layouts.app')

@section('page-title', 'Barang Masuk')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Barang Masuk</h1>

    @if(auth()->user()->role == 'Staff')
    <div>
        <a href="{{ route('barang-masuk.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Request Barang Masuk
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

<!-- Navigasi TABS (Ikon Dihapus) -->
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
// Array untuk memudahkan perulangan tab konten (DRY - Don't Repeat Yourself)
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
            // Alert info aksi HANYA muncul di tab yang relevan untuk role & tahap alur yang bersangkutan:
            // - Admin Gudang bertindak di tab Pending
            // - Kepala Gudang bertindak di tab ACC Admin Gudang
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
                    <!-- Gunakan class dataTable-custom agar 4 tabel bisa berjalan semua -->
                    <table class="table table-bordered table-hover dataTable-custom" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th width="40px">No</th>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Lokasi/Rak</th>
                                <th>Jml Masuk</th>
                                <th class="text-center">Foto</th>

                                <!-- Header Dinamis Sesuai Role -->
                                @if(auth()->user()->role == 'Staff')
                                <th class="text-center">Catatan / Aksi</th>
                                @else
                                <th width="160px" class="text-center">Aksi / Catatan</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tab['data'] as $bm)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($bm->tanggal_masuk)->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $bm->barang->nama_barang ?? '-' }}</strong><br>
                                    <small class="text-muted">ID: {{ $bm->id_barang }}</small>
                                </td>
                                <td>
                                    {{ $bm->rak->lokasi->nama_lokasi ?? '-' }}<br>
                                    <small class="text-muted">Rak: {{ $bm->rak->nama_rak ?? '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-success" style="font-size: 0.9rem;">+{{ $bm->jumlah_masuk }} {{ $bm->barang->satuan->nama_satuan ?? '' }}</span>
                                </td>

                                <td class="text-center">
                                    @if($bm->foto_bukti)
                                    <button type="button" class="btn btn-sm btn-outline-info btn-lihat-foto" data-foto="{{ asset('storage/' . $bm->foto_bukti) }}" title="Lihat Foto Bukti">
                                        <i class="fas fa-image"></i>
                                    </button>
                                    @else
                                    <span class="text-muted small">-</span>
                                    @endif
                                </td>

                                <!-- 1. Tampilan Untuk Staff -->
                                @if(auth()->user()->role == 'Staff')
                                <td class="text-center">
                                    <!-- Tombol Batal/Hapus HANYA muncul jika status pending -->
                                    @if($bm->status == 'pending')
                                    <button type="button" class="btn btn-outline-danger btn-sm btn-hapus-request"
                                        data-id="{{ $bm->id_barang_masuk }}" title="Batalkan Request">
                                        <i class="fas fa-trash"></i> Batalkan
                                    </button>
                                    @endif
                                    <!-- Tampilkan Catatan Jika Ada -->
                                    @if($bm->catatan_verifikasi)
                                    <div class="text-left bg-light p-2 rounded border mb-2">
                                        <small class="text-dark"><strong>Catatan:</strong><br>{{ $bm->catatan_verifikasi }}</small>
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
                                        @if($bm->status == 'pending')
                                        <button class="btn btn-success btn-sm btn-verifikasi-cepat" data-id="{{ $bm->id_barang_masuk }}" data-action="acc_admin" title="ACC Transaksi">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-verifikasi-cepat" data-id="{{ $bm->id_barang_masuk }}" data-action="ditolak" title="Tolak Transaksi">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @else
                                        <div class="text-left bg-light p-2 rounded border">
                                            <span class="d-block text-muted small border-bottom mb-1 pb-1"><i class="fas fa-lock"></i> Selesai Diproses</span>
                                            <small class="text-dark"><strong>Keterangan:</strong><br>{{ $bm->catatan_verifikasi ?: '-' }}</small>
                                        </div>
                                        @endif
                                    @endif

                                    <!-- LOGIKA KEPALA GUDANG -->
                                    @if(auth()->user()->role == 'Kepala Gudang')
                                        @if($bm->status == 'pending')
                                        <!-- Belum boleh diproses, harus ACC Admin Gudang dulu -->
                                        <div class="text-left bg-light p-2 rounded border mb-1">
                                            <span class="d-block text-muted small">
                                                <i class="fas fa-hourglass-half mr-1"></i> Menunggu ACC Admin Gudang
                                            </span>
                                        </div>
                                        @elseif($bm->boleh_edit)
                                        <a href="{{ route('barang-masuk.edit', $bm->id_barang_masuk) }}" class="btn btn-warning btn-sm mb-1 w-100" title="Verifikasi Transaksi">
                                            <i class="fas fa-check-circle"></i> Verifikasi
                                        </a>
                                        @else
                                        <div class="text-left bg-light p-2 rounded border mb-1">
                                            <span class="d-block text-muted small border-bottom mb-1 pb-1"><i class="fas fa-lock"></i> Selesai Diproses</span>
                                            <small class="text-dark"><strong>Keterangan:</strong><br>{{ $bm->catatan_verifikasi ?: '-' }}</small>
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

<!-- Modal Pop-up Foto Bukti -->
<div class="modal fade" id="modalFotoBukti" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-info"><i class="fas fa-image mr-2"></i>Foto Bukti Barang Masuk</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="imgBukti" class="img-fluid rounded" style="max-height: 80vh;">
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Verifikasi Cepat (Khusus Admin Gudang) -->
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
            "columnDefs": [{
                "orderable": false,
                "targets": [5]
            }]
        });

        $('body').on('click', '.btn-lihat-foto', function() {
            var fotoUrl = $(this).data('foto');
            $('#imgBukti').attr('src', fotoUrl);
            $('#modalFotoBukti').modal('show');
        });

        $('body').on('click', '.btn-hapus-request', function() {
            var id = $(this).data('id');
            var baseUrl = "{{ url('barang-masuk') }}";

            $('#formHapusRequest').attr('action', baseUrl + '/' + id);
            $('#modalHapusRequest').modal('show');
        });

        $('body').on('click', '.btn-verifikasi-cepat', function() {
            var id = $(this).data('id');
            var action = $(this).data('action');

            var baseUrl = "{{ url('barang-masuk') }}";
            $('#formVerifikasiCepat').attr('action', baseUrl + '/' + id + '/verifikasi-admin');

            $('#inputStatusVerifikasi').val(action);

            if (action == 'acc_admin') {
                $('#titleVerifikasiCepat').html('<i class="fas fa-check text-success mr-2"></i> Konfirmasi ACC');
                $('#btnSubmitVerifikasiCepat').removeClass('btn-danger').addClass('btn-success').html('Yakin, ACC Transaksi');
            } else {
                $('#titleVerifikasiCepat').html('<i class="fas fa-times text-danger mr-2"></i> Konfirmasi Tolak');
                $('#btnSubmitVerifikasiCepat').removeClass('btn-success').addClass('btn-danger').html('Yakin, Tolak Transaksi');
            }

            $('#keteranganVerifikasi').val('');
            $('#modalVerifikasiCepat').modal('show');
        });
    });
</script>
@endsection