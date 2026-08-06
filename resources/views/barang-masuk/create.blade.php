@extends('layouts.app')

@section('page-title', 'Request Barang Masuk')

@php
    $barangJs = $barang->map(function ($b) {
        return [
            'id_barang'   => $b->id_barang,
            'nama_barang' => $b->nama_barang,
            'kategori'    => $b->kategori,
            'stok'        => $b->stok,
            'satuan'      => $b->satuan->nama_satuan ?? '-',
            'lokasi'      => $b->rak->lokasi->nama_lokasi ?? '-',
            'rak'         => $b->rak->nama_rak ?? '-',
            'id_rak'      => $b->id_rak,
        ];
    });
@endphp

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Request Barang Masuk</h1>
    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-arrow-circle-down mr-2 text-success"></i>
        <h6 class="m-0 font-weight-bold text-success">Form Request Barang Masuk</h6>
    </div>
    <div class="card-body">
        <!-- WAJIB: tambahkan enctype agar bisa upload file -->
        <form action="{{ route('barang-masuk.store') }}" method="POST" id="formBarangMasuk" enctype="multipart/form-data">
            @csrf

            <!-- Filter Kategori -->
            <div class="form-group">
                <label for="kategori_filter">Kategori</label>
                <select class="form-control" id="kategori_filter">
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Suku Cadang">Suku Cadang</option>
                    <option value="Consumable">Consumable</option>
                </select>
                <div class="text-danger small mt-1" id="error-kategori_filter" style="display:none;">
                    Kategori wajib dipilih.
                </div>
            </div>

            <!-- Pilih Barang -->
            <div class="form-group">
                <label for="id_barang">Barang</label>
                <select class="form-control @error('id_barang') is-invalid @enderror"
                    id="id_barang" name="id_barang" style="width: 100%;" disabled>
                    <option value="">-- Pilih Kategori Dulu --</option>
                </select>
                @error('id_barang')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6">
                    <!-- Satuan (readonly, otomatis) -->
                    <div class="form-group">
                        <label>Satuan</label>
                        <input type="text" class="form-control" id="satuan_barang"
                            value="-" readonly style="background-color: #f8f9fc;">
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Stok Sekarang (readonly) -->
                    <div class="form-group">
                        <label>Stok Saat Ini</label>
                        <input type="text" class="form-control" id="stok_sekarang"
                            value="-" readonly style="background-color: #f8f9fc;">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <!-- Lokasi (readonly, otomatis dari barang) -->
                    <div class="form-group">
                        <label>Lokasi Penyimpanan</label>
                        <input type="text" class="form-control" id="lokasi_barang"
                            value="-" readonly style="background-color: #f8f9fc;">
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Rak (readonly, otomatis dari barang) -->
                    <div class="form-group">
                        <label>Rak</label>
                        <input type="text" class="form-control" id="rak_barang"
                            value="-" readonly style="background-color: #f8f9fc;">
                        <input type="hidden" id="id_rak" name="id_rak">
                        @error('id_rak')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <!-- Jumlah Masuk -->
                    <div class="form-group">
                        <label for="jumlah_masuk">Jumlah Masuk</label>
                        <input type="number"
                            class="form-control @error('jumlah_masuk') is-invalid @enderror"
                            id="jumlah_masuk" name="jumlah_masuk"
                            value="{{ old('jumlah_masuk') }}"
                            min="1" placeholder="Masukkan jumlah barang masuk">
                        @error('jumlah_masuk')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Total Stok Sementara (otomatis) -->
                    <div class="form-group">
                        <label>Estimasi Total Stok Nanti</label>
                        <input type="text" class="form-control text-success font-weight-bold" id="total_stok"
                            value="-" readonly style="background-color: #f8f9fc;">
                    </div>
                </div>
            </div>

            <!-- Tanggal -->
            <div class="form-group">
                <label for="tanggal_masuk">Tanggal Masuk</label>
                <input type="date"
                    class="form-control @error('tanggal_masuk') is-invalid @enderror"
                    id="tanggal_masuk" name="tanggal_masuk"
                    max="{{ date('Y-m-d') }}"
                    value="{{ old('tanggal_masuk', date('Y-m-d')) }}">
                @error('tanggal_masuk')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Foto Bukti -->
            <div class="form-group mt-4 p-3 border rounded" style="background-color: #fcfcfc;">
                <label for="foto_bukti" class="font-weight-bold text-info"><i class="fas fa-camera mr-1"></i> Foto Bukti Transaksi (Wajib)</label>
                <input type="file" 
                    class="form-control-file @error('foto_bukti') is-invalid @enderror" 
                    id="foto_bukti" name="foto_bukti" 
                    accept="image/png, image/jpeg, image/jpg" required>
                <small class="text-muted d-block mt-1">Format: JPG, JPEG, PNG. Maksimal ukuran 5MB.</small>
                
                @error('foto_bukti')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror

                <!-- Area Preview Foto -->
                <div class="mt-3">
                    <img id="preview-foto" src="" alt="Preview" style="max-height: 200px; display: none; border-radius: 8px; border: 1px solid #ddd; padding: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <i class="fas fa-paper-plane mr-1"></i> Kirim Request
            </button>
            <a href="{{ route('barang-masuk.index') }}" class="btn btn-light mt-3 ml-2">Batal</a>

        </form>
    </div>
