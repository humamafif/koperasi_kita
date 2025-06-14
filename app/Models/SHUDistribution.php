<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'tanggal_distribusi',
    ];

    protected $casts = [
        'persentase_kontribusi' => 'float',
        'tanggal_distribusi' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
