<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\BarangMasuk;
use App\Models\BarangMasukFoto;
use App\Models\BarangKeluar;
use App\Models\Barang;
use App\Models\Rak;

class BarangMasukController extends Controller
{
    public function index()
    {
        $barangMasuk = BarangMasuk::with(['barang.satuan', 'rak.lokasi'])
                        ->orderBy('tanggal_masuk', 'desc')
                        ->get();

        // Tandai tiap baris: apakah dia transaksi terbaru untuk barangnya (boleh diedit/dihapus)
        $barangMasuk->each(function ($item) {
            $item->boleh_hapus = $this->isTransaksiTerbaru($item->id_barang, $item->created_at);
            $item->boleh_edit  = $item->boleh_hapus;
        });

        return view('barang-masuk.index', compact('barangMasuk'));
    }

    public function create()
    {
        $barang = Barang::all();
        $rak    = Rak::with('lokasi')->get();
        return view('barang-masuk.create', compact('barang', 'rak'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_barang'     => 'required',
            'id_rak'        => 'required',
            'jumlah_masuk'  => 'required|integer|min:1',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
        ], [
            'id_barang.required'         => 'Barang wajib dipilih.',
            'id_rak.required'            => 'Rak wajib dipilih.',
            'jumlah_masuk.required'      => 'Jumlah masuk wajib diisi.',
            'jumlah_masuk.integer'       => 'Jumlah masuk harus berupa angka.',
            'jumlah_masuk.min'           => 'Jumlah masuk minimal 1.',
            'tanggal_masuk.required'     => 'Tanggal wajib diisi.',
            'tanggal_masuk.date'         => 'Format tanggal tidak valid.',
            'tanggal_masuk.before_or_equal' => 'Tanggal tidak boleh melebihi tanggal hari ini.',
        ]);

        // Update stok barang dulu
        $barang = Barang::findOrFail($request->id_barang);
        $barang->stok += $request->jumlah_masuk;
        $barang->save();

        // Baru simpan barang masuk + stok sesudah
        BarangMasuk::create([
            'id_barang'     => $request->id_barang,
            'id_rak'        => $request->id_rak,
            'jumlah_masuk'  => $request->jumlah_masuk,
            'tanggal_masuk' => $request->tanggal_masuk,
            'stok_sesudah'  => $barang->stok,
        ]);

        return redirect()->route('barang-masuk.index')->with('success', 'Data barang masuk berhasil ditambahkan');
    }

    public function edit(string $id_barang_masuk)
    {
        $barangMasuk = BarangMasuk::with('barang')->findOrFail($id_barang_masuk);

        if (!$this->bisaDiedit($barangMasuk)) {
            return redirect()->route('barang-masuk.index')
                ->with('error', 'Transaksi ini tidak bisa diedit lagi karena sudah ada transaksi lain setelahnya untuk barang ini.');
        }

        return view('barang-masuk.edit', compact('barangMasuk'));
    }

    public function update(Request $request, string $id_barang_masuk)
    {
        $barangMasuk = BarangMasuk::findOrFail($id_barang_masuk);

        if (!$this->bisaDiedit($barangMasuk)) {
            return redirect()->route('barang-masuk.index')
                ->with('error', 'Transaksi ini tidak bisa diedit lagi karena sudah ada transaksi lain setelahnya untuk barang ini.');
        }

        $request->validate([
            'jumlah_masuk'  => 'required|integer|min:1',
            'tanggal_masuk' => 'required|date|before_or_equal:today',
        ], [
            'jumlah_masuk.required'         => 'Jumlah masuk wajib diisi.',
            'jumlah_masuk.integer'          => 'Jumlah masuk harus berupa angka.',
            'jumlah_masuk.min'              => 'Jumlah masuk minimal 1.',
            'tanggal_masuk.required'        => 'Tanggal wajib diisi.',
            'tanggal_masuk.before_or_equal' => 'Tanggal tidak boleh melebihi tanggal hari ini.',
        ]);

        $barang = Barang::findOrFail($barangMasuk->id_barang);

        // Selisih jumlah lama vs baru, supaya stok tetap akurat
        $selisih = $request->jumlah_masuk - $barangMasuk->jumlah_masuk;
        $barang->stok += $selisih;
        $barang->save();

        $barangMasuk->jumlah_masuk  = $request->jumlah_masuk;
        $barangMasuk->tanggal_masuk = $request->tanggal_masuk;
        $barangMasuk->stok_sesudah  = $barang->stok;
        $barangMasuk->save();

        return redirect()->route('barang-masuk.index')->with('success', 'Data barang masuk berhasil diupdate');
    }

    public function destroy(string $id_barang_masuk)
    {
        $barangMasuk = BarangMasuk::findOrFail($id_barang_masuk);

        if (!$this->isTransaksiTerbaru($barangMasuk->id_barang, $barangMasuk->created_at)) {
            return redirect()->route('barang-masuk.index')
                ->with('error', 'Transaksi ini tidak bisa dihapus karena sudah ada transaksi lain (masuk/keluar) setelahnya untuk barang ini.');
        }

        // Kurangi stok barang
        $barang = Barang::findOrFail($barangMasuk->id_barang);
        $barang->stok -= $barangMasuk->jumlah_masuk;
        $barang->save();

        $barangMasuk->delete();

        return redirect()->route('barang-masuk.index')->with('success', 'Data barang masuk berhasil dihapus');
    }

    // ================== FOTO BUKTI BARANG MASUK ==================

    public function fotoIndex()
    {
        $fotos = BarangMasukFoto::orderBy('created_at', 'desc')->get();
        return view('barang-masuk.foto', compact('fotos'));
    }

    public function fotoStore(Request $request)
    {
        $request->validate([
            'tanggal_masuk' => 'required|date|before_or_equal:today',
            'kategori'      => 'required|in:Consumable,Suku Cadang',
            'foto'          => 'required|array|min:1',
            'foto.*'        => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'tanggal_masuk.required'         => 'Tanggal wajib diisi.',
            'tanggal_masuk.before_or_equal'  => 'Tanggal tidak boleh melebihi tanggal hari ini.',
            'kategori.required'              => 'Kategori wajib dipilih.',
            'foto.required'                  => 'Pilih minimal 1 foto.',
            'foto.*.image'                   => 'File harus berupa gambar.',
            'foto.*.mimes'                   => 'Format foto harus JPG, JPEG, atau PNG.',
            'foto.*.max'                      => 'Ukuran tiap foto maksimal 5MB.',
        ]);

        foreach ($request->file('foto') as $file) {
            $path = $file->store('barang-masuk-foto', 'public');

            BarangMasukFoto::create([
                'tanggal_masuk' => $request->tanggal_masuk,
                'kategori'      => $request->kategori,
                'path_foto'     => $path,
            ]);
        }

        return redirect()->route('barang-masuk.foto.index')->with('success', 'Foto bukti berhasil diupload');
    }

    public function fotoDestroy(string $id_foto)
    {
        $foto = BarangMasukFoto::findOrFail($id_foto);

        if (Storage::disk('public')->exists($foto->path_foto)) {
            Storage::disk('public')->delete($foto->path_foto);
        }

        $foto->delete();

        return redirect()->route('barang-masuk.foto.index')->with('success', 'Foto berhasil dihapus');
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