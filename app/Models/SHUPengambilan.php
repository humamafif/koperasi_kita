<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SHUPengambilan extends Model
{
    use HasFactory;

    protected $table = 'shu_pengambilans';
    protected $fillable = [
        'user_id',
        'shu_distribution_id',
        'jumlah',
        'tanggal_pengambilan',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal_pengambilan' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shuDistribution(): BelongsTo
    {
        return $this->belongsTo(SHUDistribution::class);
    }
}
