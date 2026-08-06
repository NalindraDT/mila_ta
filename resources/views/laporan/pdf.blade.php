<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 11px;
            color: #000;
        }
        h3 {
            text-align: center;
            margin: 0 0 4px 0;
            font-size: 14px;
        }
        p.sub {
            text-align: center;
            margin: 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
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
    <h3>{{ $judul }}</h3>
    <p class="sub">Kapal Negara Prajapati</p>
    <p class="sub">
        Periode: {{ \Carbon\Carbon::parse($request->tanggal_dari)->format('d/m/Y') }}
        s/d {{ \Carbon\Carbon::parse($request->tanggal_sampai)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th width="5%" rowspan="2">No</th>
                <th rowspan="2">Nama Barang</th>
                <th width="10%" rowspan="2">Satuan</th>
                <th colspan="3">Banyaknya</th>
                <th rowspan="2">Keterangan</th>
            </tr>
            <tr>
                <th>Stok Sekarang</th>
                <th>Pemakaian</th>
                <th>Sisa Pemakaian</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($laporanData as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item['nama_barang'] }}</td>
                <td class="text-center">{{ $item['satuan'] }}</td>
                <td class="text-center">{{ $item['stok_sekarang'] }}</td>
                <td class="text-center">{{ $item['pemakaian'] }}</td>
                <td class="text-center">{{ $item['sisa_pemakaian'] }}</td>
                <td>{{ $item['keterangan'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>