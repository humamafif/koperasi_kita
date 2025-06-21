<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SHUDistribution extends Model
{
    use HasFactory;
    protected $table = 'shu_distributions';

    protected $fillable = [
        'user_id',
        'tahun',
        'total_simpanan',
        'total_biaya_admin',
        'total_bunga_pinjaman',
        'persentase_kontribusi',
        'jumlah_shu',
        'status',
        'is_claimed',
        'claimed_at',
        'tanggal_distribusi',
        'detail_perhitungan',
    ];

    protected $casts = [
        'persentase_kontribusi' => 'float',
        'jumlah_shu' => 'float',
        'total_simpanan' => 'float',
        'total_biaya_admin' => 'float',
        'total_bunga_pinjaman' => 'float',
        'tanggal_distribusi' => 'datetime',
        'claimed_at' => 'datetime',
        'is_claimed' => 'boolean',
        'detail_perhitungan' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pengambilan(): HasOne
    {
        return $this->hasOne(SHUPengambilan::class,);
    }
}
