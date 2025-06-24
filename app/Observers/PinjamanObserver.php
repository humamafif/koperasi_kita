<?php

namespace App\Observers;

use App\Models\Pinjaman;
use App\Models\RiwayatTransaksi;
use App\Models\TagihanAnggota;
use App\Notifications\PinjamanStatusUpdated;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PinjamanObserver
{

    public function created(Pinjaman $pinjaman): void
    {
        RiwayatTransaksi::create([
            'user_id' => $pinjaman->user_id,
            'jenis_transaksi' => 'pinjaman',
            'jumlah' => $pinjaman->jumlah,
            'tanggal' => now(),
            'status' => 'pending',
            'keterangan' => 'Pengajuan pinjaman baru sebesar Rp ' . number_format($pinjaman->jumlah, 0, ',', '.'),
            'referensi_id' => $pinjaman->id,
            'referensi_tipe' => 'App\\Models\\Pinjaman',
        ]);
    }

    // public function updated(Pinjaman $pinjaman): void
    // {

    //     if ($pinjaman->wasChanged('status')) {
    //         $newStatus = $pinjaman->status;
    //         if ($newStatus === 'disetujui' || $newStatus === 'ditolak') {
    //             $pinjaman->user->notify(new PinjamanStatusUpdated($pinjaman));
    //         }
    //         if ($newStatus === 'disetujui') {
    //             $this->calculateAndSaveLoanFinancials($pinjaman);
    //             $this->createLoanInstallments($pinjaman);
    //         }
    //     }
    //     if ($pinjaman->wasChanged('status')) {
    //         RiwayatTransaksi::where('user_id', $pinjaman->user_id)
    //             ->where('jenis_transaksi', 'pinjaman')
    //             ->where('jumlah', $pinjaman->jumlah)
    //             ->where('status', 'pending')
    //             ->where('referensi_id', $pinjaman->id)
    //             ->where('referensi_tipe', 'App\\Models\\Pinjaman')
    //             ->update([
    //                 'status' => $pinjaman->status,
    //                 'keterangan' => match ($pinjaman->status) {
    //                     'disetujui' => 'Pinjaman disetujui sebesar Rp ' . number_format($pinjaman->jumlah, 0, ',', '.'),
    //                     'ditolak' => 'Pinjaman ditolak dengan alasan: ' . ($pinjaman->alasan_penolakan ?? 'Tidak ada alasan'),
    //                     default => 'Pengajuan pinjaman baru sebesar Rp ' . number_format($pinjaman->jumlah, 0, ',', '.'),
    //                 },
    //             ]);

    //         if ($pinjaman->status === 'disetujui') {
    //             $this->createLoanInstallments($pinjaman);
    //         }
    //         $pinjaman->user->notify(new PinjamanStatusUpdated($pinjaman));
    //     }
    // }
    // protected function calculateAndSaveLoanFinancials(Pinjaman $pinjaman): void
    // {
    //     if (!$pinjaman->tenorPinjaman) {
    //         Log::warning("Tenor not found for loan #{$pinjaman->id}");
    //         return;
    //     }
    //     $tenor = $pinjaman->tenorPinjaman->durasi;
    //     $bunga = $pinjaman->tenorPinjaman->bunga;
    //     $jumlah = $pinjaman->jumlah;
    //     $totalBunga = $jumlah * $bunga / 100 * ($tenor / 12);
    //     $pokok = $jumlah / $tenor;
    //     $bungaPerBulan = ($jumlah * $bunga / 100) / 12;
    //     $angsuran = $pokok + $bungaPerBulan;
    //     Log::info("Loan #{$pinjaman->id}: amount={$jumlah}, tenor={$tenor}, bunga={$bunga}%, total_bunga={$totalBunga}, angsuran={$angsuran}");

    //     $pinjaman->update([
    //         'total_bunga' => $totalBunga,
    //         'angsuran_per_bulan' => $angsuran
    //     ]);
    // }
    // protected function createLoanInstallments(Pinjaman $pinjaman): void
    // {
    //     $tenor = $pinjaman->tenorPinjaman->durasi;
    //     $startDate = $pinjaman->tanggal_persetujuan;
    //     for ($i = 1; $i <= $tenor; $i++) {
    //         $dueDate = Carbon::parse($startDate)->addMonths($i)->setDay(10);
    //         $periode = $dueDate->format('Y-m');
    //         Log::info("Creating installment for loan #{$pinjaman->id}, period: {$periode}, due: {$dueDate->format('Y-m-d')}");

    //         TagihanAnggota::create([
    //             'user_id' => $pinjaman->user_id,
    //             'jenis_tagihan' => 'angsuran_pinjaman',
    //             'jumlah' => $pinjaman->angsuran_per_bulan,
    //             'tanggal_jatuh_tempo' => $dueDate,
    //             'status' => 'belum_bayar',
    //             'pinjaman_id' => $pinjaman->id,
    //             'periode' => $periode,
    //             'keterangan' => "Angsuran pinjaman ke-{$i} dari {$tenor} bulan",
    //         ]);
    //     }
    // }
}
