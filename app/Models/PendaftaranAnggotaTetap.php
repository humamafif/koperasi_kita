<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranAnggotaTetap extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'nik',
        'alamat',
        'no_telepon',
        'bukti_pembayaran',
        'status',
        'keterangan',
        'tanggal_pengajuan',
        'tanggal_verifikasi',
        'diverifikasi_oleh',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_verifikasi' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
