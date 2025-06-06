<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pinjaman extends Model
{
    use HasFactory;
    protected  $table = 'pinjamans';
    protected $fillable = [
        'user_id',
        'jumlah',
        'tenor_id',
        'tujuan',
        'keterangan',
        'status',
        'alasan_penolakan',
        'tanggal_pengajuan',
        'tanggal_persetujuan',
        'disetujui_oleh',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal_pengajuan' => 'date',
        'tanggal_persetujuan' => 'date',
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenorPinjaman(): BelongsTo
    {
        return $this->belongsTo(TenorPinjaman::class, 'tenor_id');
    }

    public function getAngsuranPerBulanAttribute()
    {
        if (!$this->tenorPinjaman) {
            return 0;
        }

        $tenor = $this->tenorPinjaman->durasi;
        $bunga = $this->tenorPinjaman->bunga;

        $pokok = $this->jumlah / $tenor;
        $bungaPerBulan = ($this->jumlah * $bunga / 100) / $tenor;

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
