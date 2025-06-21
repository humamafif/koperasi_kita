<?php

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
        'persentase_dana_cadangan',
        'persentase_jasa_usaha',
        'persentase_jasa_modal',
        'persentase_jasa_pinjaman',
        'persentase_jasa_usaha_adjusted',
        'persentase_jasa_modal_adjusted',
        'persentase_jasa_pinjaman_adjusted',
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
        'persentase_dana_cadangan' => 'float',
        'persentase_jasa_usaha' => 'float',
        'persentase_jasa_modal' => 'float',
        'persentase_jasa_pinjaman' => 'float',
        'persentase_jasa_usaha_adjusted' => 'float',
        'persentase_jasa_modal_adjusted' => 'float',
        'persentase_jasa_pinjaman_adjusted' => 'float',
    ];
}
