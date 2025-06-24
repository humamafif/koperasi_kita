<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenorPinjaman extends Model
{
    use HasFactory;

    protected $table = 'tenor_pinjamans';

    protected $fillable = [
        'durasi',
        'nama',
        'bunga',
        'keterangan',
        'aktif'
    ];

    protected $casts = [
        'durasi' => 'integer',
        'bunga' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function pinjamans(): HasMany
    {
        return $this->hasMany(Pinjaman::class, 'tenor_id');
    }
}
