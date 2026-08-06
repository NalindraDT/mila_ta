@extends('layouts.app')

@section('page-title', 'Upload Foto Bukti Barang Masuk')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Upload Foto Bukti Barang Masuk</h1>
    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Form Upload -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-camera mr-2 text-info"></i>
        <h6 class="m-0 font-weight-bold text-info">Form Upload Foto Bukti</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('barang-masuk.foto.store') }}" method="POST" enctype="multipart/form-data" id="formUploadFoto">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_masuk">Tanggal Penerimaan</label>
                        <input type="date"
                            class="form-control @error('tanggal_masuk') is-invalid @enderror"
                            id="tanggal_masuk" name="tanggal_masuk"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_masuk', date('Y-m-d')) }}">
                        <div class="invalid-feedback" id="error-tanggal_masuk">
                            @error('tanggal_masuk'){{ $message }}@enderror
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kategori">Jenis</label>
                        <select class="form-control @error('kategori') is-invalid @enderror"
                            id="kategori" name="kategori">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Consumable" {{ old('kategori') == 'Consumable' ? 'selected' : '' }}>Consumable</option>
                            <option value="Suku Cadang" {{ old('kategori') == 'Suku Cadang' ? 'selected' : '' }}>Suku Cadang</option>
                        </select>
                        <div class="invalid-feedback" id="error-kategori">
                            @error('kategori'){{ $message }}@enderror
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="foto">Foto Bukti</label>
                        <div>
                            <button type="button" id="btnPilihFoto" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus mr-1"></i> Pilih Foto
                            </button>
                            <input type="file" id="foto" name="foto[]" multiple
                                accept="image/png, image/jpeg, image/jpg" style="display:none;">
                        </div>
                        <small class="text-muted d-block mt-1">Bisa klik berkali-kali buat nambah foto. Maks 5MB per foto (JPG/PNG).</small>
                        @error('foto')
                            <div class="text-danger small mt-1" id="error-foto">{{ $message }}</div>
                        @enderror
                        @error('foto.*')
                            <div class="text-danger small mt-1" id="error-foto">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Preview foto yang udah dipilih -->
            <div id="previewFotoContainer" class="d-flex flex-wrap mb-3" style="gap: 10px;"></div>

            <button type="submit" class="btn btn-primary" id="btnSubmitUpload">
                <i class="fas fa-upload mr-1"></i> Upload
            </button>
        </form>
    </div>
</div>

<!-- Daftar Foto -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-images mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Daftar Foto Bukti</h6>
    </div>
    <div class="card-body">
        @if($fotos->isEmpty())
            <p class="text-center text-muted mb-0">Belum ada foto bukti yang diupload.</p>
        @else
            <div class="row">
                @foreach ($fotos as $foto)
                <div class="col-md-3 col-sm-4 col-6 mb-4">
                    <div class="card h-100">
                        <img src="{{ asset('storage/' . $foto->path_foto) }}"
                            class="card-img-top" style="height: 160px; object-fit: cover;"
                            alt="Foto Bukti">
                        <div class="card-body p-2">
                            <p class="mb-1 small">
                                <i class="fas fa-calendar-alt mr-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($foto->tanggal_masuk)->format('d/m/Y') }}
                            </p>
                            <span class="badge {{ $foto->kategori == 'Consumable' ? 'badge-info' : 'badge-success' }} mb-2">
                                {{ $foto->kategori }}
                            </span>
                            <form action="{{ route('barang-masuk.foto.destroy', $foto->id_foto) }}"
                                method="POST" onsubmit="return confirm('Yakin mau hapus foto ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm btn-block">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<style>
    .preview-foto-item {
        position: relative;
        width: 100px;
    }
    .preview-foto-item img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e3e6f0;
    }
    .preview-foto-item .btn-remove-foto {
        position: absolute;
        top: -8px;
        right: -8px;
        width: 22px;
        height: 22px;
        line-height: 20px;
        padding: 0;
        border-radius: 50%;
        background-color: #e74a3b;
        color: white;
        border: 2px solid white;
        font-size: 14px;
        text-align: center;
        cursor: pointer;
    }
    .preview-foto-item .nama-file {
        display: block;
        font-size: 11px;
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-top: 3px;
    }
</style>
<script>
    const fotoInput        = document.getElementById('foto');
    const btnPilihFoto      = document.getElementById('btnPilihFoto');
    const previewContainer = document.getElementById('previewFotoContainer');

    // Kumpulan file yang sedang dipilih (akumulatif)
    let dataTransferFoto = new DataTransfer();

    btnPilihFoto.addEventListener('click', function() {
        fotoInput.click();
    });

    fotoInput.addEventListener('change', function() {
        for (const file of fotoInput.files) {
            dataTransferFoto.items.add(file);
        }
        fotoInput.files = dataTransferFoto.files;
        renderPreview();
        bersihkanError('foto');
    });

    function renderPreview() {
        previewContainer.innerHTML = '';

        Array.from(dataTransferFoto.files).forEach((file, index) => {
            const url = URL.createObjectURL(file);

            const wrapper = document.createElement('div');
            wrapper.className = 'preview-foto-item';
            wrapper.innerHTML = `
                <img src="${url}" alt="${file.name}">
                <button type="button" class="btn-remove-foto" data-index="${index}" title="Hapus foto ini">&times;</button>
                <span class="nama-file">${file.name}</span>
            `;
            previewContainer.appendChild(wrapper);
        });

        document.querySelectorAll('.btn-remove-foto').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                hapusFotoDariPreview(index);
            });
        });
    }

    function hapusFotoDariPreview(index) {
        const dtBaru = new DataTransfer();
        Array.from(dataTransferFoto.files).forEach((file, i) => {
            if (i !== index) {
                dtBaru.items.add(file);
            }
        });
        dataTransferFoto = dtBaru;
        fotoInput.files = dataTransferFoto.files;
        renderPreview();
    }

    // ==== Bersihin tampilan error begitu field diisi (real-time) ====
    function bersihkanError(fieldId) {
        const field    = document.getElementById(fieldId);
        const errorEl  = document.getElementById('error-' + fieldId);

        if (field) field.classList.remove('is-invalid');
        if (errorEl) errorEl.style.display = 'none';
    }

    document.getElementById('tanggal_masuk').addEventListener('input', function() {
        bersihkanError('tanggal_masuk');
    });

    document.getElementById('kategori').addEventListener('change', function() {
        bersihkanError('kategori');
    });

    // Cegah submit kalau belum ada foto yang dipilih sama sekali
    document.getElementById('formUploadFoto').addEventListener('submit', function(e) {
        if (dataTransferFoto.files.length === 0) {
            e.preventDefault();
            alert('Pilih minimal 1 foto dulu ya.');
        }
    });
</script>
@endsection