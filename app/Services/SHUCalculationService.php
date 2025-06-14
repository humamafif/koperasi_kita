<?php
// filepath: d:\Development\menpro\koperasi_kita\app\Services\SHUCalculationService.php

namespace App\Services;

use App\Models\Simpanan;
use App\Models\SHUDistribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SHUCalculationService
{
    /**
     * Mendapatkan jumlah anggota yang berhak mendapatkan SHU
     *
     * @return int
     */
    public function getJumlahAnggotaBerhakSHU(): int
    {
        return DB::table('simpanans')
            ->select('user_id')
            ->where('status', 'disetujui')
            ->groupBy('user_id')
            ->count();
    }

    /**
     * Mendapatkan rata-rata simpanan anggota
     *
     * @return float
     */
    public function getRataRataSimpananAnggota(): float
    {
        $totalSimpanan = Simpanan::where('status', 'disetujui')->sum('jumlah');
        $jumlahAnggota = $this->getJumlahAnggotaBerhakSHU();

        if ($jumlahAnggota === 0) {
            return 0;
        }

        return $totalSimpanan / $jumlahAnggota;
    }

    /**
     * Hitung SHU untuk semua anggota berdasarkan proporsi simpanan
     *
     * Rumus: SHU Anggota = (Total Simpanan Anggota / Total Seluruh Simpanan) * Total SHU Koperasi
     *
     * @param int $tahun
     * @param float $totalSHU Total SHU Koperasi yang akan dibagikan
     * @return array
     */
    public function calculateSHU(int $tahun, float $totalSHU): array
    {
        try {
            // Debug
            Log::info("Memulai perhitungan SHU untuk tahun $tahun dengan total SHU: $totalSHU");

            // Mendapatkan semua simpanan anggota yang disetujui
            $simpananAnggota = $this->getSimpananAnggotaTersetujui();

            Log::info("Jumlah anggota dengan simpanan: " . count($simpananAnggota));
            Log::info("Data simpanan: " . json_encode($simpananAnggota)); // Log full data for debugging

            if (count($simpananAnggota) === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada anggota yang memiliki simpanan tersetujui.'
                ];
            }

            // Menghitung total simpanan dari semua anggota
            $totalSimpanan = 0;
            foreach ($simpananAnggota as $anggota) {
                $totalSimpanan += (float) $anggota->total_simpanan;
            }

            Log::info("Total simpanan seluruh anggota: $totalSimpanan");

            if ($totalSimpanan <= 0) {
                return [
                    'success' => false,
                    'message' => 'Total simpanan anggota adalah 0. Tidak bisa membagi SHU.'
                ];
            }

            // Distribusi SHU
            $distribusi = [];

            foreach ($simpananAnggota as $anggota) {
                // Debug untuk setiap anggota
                Log::info("Memproses user_id: " . $anggota->user_id . " dengan total_simpanan: " . $anggota->total_simpanan);

                // Menghitung persentase kontribusi anggota
                $persentaseKontribusi = (float) $anggota->total_simpanan / $totalSimpanan;

                // Menghitung jumlah SHU yang didapatkan anggota
                $jumlahSHU = $totalSHU * $persentaseKontribusi;

                try {
                    // Menyimpan hasil perhitungan
                    $shuDistribution = SHUDistribution::create([
                        'user_id' => $anggota->user_id,
                        'tahun' => $tahun,
                        'total_simpanan' => $anggota->total_simpanan,
                        'persentase_kontribusi' => $persentaseKontribusi,
                        'jumlah_shu' => $jumlahSHU,
                        'status' => 'pending',
                        'tanggal_distribusi' => now()
                    ]);

                    $distribusi[] = $shuDistribution;
                    Log::info("Berhasil buat SHU untuk user_id: " . $anggota->user_id . " dengan jumlah: " . $jumlahSHU);
                } catch (\Exception $e) {
                    Log::error("Gagal menyimpan SHU untuk user_id: {$anggota->user_id}: " . $e->getMessage());
                    throw $e;
                }
            }

            return [
                'success' => true,
                'message' => 'SHU berhasil dihitung dan disimpan.',
                'distribusi' => $distribusi
            ];
        } catch (\Exception $e) {
            Log::error("Error saat menghitung SHU: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
    private function getSimpananAnggotaTersetujui(): array
    {
        try {
            Log::info("Mengambil data simpanan anggota tersetujui");

            $result = DB::table('simpanans')
                ->select('user_id', DB::raw('SUM(jumlah) as total_simpanan'))
                ->where('status', 'disetujui')
                ->groupBy('user_id')
                ->get();

            Log::info("Ditemukan " . $result->count() . " anggota dengan simpanan tersetujui");

            if ($result->count() > 0) {
                Log::info("Contoh data: " . json_encode($result[0]));
            }

            // Ubah menjadi array yang mempertahankan properti
            $formattedResult = [];
            foreach ($result as $item) {
                $formattedResult[] = (object) [
                    'user_id' => $item->user_id,
                    'total_simpanan' => $item->total_simpanan
                ];
            }

            return $formattedResult;
        } catch (\Exception $e) {
            Log::error("Error saat mengambil simpanan anggota tersetujui: " . $e->getMessage());
            throw $e;
        }
    }
    /**
     * Batalkan perhitungan SHU untuk tahun tertentu
     *
     * @param int $tahun
     * @return bool
     */
    public function cancelSHUCalculation(int $tahun): bool
    {
        Log::info("Membatalkan perhitungan SHU tahun {$tahun}");

        // Hapus dari tabel shu_distributions
        $count = SHUDistribution::where('tahun', $tahun)
            ->where('status', 'pending')
            ->delete();

        // Hapus juga dari tabel shu_biayas
        \App\Models\SHUBiaya::where('tahun', $tahun)->delete();

        Log::info("Pembatalan SHU tahun {$tahun}: {$count} record dihapus");
        return $count > 0;
    }

    /**
     * Distribusikan SHU yang sudah dihitung
     *
     * @param int $tahun
     * @return array
     */
    public function distributeSHU(int $tahun): array
    {
        Log::info("Memulai distribusi SHU tahun {$tahun}");

        // Ambil semua SHU pending untuk tahun tertentu
        $pendingSHU = SHUDistribution::where('tahun', $tahun)
            ->where('status', 'pending')
            ->get();

        if ($pendingSHU->isEmpty()) {
            Log::warning("Tidak ada SHU pending untuk didistribusikan tahun {$tahun}");
            return [
                'success' => false,
                'message' => 'Tidak ada SHU pending untuk didistribusikan'
            ];
        }

        Log::info("Jumlah SHU pending untuk didistribusikan: " . $pendingSHU->count());

        $count = 0;
        $total = 0;

        foreach ($pendingSHU as $shu) {
            $shu->status = 'dibagikan';
            $shu->tanggal_distribusi = now();
            $shu->save();

            // Tambahkan ke riwayat transaksi
            $user = User::find($shu->user_id);
            if ($user) {
                $keterangan = "Penerimaan SHU tahun $tahun sebesar Rp " . number_format($shu->jumlah_shu, 0, ',', '.');

                Log::info("Membuat riwayat transaksi SHU untuk {$user->name}: {$keterangan}");

                $user->riwayatTransaksis()->create([
                    'jenis_transaksi' => 'shu',
                    'jumlah' => $shu->jumlah_shu,
                    'tanggal' => now(),
                    'status' => 'success',
                    'keterangan' => $keterangan,
                    'referensi_id' => $shu->id,
                    'referensi_tipe' => SHUDistribution::class,
                ]);
            } else {
                Log::warning("User tidak ditemukan untuk SHU ID {$shu->id}");
            }

            $count++;
            $total += $shu->jumlah_shu;
        }

        Log::info("Distribusi SHU tahun {$tahun} selesai: {$count} record, total Rp " . number_format($total, 0, ',', '.'));

        return [
            'success' => true,
            'message' => "$count SHU berhasil didistribusikan dengan total Rp " . number_format($total, 0, ',', '.'),
            'tahun' => $tahun,
            'total_distribusi' => $total
        ];
    }

    /**
     * Dapatkan informasi tahun yang sudah memiliki distribusi SHU
     *
     * @return array
     */
    public function getDistributionYears(): array
    {
        $years = SHUDistribution::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();

        Log::info("Daftar tahun distribusi SHU: " . implode(', ', $years ?: ['belum ada']));
        return $years;
    }

    /**
     * Mendapatkan saldo koperasi
     *
     * @return float
     */
    public function getSaldoKoperasi(): float
    {
        // Ambil seluruh simpanan yang disetujui
        $totalSimpanan = Simpanan::where('status', 'disetujui')->sum('jumlah');

        return max(0, $totalSimpanan);
    }
}
