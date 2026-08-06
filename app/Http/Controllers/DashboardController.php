<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Barang;
use App\Models\BarangMasuk;
use App\Models\BarangKeluar;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik card - Master Data Barang
        $totalBarang  = Barang::count();
        $totalSukuCadang = Barang::where('kategori', 'Suku Cadang')->count();
        $totalConsumable = Barang::where('kategori', 'Consumable')->count();

        // Statistik Barang Masuk
        // HANYA hitung transaksi berstatus 'selesai' (final & sudah menambah stok).
        // Transaksi 'pending', 'acc_admin', apalagi 'ditolak' TIDAK dihitung,
        // karena barang belum/tidak benar-benar masuk ke gudang.
        $totalMasuk        = BarangMasuk::where('status', 'selesai')->count();
        $totalJumlahMasuk  = BarangMasuk::where('status', 'selesai')->sum('jumlah_masuk');

        // Statistik Barang Keluar
        // Sama seperti Barang Masuk: hanya status 'selesai' yang dihitung.
        $totalKeluar       = BarangKeluar::where('status', 'selesai')->count();
        $totalJumlahKeluar = BarangKeluar::where('status', 'selesai')->sum('jumlah_keluar');

        $totalUser    = User::count();
        $totalAdmin   = User::where('role', 'Administrator')->count();
        $totalKepala  = User::where('role', 'Kepala Gudang')->count();

        // Detail untuk modal
        // Detail tetap menampilkan SEMUA status (termasuk pending/ditolak) supaya
        // riwayat lengkap tetap bisa diaudit/dilihat di modal, dengan asumsi
        // view menampilkan badge status pada masing-masing baris.
        $detailBarang  = Barang::with(['satuan'])->get();
        $detailMasuk   = BarangMasuk::with(['barang'])->orderBy('tanggal_masuk', 'desc')->get();
        $detailKeluar  = BarangKeluar::with(['barang'])->orderBy('tanggal_keluar', 'desc')->get();
        $detailUser    = User::all();

        // Stok menipis (dibandingkan ke batas minimum masing-masing barang)
        // Tidak perlu difilter status karena kolom stok di tabel barang
        // memang sudah hanya diupdate saat transaksi berstatus 'selesai'.
        $barangMenipis = Barang::with(['satuan', 'rak.lokasi'])
                            ->whereColumn('stok', '<=', 'stok_minimum')
                            ->get();

        return view('dashboard', compact(
            'totalBarang', 'totalSukuCadang', 'totalConsumable',
            'totalMasuk', 'totalJumlahMasuk',
            'totalKeluar', 'totalJumlahKeluar',
            'totalUser', 'totalAdmin', 'totalKepala',
            'detailBarang', 'detailMasuk', 'detailKeluar', 'detailUser',
            'barangMenipis'
        ));
    }
}