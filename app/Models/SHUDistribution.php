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
        'persentase_kontribusi',
        'jumlah_shu',
        'status',
        'is_claimed',
        'claimed_at',
        'tanggal_distribusi',
    ];

    protected $casts = [
        'persentase_kontribusi' => 'decimal:6',
        'jumlah_shu' => 'decimal:2',
        'total_simpanan' => 'decimal:2',
        'tanggal_distribusi' => 'datetime',
        'claimed_at' => 'datetime',
        'is_claimed' => 'boolean',
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
