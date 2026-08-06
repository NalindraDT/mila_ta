<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';

    protected $primaryKey = 'id_log';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_user',
        'aktivitas',
        'created_at'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}