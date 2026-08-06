<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $table = 'barang';
    protected $primaryKey = 'id_barang';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id_barang',
        'nama_barang',
        'id_satuan',
        'id_rak',
        'stok',
        'stok_minimum',
        'kategori',
        'deskripsi',
    ];

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan', 'id_satuan');
    }

    public function rak()
    {
        return $this->belongsTo(Rak::class, 'id_rak', 'id_rak');
    }
}
