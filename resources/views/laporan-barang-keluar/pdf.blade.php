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
                <th width="12%">Satuan</th>
                <th width="14%">Jumlah Keluar</th>
                <th width="20%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal_keluar)->format('d/m/Y') }}</td>
                <td>{{ $item->barang->nama_barang ?? '-' }}</td>
                <td class="text-center">{{ $item->barang->satuan->nama_satuan ?? '-' }}</td>
                <td class="text-center">{{ $item->jumlah_keluar }}</td>
                <td>{{ $item->keterangan ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data barang keluar untuk filter ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>