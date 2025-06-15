<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pinjaman extends Model
{
    use HasFactory;

    protected $table = 'pinjamans';

    protected $fillable = [
        'user_id',
        'tenor_pinjaman_id',
        'jumlah',
        'tujuan',
        'status',
        'keterangan',
        'tanggal_pengajuan',
        'tanggal_persetujuan',
        'disetujui_oleh',
        'alasan_penolakan',
        'angsuran_per_bulan',
        'total_bunga',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_persetujuan' => 'date',
        'jumlah' => 'decimal:2',
        'angsuran_per_bulan' => 'decimal:2',
        'total_bunga' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenorPinjaman(): BelongsTo
    {
        return $this->belongsTo(TenorPinjaman::class, 'tenor_pinjaman_id');
    }

    public function tagihan(): HasMany
    {
        return $this->hasMany(TagihanAnggota::class, 'pinjaman_id');
    }

    public function getAngsuranPerBulanAttribute()
    {
        if ($this->attributes['angsuran_per_bulan'] ?? null) {
            return $this->attributes['angsuran_per_bulan'];
        }

        if (!$this->tenorPinjaman) {
            return 0;
        }

        $tenor = $this->tenorPinjaman->durasi;
        $bunga = $this->tenorPinjaman->bunga;

        $pokok = $this->jumlah / $tenor;
        $bungaPerBulan = ($this->jumlah * $bunga / 100) / 12;

        return $pokok + $bungaPerBulan;
    }

    public function getNilaiBungaAttribute()
    {
        return $this->tenorPinjaman ? $this->tenorPinjaman->bunga : 0;
    }

    public function getDurasiTenorAttribute()
    {
        return $this->tenorPinjaman ? $this->tenorPinjaman->durasi : 0;
    }

    public function getTotalBungaAttribute()
    {
        if ($this->attributes['total_bunga'] ?? null) {
            return $this->attributes['total_bunga'];
        }
        if (!$this->tenorPinjaman) {
            return 0;
        }

        $bunga = $this->tenorPinjaman->bunga;
        $tenor = $this->tenorPinjaman->durasi;

        return $this->jumlah * $bunga / 100 * ($tenor / 12);
    }
    public function getTotalPembayaranAttribute()
    {
        return $this->jumlah + $this->total_bunga;
    }
}
