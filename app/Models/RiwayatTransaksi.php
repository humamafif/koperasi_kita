<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RiwayatTransaksi extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_transaksi',
        'referensi_id',
        'referensi_tipe',
        'jumlah',
        'tanggal',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent reference model (Simpanan, Pinjaman, etc).
     */
    public function referensi(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the detail URL based on reference type.
     */
    public function getDetailUrlAttribute(): string
    {
        if ($this->referensi_tipe === 'App\\Models\\Simpanan') {
            return route('filament.anggota.resources.simpanan.view', ['record' => $this->referensi_id]);
        }

        if ($this->referensi_tipe === 'App\\Models\\Pinjaman') {
            return route('filament.anggota.resources.pinjaman.view', ['record' => $this->referensi_id]);
        }

        return '#';
    }
}
