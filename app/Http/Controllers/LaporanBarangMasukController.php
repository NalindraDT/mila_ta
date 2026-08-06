<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangMasuk;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBarangMasukController extends Controller
{
    public function index()
    {
        return view('laporan-barang-masuk.index');
    }

    private function getData($kategori, $tanggal_dari, $tanggal_sampai)
    {
        // Hanya ambil transaksi yang sudah berstatus 'selesai' (stok benar-benar masuk)
        $query = BarangMasuk::with(['barang.satuan'])
                    ->where('status', 'selesai')
                    ->whereBetween('tanggal_masuk', [$tanggal_dari, $tanggal_sampai]);

        if ($kategori != 'semua') {
            $query->whereHas('barang', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        return $query->orderBy('tanggal_masuk')->get();
    }

    // Fungsi bantu PDF: ubah foto_bukti jadi base64
    private function getFotosBase64($items)
    {
        // Filter item yang memiliki foto_bukti
        $itemsWithFoto = $items->filter(fn($item) => !is_null($item->foto_bukti));

        return $itemsWithFoto->map(function ($item) {
            $path   = storage_path('app/public/' . $item->foto_bukti);
            $base64 = null;

            if (file_exists($path)) {
                $mime   = mime_content_type($path);
                $base64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
            }

            return [
                'base64'        => $base64,
                'tanggal_masuk' => $item->tanggal_masuk,
                'nama_barang'   => $item->barang->nama_barang ?? '-'
            ];
        })->filter(fn($f) => $f['base64'] !== null)->values();
    }

    private function getJudul($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $tglAwal  = \Carbon\Carbon::parse($tanggal_dari)->locale('id')->isoFormat('D MMMM YYYY');
        $tglAkhir = \Carbon\Carbon::parse($tanggal_sampai)->locale('id')->isoFormat('D MMMM YYYY');
        $periode  = $tglAwal == $tglAkhir ? $tglAwal : "$tglAwal s/d $tglAkhir";
        $jenis    = $kategori == 'semua' ? 'SEMUA JENIS' : strtoupper($kategori);

        return "BARANG MASUK KN PRAJAPATI\nTANGGAL $periode\nJENIS $jenis";
    }

    private function getFilename($kategori, $tanggal_dari, $tanggal_sampai, $ext)
    {
        $tglAwal  = \Carbon\Carbon::parse($tanggal_dari)->format('d-m-Y');
        $tglAkhir = \Carbon\Carbon::parse($tanggal_sampai)->format('d-m-Y');
        $periode  = $tglAwal == $tglAkhir ? $tglAwal : "{$tglAwal}_sd_{$tglAkhir}";
        $kategoriLabel = $kategori == 'semua' ? 'Semua' : str_replace(' ', '_', $kategori);

        return "Laporan_Barang_Masuk_{$kategoriLabel}_{$periode}.{$ext}";
    }

    public function generate(Request $request)
    {
        $request->validate([
            'kategori'       => 'required',
            'tanggal_dari'   => 'required|date|before_or_equal:today',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari|before_or_equal:today',
        ], [
            'kategori.required'              => 'Jenis wajib dipilih.',
            'tanggal_dari.required'          => 'Tanggal awal wajib diisi.',
            'tanggal_dari.before_or_equal'   => 'Tanggal awal tidak boleh melebihi tanggal hari ini.',
            'tanggal_sampai.required'        => 'Tanggal akhir wajib diisi.',
            'tanggal_sampai.after_or_equal'  => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'tanggal_sampai.before_or_equal' => 'Tanggal akhir tidak boleh melebihi tanggal hari ini.',
        ]);

        $items = $this->getData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);

        return view('laporan-barang-masuk.index', compact('items', 'judul', 'request'));
    }

    public function downloadPdf(Request $request)
    {
        $request->validate([
            'kategori'       => 'required',
            'tanggal_dari'   => 'required|date|before_or_equal:today',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari|before_or_equal:today',
        ]);

        $items    = $this->getData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $fotos    = $this->getFotosBase64($items);
        $judul    = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $filename = $this->getFilename($request->kategori, $request->tanggal_dari, $request->tanggal_sampai, 'pdf');

        $pdf = Pdf::loadView('laporan-barang-masuk.pdf', [
            'items' => $items,
            'fotos' => $fotos,
            'judul' => $judul,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}