<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BungaPinjaman extends Model
{
    protected $table = 'bunga_pinjamans';
    use HasFactory;
    protected $fillable = [
        'nama',
        'bunga',
        'keterangan',
        'aktif',
    ];

    protected $casts = [
        'bunga' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function pinjamans()
    {
        return $this->hasMany(Pinjaman::class, 'bunga', 'id');
    }
    //
}
