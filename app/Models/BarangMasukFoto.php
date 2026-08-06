<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangMasukFoto extends Model
{
    use HasFactory;

    protected $table      = 'barang_masuk_foto';
    protected $primaryKey = 'id_foto';
    public $timestamps    = false;

    protected $fillable = [
        'tanggal_masuk',
        'kategori',
        'path_foto',
    ];
}