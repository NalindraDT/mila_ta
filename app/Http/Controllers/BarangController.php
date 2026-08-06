<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Rak;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;

class BarangController extends Controller
{
    public function index()
    {
        $consumable = Barang::with(['satuan', 'rak.lokasi'])
                        ->where('kategori', 'Consumable')
                        ->get();
        $sukuCadang = Barang::with(['satuan', 'rak.lokasi'])
                        ->where('kategori', 'Suku Cadang')
                        ->get();

        $tandaiPemakaian = function ($items) {
            return $items->map(function ($item) {
                $item->sedang_dipakai = BarangMasuk::where('id_barang', $item->id_barang)->exists()
                    || BarangKeluar::where('id_barang', $item->id_barang)->exists();
                return $item;
            });
        };

        $consumable = $tandaiPemakaian($consumable);
        $sukuCadang = $tandaiPemakaian($sukuCadang);

        return view('barang.index', compact('consumable', 'sukuCadang'));
    }

    public function create()
    {
        $satuan = Satuan::all();
        $rak    = Rak::with('lokasi')->where('status', 'Aktif')->get();
        return view('barang.create', compact('satuan', 'rak'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang'     => 'nullable|unique:barang,id_barang',
            'nama_barang'   => 'required|unique:barang,nama_barang',
            'id_satuan'     => 'required',
            'id_rak'        => 'required',
            'stok'          => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:1',
            'kategori'      => 'required',
            'deskripsi'     => 'nullable',
        ], [
            'id_barang.unique'      => 'ID Barang sudah dipakai, generate/scan ulang ya.',
            'nama_barang.required'  => 'Nama barang wajib diisi.',
            'nama_barang.unique'    => 'Nama barang sudah terdaftar, tidak boleh sama.',
            'id_satuan.required'    => 'Satuan wajib dipilih.',
            'id_rak.required'       => 'Rak wajib dipilih.',
            'stok.required'         => 'Stok wajib diisi.',
            'stok.integer'          => 'Stok harus berupa angka.',
            'stok.min'              => 'Stok minimal 0.',
            'stok_minimum.required' => 'Batas minimum stok wajib diisi.',
            'stok_minimum.integer'  => 'Batas minimum stok harus berupa angka.',
            'stok_minimum.min'      => 'Batas minimum stok minimal 1.',
            'kategori.required'     => 'Kategori wajib dipilih.',
        ]);

        $idBarang = $request->filled('id_barang')
            ? $request->id_barang
            : $this->generateNextId();

        Barang::create([
            'id_barang'    => $idBarang,
            'nama_barang'  => $request->nama_barang,
            'id_satuan'    => $request->id_satuan,
            'id_rak'       => $request->id_rak,
            'stok'         => $request->stok,
            'stok_minimum' => $request->stok_minimum,
            'kategori'     => $request->kategori,
            'deskripsi'    => $request->deskripsi ?? '',
        ]);

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil ditambahkan');
    }

    public function edit(string $id_barang)
    {
        $barang = Barang::findOrFail($id_barang);
        $satuan = Satuan::all();
        $rak    = Rak::with('lokasi')
                    ->where('status', 'Aktif')
                    ->orWhere('id_rak', $barang->id_rak)
                    ->get();
        return view('barang.edit', compact('barang', 'satuan', 'rak'));
    }

    public function update(Request $request, string $id_barang)
    {
        $barang = Barang::findOrFail($id_barang);

        $request->validate([
            'nama_barang'   => 'required|unique:barang,nama_barang,' . $id_barang . ',id_barang',
            'id_satuan'     => 'required',
            'id_rak'        => 'required',
            'stok'          => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:1',
            'kategori'      => 'required',
            'deskripsi'     => 'nullable',
        ], [
            'nama_barang.required'  => 'Nama barang wajib diisi.',
            'nama_barang.unique'    => 'Nama barang sudah terdaftar, tidak boleh sama.',
            'id_satuan.required'    => 'Satuan wajib dipilih.',
            'id_rak.required'       => 'Rak wajib dipilih.',
            'stok.required'         => 'Stok wajib diisi.',
            'stok.integer'          => 'Stok harus berupa angka.',
            'stok.min'              => 'Stok minimal 0.',
            'stok_minimum.required' => 'Batas minimum stok wajib diisi.',
            'stok_minimum.integer'  => 'Batas minimum stok harus berupa angka.',
            'stok_minimum.min'      => 'Batas minimum stok minimal 1.',
            'kategori.required'     => 'Kategori wajib dipilih.',
        ]);

        $barang->nama_barang   = $request->nama_barang;
        $barang->id_satuan     = $request->id_satuan;
        $barang->id_rak        = $request->id_rak;
        $barang->stok          = $request->stok;
        $barang->stok_minimum  = $request->stok_minimum;
        $barang->kategori      = $request->kategori;
        $barang->deskripsi     = $request->deskripsi ?? '';
        $barang->save();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil diupdate');
    }

    public function destroy(string $id_barang)
    {
        $barang = Barang::findOrFail($id_barang);

        $dipakai = BarangMasuk::where('id_barang', $id_barang)->exists()
            || BarangKeluar::where('id_barang', $id_barang)->exists();

        if ($dipakai) {
            return redirect()->route('barang.index')
                ->with('error', 'Barang tidak bisa dihapus karena sudah memiliki riwayat transaksi masuk/keluar.');
        }

        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Data barang berhasil dihapus');
    }

    public function generateId()
    {
        return response()->json(['id_barang' => $this->generateNextId()]);
    }

    private function generateNextId(): string
    {
        $tahun      = date('Y');
        $lastBarang = Barang::where('id_barang', 'like', "BRG-$tahun-%")
                        ->orderBy('id_barang', 'desc')
                        ->first();

        if ($lastBarang) {
            $lastNumber = intval(substr($lastBarang->id_barang, -4));
            $newNumber  = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "BRG-$tahun-$newNumber";
    }
}