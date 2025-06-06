<?php

namespace App\Observers;

use App\Models\Pinjaman;
use App\Models\RiwayatTransaksi;
use App\Notifications\PinjamanStatusUpdated;

class PinjamanObserver
{
    /**
     * Handle the Pinjaman "created" event.
     */
    public function created(Pinjaman $pinjaman): void
    {
        RiwayatTransaksi::create([
            'user_id' => $pinjaman->user_id,
            'jenis_transaksi' => 'pinjaman',
            'referensi_id' => $pinjaman->id,
            'referensi_tipe' => 'App\\Models\\Pinjaman',
            'jumlah' => $pinjaman->jumlah,
            'tanggal' => $pinjaman->tanggal_pengajuan,
            'status' => $pinjaman->status,
            'keterangan' => $pinjaman->tujuan ?? 'Pengajuan pinjaman',
        ]);
    }

    /**
     * Handle the Pinjaman "updated" event.
     */
    public function updated(Pinjaman $pinjaman): void
    {
        // Kirim notifikasi jika status pinjaman berubah menjadi disetujui atau ditolak
        if ($pinjaman->wasChanged('status')) {
            $newStatus = $pinjaman->status;

            if ($newStatus === 'disetujui' || $newStatus === 'ditolak') {
                $pinjaman->user->notify(new PinjamanStatusUpdated($pinjaman));
            }
        }
        if ($pinjaman->isDirty('status') || $pinjaman->isDirty('jumlah')) {
            RiwayatTransaksi::where('referensi_id', $pinjaman->id)
                ->where('referensi_tipe', 'App\\Models\\Pinjaman')
                ->update([
                    'status' => $pinjaman->status,
                    'jumlah' => $pinjaman->jumlah,
                    'keterangan' => $pinjaman->tujuan ?? 'Pengajuan pinjaman',
                ]);
        }
    }

    /**
     * Handle the Pinjaman "deleted" event.
     */
    public function deleted(Pinjaman $pinjaman): void
    {
        //
    }

    /**
     * Handle the Pinjaman "restored" event.
     */
    public function restored(Pinjaman $pinjaman): void
    {
        //
    }

    /**
     * Handle the Pinjaman "force deleted" event.
     */
    public function forceDeleted(Pinjaman $pinjaman): void
    {
        //
    }
}
