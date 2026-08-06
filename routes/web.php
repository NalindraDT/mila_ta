<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\LokasiController;
use App\Http\Controllers\RakController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\UbahPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanBarangMasukController;
use App\Http\Controllers\LaporanBarangKeluarController;
use App\Http\Controllers\LogAktivitasController;

// Route Login (hanya bisa diakses kalau belum login)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Route Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route yang bisa diakses semua role yang sudah login
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('ubah-password', [UbahPasswordController::class, 'index'])->name('ubah-password');
    Route::put('ubah-password', [UbahPasswordController::class, 'update'])->name('ubah-password.update');

    // Route Read-Only yang bisa dilihat semua role
    Route::get('barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
    Route::get('barang-keluar', [BarangKeluarController::class, 'index'])->name('barang-keluar.index');

    // =================================================================
    // 1. ROLE: STAFF
    // Tugas: Hanya melakukan request barang masuk dan keluar
    // =================================================================
    Route::middleware(['role:Staff'])->group(function () {
        // Request Barang Masuk
        Route::get('barang-masuk/create', [BarangMasukController::class, 'create'])->name('barang-masuk.create');
        Route::post('barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');

        // Upload Foto Bukti Barang Masuk
        Route::get('barang-masuk/foto', [BarangMasukController::class, 'fotoIndex'])->name('barang-masuk.foto.index');
        Route::post('barang-masuk/foto', [BarangMasukController::class, 'fotoStore'])->name('barang-masuk.foto.store');
        Route::delete('barang-masuk/foto/{id_foto}', [BarangMasukController::class, 'fotoDestroy'])->name('barang-masuk.foto.destroy');

        // Request Barang Keluar
        Route::get('barang-keluar/create', [BarangKeluarController::class, 'create'])->name('barang-keluar.create');
        Route::post('barang-keluar', [BarangKeluarController::class, 'store'])->name('barang-keluar.store');
    });

    // =================================================================
    // 2. ROLE: ADMINISTRATOR & KEPALA GUDANG
    // Tugas: Laporan & Proses ACC/Revisi Request Barang
    // =================================================================
    Route::middleware(['role:Admin Gudang,Kepala Gudang'])->group(function () {
        // Laporan
        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::post('laporan', [LaporanController::class, 'generate'])->name('laporan.generate');
        Route::post('laporan/download-pdf', [LaporanController::class, 'downloadPdf'])
            ->name('laporan.download-pdf');

        Route::get('laporan-barang-masuk', [LaporanBarangMasukController::class, 'index'])->name('laporan-barang-masuk.index');
        Route::post('laporan-barang-masuk', [LaporanBarangMasukController::class, 'generate'])->name('laporan-barang-masuk.generate');
        Route::post('laporan-barang-masuk/download-pdf', [LaporanBarangMasukController::class, 'downloadPdf'])
            ->name('laporan-barang-masuk.download-pdf');

        Route::get('laporan-barang-keluar', [LaporanBarangKeluarController::class, 'index'])->name('laporan-barang-keluar.index');
        Route::post('laporan-barang-keluar', [LaporanBarangKeluarController::class, 'generate'])->name('laporan-barang-keluar.generate');
        Route::post('/laporan-barang-keluar/download-pdf', [App\Http\Controllers\LaporanBarangKeluarController::class, 'downloadPdf'])->name('laporan-barang-keluar.download-pdf');

        // ACC & Revisi Barang Masuk
        Route::get('barang-masuk/{id_barang_masuk}/edit', [BarangMasukController::class, 'edit'])->name('barang-masuk.edit');
        Route::put('barang-masuk/{id_barang_masuk}', [BarangMasukController::class, 'update'])->name('barang-masuk.update');
        Route::put('barang-masuk/{id}/verifikasi-admin', [BarangMasukController::class, 'verifikasiAdmin'])->name('barang-masuk.verifikasi-admin');

        // ACC & Revisi Barang Keluar
        Route::get('barang-keluar/{id_barang_keluar}/edit', [BarangKeluarController::class, 'edit'])->name('barang-keluar.edit');
        Route::put('barang-keluar/{id_barang_keluar}', [BarangKeluarController::class, 'update'])->name('barang-keluar.update');
        Route::put('barang-keluar/{id}/verifikasi-admin', [BarangKeluarController::class, 'verifikasiAdmin'])->name('barang-keluar.verifikasi-admin');
        Route::put('barang-keluar/{id_barang_keluar}/verifikasi-kepala', [BarangKeluarController::class, 'verifikasiKepala'])
            ->name('barang-keluar.verifikasi-kepala');
    });

    // =================================================================
    // 3. ROLE: KEPALA GUDANG
    // Tugas: Master Data & Log Aktivitas
    // =================================================================
    Route::middleware(['role:Kepala Gudang'])->group(function () {
        // Master Data
        Route::resource('satuan', SatuanController::class);
        Route::resource('lokasi', LokasiController::class);
        Route::resource('rak', RakController::class);
        Route::resource('manajemen-user', ManajemenUserController::class)->except(['show']);

        // Master Data Barang
        Route::get('barang/generate-id', [BarangController::class, 'generateId'])->name('barang.generate-id');
        Route::get('barang/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('barang', [BarangController::class, 'store'])->name('barang.store');
        Route::get('barang/{id_barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('barang/{id_barang}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('barang/{id_barang}', [BarangController::class, 'destroy'])->name('barang.destroy');

        // Log Aktivitas
        Route::get('log-aktivitas', [LogAktivitasController::class, 'index'])->name('log-aktivitas.index');
    });

    // =================================================================
    // 4. ROLE: STAFF & KEPALA GUDANG
    // Tugas: Hapus Transaksi (Barang Masuk & Barang Keluar)
    // - Staff: hanya bisa hapus request miliknya sendiri yang masih pending
    // - Kepala Gudang: bisa hapus jika transaksi terbaru (sesuai logic controller)
    // =================================================================
    Route::middleware(['role:Staff,Kepala Gudang'])->group(function () {
        Route::delete('barang-masuk/{id_barang_masuk}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');
        Route::delete('barang-keluar/{id_barang_keluar}', [BarangKeluarController::class, 'destroy'])->name('barang-keluar.destroy');
    });
});
