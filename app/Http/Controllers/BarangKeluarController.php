<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\Barang;

class BarangKeluarController extends Controller
{
    public function index()
    {
        $barangKeluar = BarangKeluar::with(['barang.satuan', 'barang.rak.lokasi'])
                        ->orderBy('tanggal_keluar', 'desc')
                        ->get();

        $barangKeluar->each(function ($item) {
            $item->boleh_hapus = $this->isTransaksiTerbaru($item->id_barang, $item->created_at);
            $item->boleh_edit  = $item->boleh_hapus;
        });

        return view('barang-keluar.index', compact('barangKeluar'));
    }

    public function create()
    {
        $barang = Barang::with(['satuan', 'rak.lokasi'])->get();
        return view('barang-keluar.create', compact('barang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang'      => 'required',
            'jumlah_keluar'  => 'required|integer|min:1',
            'tanggal_keluar' => 'required|date|before_or_equal:today',
            'keterangan'     => 'nullable',
        ], [
            'id_barang.required'          => 'Barang wajib dipilih.',
            'jumlah_keluar.required'      => 'Jumlah keluar wajib diisi.',
            'jumlah_keluar.integer'       => 'Jumlah keluar harus berupa angka.',
            'jumlah_keluar.min'           => 'Jumlah keluar minimal 1.',
            'tanggal_keluar.required'     => 'Tanggal wajib diisi.',
            'tanggal_keluar.date'         => 'Format tanggal tidak valid.',
            'tanggal_keluar.before_or_equal' => 'Tanggal tidak boleh melebihi tanggal hari ini.',
        ]);

        $barang = Barang::findOrFail($request->id_barang);
        if ($request->jumlah_keluar > $barang->stok) {
            return back()->withErrors([
                'jumlah_keluar' => 'Jumlah keluar melebihi stok yang tersedia (' . $barang->stok . ').'
            ])->withInput();
        }

        $barang->stok -= $request->jumlah_keluar;
        $barang->save();

        BarangKeluar::create([
            'id_barang'           => $request->id_barang,
            'jumlah_keluar'       => $request->jumlah_keluar,
            'tanggal_keluar'      => $request->tanggal_keluar,
            'keterangan'          => $request->keterangan ?: null,
            'stok_sesudah_keluar' => $barang->stok,
        ]);

        return redirect()->route('barang-keluar.index')->with('success', 'Data barang keluar berhasil ditambahkan');
    }

    public function edit(string $id_barang_keluar)
    {
        $barangKeluar = BarangKeluar::with('barang')->findOrFail($id_barang_keluar);

        if (!$this->bisaDiedit($barangKeluar)) {
            return redirect()->route('barang-keluar.index')
                ->with('error', 'Transaksi ini tidak bisa diedit lagi karena sudah ada transaksi lain setelahnya untuk barang ini.');
        }

        return view('barang-keluar.edit', compact('barangKeluar'));
    }

    public function update(Request $request, string $id_barang_keluar)
    {
        $barangKeluar = BarangKeluar::findOrFail($id_barang_keluar);

        if (!$this->bisaDiedit($barangKeluar)) {
            return redirect()->route('barang-keluar.index')
                ->with('error', 'Transaksi ini tidak bisa diedit lagi karena sudah ada transaksi lain setelahnya untuk barang ini.');
        }

        $request->validate([
            'jumlah_keluar'  => 'required|integer|min:1',
            'tanggal_keluar' => 'required|date|before_or_equal:today',
            'keterangan'     => 'nullable',
        ], [
            'jumlah_keluar.required'         => 'Jumlah keluar wajib diisi.',
            'jumlah_keluar.integer'          => 'Jumlah keluar harus berupa angka.',
            'jumlah_keluar.min'              => 'Jumlah keluar minimal 1.',
            'tanggal_keluar.required'        => 'Tanggal wajib diisi.',
            'tanggal_keluar.before_or_equal' => 'Tanggal tidak boleh melebihi tanggal hari ini.',
        ]);

        $barang = Barang::findOrFail($barangKeluar->id_barang);

        // Kembalikan dulu stok lama secara virtual, baru cek & kurangi sesuai jumlah baru
        $stokTersedia = $barang->stok + $barangKeluar->jumlah_keluar;

        if ($request->jumlah_keluar > $stokTersedia) {
            return back()->withErrors([
                'jumlah_keluar' => 'Jumlah keluar melebihi stok yang tersedia (' . $stokTersedia . ').'
            ])->withInput();
        }

        $barang->stok = $stokTersedia - $request->jumlah_keluar;
        $barang->save();

        $barangKeluar->jumlah_keluar       = $request->jumlah_keluar;
        $barangKeluar->tanggal_keluar      = $request->tanggal_keluar;
        $barangKeluar->keterangan          = $request->keterangan ?: null;
        $barangKeluar->stok_sesudah_keluar = $barang->stok;
        $barangKeluar->save();

        return redirect()->route('barang-keluar.index')->with('success', 'Data barang keluar berhasil diupdate');
    }

    public function destroy(string $id_barang_keluar)
    {
        $barangKeluar = BarangKeluar::findOrFail($id_barang_keluar);

        if (!$this->isTransaksiTerbaru($barangKeluar->id_barang, $barangKeluar->created_at)) {
            return redirect()->route('barang-keluar.index')
                ->with('error', 'Transaksi ini tidak bisa dihapus karena sudah ada transaksi lain (masuk/keluar) setelahnya untuk barang ini.');
        }

        $barang = Barang::findOrFail($barangKeluar->id_barang);
        $barang->stok += $barangKeluar->jumlah_keluar;
        $barang->save();

        $barangKeluar->delete();

        return redirect()->route('barang-keluar.index')->with('success', 'Data barang keluar berhasil dihapus');
    }

    // ================== HELPER: TRANSAKSI TERBARU ==================

    private function isTransaksiTerbaru($id_barang, $createdAt)
    {
        $lastMasuk  = BarangMasuk::where('id_barang', $id_barang)->max('created_at');
        $lastKeluar = BarangKeluar::where('id_barang', $id_barang)->max('created_at');

        $lastOverall = max($lastMasuk, $lastKeluar);

        return $createdAt == $lastOverall;
    }

    private function bisaDiedit($transaksi)
    {
        return $this->isTransaksiTerbaru($transaksi->id_barang, $transaksi->created_at);
    }
}