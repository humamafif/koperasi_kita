<?php

namespace App\Services;

use App\Models\SHUBiaya;
use App\Models\Simpanan;
use App\Models\SHUDistribution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SHUCalculationService
{
    public function getJumlahAnggotaBerhakSHU(): int
    {
        return DB::table('simpanans')
            ->select('user_id')
            ->where('status', 'disetujui')
            ->groupBy('user_id')
            ->count();
    }

    public function getRataRataSimpananAnggota(): float
    {
        $totalSimpanan = Simpanan::where('status', 'disetujui')->sum('jumlah');
        $jumlahAnggota = $this->getJumlahAnggotaBerhakSHU();
        if ($jumlahAnggota === 0) {
            return 0;
        }
        return $totalSimpanan / $jumlahAnggota;
    }
    public function calculateSHU(int $tahun, float $totalSHU): array
    {
        try {
            $simpananAnggota = $this->getSimpananAnggotaTersetujui();

            if (count($simpananAnggota) === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada anggota yang memiliki simpanan tersetujui.'
                ];
            }

            $totalSimpanan = 0;
            foreach ($simpananAnggota as $anggota) {
                $totalSimpanan += (float) $anggota->total_simpanan;
            }
            if ($totalSimpanan <= 0) {
                return [
                    'success' => false,
                    'message' => 'Total simpanan anggota adalah 0. Tidak bisa membagi SHU.'
                ];
            }
            $distribusi = [];
            foreach ($simpananAnggota as $anggota) {
                $persentaseKontribusi = (float) $anggota->total_simpanan / $totalSimpanan;
                $jumlahSHU = $totalSHU * $persentaseKontribusi;

                try {
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
            $result = DB::table('simpanans')
                ->select('user_id', DB::raw('SUM(jumlah) as total_simpanan'))
                ->where('status', 'disetujui')
                ->groupBy('user_id')
                ->get();
            if ($result->count() > 0) {
                Log::info("Contoh data: " . json_encode($result[0]));
            }
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
    public function cancelSHUCalculation(int $tahun): bool
    {
        try {
            DB::beginTransaction();
            SHUDistribution::where('tahun', $tahun)
                ->where('status', 'pending')
                ->delete();
            SHUBiaya::where('tahun', $tahun)->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat membatalkan perhitungan SHU: " . $e->getMessage());
            return false;
        }
    }

    public function distributeSHU(int $tahun): array
    {
        try {
            $distributions = SHUDistribution::where('tahun', $tahun)
                ->where('status', 'pending')
                ->get();

            if ($distributions->isEmpty()) {
                return [
                    'success' => false,
                    'message' => "Tidak ada SHU untuk tahun $tahun yang perlu didistribusikan atau sudah didistribusikan sebelumnya."
                ];
            }

            DB::beginTransaction();

            $totalDistributed = 0;
            $totalMembers = $distributions->count();
            foreach ($distributions as $distribution) {
                $distribution->update([
                    'status' => 'dibagikan',
                    'tanggal_distribusi' => now(),
                ]);
                $totalDistributed += $distribution->jumlah_shu;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Distribusi SHU tahun $tahun berhasil. Total Rp " .
                    number_format($totalDistributed, 0, ',', '.') .
                    " didistribusikan ke $totalMembers anggota.",
                'total_distributed' => $totalDistributed,
                'total_members' => $totalMembers
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat mendistribusikan SHU: " . $e->getMessage());

            return [
                'success' => false,
                'message' => "Terjadi kesalahan: " . $e->getMessage()
            ];
        }
    }

    public function getDistributionYears(): array
    {
        return SHUDistribution::select('tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
    }

    public function getSaldoKoperasi(): float
    {
        $totalSimpanan = Simpanan::where('status', 'disetujui')->sum('jumlah');
        return max(0, $totalSimpanan);
    }
}
