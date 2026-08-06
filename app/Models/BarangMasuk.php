<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk';
    protected $primaryKey = 'id_barang_masuk';
    public $timestamps = false;

    protected $fillable = [
        'id_barang',
        'id_rak',
        'jumlah_masuk',
        'tanggal_masuk',
        'stok_sesudah',
        'status',
        'catatan_verifikasi',
        'foto_bukti' // Kolom baru untuk menampung path gambar
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }

    public function rak()
    {
        return $this->belongsTo(Rak::class, 'id_rak', 'id_rak');
    }
}