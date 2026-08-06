@extends('layouts.app')

@section('page-title', 'Laporan Stok')

@section('content')

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Laporan Stok Barang</h1>
</div>

<!-- Form Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center">
        <i class="fas fa-filter mr-2 text-primary"></i>
        <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('laporan.generate') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select class="form-control @error('kategori') is-invalid @enderror"
                            id="kategori" name="kategori">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Suku Cadang" {{ old('kategori', isset($request) ? $request->kategori : '') == 'Suku Cadang' ? 'selected' : '' }}>Suku Cadang</option>
                            <option value="Consumable" {{ old('kategori', isset($request) ? $request->kategori : '') == 'Consumable' ? 'selected' : '' }}>Consumable</option>
                            <option value="semua" {{ old('kategori', isset($request) ? $request->kategori : '') == 'semua' ? 'selected' : '' }}>Semua</option>
                        </select>
                        @error('kategori')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_dari">Tanggal Dari</label>
                        <input type="date"
                            class="form-control @error('tanggal_dari') is-invalid @enderror"
                            id="tanggal_dari" name="tanggal_dari"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_dari', isset($request) ? $request->tanggal_dari : '') }}">
                        @error('tanggal_dari')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tanggal_sampai">Tanggal Sampai</label>
                        <input type="date"
                            class="form-control @error('tanggal_sampai') is-invalid @enderror"
                            id="tanggal_sampai" name="tanggal_sampai"
                            max="{{ date('Y-m-d') }}"
                            value="{{ old('tanggal_sampai', isset($request) ? $request->tanggal_sampai : '') }}">
                        @error('tanggal_sampai')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search mr-1"></i> Tampilkan Laporan
            </button>
        </form>
    </div>
</div>

<!-- Hasil Laporan -->
@isset($laporanData)
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="fas fa-file-alt mr-2 text-primary"></i>
            <h6 class="m-0 font-weight-bold text-primary">Hasil Laporan</h6>
        </div>
        <div>
            <!-- Tombol Cetak -->
            <button onclick="cetakLaporan()" class="btn btn-success btn-sm mr-1">
                <i class="fas fa-print mr-1"></i> Cetak
            </button>

            <!-- Form Download PDF -->
            <form action="{{ route('laporan.download-pdf') }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="kategori" value="{{ $request->kategori }}">
                <input type="hidden" name="tanggal_dari" value="{{ $request->tanggal_dari }}">
                <input type="hidden" name="tanggal_sampai" value="{{ $request->tanggal_sampai }}">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </button>
            </form>
        </div>
    </div>
    
    <div class="card-body" id="area-cetak">
        
        <!-- Header Judul untuk Hasil Print -->
        <div class="text-center mb-4">
            <h5 class="font-weight-bold" style="line-height: 1.5; text-transform: uppercase;">
                {{ $judul }}<br>
                KAPAL NEGARA PRAJAPATI<br>
                TANGGAL {{ \Carbon\Carbon::parse($request->tanggal_dari)->format('d/m/Y') }} S/D {{ \Carbon\Carbon::parse($request->tanggal_sampai)->format('d/m/Y') }}
            </h5>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead class="thead-light">
                    <tr>
                        <th width="50px" class="text-center align-middle" rowspan="2">No</th>
                        <th class="align-middle" rowspan="2">Nama Barang</th>
                        <th class="text-center align-middle" rowspan="2">Satuan</th>
                        <th colspan="3" class="text-center">Banyaknya</th>
                        <th class="align-middle" rowspan="2">Keterangan</th>
                    </tr>
                    <tr>
                        <th class="text-center">Stok Sekarang</th>
                        <th class="text-center">Pemakaian</th>
                        <th class="text-center">Sisa Pemakaian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($laporanData as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item['nama_barang'] }}</td>
                        <td class="text-center">{{ $item['satuan'] }}</td>
                        <td class="text-center">
                            @if($item['stok_sekarang'] <= ($setting->stok_minimum ?? 5))
                                <span class="badge badge-danger" style="font-size: 0.9rem;">{{ $item['stok_sekarang'] }}</span>
                            @else
                                <span class="badge badge-success" style="font-size: 0.9rem;">{{ $item['stok_sekarang'] }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item['pemakaian'] }}</td>
                        <td class="text-center">{{ $item['sisa_pemakaian'] }}</td>
                        <td>{{ $item['keterangan'] ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Tidak ada data untuk filter ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endisset

@endsection

@section('scripts')
<script>
    function cetakLaporan() {
        const areaCetak = document.getElementById('area-cetak').innerHTML;
        const windowCetak = window.open('', '', 'height=800,width=1000');
        
        windowCetak.document.write('<html><head><title>Laporan Stok Barang</title>');
        // Memuat styling Bootstrap agar tabel rapi saat di print
        windowCetak.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">');
        // Tambahan styling khusus print untuk margin dan styling header tabel
        windowCetak.document.write('<style>body { padding: 20px; font-family: sans-serif; color: #000; } .table th { background-color: #f8f9fa !important; -webkit-print-color-adjust: exact; vertical-align: middle !important; } .badge { border: 1px solid #000; color: #000 !important; background-color: transparent !important; }</style>');
        windowCetak.document.write('</head><body>');
        windowCetak.document.write(areaCetak);
        windowCetak.document.write('</body></html>');
        windowCetak.document.close();
        
        // Timeout sedikit sebelum print agar styling ter-load sempurna
        setTimeout(() => {
            windowCetak.focus();
            windowCetak.print();
        }, 500);
    }
</script>
@endsection