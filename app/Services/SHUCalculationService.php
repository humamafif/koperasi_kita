<?php

namespace App\Services;

use App\Models\SHUBiaya;
use App\Models\Simpanan;
use App\Models\SHUDistribution;
use App\Models\User;
use App\Models\PembelianProduk;
use App\Models\TagihanAnggota;
use App\Models\Pinjaman;
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

    // Get total admin fees from product purchases
    public function getTotalBiayaAdmin(): float
    {
        return PembelianProduk::where('status', 'selesai')->sum('biaya_admin');
    }

    // Get total loan interest paid
    public function getTotalBungaPinjaman(): float
    {
        $tagihanLunas = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
            ->where('status', 'lunas')
            ->whereNotNull('pinjaman_id')
            ->get();

        $totalBungaTerbayar = 0;

        foreach ($tagihanLunas as $tagihan) {
            $pinjaman = Pinjaman::find($tagihan->pinjaman_id);
            if ($pinjaman && $pinjaman->total_bunga > 0) {
                $totalBunga = $pinjaman->total_bunga;
                $tenorPinjaman = $pinjaman->tenorPinjaman;
                $tenor = $tenorPinjaman ? $tenorPinjaman->durasi : 0;
                if ($tenor > 0) {
                    $bungaPerAngsuran = $totalBunga / $tenor;
                    $totalBungaTerbayar += $bungaPerAngsuran;
                }
            }
        }

        return $totalBungaTerbayar;
    }

    // Get total member savings
    public function getTotalSimpanan(): float
    {
        return Simpanan::where('status', 'disetujui')->sum('jumlah');
    }

    // Calculate total cooperative balance
    public function calculateTotalKoperasiBalance(): float
    {
        $totalBiayaAdmin = $this->getTotalBiayaAdmin();
        $totalBungaPinjaman = $this->getTotalBungaPinjaman();
        $totalSimpanan = $this->getTotalSimpanan();

        return $totalBiayaAdmin + $totalBungaPinjaman + $totalSimpanan;
    }

    // Get admin fees per user
    public function getBiayaAdminPerUser(): array
    {
        $result = PembelianProduk::where('status', 'selesai')
            ->select('penjual_id', DB::raw('SUM(biaya_admin) as total_biaya_admin'))
            ->groupBy('penjual_id')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->penjual_id => $item->total_biaya_admin];
            })
            ->toArray();
        return $result;
    }

    public function getBungaPinjamanPerUser(): array
    {
        $users = User::all();
        $bungaPerUser = [];

        foreach ($users as $user) {
            $tagihanLunas = TagihanAnggota::where('jenis_tagihan', 'angsuran_pinjaman')
                ->where('status', 'lunas')
                ->where('user_id', $user->id)
                ->whereNotNull('pinjaman_id')
                ->get();
            $totalBungaTerbayar = 0;

            foreach ($tagihanLunas as $tagihan) {
                $pinjaman = Pinjaman::find($tagihan->pinjaman_id);
                if ($pinjaman && $pinjaman->total_bunga > 0) {
                    $totalBunga = $pinjaman->total_bunga;
                    $tenorPinjaman = $pinjaman->tenorPinjaman;
                    $tenor = $tenorPinjaman ? $tenorPinjaman->durasi : 0;
                    if ($tenor > 0) {
                        $bungaPerAngsuran = $totalBunga / $tenor;
                        $totalBungaTerbayar += $bungaPerAngsuran;
                    }
                }
            }
            if ($totalBungaTerbayar > 0) {
                $bungaPerUser[$user->id] = $totalBungaTerbayar;
            }
        }
        return $bungaPerUser;
    }

    public function calculateSHU(int $tahun, float $totalSHU, float $persentaseJasaUsaha, float $persentaseJasaModal, float $persentaseJasaPinjaman): array
    {
        try {
            // Ambil data yang diperlukan
            $simpananAnggota = $this->getSimpananAnggotaTersetujui();
            $biayaAdminPerUser = $this->getBiayaAdminPerUser();
            $bungaPinjamanPerUser = $this->getBungaPinjamanPerUser();

            if (count($simpananAnggota) === 0) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada anggota yang memiliki simpanan tersetujui.'
                ];
            }

            // Get totals
            $totalSimpanan = $this->getTotalSimpanan();
            $totalBiayaAdmin = $this->getTotalBiayaAdmin();
            $totalBungaPinjaman = $this->getTotalBungaPinjaman();

            // Tentukan komponen mana yang aktif (nilainya > 0)
            $isJasaUsahaAktif = $totalBiayaAdmin > 0;
            $isJasaModalAktif = $totalSimpanan > 0;
            $isJasaPinjamanAktif = $totalBungaPinjaman > 0;

            // Menghitung persentase yang tidak terpakai
            $persentaseTidakTerpakai = 0;
            if (!$isJasaUsahaAktif) {
                $persentaseTidakTerpakai += $persentaseJasaUsaha;
                Log::info("Jasa usaha tidak aktif, mengalokasikan persentase $persentaseJasaUsaha%");
            }
            if (!$isJasaPinjamanAktif) {
                $persentaseTidakTerpakai += $persentaseJasaPinjaman;
                Log::info("Jasa pinjaman tidak aktif, mengalokasikan persentase $persentaseJasaPinjaman%");
            }
            // Modal selalu ada karena kita memastikan ada simpanan di atas

            // Jika ada persentase yang tidak terpakai, distribusikan secara proporsional
            if ($persentaseTidakTerpakai > 0) {
                // Hitung total persentase komponen yang aktif (selain dana cadangan)
                $totalPersentaseKomponen = 0;
                if ($isJasaUsahaAktif) $totalPersentaseKomponen += $persentaseJasaUsaha;
                if ($isJasaModalAktif) $totalPersentaseKomponen += $persentaseJasaModal;
                if ($isJasaPinjamanAktif) $totalPersentaseKomponen += $persentaseJasaPinjaman;

                // Jika masih ada komponen aktif selain dana cadangan
                if ($totalPersentaseKomponen > 0) {
                    // Distribusikan persentase yang tidak terpakai secara proporsional
                    if ($isJasaUsahaAktif) {
                        $bonusJasaUsaha = ($persentaseJasaUsaha / $totalPersentaseKomponen) * $persentaseTidakTerpakai;
                        $persentaseJasaUsaha += $bonusJasaUsaha;
                        Log::info("Redistribusi ke jasa usaha: +$bonusJasaUsaha% (total: $persentaseJasaUsaha%)");
                    }

                    if ($isJasaModalAktif) {
                        $bonusJasaModal = ($persentaseJasaModal / $totalPersentaseKomponen) * $persentaseTidakTerpakai;
                        $persentaseJasaModal += $bonusJasaModal;
                        Log::info("Redistribusi ke jasa modal: +$bonusJasaModal% (total: $persentaseJasaModal%)");
                    }

                    if ($isJasaPinjamanAktif) {
                        $bonusJasaPinjaman = ($persentaseJasaPinjaman / $totalPersentaseKomponen) * $persentaseTidakTerpakai;
                        $persentaseJasaPinjaman += $bonusJasaPinjaman;
                        Log::info("Redistribusi ke jasa pinjaman: +$bonusJasaPinjaman% (total: $persentaseJasaPinjaman%)");
                    }
                } else {
                    // Jika tidak ada komponen aktif selain dana cadangan, tambahkan ke dana cadangan
                    Log::info("Tidak ada komponen aktif selain dana cadangan, tidak melakukan redistribusi");
                }
            }

            // Hitung jumlah SHU untuk masing-masing komponen dengan persentase baru
            $shuJasaUsaha = $isJasaUsahaAktif ? $totalSHU * ($persentaseJasaUsaha / 100) : 0;
            $shuJasaModal = $isJasaModalAktif ? $totalSHU * ($persentaseJasaModal / 100) : 0;
            $shuJasaPinjaman = $isJasaPinjamanAktif ? $totalSHU * ($persentaseJasaPinjaman / 100) : 0;

            Log::info("Distribusi SHU: Jasa Usaha = Rp" . number_format($shuJasaUsaha, 2) .
                ", Jasa Modal = Rp" . number_format($shuJasaModal, 2) .
                ", Jasa Pinjaman = Rp" . number_format($shuJasaPinjaman, 2));

            if ($totalSimpanan <= 0) {
                return [
                    'success' => false,
                    'message' => 'Total simpanan anggota adalah 0. Tidak bisa membagi SHU.'
                ];
            }

            $distribusi = [];

            // Calculate and create SHU distribution for each member
            foreach ($simpananAnggota as $anggota) {
                $userId = $anggota->user_id;
                $simpananAnggota = (float)$anggota->total_simpanan;
                $biayaAdminAnggota = isset($biayaAdminPerUser[$userId]) ? $biayaAdminPerUser[$userId] : 0;
                $bungaPinjamanAnggota = isset($bungaPinjamanPerUser[$userId]) ? $bungaPinjamanPerUser[$userId] : 0;

                // Calculate JMA (Jasa Modal Anggota) - selalu ada karena ada simpanan
                $jma = 0;
                if ($isJasaModalAktif && $totalSimpanan > 0) {
                    $persentaseKontribusiModal = $simpananAnggota / $totalSimpanan;
                    $jma = $shuJasaModal * $persentaseKontribusiModal;
                }

                // Calculate JUA (Jasa Usaha Anggota)
                $jua = 0;
                if ($isJasaUsahaAktif && $totalBiayaAdmin > 0 && isset($biayaAdminPerUser[$userId])) {
                    $persentaseBiayaAdmin = $biayaAdminAnggota / $totalBiayaAdmin;
                    $jua = $shuJasaUsaha * $persentaseBiayaAdmin;
                }

                // Calculate JPA (Jasa Pinjaman Anggota)
                $jpa = 0;
                if ($isJasaPinjamanAktif && $totalBungaPinjaman > 0 && isset($bungaPinjamanPerUser[$userId])) {
                    $persentaseBungaPinjaman = $bungaPinjamanAnggota / $totalBungaPinjaman;
                    $jpa = $shuJasaPinjaman * $persentaseBungaPinjaman;
                }

                // Calculate total SHU for this member
                $jumlahSHU = $jma + $jua + $jpa;
                $persentaseKontribusi = $totalSHU > 0 ? $jumlahSHU / $totalSHU : 0;

                try {
                    $shuDistribution = SHUDistribution::create([
                        'user_id' => $userId,
                        'tahun' => $tahun,
                        'total_simpanan' => $simpananAnggota,
                        'total_biaya_admin' => $biayaAdminAnggota,
                        'total_bunga_pinjaman' => $bungaPinjamanAnggota,
                        'persentase_kontribusi' => $persentaseKontribusi,
                        'jumlah_shu' => $jumlahSHU,
                        'status' => 'pending',
                        'tanggal_distribusi' => now(),
                        // Tambahkan field untuk menyimpan detail perhitungan
                        'detail_perhitungan' => json_encode([
                            'jasa_modal' => $jma,
                            'jasa_usaha' => $jua,
                            'jasa_pinjaman' => $jpa,
                            'persentase_jasa_modal' => $isJasaModalAktif ? $persentaseJasaModal : 0,
                            'persentase_jasa_usaha' => $isJasaUsahaAktif ? $persentaseJasaUsaha : 0,
                            'persentase_jasa_pinjaman' => $isJasaPinjamanAktif ? $persentaseJasaPinjaman : 0,
                        ])
                    ]);

                    $distribusi[] = $shuDistribution;
                } catch (\Exception $e) {
                    Log::error("Gagal menyimpan SHU untuk user_id: {$userId}: " . $e->getMessage());
                    throw $e;
                }
            }

            return [
                'success' => true,
                'message' => 'SHU berhasil dihitung dan disimpan. Redistribusi persentase diterapkan untuk komponen yang tidak aktif.',
                'distribusi' => $distribusi,
                'redistribusi_info' => [
                    'persentase_jasa_usaha_adjusted' => $persentaseJasaUsaha,
                    'persentase_jasa_modal_adjusted' => $persentaseJasaModal,
                    'persentase_jasa_pinjaman_adjusted' => $persentaseJasaPinjaman,
                    'persentase_tidak_terpakai' => $persentaseTidakTerpakai
                ]
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

    /**
     * Mendapatkan analisis komponen SHU yang aktif
     * @return array
     */
    public function getActiveComponentsAnalysis(): array
    {
        // Get totals
        $totalSimpanan = $this->getTotalSimpanan();
        $totalBiayaAdmin = $this->getTotalBiayaAdmin();
        $totalBungaPinjaman = $this->getTotalBungaPinjaman();

        // Tentukan komponen mana yang aktif (nilainya > 0)
        $isJasaUsahaAktif = $totalBiayaAdmin > 0;
        $isJasaModalAktif = $totalSimpanan > 0;
        $isJasaPinjamanAktif = $totalBungaPinjaman > 0;

        return [
            'jasa_usaha' => [
                'aktif' => $isJasaUsahaAktif,
                'total' => $totalBiayaAdmin,
                'keterangan' => $isJasaUsahaAktif ? 'Aktif' : 'Tidak Aktif (Tidak ada biaya admin)',
            ],
            'jasa_modal' => [
                'aktif' => $isJasaModalAktif,
                'total' => $totalSimpanan,
                'keterangan' => $isJasaModalAktif ? 'Aktif' : 'Tidak Aktif (Tidak ada simpanan)',
            ],
            'jasa_pinjaman' => [
                'aktif' => $isJasaPinjamanAktif,
                'total' => $totalBungaPinjaman,
                'keterangan' => $isJasaPinjamanAktif ? 'Aktif' : 'Tidak Aktif (Tidak ada bunga pinjaman)',
            ],
        ];
    }

    // Keep existing methods
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

    // Keep existing methods for cancelSHUCalculation, distributeSHU, getDistributionYears, getSaldoKoperasi
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
}
