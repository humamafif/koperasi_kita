<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Models\SHUBiaya.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SHUBiaya extends Model
{
    use HasFactory;
    protected $table = 'shu_biayas';

    protected $fillable = [
        'tahun',
        'saldo_koperasi',
        'biaya_operasional',
        'pajak',
        'dana_cadangan',
        'biaya_lainnya',
        'keterangan_biaya',
        'total_biaya',
        'total_shu',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'saldo_koperasi' => 'float',
        'biaya_operasional' => 'float',
        'pajak' => 'float',
        'dana_cadangan' => 'float',
        'biaya_lainnya' => 'float',
        'total_biaya' => 'float',
        'total_shu' => 'float',
    ];
}
