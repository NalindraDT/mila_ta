<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;
use App\Models\Barang;
use App\Models\Rak;
use App\Models\LogAktivitas;

class BarangMasukController extends Controller
{
    public function index()
    {
        // Ambil semua data
        $semuaBarangMasuk = BarangMasuk::with(['barang.satuan', 'rak.lokasi'])
                        ->orderBy('tanggal_masuk', 'desc')
                        ->get();

        $semuaBarangMasuk->each(function ($item) {
            $item->boleh_edit  = $this->bisaDiedit($item);
            $item->boleh_hapus = $this->isTransaksiTerbaru($item->id_barang, $item->created_at);
        });

        // Pecah data berdasarkan status untuk masing-masing Tab
        $pending  = $semuaBarangMasuk->where('status', 'pending');
        $accAdmin = $semuaBarangMasuk->where('status', 'acc_admin');
        $selesai  = $semuaBarangMasuk->where('status', 'selesai');
        $ditolak  = $semuaBarangMasuk->where('status', 'ditolak');

        return view('barang-masuk.index', compact('pending', 'accAdmin', 'selesai', 'ditolak'));
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
            'foto_bukti'    => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $barang = Barang::findOrFail($request->id_barang);

        $pathFoto = null;
        if ($request->hasFile('foto_bukti')) {
            $pathFoto = $request->file('foto_bukti')->store('barang-masuk-foto', 'public');
        }

        $transaksi = BarangMasuk::create([
            'id_barang'     => $request->id_barang,
            'id_rak'        => $request->id_rak,
            'jumlah_masuk'  => $request->jumlah_masuk,
            'tanggal_masuk' => $request->tanggal_masuk,
            'stok_sesudah'  => $barang->stok, 
            'status'        => 'pending',
            'foto_bukti'    => $pathFoto 
        ]);

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Membuat request barang masuk (ID: ' . $transaksi->id_barang_masuk . ') beserta foto bukti'
        ]);

        return redirect()->route('barang-masuk.index')->with('success', 'Request barang masuk berhasil dibuat, menunggu verifikasi.');
    }

    public function edit(string $id_barang_masuk)
    {
        $barangMasuk = BarangMasuk::with('barang')->findOrFail($id_barang_masuk);

        // Kunci form jika belum waktunya (belum ACC Admin) atau sudah final
        if (!$this->bisaDiedit($barangMasuk)) {
            $pesan = $barangMasuk->status == 'pending'
                ? 'Transaksi ini belum di-ACC oleh Admin Gudang. Silakan tunggu verifikasi Admin Gudang terlebih dahulu.'
                : 'Transaksi ini sudah diverifikasi final dan tidak dapat diubah lagi.';

            return redirect()->route('barang-masuk.index')->with('error', $pesan);
        }

        return view('barang-masuk.edit', compact('barangMasuk'));
    }

    public function update(Request $request, string $id_barang_masuk)
    {
        $barangMasuk = BarangMasuk::findOrFail($id_barang_masuk);
        
        if (!$this->bisaDiedit($barangMasuk)) {
            $pesan = $barangMasuk->status == 'pending'
                ? 'Transaksi ini belum di-ACC oleh Admin Gudang. Silakan tunggu verifikasi Admin Gudang terlebih dahulu.'
                : 'Transaksi ini sudah diverifikasi final dan tidak dapat diubah lagi.';

            return redirect()->route('barang-masuk.index')->with('error', $pesan);
        }

        if (auth()->user()->role == 'Kepala Gudang') {
            $request->validate([
                'jumlah_masuk'  => 'required|integer|min:1',
                'tanggal_masuk' => 'required|date|before_or_equal:today',
            ]);
        }

        $barang = Barang::findOrFail($barangMasuk->id_barang);
        
        $statusLama = $barangMasuk->status;
        $statusBaru = $request->input('status', $barangMasuk->status);
        $jumlahLama = $barangMasuk->jumlah_masuk;

        if (auth()->user()->role == 'Admin Gudang') {
            $jumlahBaru  = $jumlahLama;
            $tanggalBaru = $barangMasuk->tanggal_masuk;
        } else {
            $jumlahBaru  = $request->jumlah_masuk;
            $tanggalBaru = $request->tanggal_masuk;
        }

        $barangMasuk->jumlah_masuk  = $jumlahBaru;
        $barangMasuk->tanggal_masuk = $tanggalBaru;
        
        if ($request->has('catatan_verifikasi')) {
            $barangMasuk->catatan_verifikasi = $request->catatan_verifikasi;
        }
        $barangMasuk->status = $statusBaru;

        if ($statusBaru == 'selesai') {
            if ($statusLama != 'selesai') {
                $barang->stok += $jumlahBaru;
            } else {
                $selisih = $jumlahBaru - $jumlahLama;
                $barang->stok += $selisih;
            }
            $barang->save();
            $barangMasuk->stok_sesudah = $barang->stok;
        } elseif ($statusLama == 'selesai' && $statusBaru != 'selesai') {
            $barang->stok -= $jumlahLama;
            $barang->save();
            $barangMasuk->stok_sesudah = $barang->stok;
        }

        $barangMasuk->save();

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Memperbarui/Verifikasi barang masuk (ID: ' . $barangMasuk->id_barang_masuk . ') menjadi status: ' . $statusBaru
        ]);

        return redirect()->route('barang-masuk.index')->with('success', 'Data barang masuk berhasil diverifikasi.');
    }

    public function destroy(string $id_barang_masuk)
    {
        $barangMasuk = BarangMasuk::findOrFail($id_barang_masuk);

        // ATURAN 1: Untuk Staff (Hanya boleh hapus jika status masih pending)
        if (auth()->user()->role == 'Staff') {
            if ($barangMasuk->status != 'pending') {
                return redirect()->route('barang-masuk.index')
                    ->with('error', 'Request tidak bisa dibatalkan karena sudah diproses oleh Admin/Kepala Gudang.');
            }
        } 
        // ATURAN 2: Untuk Kepala Gudang
        else if (auth()->user()->role == 'Kepala Gudang') {
            // Kepala Gudang hanya boleh hapus jika ini transaksi terbaru di barang tersebut
            if (!$this->isTransaksiTerbaru($barangMasuk->id_barang, $barangMasuk->created_at)) {
                return redirect()->route('barang-masuk.index')
                    ->with('error', 'Transaksi ini tidak bisa dihapus karena sudah ada transaksi lain (masuk/keluar) setelahnya.');
            }

            // Jika statusnya sudah selesai, kembalikan/kurangi stok barang
            if ($barangMasuk->status == 'selesai') {
                $barang = Barang::findOrFail($barangMasuk->id_barang);
                $barang->stok -= $barangMasuk->jumlah_masuk;
                $barang->save();
            }
        } 
        // ATURAN 3: Admin Gudang (Kerani) tidak punya hak hapus sama sekali
        else {
            return redirect()->route('barang-masuk.index')
                ->with('error', 'Anda tidak memiliki hak akses untuk menghapus data ini.');
        }

        // Hapus file fisik foto dari storage jika ada
        if ($barangMasuk->foto_bukti && Storage::disk('public')->exists($barangMasuk->foto_bukti)) {
            Storage::disk('public')->delete($barangMasuk->foto_bukti);
        }

        $barangMasuk->delete();

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Menghapus/Membatalkan request barang masuk (ID: ' . $id_barang_masuk . ')'
        ]);

        return redirect()->route('barang-masuk.index')->with('success', 'Data request barang masuk berhasil dihapus.');
    }

    // ================== VERIFIKASI CEPAT ADMIN GUDANG ==================

    public function verifikasiAdmin(Request $request, $id_barang_masuk)
    {
        if (auth()->user()->role != 'Admin Gudang') {
            return back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'status'             => 'required|in:acc_admin,ditolak',
            'catatan_verifikasi' => 'nullable|string'
        ]);

        $barangMasuk = BarangMasuk::findOrFail($id_barang_masuk);

        if ($barangMasuk->status != 'pending') {
            return back()->with('error', 'Transaksi ini sudah diverifikasi sebelumnya.');
        }

        $barangMasuk->status = $request->status;
        if ($request->has('catatan_verifikasi')) {
            $barangMasuk->catatan_verifikasi = $request->catatan_verifikasi;
        }
        $barangMasuk->save();

        $statusText = $request->status == 'acc_admin' ? 'ACC' : 'Tolak';

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Melakukan Verifikasi Cepat (Status: '. $statusText .') pada barang masuk ID: ' . $id_barang_masuk
        ]);

        return back()->with('success', 'Transaksi berhasil di-' . $statusText . '.');
    }

    // ================== HELPER ==================

    private function isTransaksiTerbaru($id_barang, $createdAt)
    {
        $lastMasuk  = BarangMasuk::where('id_barang', $id_barang)->max('created_at');
        $lastKeluar = BarangKeluar::where('id_barang', $id_barang)->max('created_at');
        $lastOverall = max($lastMasuk, $lastKeluar);
        return $createdAt == $lastOverall;
    }

    private function bisaDiedit($transaksi)
    {
        // Kepala Gudang HANYA boleh verifikasi jika transaksi sudah di-ACC oleh Admin Gudang
        // (mencegah alur loncat dari Pending langsung ke Selesai/Ditolak)
        if (auth()->check() && auth()->user()->role == 'Kepala Gudang') {
            return $transaksi->status == 'acc_admin';
        }

        // Default (role lain): boleh selama belum berstatus final
        return !in_array($transaksi->status, ['selesai', 'ditolak']);
    }
}