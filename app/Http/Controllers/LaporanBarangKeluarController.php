<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangKeluar;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanBarangKeluarController extends Controller
{
    public function index()
    {
        return view('laporan-barang-keluar.index');
    }

    private function getData($kategori, $tanggal_dari, $tanggal_sampai)
    {
        // Hanya ambil transaksi yang sudah di-ACC / Selesai
        $query = BarangKeluar::with(['barang.satuan'])
                    ->where('status', 'selesai')
                    ->whereBetween('tanggal_keluar', [$tanggal_dari, $tanggal_sampai]);

        if ($kategori != 'semua') {
            $query->whereHas('barang', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        return $query->orderBy('tanggal_keluar')->get();
    }

    private function getJudul($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $tglAwal  = \Carbon\Carbon::parse($tanggal_dari)->locale('id')->isoFormat('D MMMM YYYY');
        $tglAkhir = \Carbon\Carbon::parse($tanggal_sampai)->locale('id')->isoFormat('D MMMM YYYY');
        $periode  = $tglAwal == $tglAkhir ? $tglAwal : "$tglAwal s/d $tglAkhir";
        $jenis    = $kategori == 'semua' ? 'SEMUA JENIS' : strtoupper($kategori);

        // Tambahkan \n agar bisa dibaca sebagai baris baru (enter)
        return "LAPORAN BARANG KELUAR KN PRAJAPATI\nTANGGAL $periode\nJENIS $jenis";
    }

    private function getFilename($kategori, $tanggal_dari, $tanggal_sampai, $ext)
    {
        $tglAwal  = \Carbon\Carbon::parse($tanggal_dari)->format('d-m-Y');
        $tglAkhir = \Carbon\Carbon::parse($tanggal_sampai)->format('d-m-Y');
        $periode  = $tglAwal == $tglAkhir ? $tglAwal : "{$tglAwal}_sd_{$tglAkhir}";
        $kategoriLabel = $kategori == 'semua' ? 'Semua' : str_replace(' ', '_', $kategori);

        return "Laporan_Barang_Keluar_{$kategoriLabel}_{$periode}.{$ext}";
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

        return view('laporan-barang-keluar.index', compact('items', 'judul', 'request'));
    }

    public function downloadPdf(Request $request)
    {
        $request->validate([
            'kategori'       => 'required',
            'tanggal_dari'   => 'required|date|before_or_equal:today',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari|before_or_equal:today',
        ]);

        $items    = $this->getData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul    = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $filename = $this->getFilename($request->kategori, $request->tanggal_dari, $request->tanggal_sampai, 'pdf');

        $pdf = Pdf::loadView('laporan-barang-keluar.pdf', [
            'items' => $items,
            'judul' => $judul,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}