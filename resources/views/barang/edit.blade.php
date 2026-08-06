@extends('layouts.app')

@section('page-title', 'Edit Barang')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Barang</h1>
    <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm shadow-sm">
        <i class="fas fa-arrow-left fa-sm mr-1"></i> Kembali
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-boxes mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Form Edit Barang</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('barang.update', $barang->id_barang) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- ID Barang -->
            <div class="form-group">
                <label>ID Barang</label>
                <input type="text" class="form-control" value="{{ $barang->id_barang }}" readonly
                    style="background-color: #f8f9fc;">
                <small class="text-muted">ID Barang tidak bisa diubah.</small>
            </div>

            <!-- Nama Barang -->
            <div class="form-group">
                <label for="nama_barang">Nama Barang</label>
                <input type="text"
                    class="form-control @error('nama_barang') is-invalid @enderror"
                    id="nama_barang"
                    name="nama_barang"
                    value="{{ old('nama_barang', $barang->nama_barang) }}"
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
                    <option value="Suku Cadang" {{ old('kategori', $barang->kategori) == 'Suku Cadang' ? 'selected' : '' }}>Suku Cadang</option>
                    <option value="Consumable" {{ old('kategori', $barang->kategori) == 'Consumable' ? 'selected' : '' }}>Consumable</option>
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
                        <option value="{{ $s->id_satuan }}"
                            {{ old('id_satuan', $barang->id_satuan) == $s->id_satuan ? 'selected' : '' }}>
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>
                @error('id_satuan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Lokasi & Rak -->
            <div class="form-group">
                <label for="id_lokasi">Lokasi</label>
                <select class="form-control" id="id_lokasi">
                    <option value="">-- Pilih Lokasi --</option>
                    @foreach ($rak->groupBy('id_lokasi') as $lokasiId => $raks)
                        <option value="{{ $lokasiId }}"
                            {{ $barang->rak->id_lokasi == $lokasiId ? 'selected' : '' }}>
                            {{ $raks->first()->lokasi->nama_lokasi ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="id_rak">Rak</label>
                <select class="form-control @error('id_rak') is-invalid @enderror"
                    id="id_rak" name="id_rak">
                    <option value="">-- Pilih Rak --</option>
                    @foreach ($rak as $r)
                        @if($r->id_lokasi == $barang->rak->id_lokasi)
                            <option value="{{ $r->id_rak }}"
                                {{ old('id_rak', $barang->id_rak) == $r->id_rak ? 'selected' : '' }}>
                                {{ $r->nama_rak }}
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('id_rak')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Stok -->
            <div class="form-group">
                <label for="stok">Stok</label>
                <input type="number"
                    class="form-control @error('stok') is-invalid @enderror"
                    id="stok"
                    name="stok"
                    value="{{ old('stok', $barang->stok) }}"
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
                    value="{{ old('stok_minimum', $barang->stok_minimum) }}"
                    min="1">
                <small class="text-muted">Kalau stok barang ini sampai di angka ini atau kurang, akan muncul peringatan "Stok Menipis".</small>
                @error('stok_minimum')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label for="deskripsi">Deskripsi <small class="text-muted">(opsional)</small></label>
                <textarea class="form-control" id="deskripsi" name="deskripsi"
                    rows="3">{{ old('deskripsi', $barang->deskripsi) }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Update
            </button>
            <a href="{{ route('barang.index') }}" class="btn btn-light ml-2">Batal</a>

        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const rakData = @json($rak);

    document.getElementById('id_lokasi').addEventListener('change', function() {
        const lokasiId = this.value;
        const rakSelect = document.getElementById('id_rak');
        rakSelect.innerHTML = '<option value="">-- Pilih Rak --</option>';
        if (lokasiId) {
            const filteredRak = rakData.filter(r => r.id_lokasi == lokasiId);
            filteredRak.forEach(r => {
                rakSelect.innerHTML += `<option value="${r.id_rak}">${r.nama_rak}</option>`;
            });
        }
    });
</script>
@endsection