<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;

    protected $table      = 'barang_keluar';
    protected $primaryKey = 'id_barang_keluar';
    public $timestamps    = false;

    protected $fillable = [
        'id_barang',
        'jumlah_keluar',
        'tanggal_keluar',
        'keterangan',
        'stok_sesudah_keluar',
        'status',
        'catatan_verifikasi',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id_barang');
    }
}