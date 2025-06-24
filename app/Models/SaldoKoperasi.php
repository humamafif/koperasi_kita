<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaldoKoperasi extends Model
{
    use HasFactory;

    protected $fillable = ['saldo'];

    public static function getSaldo(): float
    {
        $record = self::first();
        return $record ? $record->saldo : 0;
    }
    public static function tambah(float $jumlah): float
    {
        $record = self::first();

        if (!$record) {
            $record = self::create(['saldo' => $jumlah]);
        } else {
            $record->saldo += $jumlah;
            $record->save();
        }

        return $record->saldo;
    }

    public static function kurang(float $jumlah): float
    {
        $record = self::first();

        if (!$record) {
            $record = self::create(['saldo' => -$jumlah]);
        } else {
            $record->saldo -= $jumlah;
            $record->save();
        }

        return $record->saldo;
    }
}
