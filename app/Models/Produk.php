<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'aktif',
        'user_id',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'stok' => 'integer',
        'aktif' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pembelian(): HasMany
    {
        return $this->hasMany(PembelianProduk::class);
    }

    public function getDeskripsiCleanAttribute()
    {
        return strip_tags($this->deskripsi);
    }
}
