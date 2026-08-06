<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\BarangKeluar;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index');
    }

    // Fungsi bantu ambil data laporan
    private function getLaporanData($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $query = Barang::with(['satuan']);
        if ($kategori != 'semua') {
            $query->where('kategori', $kategori);
        }
        $barangs = $query->get();

        return $barangs->map(function($barang) use ($tanggal_dari, $tanggal_sampai) {
            $keluarData = BarangKeluar::where('id_barang', $barang->id_barang)
                ->whereBetween('tanggal_keluar', [$tanggal_dari, $tanggal_sampai])
                ->get();

            $totalKeluar = $keluarData->sum('jumlah_keluar');
            $keterangan  = $keluarData->pluck('keterangan')
                            ->filter()->unique()->implode(', ');

            return [
                'nama_barang'    => $barang->nama_barang,
                'satuan'         => $barang->satuan->nama_satuan ?? '-',
                'stok_sekarang'  => $barang->stok,
                'stok_minimum'   => $barang->stok_minimum,
                'pemakaian'      => $totalKeluar,
                'sisa_pemakaian' => $barang->stok - $totalKeluar,
                'keterangan'     => $keterangan ?: '-',
            ];
        });
    }

    // Fungsi bantu buat judul laporan (ditampilkan di halaman & isi dokumen)
    private function getJudul($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $bulanAwal  = \Carbon\Carbon::parse($tanggal_dari)->locale('id')->isoFormat('MMMM YYYY');
        $bulanAkhir = \Carbon\Carbon::parse($tanggal_sampai)->locale('id')->isoFormat('MMMM YYYY');
        $periode    = $bulanAwal == $bulanAkhir ? strtoupper($bulanAwal) : strtoupper($bulanAwal . ' - ' . $bulanAkhir);

        if ($kategori == 'semua') {
            return "DAFTAR STOCK BARANG DI GUDANG DECK $periode";
        } else {
            return "DAFTAR STOCK BARANG " . strtoupper($kategori) . " DI GUDANG DECK $periode";
        }
    }

    // Fungsi bantu buat nama file download
    private function getFilename($kategori, $tanggal_dari, $tanggal_sampai, $ext)
    {
        $bulanAwal  = \Carbon\Carbon::parse($tanggal_dari)->locale('id')->isoFormat('MMMM_YYYY');
        $bulanAkhir = \Carbon\Carbon::parse($tanggal_sampai)->locale('id')->isoFormat('MMMM_YYYY');
        $periode    = $bulanAwal == $bulanAkhir ? $bulanAwal : $bulanAwal . '-' . $bulanAkhir;

        $kategoriLabel = $kategori == 'semua' ? 'Semua' : str_replace(' ', '_', $kategori);

        return "Laporan_Stok_Barang_{$kategoriLabel}_{$periode}.{$ext}";
    }

    public function generate(Request $request)
    {
        $request->validate([
            'kategori'       => 'required',
            'tanggal_dari'   => 'required|date|before_or_equal:today',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari|before_or_equal:today',
        ], [
            'kategori.required'              => 'Kategori wajib dipilih.',
            'tanggal_dari.required'          => 'Tanggal awal wajib diisi.',
            'tanggal_dari.before_or_equal'   => 'Tanggal awal tidak boleh melebihi tanggal hari ini.',
            'tanggal_sampai.required'        => 'Tanggal akhir wajib diisi.',
            'tanggal_sampai.after_or_equal'  => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'tanggal_sampai.before_or_equal' => 'Tanggal akhir tidak boleh melebihi tanggal hari ini.',
        ]);

        $laporanData = $this->getLaporanData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul       = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);

        return view('laporan.index', compact('laporanData', 'request', 'judul'));
    }

    public function downloadPdf(Request $request)
    {
        $request->validate([
            'kategori'       => 'required',
            'tanggal_dari'   => 'required|date|before_or_equal:today',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari|before_or_equal:today',
        ]);

        $laporanData = $this->getLaporanData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul       = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $filename    = $this->getFilename($request->kategori, $request->tanggal_dari, $request->tanggal_sampai, 'pdf');

        $pdf = Pdf::loadView('laporan.pdf', [
            'laporanData' => $laporanData,
            'judul'       => $judul,
            'request'     => $request,
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}