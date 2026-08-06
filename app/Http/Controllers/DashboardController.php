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
        // Statistik card
        $totalBarang  = Barang::count();
        $totalSukuCadang = Barang::where('kategori', 'Suku Cadang')->count();
        $totalConsumable = Barang::where('kategori', 'Consumable')->count();

        $totalMasuk   = BarangMasuk::count();
        $totalJumlahMasuk = BarangMasuk::sum('jumlah_masuk');

        $totalKeluar  = BarangKeluar::count();
        $totalJumlahKeluar = BarangKeluar::sum('jumlah_keluar');

        $totalUser    = User::count();
        $totalAdmin   = User::where('role', 'Administrator')->count();
        $totalKepala  = User::where('role', 'Kepala Gudang')->count();

        // Detail untuk modal
        $detailBarang  = Barang::with(['satuan'])->get();
        $detailMasuk   = BarangMasuk::with(['barang'])->orderBy('tanggal_masuk', 'desc')->get();
        $detailKeluar  = BarangKeluar::with(['barang'])->orderBy('tanggal_keluar', 'desc')->get();
        $detailUser    = User::all();

        // Stok menipis (dibandingkan ke batas minimum masing-masing barang)
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