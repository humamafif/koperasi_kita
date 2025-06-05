<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'deskripsi',
        'harga',
        'stok',
        'kategori',
        'gambar',
        'aktif'
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
        'aktif' => 'boolean',
    ];
}