</div>

@endsection

@section('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const semuaBarang = @json($barangJs);
    const oldIdBarang = "{{ old('id_barang') }}";

    function renderOpsiBarang(kategori, selectedId = null) {
        const $select = $('#id_barang');
        $select.empty().append('<option value="">-- Pilih Barang --</option>');

        const filtered = semuaBarang.filter(b => b.kategori === kategori);

        filtered.forEach(b => {
            const opt = new Option(`${b.id_barang} - ${b.nama_barang}`, b.id_barang, false, b.id_barang === selectedId);
            $(opt).attr({
                'data-stok': b.stok,
                'data-satuan': b.satuan,
                'data-lokasi': b.lokasi,
                'data-rak': b.rak,
                'data-idrak': b.id_rak,
            });
            $select.append(opt);
        });

        $select.prop('disabled', false);
        $select.trigger('change.select2');
    }

    function isiOtomatisDariBarang() {
        const selected = $('#id_barang').find(':selected');
        const stok   = selected.attr('data-stok');
        const satuan = selected.attr('data-satuan');
        const lokasi = selected.attr('data-lokasi');
        const rak    = selected.attr('data-rak');
        const idRak  = selected.attr('data-idrak');

        if (stok !== undefined && stok !== '') {
            document.getElementById('stok_sekarang').value = stok;
            document.getElementById('satuan_barang').value = satuan || '-';
            document.getElementById('lokasi_barang').value = lokasi || '-';
            document.getElementById('rak_barang').value    = rak || '-';
            document.getElementById('id_rak').value        = idRak || '';
            hitungTotal();
        } else {
            document.getElementById('stok_sekarang').value = '-';
            document.getElementById('satuan_barang').value = '-';
            document.getElementById('lokasi_barang').value = '-';
            document.getElementById('rak_barang').value    = '-';
            document.getElementById('id_rak').value        = '';
            document.getElementById('total_stok').value    = '-';
        }
    }

    function hitungTotal() {
        const stok   = parseInt(document.getElementById('stok_sekarang').value) || 0;
        const jumlah = parseInt(document.getElementById('jumlah_masuk').value) || 0;
        if (stok >= 0 && jumlah > 0) {
            document.getElementById('total_stok').value = stok + jumlah;
        } else {
            document.getElementById('total_stok').value = '-';
        }
    }

    $(document).ready(function() {
        $('#id_barang').select2({
            theme: 'bootstrap-5',
            placeholder: '-- Pilih Barang --',
            width: '100%',
            language: {
                noResults: function() { return 'Barang tidak ditemukan'; },
                searching: function() { return 'Mencari...'; }
            }
        });

        let kategoriAwal = '';
        if (oldIdBarang) {
            const matched = semuaBarang.find(b => b.id_barang === oldIdBarang);
            if (matched) kategoriAwal = matched.kategori;
        }

        if (kategoriAwal) {
            $('#kategori_filter').val(kategoriAwal);
            renderOpsiBarang(kategoriAwal, oldIdBarang || null);
            if (oldIdBarang) isiOtomatisDariBarang();
        }

        // Script untuk Live Preview Foto
        $('#foto_bukti').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview-foto').attr('src', e.target.result).show();
                }
                reader.readAsDataURL(file);
            } else {
                $('#preview-foto').hide();
            }
        });
    });

    $('#kategori_filter').on('change', function() {
        if (this.value) {
            document.getElementById('error-kategori_filter').style.display = 'none';
            this.classList.remove('is-invalid');
            renderOpsiBarang(this.value);
        } else {
            $('#id_barang').empty()
                .append('<option value="">-- Pilih Kategori Dulu --</option>')
                .prop('disabled', true)
                .trigger('change.select2');
        }
        isiOtomatisDariBarang();
    });

    $('#id_barang').on('change', isiOtomatisDariBarang);

    document.getElementById('jumlah_masuk').addEventListener('input', hitungTotal);

    // Validasi submit
    document.getElementById('formBarangMasuk').addEventListener('submit', function(e) {
        const kategoriFilter = document.getElementById('kategori_filter');
        if (!kategoriFilter.value) {
            e.preventDefault();
            kategoriFilter.classList.add('is-invalid');
            document.getElementById('error-kategori_filter').style.display = 'block';
            kategoriFilter.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
@endsection