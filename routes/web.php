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

// Route Login (hanya bisa diakses kalau belum login)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Route Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route yang bisa diakses kedua role (administrator & kepala gudang)
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route semua role (administrator & kepala gudang)
    Route::get('ubah-password', [UbahPasswordController::class, 'index'])->name('ubah-password');
    Route::put('ubah-password', [UbahPasswordController::class, 'update'])->name('ubah-password.update');

    // Route barang - semua role bisa lihat
    Route::get('barang', [BarangController::class, 'index'])->name('barang.index');

    // Route barang masuk - semua role bisa lihat, kelola khusus administrator
    Route::get('barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');

    // Route barang keluar - semua role bisa lihat, kelola khusus administrator
    Route::get('barang-keluar', [BarangKeluarController::class, 'index'])->name('barang-keluar.index');

    // Route laporan stok barang - semua role
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::post('laporan', [LaporanController::class, 'generate'])->name('laporan.generate');
    Route::post('laporan/download-word', [LaporanController::class, 'downloadWord'])->name('laporan.download-word');
    Route::post('laporan/download-excel', [LaporanController::class, 'downloadExcel'])->name('laporan.download-excel');

    // Route laporan barang masuk - semua role
    Route::get('laporan-barang-masuk', [LaporanBarangMasukController::class, 'index'])->name('laporan-barang-masuk.index');
    Route::post('laporan-barang-masuk', [LaporanBarangMasukController::class, 'generate'])->name('laporan-barang-masuk.generate');
    Route::post('laporan-barang-masuk/download-word', [LaporanBarangMasukController::class, 'downloadWord'])->name('laporan-barang-masuk.download-word');
    Route::post('laporan-barang-masuk/download-excel', [LaporanBarangMasukController::class, 'downloadExcel'])->name('laporan-barang-masuk.download-excel');

    // Route laporan barang keluar - semua role
    Route::get('laporan-barang-keluar', [LaporanBarangKeluarController::class, 'index'])->name('laporan-barang-keluar.index');
    Route::post('laporan-barang-keluar', [LaporanBarangKeluarController::class, 'generate'])->name('laporan-barang-keluar.generate');
    Route::post('laporan-barang-keluar/download-word', [LaporanBarangKeluarController::class, 'downloadWord'])->name('laporan-barang-keluar.download-word');
    Route::post('laporan-barang-keluar/download-excel', [LaporanBarangKeluarController::class, 'downloadExcel'])->name('laporan-barang-keluar.download-excel');

    // Route khusus administrator saja
    Route::middleware(['role:Administrator'])->group(function () {
        Route::resource('satuan', SatuanController::class);
        Route::resource('lokasi', LokasiController::class);
        Route::resource('rak', RakController::class);
        Route::resource('manajemen-user', ManajemenUserController::class)->except(['show']);

        // CRUD barang hanya administrator
        Route::get('barang/generate-id', [BarangController::class, 'generateId'])->name('barang.generate-id');
        Route::get('barang/create', [BarangController::class, 'create'])->name('barang.create');
        Route::post('barang', [BarangController::class, 'store'])->name('barang.store');
        Route::get('barang/{id_barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('barang/{id_barang}', [BarangController::class, 'update'])->name('barang.update');
        Route::delete('barang/{id_barang}', [BarangController::class, 'destroy'])->name('barang.destroy');

        // Kelola barang masuk hanya administrator
        Route::get('barang-masuk/create', [BarangMasukController::class, 'create'])->name('barang-masuk.create');
        Route::post('barang-masuk', [BarangMasukController::class, 'store'])->name('barang-masuk.store');
        Route::get('barang-masuk/{id_barang_masuk}/edit', [BarangMasukController::class, 'edit'])->name('barang-masuk.edit');
        Route::put('barang-masuk/{id_barang_masuk}', [BarangMasukController::class, 'update'])->name('barang-masuk.update');
        Route::delete('barang-masuk/{id_barang_masuk}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');

        // Foto bukti barang masuk hanya administrator
        Route::get('barang-masuk/foto', [BarangMasukController::class, 'fotoIndex'])->name('barang-masuk.foto.index');
        Route::post('barang-masuk/foto', [BarangMasukController::class, 'fotoStore'])->name('barang-masuk.foto.store');
        Route::delete('barang-masuk/foto/{id_foto}', [BarangMasukController::class, 'fotoDestroy'])->name('barang-masuk.foto.destroy');

        // Kelola barang keluar hanya administrator
        Route::get('barang-keluar/create', [BarangKeluarController::class, 'create'])->name('barang-keluar.create');
        Route::post('barang-keluar', [BarangKeluarController::class, 'store'])->name('barang-keluar.store');
        Route::get('barang-keluar/{id_barang_keluar}/edit', [BarangKeluarController::class, 'edit'])->name('barang-keluar.edit');
        Route::put('barang-keluar/{id_barang_keluar}', [BarangKeluarController::class, 'update'])->name('barang-keluar.update');
        Route::delete('barang-keluar/{id_barang_keluar}', [BarangKeluarController::class, 'destroy'])->name('barang-keluar.destroy');
    });
});