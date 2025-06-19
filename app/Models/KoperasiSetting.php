<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class KoperasiSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value', 'description'];

    /**
     * Mendapatkan nilai konfigurasi berdasarkan key
     */
    public static function getValue(string $key, $default = null)
    {
        return Cache::rememberForever("koperasi_setting.$key", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set nilai konfigurasi
     */
    public static function setValue(string $key, $value, $description = null)
    {
        // Validasi dan format nilai khusus untuk biaya admin
        if ($key === 'biaya_admin_percent') {
            $value = self::validateBiayaAdminValue($value);
        }

        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description ?? $key,
            ]
        );

        Cache::forget("koperasi_setting.$key");

        return $setting;
    }

    /**
     * Mendapatkan jumlah simpanan pokok
     */
    public static function getSimpananPokokAmount()
    {
        return (int) self::getValue('simpanan_pokok_amount');
    }

    /**
     * Mendapatkan jumlah simpanan wajib
     */
    public static function getSimpananWajibAmount()
    {
        return (int) self::getValue('simpanan_wajib_amount');
    }
    public static function validateBiayaAdminValue($value): float
    {
        $float_value = (float) $value;
        $rounded = round($float_value * 10) / 10;
        return max(0.1, $rounded);
    }
    public static function setBiayaAdminPercent(float $percent): bool
    {
        if ($percent < 0.1) {
            throw new \InvalidArgumentException("Persentase biaya admin minimal 0.1%");
        }
        $formattedPercent = number_format($percent, 1, '.', '');

        $updated = self::setValue(
            'biaya_admin_percent',
            $formattedPercent,
            'Persentase biaya admin untuk transaksi produk (%)'
        );
        Cache::forget('koperasi_setting.biaya_admin_percent');

        return $updated ? true : false;
    }
    public static function getBiayaAdminPercent()
    {
        return (float) self::getValue('biaya_admin_percent');
    }

    /**
     * Menghitung biaya admin berdasarkan total transaksi
     */
    public static function calculateBiayaAdmin($totalTransaksi)
    {
        $percent = self::getBiayaAdminPercent();
        return $totalTransaksi * ($percent / 100);
    }

    /**
     * Format persentase biaya admin untuk ditampilkan
     */
    public static function getBiayaAdminPercentDisplay()
    {
        return number_format(self::getBiayaAdminPercent(), 1) . '%';
    }
}
