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

    /**
     * Mendapatkan hari cutoff untuk tagihan
     * @return int
     */
    public static function getTagihanCutoffDay(): int
    {
        return (int) self::getValue('tagihan_cutoff_day', 20);
    }

    /**
     * Mendapatkan hari jatuh tempo untuk anggota baru
     * @return int
     */
    public static function getTagihanDueDayNewMember(): int
    {
        return (int) self::getValue('tagihan_due_day_new_member', 25);
    }

    /**
     * Mendapatkan hari jatuh tempo untuk tagihan reguler
     * @return int
     */
    public static function getTagihanDueDayRegular(): int
    {
        return (int) self::getValue('tagihan_due_day_regular', 10);
    }

    /**
     * Memperbarui pengaturan hari untuk tagihan
     *
     * @param int $cutoffDay
     * @param int $dueDayNewMember
     * @param int $dueDayRegular
     * @return bool
     */
    public static function updateTagihanDaySettings(int $cutoffDay, int $dueDayNewMember, int $dueDayRegular): bool
    {
        if (
            $cutoffDay < 1 || $cutoffDay > 28 ||
            $dueDayNewMember < 1 || $dueDayNewMember > 28 ||
            $dueDayRegular < 1 || $dueDayRegular > 28
        ) {
            throw new \InvalidArgumentException("Nilai hari harus antara 1-28");
        }

        $updated1 = self::setValue('tagihan_cutoff_day', $cutoffDay, 'Batas hari dalam bulan untuk menentukan apakah tagihan dibuat bulan ini atau bulan depan');
        $updated2 = self::setValue('tagihan_due_day_new_member', $dueDayNewMember, 'Hari jatuh tempo untuk tagihan anggota baru');
        $updated3 = self::setValue('tagihan_due_day_regular', $dueDayRegular, 'Hari jatuh tempo untuk tagihan bulanan reguler');

        Cache::forget('koperasi_setting.tagihan_cutoff_day');
        Cache::forget('koperasi_setting.tagihan_due_day_new_member');
        Cache::forget('koperasi_setting.tagihan_due_day_regular');

        return $updated1 && $updated2 && $updated3;
    }
}
