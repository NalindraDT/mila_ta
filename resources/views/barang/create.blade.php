@extends('layouts.app')

@section('page-title', 'Tambah Barang')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Tambah Barang</h1>
    <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-boxes mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Form Tambah Barang</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('barang.store') }}" method="POST">
            @csrf

            <!-- ID Barang -->
            <div class="form-group">
                <label>ID Barang</label>
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="id_barang" name="id_barang"
                        value="{{ old('id_barang') }}"
                        placeholder="Scan, generate, atau ketik manual"
                        style="background-color: {{ old('id_barang') ? 'white' : '#f8f9fc' }};"
                        {{ old('id_barang') ? '' : 'readonly' }}>
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <i class="fas fa-barcode"></i>
                        </span>
                    </div>
                </div>
                <button type="button" class="btn btn-info btn-sm mr-2" id="btnScan">
                    <i class="fas fa-camera mr-1"></i> Scan Barcode
                </button>
                <button type="button" class="btn btn-secondary btn-sm mr-2" id="btnGenerate">
                    <i class="fas fa-magic mr-1"></i> Generate Otomatis
                </button>
                <button type="button" class="btn btn-light btn-sm" id="btnManual">
                    <i class="fas fa-keyboard mr-1"></i> Ketik Manual
                </button>
                @error('id_barang')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                <small class="d-block mt-2 text-muted">Format generate: BRG-TAHUN-XXXX. Ketik manual untuk barcode produk.</small>
            </div>

            <!-- Nama Barang -->
            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text"
                    class="form-control @error('nama_barang') is-invalid @enderror"
                    id="nama_barang"
                    name="nama_barang"
                    value="{{ old('nama_barang') }}"
                    placeholder="Contoh: Cat Marine Biru">
                @error('nama_barang')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Kategori -->
            <div class="form-group">
                <label for="kategori">Kategori</label>
                <select class="form-control @error('kategori') is-invalid @enderror"
                    id="kategori" name="kategori">
                    <option value="">-- Pilih Kategori --</option>
                    <option value="Suku Cadang" {{ old('kategori') == 'Suku Cadang' ? 'selected' : '' }}>Suku Cadang</option>
                    <option value="Consumable" {{ old('kategori') == 'Consumable' ? 'selected' : '' }}>Consumable</option>
                </select>
                @error('kategori')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Satuan -->
            <div class="form-group">
                <label for="id_satuan">Satuan</label>
                <select class="form-control @error('id_satuan') is-invalid @enderror"
                    id="id_satuan" name="id_satuan">
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($satuan as $s)
                        <option value="{{ $s->id_satuan }}" {{ old('id_satuan') == $s->id_satuan ? 'selected' : '' }}>
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>
                @error('id_satuan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Lokasi & Rak (Dependent Dropdown) -->
            <div class="form-group">
                <label for="id_lokasi">Lokasi</label>
                <select class="form-control" id="id_lokasi">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach ($rak->groupBy('id_lokasi') as $lokasiId => $raks)
                        <option value="{{ $lokasiId }}">
                            {{ $raks->first()->lokasi->nama_lokasi ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="id_rak">Rak</label>
                <select class="form-control @error('id_rak') is-invalid @enderror"
                    id="id_rak" name="id_rak">
                    <option value="">-- Pilih Lokasi Dulu --</option>
                </select>
                @error('id_rak')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Stok -->
            <div class="form-group">
                <label for="stok">Stok Awal</label>
                <input type="number"
                    class="form-control @error('stok') is-invalid @enderror"
                    id="stok"
                    name="stok"
                    value="{{ old('stok', 0) }}"
                    min="0">
                @error('stok')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Stok Minimum -->
            <div class="form-group">
                <label for="stok_minimum">Batas Minimum Stok</label>
                <input type="number"
                    class="form-control @error('stok_minimum') is-invalid @enderror"
                    id="stok_minimum"
                    name="stok_minimum"
                    value="{{ old('stok_minimum', 5) }}"
                    min="1">
                <small class="text-muted">Kalau stok barang ini sampai di angka ini atau kurang, akan muncul peringatan "Stok Menipis".</small>
                @error('stok_minimum')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label for="deskripsi">Deskripsi <small class="text-muted">(opsional)</small></label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                    placeholder="Keterangan tambahan tentang barang ini">{{ old('deskripsi') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Simpan
            </button>
            <a href="{{ route('barang.index') }}" class="btn btn-light ml-2">Batal</a>

        </form>
    </div>
</div>

<!-- Modal Scan Barcode -->
<div class="modal fade" id="scanModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Scan Barcode</h5>
                <button class="close" type="button" data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <video id="video-preview" style="width: 100%;" autoplay></video>
                <p class="text-muted mt-2 small">Arahkan kamera ke barcode barang</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<script>
    const rakData = @json($rak);

    function isiRak(lokasiId, selectedRakId = null) {
        const rakSelect = document.getElementById('id_rak');
        rakSelect.innerHTML = '<option value="">-- Pilih Rak --</option>';
        if (lokasiId) {
            const filteredRak = rakData.filter(r => r.id_lokasi == lokasiId);
            filteredRak.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id_rak;
                opt.text = r.nama_rak;
                if (selectedRakId && r.id_rak == selectedRakId) opt.selected = true;
                rakSelect.appendChild(opt);
            });
        }
    }

    // Dependent dropdown lokasi & rak (manual)
    document.getElementById('id_lokasi').addEventListener('change', function() {
        isiRak(this.value);
    });

    // Generate otomatis
    document.getElementById('btnGenerate').addEventListener('click', function() {
        fetch('{{ route("barang.generate-id") }}')
            .then(res => res.json())
            .then(data => {
                const input = document.getElementById('id_barang');
                input.value = data.id_barang;
                input.setAttribute('readonly', true);
                input.style.backgroundColor = '#f8f9fc';
            });
    });

    // Ketik manual
    document.getElementById('btnManual').addEventListener('click', function() {
        const input = document.getElementById('id_barang');
        input.removeAttribute('readonly');
        input.style.backgroundColor = 'white';
        input.value = '';
        input.placeholder = 'Ketik ID barcode di sini...';
        input.focus();
    });

    // Scan barcode pakai ZXing
    let codeReader = null;

    document.getElementById('btnScan').addEventListener('click', function() {
        $('#scanModal').modal('show');
    });

    $('#scanModal').on('shown.bs.modal', function() {
        codeReader = new ZXing.BrowserMultiFormatReader();
        codeReader.decodeFromVideoDevice(null, 'video-preview', (result, err) => {
            if (result) {
                document.getElementById('id_barang').value = result.getText();
                codeReader.reset();
                $('#scanModal').modal('hide');
            }
        });
    });

    $('#scanModal').on('hidden.bs.modal', function() {
        if (codeReader) {
            codeReader.reset();
        }
    });

    // Kalau submit gagal, kembalikan pilihan Lokasi & Rak sesuai input sebelumnya
    document.addEventListener('DOMContentLoaded', function() {
        const oldIdRak = "{{ old('id_rak') }}";
        if (oldIdRak) {
            const rakInfo = rakData.find(r => r.id_rak == oldIdRak);
            if (rakInfo) {
                document.getElementById('id_lokasi').value = rakInfo.id_lokasi;
                isiRak(rakInfo.id_lokasi, oldIdRak);
            }
        }
    });
</script>
@endsection