<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ str_replace("\n", " ", $judul) }}</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        h3 {
            text-align: center;
            margin: 0 0 15px 0;
            font-size: 13px;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
        }
        th {
            background-color: #D9D9D9;
            text-align: center;
            font-weight: bold;
        }
        td.text-center {
            text-align: center;
        }
        .foto-section {
            margin-top: 25px;
        }
        .foto-title {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .foto-item {
            display: inline-block;
            width: 46%;
            margin: 1.5% 1.5% 15px 1.5%;
            text-align: center;
            vertical-align: top;
        }
        .foto-item img {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border: 1px solid #999;
        }
        .foto-caption {
            font-size: 10px;
            color: #333;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <h3>{!! nl2br(e($judul)) !!}</h3>

    <table>
        <thead>
            <tr>
                <th width="6%">No</th>
                <th width="14%">Tanggal</th>
                <th>Nama Barang</th>
                <th width="14%">Satuan</th>
                <th width="16%">Jumlah Masuk</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_masuk)->format('d/m/Y') }}</td>
                <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                <td class="text-center">{{ $item->barang->satuan->nama_satuan ?? '-' }}</td>
                <td class="text-center">{{ $item->jumlah_masuk }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada data barang masuk untuk filter ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($fotos->count() > 0)
    <div class="foto-section">
        <div class="foto-title">Lampiran Foto Bukti Penerimaan</div>
        @foreach ($fotos as $foto)
        <div class="foto-item">
            <img src="{{ $foto['base64'] }}" alt="Foto Bukti">
            <div class="foto-caption">
                <strong>{{ \Carbon\Carbon::parse($foto['tanggal_masuk'])->format('d/m/Y') }}</strong><br>
                {{ $foto['nama_barang'] }}
            </div>
        </div>
        @endforeach
    </div>
    @endif
</body>
</html>