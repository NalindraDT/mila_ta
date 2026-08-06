@extends('layouts.app')

@section('page-title', 'Verifikasi & Edit Barang Masuk')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Verifikasi Barang Masuk</h1>
    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    Pastikan data fisik sesuai dengan foto bukti. Anda dapat merevisi jumlah jika terjadi kesalahan input oleh Staff. Tambahkan catatan alasan jika ada revisi.
</div>

<div class="row">
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex align-items-center">
                <i class="fas fa-edit mr-2 text-warning"></i>
                <h6 class="m-0 font-weight-bold text-warning">Form Verifikasi Kepala Gudang</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('barang-masuk.update', $barangMasuk->id_barang_masuk) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Barang</label>
                            <input type="text" class="form-control" value="{{ $barangMasuk->barang->nama_barang ?? '-' }}" readonly style="background-color: #f8f9fc;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Satuan</label>
                            <input type="text" class="form-control" value="{{ $barangMasuk->barang->satuan->nama_satuan ?? '-' }}" readonly style="background-color: #f8f9fc;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Lokasi Penyimpanan</label>
                            <input type="text" class="form-control" value="{{ $barangMasuk->rak->lokasi->nama_lokasi ?? '-' }}" readonly style="background-color: #f8f9fc;">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Rak</label>
                            <input type="text" class="form-control" value="{{ $barangMasuk->rak->nama_rak ?? '-' }}" readonly style="background-color: #f8f9fc;">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="jumlah_masuk">Jumlah Masuk (Bisa Direvisi)</label>
                            <input type="number" class="form-control @error('jumlah_masuk') is-invalid @enderror"
                                id="jumlah_masuk" name="jumlah_masuk"
                                value="{{ old('jumlah_masuk', $barangMasuk->jumlah_masuk) }}" min="1">
                            @error('jumlah_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="tanggal_masuk">Tanggal Masuk</label>
                            <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                id="tanggal_masuk" name="tanggal_masuk" max="{{ date('Y-m-d') }}"
                                value="{{ old('tanggal_masuk', \Carbon\Carbon::parse($barangMasuk->tanggal_masuk)->format('Y-m-d')) }}" readonly>
                            @error('tanggal_masuk')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <!-- Pemilihan Status Menggunakan Radio Button Berbentuk Kotak -->
                    <div class="form-group">
                        <label class="font-weight-bold d-block">Keputusan Verifikasi</label>
                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                            <!-- Tombol ACC -->
                            <label class="btn btn-outline-success {{ $barangMasuk->status == 'selesai' ? 'active' : '' }}">
                                <input type="radio" name="status" id="status_acc" value="selesai" {{ $barangMasuk->status == 'selesai' ? 'checked' : '' }} required>
                                <i class="fas fa-check-double mr-1"></i> ACC (Selesai & Tambah Stok)
                            </label>
                            <!-- Tombol Tolak -->
                            <label class="btn btn-outline-danger {{ $barangMasuk->status == 'ditolak' ? 'active' : '' }}">
                                <input type="radio" name="status" id="status_tolak" value="ditolak" {{ $barangMasuk->status == 'ditolak' ? 'checked' : '' }} required>
                                <i class="fas fa-times mr-1"></i> Tolak Transaksi
                            </label>
                        </div>
                        @error('status')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mt-3">
                        <label for="catatan_verifikasi">Catatan Verifikasi / Alasan Penolakan</label>
                        <textarea class="form-control" id="catatan_verifikasi" name="catatan_verifikasi" rows="3" 
                            placeholder="Tulis alasan jika Anda mengubah jumlah fisik atau menolak transaksi ini...">{{ old('catatan_verifikasi', $barangMasuk->catatan_verifikasi) }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-save mr-1"></i> Simpan Keputusan
                    </button>
                    <a href="{{ route('barang-masuk.index') }}" class="btn btn-light mt-3 ml-2">Batal</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Kolom Samping untuk Menampilkan Foto Bukti -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-image mr-1"></i> Foto Bukti Transaksi</h6>
            </div>
            <div class="card-body text-center">
                @if($barangMasuk->foto_bukti)
                    <!-- Gambar sekarang bertindak sebagai tombol pembuka Modal -->
                    <img src="{{ asset('storage/' . $barangMasuk->foto_bukti) }}" alt="Foto Bukti" class="img-fluid rounded shadow-sm" style="max-height: 300px; border: 1px solid #ddd; padding: 4px; cursor: pointer;" data-toggle="modal" data-target="#modalFotoBuktiEdit">
                    <p class="small text-muted mt-2"><i class="fas fa-search-plus"></i> Klik gambar untuk memperbesar</p>
                @else
                    <div class="p-4 border rounded bg-light text-muted">
                        <i class="fas fa-camera-slash fa-3x mb-3"></i>
                        <p class="mb-0">Tidak ada foto bukti yang dilampirkan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Pop-up Foto Bukti khusus untuk Halaman Edit -->
<div class="modal fade" id="modalFotoBuktiEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-info"><i class="fas fa-image mr-2"></i>Foto Bukti Barang Masuk</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/' . $barangMasuk->foto_bukti) }}" class="img-fluid rounded" style="max-height: 80vh;" alt="Foto Bukti">
            </div>
        </div>
    </div>
</div>

@endsection