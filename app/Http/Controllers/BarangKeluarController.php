<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangKeluar;
use App\Models\BarangMasuk;
use App\Models\Barang;
use App\Models\LogAktivitas;

class BarangKeluarController extends Controller
{
    public function index()
    {
        // Ambil semua data
        $semuaBarangKeluar = BarangKeluar::with(['barang.satuan', 'barang.rak.lokasi'])
                        ->orderBy('tanggal_keluar', 'desc')
                        ->get();

        $semuaBarangKeluar->each(function ($item) {
            $item->boleh_edit  = $this->bisaDiedit($item);
            $item->boleh_hapus = $this->isTransaksiTerbaru($item->id_barang, $item->created_at);
        });

        // Pecah data berdasarkan status untuk masing-masing Tab (sama seperti Barang Masuk)
        $pending  = $semuaBarangKeluar->where('status', 'pending');
        $accAdmin = $semuaBarangKeluar->where('status', 'acc_admin');
        $selesai  = $semuaBarangKeluar->where('status', 'selesai');
        $ditolak  = $semuaBarangKeluar->where('status', 'ditolak');

        return view('barang-keluar.index', compact('pending', 'accAdmin', 'selesai', 'ditolak'));
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
            'id_barang.required'             => 'Barang wajib dipilih.',
            'jumlah_keluar.required'         => 'Jumlah keluar wajib diisi.',
            'jumlah_keluar.integer'          => 'Jumlah keluar harus berupa angka.',
            'jumlah_keluar.min'              => 'Jumlah keluar minimal 1.',
            'tanggal_keluar.required'        => 'Tanggal wajib diisi.',
            'tanggal_keluar.date'            => 'Format tanggal tidak valid.',
            'tanggal_keluar.before_or_equal' => 'Tanggal tidak boleh melebihi tanggal hari ini.',
        ]);

        $barang = Barang::findOrFail($request->id_barang);

        // Validasi awal (early warning) supaya Staff tidak request jumlah yang jelas2
        // melebihi stok saat ini. Stok BELUM dipotong di sini — baru benar-benar
        // dipotong nanti saat status transaksi menjadi 'selesai' (final oleh Kepala Gudang).
        if ($request->jumlah_keluar > $barang->stok) {
            return back()->withErrors([
                'jumlah_keluar' => 'Jumlah keluar melebihi stok yang tersedia (' . $barang->stok . ').'
            ])->withInput();
        }

        $transaksi = BarangKeluar::create([
            'id_barang'      => $request->id_barang,
            'jumlah_keluar'  => $request->jumlah_keluar,
            'tanggal_keluar' => $request->tanggal_keluar,
            'keterangan'     => $request->keterangan ?: null,
            'status'         => 'pending',
        ]);

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Membuat request barang keluar (ID: ' . $transaksi->id_barang_keluar . ')'
        ]);

        return redirect()->route('barang-keluar.index')->with('success', 'Request barang keluar berhasil dibuat, menunggu verifikasi.');
    }

    public function edit(string $id_barang_keluar)
    {
        $barangKeluar = BarangKeluar::with('barang')->findOrFail($id_barang_keluar);

        // Kunci form jika belum di-ACC Admin Gudang, atau sudah final
        if (!$this->bisaDiedit($barangKeluar)) {
            $pesan = $barangKeluar->status == 'pending'
                ? 'Transaksi ini belum di-ACC oleh Admin Gudang. Silakan tunggu verifikasi Admin Gudang terlebih dahulu.'
                : 'Transaksi ini sudah diverifikasi final dan tidak dapat diubah lagi.';

            return redirect()->route('barang-keluar.index')->with('error', $pesan);
        }

        return view('barang-keluar.edit', compact('barangKeluar'));
    }

    public function update(Request $request, string $id_barang_keluar)
    {
        $barangKeluar = BarangKeluar::findOrFail($id_barang_keluar);

        if (!$this->bisaDiedit($barangKeluar)) {
            $pesan = $barangKeluar->status == 'pending'
                ? 'Transaksi ini belum di-ACC oleh Admin Gudang. Silakan tunggu verifikasi Admin Gudang terlebih dahulu.'
                : 'Transaksi ini sudah diverifikasi final dan tidak dapat diubah lagi.';

            return redirect()->route('barang-keluar.index')->with('error', $pesan);
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

        $statusBaru = $request->input('status', $barangKeluar->status);
        $jumlahBaru = $request->jumlah_keluar;

        // Stok baru benar-benar dipotong SAAT transaksi difinalkan jadi 'selesai'.
        // Validasi ulang di sini karena stok bisa saja sudah berubah sejak request dibuat.
        if ($statusBaru == 'selesai') {
            if ($jumlahBaru > $barang->stok) {
                return back()->withErrors([
                    'jumlah_keluar' => 'Jumlah keluar melebihi stok yang tersedia (' . $barang->stok . ').'
                ])->withInput();
            }

            $barang->stok -= $jumlahBaru;
            $barang->save();
            $barangKeluar->stok_sesudah_keluar = $barang->stok;
        }

        $barangKeluar->jumlah_keluar  = $jumlahBaru;
        $barangKeluar->tanggal_keluar = $request->tanggal_keluar;
        $barangKeluar->keterangan     = $request->keterangan ?: null;

        if ($request->has('catatan_verifikasi')) {
            $barangKeluar->catatan_verifikasi = $request->catatan_verifikasi;
        }

        $barangKeluar->status = $statusBaru;
        $barangKeluar->save();

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Memperbarui/Verifikasi barang keluar (ID: ' . $barangKeluar->id_barang_keluar . ') menjadi status: ' . $statusBaru
        ]);

        return redirect()->route('barang-keluar.index')->with('success', 'Data barang keluar berhasil diverifikasi.');
    }

    public function destroy(string $id_barang_keluar)
    {
        $barangKeluar = BarangKeluar::findOrFail($id_barang_keluar);

        // ATURAN 1: Staff (hanya boleh batalkan jika masih pending)
        if (auth()->user()->role == 'Staff') {
            if ($barangKeluar->status != 'pending') {
                return redirect()->route('barang-keluar.index')
                    ->with('error', 'Request tidak bisa dibatalkan karena sudah diproses oleh Admin/Kepala Gudang.');
            }
        }
        // ATURAN 2: Kepala Gudang (hanya boleh hapus transaksi terbaru)
        elseif (auth()->user()->role == 'Kepala Gudang') {
            if (!$this->isTransaksiTerbaru($barangKeluar->id_barang, $barangKeluar->created_at)) {
                return redirect()->route('barang-keluar.index')
                    ->with('error', 'Transaksi ini tidak bisa dihapus karena sudah ada transaksi lain (masuk/keluar) setelahnya.');
            }

            // Jika sudah selesai (stok sudah terpotong), kembalikan stoknya
            if ($barangKeluar->status == 'selesai') {
                $barang = Barang::findOrFail($barangKeluar->id_barang);
                $barang->stok += $barangKeluar->jumlah_keluar;
                $barang->save();
            }
        }
        // ATURAN 3: Admin Gudang tidak punya hak hapus
        else {
            return redirect()->route('barang-keluar.index')
                ->with('error', 'Anda tidak memiliki hak akses untuk menghapus data ini.');
        }

        $barangKeluar->delete();

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Menghapus/Membatalkan request barang keluar (ID: ' . $id_barang_keluar . ')'
        ]);

        return redirect()->route('barang-keluar.index')->with('success', 'Data barang keluar berhasil dihapus.');
    }

    // ================== VERIFIKASI CEPAT ADMIN GUDANG ==================

    public function verifikasiAdmin(Request $request, $id_barang_keluar)
    {
        if (auth()->user()->role != 'Admin Gudang') {
            return back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'status'             => 'required|in:acc_admin,ditolak',
            'catatan_verifikasi' => 'nullable|string'
        ]);

        $barangKeluar = BarangKeluar::findOrFail($id_barang_keluar);

        if ($barangKeluar->status != 'pending') {
            return back()->with('error', 'Transaksi ini sudah diverifikasi sebelumnya.');
        }

        $barangKeluar->status = $request->status;
        if ($request->has('catatan_verifikasi')) {
            $barangKeluar->catatan_verifikasi = $request->catatan_verifikasi;
        }
        $barangKeluar->save();

        $statusText = $request->status == 'acc_admin' ? 'ACC' : 'Tolak';

        LogAktivitas::create([
            'id_user'   => auth()->user()->id_user,
            'aktivitas' => 'Melakukan Verifikasi Cepat (Status: '. $statusText .') pada barang keluar ID: ' . $id_barang_keluar
        ]);

        return back()->with('success', 'Transaksi berhasil di-' . $statusText . '.');
    }

    // ================== HELPER ==================

    private function isTransaksiTerbaru($id_barang, $createdAt)
    {
        $lastMasuk   = BarangMasuk::where('id_barang', $id_barang)->max('created_at');
        $lastKeluar  = BarangKeluar::where('id_barang', $id_barang)->max('created_at');
        $lastOverall = max($lastMasuk, $lastKeluar);
        return $createdAt == $lastOverall;
    }

    private function bisaDiedit($transaksi)
    {
        // Kepala Gudang HANYA boleh verifikasi final jika sudah di-ACC Admin Gudang
        if (auth()->check() && auth()->user()->role == 'Kepala Gudang') {
            return $transaksi->status == 'acc_admin';
        }

        return !in_array($transaksi->status, ['selesai', 'ditolak']);
    }
    public function verifikasiKepala(Request $request, $id_barang_keluar)
{
    if (auth()->user()->role != 'Kepala Gudang') {
        return back()->with('error', 'Akses ditolak.');
    }

    $request->validate([
        'status'             => 'required|in:selesai,ditolak',
        'catatan_verifikasi' => 'nullable|string'
    ]);

    $barangKeluar = BarangKeluar::findOrFail($id_barang_keluar);

    if ($barangKeluar->status != 'acc_admin') {
        return back()->with('error', 'Transaksi ini belum di-ACC Admin Gudang atau sudah diverifikasi final.');
    }

    // Jika di-ACC (selesai), stok baru benar-benar dipotong di sini
    if ($request->status == 'selesai') {
        $barang = Barang::findOrFail($barangKeluar->id_barang);

        if ($barangKeluar->jumlah_keluar > $barang->stok) {
            return back()->with('error', 'Stok tidak mencukupi (' . $barang->stok . '). Transaksi tidak bisa diselesaikan.');
        }

        $barang->stok -= $barangKeluar->jumlah_keluar;
        $barang->save();

        $barangKeluar->stok_sesudah_keluar = $barang->stok;
    }

    $barangKeluar->status = $request->status;
    if ($request->has('catatan_verifikasi')) {
        $barangKeluar->catatan_verifikasi = $request->catatan_verifikasi;
    }
    $barangKeluar->save();

    $statusText = $request->status == 'selesai' ? 'Selesaikan' : 'Tolak';

    LogAktivitas::create([
        'id_user'   => auth()->user()->id_user,
        'aktivitas' => 'Melakukan Verifikasi Final (Status: ' . $statusText . ') pada barang keluar ID: ' . $id_barang_keluar
    ]);

    return back()->with('success', 'Transaksi berhasil di-' . $statusText . '.');
}
}