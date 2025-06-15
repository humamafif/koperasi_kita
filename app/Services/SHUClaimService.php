<?php

namespace App\Services;

use App\Models\SaldoAnggota;
use App\Models\SHUDistribution;
use App\Models\SHUPengambilan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SHUClaimService
{
    public function claimSHU(SHUDistribution $shuDistribution)
    {
        try {
            DB::beginTransaction();

            // Cek apakah sudah diambil
            if ($shuDistribution->is_claimed) {
                return [
                    'success' => false,
                    'message' => 'SHU ini sudah pernah diambil sebelumnya.'
                ];
            }

            // Cek apakah status SHU sudah dibagikan
            if ($shuDistribution->status !== 'dibagikan') {
                return [
                    'success' => false,
                    'message' => 'Hanya SHU dengan status "dibagikan" yang dapat diambil.'
                ];
            }

            $userId = $shuDistribution->user_id;
            $jumlahSHU = $shuDistribution->jumlah_shu;

            // Update status SHU menjadi sudah diambil
            $shuDistribution->update([
                'is_claimed' => true,
                'claimed_at' => now(),
            ]);

            // Buat record pengambilan SHU
            SHUPengambilan::create([
                'user_id' => $userId,
                'shu_distribution_id' => $shuDistribution->id,
                'jumlah' => $jumlahSHU,
                'tanggal_pengambilan' => now(),
                'keterangan' => 'Pengambilan SHU tahun ' . $shuDistribution->tahun,
            ]);

            // Update atau buat saldo anggota
            $saldo = SaldoAnggota::firstOrCreate(
                ['user_id' => $userId],
                ['saldo' => 0]
            );

            $saldo->saldo += $jumlahSHU;
            $saldo->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Dana SHU berhasil diambil dan ditambahkan ke saldo Anda.',
                'amount' => $jumlahSHU,
                'new_balance' => $saldo->saldo
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error saat mengambil SHU: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ];
        }
    }
}
