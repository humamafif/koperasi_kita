<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagihanAnggota extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jenis_tagihan',
        'jumlah',
        'tanggal_jatuh_tempo',
        'status',
        'bukti_pembayaran',
        'tanggal_pembayaran',
        'keterangan',
        'pinjaman_id',
        'periode',
        'tanggal_verifikasi',
        'diverifikasi_oleh',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_pembayaran' => 'date',
        'tanggal_verifikasi' => 'date',
        'jumlah' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pinjaman(): BelongsTo
    {
        return $this->belongsTo(Pinjaman::class);
    }
    public function getFormattedPeriodeAttribute(): string
    {
        if ($this->periode) {
            list($year, $month) = explode('-', $this->periode);
            return date('F Y', mktime(0, 0, 0, $month, 1, (int)$year));
        }
        return '-';
    }
}
