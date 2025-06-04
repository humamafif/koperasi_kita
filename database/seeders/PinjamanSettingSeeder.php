<?php

namespace Database\Seeders;

use App\Models\BungaPinjaman;
use App\Models\TenorPinjaman;
use Illuminate\Database\Seeder;

class PinjamanSettingSeeder extends Seeder
{
    public function run(): void
    {
        // Tenor dan bunga pinjaman
        $tenorData = [
            ['durasi' => 3, 'nama' => '3 Bulan', 'bunga' => 0.8, 'keterangan' => 'Tenor jangka pendek dengan bunga rendah'],
            ['durasi' => 6, 'nama' => '6 Bulan', 'bunga' => 1.0, 'keterangan' => 'Tenor jangka pendek dengan bunga standar'],
            ['durasi' => 12, 'nama' => '1 Tahun', 'bunga' => 1.2, 'keterangan' => 'Tenor jangka menengah dengan bunga menengah'],
            ['durasi' => 24, 'nama' => '2 Tahun', 'bunga' => 1.5, 'keterangan' => 'Tenor jangka panjang dengan bunga tinggi'],
            ['durasi' => 36, 'nama' => '3 Tahun', 'bunga' => 1.8, 'keterangan' => 'Tenor jangka panjang maksimal dengan bunga maksimal'],
        ];

        foreach ($tenorData as $tenor) {
            TenorPinjaman::updateOrCreate(
                ['durasi' => $tenor['durasi']],
                [
                    'nama' => $tenor['nama'],
                    'bunga' => $tenor['bunga'],
                    'keterangan' => $tenor['keterangan'],
                    'aktif' => true,
                ]
            );
        }
    }
}
