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
     * Mendapatkan informasi rekening koperasi
     *
     * @return array
     */
    public static function getRekeningInfo(): array
    {
        return [
            'nomor' => self::getValue('rekening_koperasi'),
            'bank' => self::getValue('bank_koperasi'),
            'nama_pemilik' => self::getValue('nama_pemilik_rekening'),
        ];
    }

    /**
     * Mendapatkan informasi rekening koperasi dalam format yang siap ditampilkan
     *
     * @return string
     */
    public static function getRekeningInfoDisplay(): string
    {
        $info = self::getRekeningInfo();
        return "{$info['bank']} {$info['nomor']} a.n {$info['nama_pemilik']}";
    }

    /**
     * Update informasi rekening koperasi
     *
     * @param string $nomor
     * @param string $bank
     * @param string $namaPemilik
     * @return bool
     */
    public static function updateRekeningInfo(string $nomor, string $bank, string $namaPemilik): bool
    {
        $updated1 = self::setValue('rekening_koperasi', $nomor, 'Nomor rekening koperasi untuk pembayaran');
        $updated2 = self::setValue('bank_koperasi', $bank, 'Nama bank rekening koperasi');
        $updated3 = self::setValue('nama_pemilik_rekening', $namaPemilik, 'Nama pemilik rekening koperasi');

        Cache::forget('koperasi_setting.rekening_koperasi');
        Cache::forget('koperasi_setting.bank_koperasi');
        Cache::forget('koperasi_setting.nama_pemilik_rekening');

        return $updated1 && $updated2 && $updated3;
    }
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
